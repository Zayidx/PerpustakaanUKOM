<?php

namespace App\Livewire\Siswa;

use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\Peminjaman;
use App\Models\PeminjamanItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class ListBuku extends Component
{
    use WithPagination;

    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Daftar Buku')]
    public string $search = '';
    public ?int $categoryFilter = null;

    public array $selectedBooks = [];
    public ?int $detailBookId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => null],
    ];

    public function mount(): void
    {
        $this->selectedBooks = array_values(array_filter( 
            session()->get('loan_cart', []), 
            fn ($id) => is_numeric($id) 
        )); 
    } 

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function toggleSelection(int $bookId): void
    {
        $book = Buku::query()->select(['id', 'stok', 'nama_buku'])->find($bookId); 

        if (! $book) { 
            $this->addError('selection', 'Buku tidak ditemukan.');
            return;
        }

        if ($book->stok < 1) { 
            $this->addError('selection', "Stok buku {$book->nama_buku} habis.");
            $this->selectedBooks = array_values(array_diff($this->selectedBooks, [$bookId])); 
            session()->put('loan_cart', $this->selectedBooks); 
            return;
        }

        if ($this->bookHasActiveLoan($bookId)) {
            $this->addError('selection', "Buku {$book->nama_buku} sedang dalam proses peminjaman.");
            $this->selectedBooks = array_values(array_diff($this->selectedBooks, [$bookId]));
            session()->put('loan_cart', $this->selectedBooks);
            return;
        }

        if (in_array($bookId, $this->selectedBooks, true)) { 
            $this->selectedBooks = array_values(array_diff($this->selectedBooks, [$bookId])); 
        } else { 
            $this->selectedBooks[] = $bookId; 
        }

        session()->put('loan_cart', $this->selectedBooks); 
    } 

    public function showDetail(int $bookId): void
    {
        $this->detailBookId = $bookId; 
        $this->dispatch('show-detail-modal'); 
    } 

    public function clearDetail(): void
    {
        $this->detailBookId = null; 
        $this->dispatch('hide-detail-modal'); 
    } 

    #[On('detail-modal-hidden')]
    public function handleDetailModalHidden(): void
    {
        $this->detailBookId = null; 
    } 

    public function removeFromSelection(int $bookId): void
    {
        $this->selectedBooks = array_values(array_diff($this->selectedBooks, [$bookId])); 
        session()->put('loan_cart', $this->selectedBooks); 
    } 

    public function clearSelection(): void
    {
        $this->selectedBooks = []; 
        session()->forget('loan_cart'); 
        $this->dispatch('hide-loan-modal'); 
    } 

    public function generateLoanCode()
    {
        $user = Auth::user(); 

        if (! $user?->siswa) { 
            $this->addError('selection', 'Akun tidak memiliki data siswa.');
            return null;
        }

        if (empty($this->selectedBooks)) { 
            $this->addError('selection', 'Pilih minimal satu buku sebelum membuat kode peminjaman.');
            return null;
        }

        $bookIds = array_values(array_unique(array_map('intval', $this->selectedBooks))); 

        try {
            $loan = DB::transaction(function () use ($bookIds, $user) { 
                $books = Buku::query() 
                    ->whereIn('id', $bookIds) 
                    ->lockForUpdate() 
                    ->get(); 

                if ($books->count() !== count($bookIds)) { 
                    throw ValidationException::withMessages([
                        'selection' => 'Beberapa buku tidak ditemukan. Muat ulang halaman dan coba lagi.',
                    ]);
                }

                $outOfStock = $books->filter(fn ($book) => $book->stok < 1); 
                if ($outOfStock->isNotEmpty()) { 
                    throw ValidationException::withMessages([
                        'selection' => 'Stok buku berikut habis: '.$outOfStock->pluck('nama_buku')->join(', '),
                    ]);
                }

                $loan = Peminjaman::create([ 
                    'kode' => $this->generateUniqueCode(), 
                    'siswa_id' => $user->siswa->id, 
                    'status' => 'pending', 
                    'metadata' => [ 
                        'book_ids' => $books->pluck('id')->all(), 
                        'generated_by' => $user->id, 
                    ],
                ]);

                foreach ($books as $book) { 
                    PeminjamanItem::create([ 
                        'peminjaman_id' => $loan->id, 
                        'buku_id' => $book->id, 
                        'quantity' => 1, 
                    ]);
                }

                return $loan; 
            });
        } catch (ValidationException $exception) { 
            $this->resetErrorBag(); 

            foreach ($exception->errors() as $field => $messages) { 
                foreach ((array) $messages as $message) {
                    $this->addError($field, $message);
                }
            }

            return null;
        }

        session()->forget('loan_cart'); 
        $this->selectedBooks = []; 
        $this->dispatch('hide-loan-modal'); 

        return $this->redirectRoute('siswa.kode-peminjaman', ['kode' => $loan->kode], navigate: true); 
    } 

    public function render()
    {
        $books = $this->getPaginatedBooks();
        $selectedBooks = $this->getSelectedBooksInfo();
        $detailBook = $this->getDetailBook();
        $activeLoanBookIds = $this->getActiveLoanBookIds();

        return view('livewire.siswa.list-buku', [
            'books' => $books,
            'detailBook' => $detailBook,
            'selectedBooksInfo' => $selectedBooks,
            'activeLoanBookIds' => $activeLoanBookIds,
            'categoryOptions' => $this->getCategoryOptions(),
        ]);
    } 

    private function generateUniqueCode(): string
    {
        do {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT); 
        } while (Peminjaman::where('kode', $code)->exists()); 

        return $code; 
    }

    private function getPaginatedBooks(): LengthAwarePaginator
    {
        $books = Buku::query()
            ->with(['author', 'kategori', 'penerbit'])
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('nama_buku', 'like', $term)
                        ->orWhereHas('author', fn ($author) => $author->where('nama_author', 'like', $term))
                        ->orWhereHas('penerbit', fn ($penerbit) => $penerbit->where('nama_penerbit', 'like', $term));
                });
            })
            ->when($this->categoryFilter, fn ($query) => $query->where('kategori_id', (int) $this->categoryFilter))
            ->orderBy('nama_buku')
            ->paginate(12);

        $books->setCollection($books->getCollection()->map(fn (Buku $book) => $this->transformBookCover($book)));

        return $books;
    }

    private function getSelectedBooksInfo(): Collection
    {
        $selected = Buku::query()
            ->with(['author', 'kategori'])
            ->whereIn('id', $this->selectedBooks)
            ->get()
            ->sortBy(fn ($book) => array_search($book->id, $this->selectedBooks, true) ?? PHP_INT_MAX)
            ->map(fn (Buku $book) => $this->transformBookCover($book))
            ->values();

        $this->syncSelectedIds($selected);

        return $selected;
    }

    private function getDetailBook(): ?Buku
    {
        if (! $this->detailBookId) {
            return null;
        }

        $detailBook = Buku::with(['author', 'kategori', 'penerbit'])->find($this->detailBookId);

        if (! $detailBook) {
            $this->clearDetail();
            return null;
        }

        return $this->transformBookCover($detailBook);
    }

    private function syncSelectedIds(Collection $selectedBooks): void
    {
        $missingSelection = array_diff($this->selectedBooks, $selectedBooks->pluck('id')->all());

        if (empty($missingSelection)) {
            return;
        }

        $this->selectedBooks = array_values(array_diff($this->selectedBooks, $missingSelection));
        session()->put('loan_cart', $this->selectedBooks);
    }

    private function transformBookCover(Buku $book): Buku
    {
        $book->setAttribute('cover_depan_url', $this->resolveCoverUrl($book->cover_depan));
        $book->setAttribute('cover_belakang_url', $this->resolveCoverUrl($book->cover_belakang));

        return $book;
    }

    private function resolveCoverUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalized = ltrim($path, '/');

        if (Str::startsWith($normalized, ['http://', 'https://'])) {
            return $normalized;
        }

        if (Str::startsWith($normalized, 'assets/')) {
            return asset($normalized);
        }

        if (Str::startsWith($normalized, 'storage/')) {
            return asset($normalized);
        }

        $publicPath = public_path($normalized);
        if (is_file($publicPath)) {
            return asset($normalized);
        }

        $storagePath = storage_path('app/public/'.$normalized);
        if (is_file($storagePath)) {
            return asset('storage/'.$normalized);
        }

        if (Storage::disk('public')->exists($normalized)) {
            return Storage::url($normalized);
        }

        return null;
    }

    private function getActiveLoanBookIds(): array
    {
        $user = Auth::user();
        $siswaId = $user?->siswa?->id;

        if (! $siswaId) {
            return [];
        }

        return PeminjamanItem::query()
            ->select('buku_id')
            ->whereHas('peminjaman', fn ($query) => $query
                ->where('siswa_id', $siswaId)
                ->whereIn('status', ['pending', 'accepted']))
            ->pluck('buku_id')
            ->unique()
            ->values()
            ->all();
    }

    private function bookHasActiveLoan(int $bookId): bool
    {
        $user = Auth::user();
        $siswaId = $user?->siswa?->id;

        if (! $siswaId) {
            return false;
        }

        return PeminjamanItem::query()
            ->where('buku_id', $bookId)
            ->whereHas('peminjaman', fn ($query) => $query
                ->where('siswa_id', $siswaId)
                ->whereIn('status', ['pending', 'accepted']))
            ->exists();
    }

    private function getCategoryOptions()
    {
        return KategoriBuku::query()
            ->orderBy('nama_kategori_buku')
            ->get(['id', 'nama_kategori_buku']);
    }
}
