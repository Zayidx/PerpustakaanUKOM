<?php

namespace App\Livewire\Siswa;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    
    public array $selectedBooks = [];

    public ?int $detailBookId = null;

    protected $queryString = [
        'search' => ['except' => ''],
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
        $books = Buku::query()
            ->with(['author', 'kategori', 'penerbit']) 
            ->when($this->search !== '', function ($query) { 
                $query->where(function ($inner) { 
                    $inner->where('nama_buku', 'like', '%'.$this->search.'%') 
                        ->orWhereHas('author', function ($author) { 
                            $author->where('nama_author', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('kategori', function ($kategori) { 
                            $kategori->where('nama_kategori_buku', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('penerbit', function ($penerbit) { 
                            $penerbit->where('nama_penerbit', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->orderBy('nama_buku') 
            ->paginate(12); 

        $books->setCollection(
            $books->getCollection()->map(function (Buku $book) {
                $book->append(['cover_depan_url', 'cover_belakang_url']);

                return $book;
            })
        );

        $selectedBooks = Buku::query()
            ->with(['author', 'kategori']) 
            ->whereIn('id', $this->selectedBooks) 
            ->get() 
            ->sortBy(fn ($book) => array_search($book->id, $this->selectedBooks, true) ?? PHP_INT_MAX); 

        $selectedBooks = $selectedBooks->map(function (Buku $book) { 
            $book->append(['cover_depan_url', 'cover_belakang_url']);

            return $book;
        })->values(); 

        $missingSelection = array_diff($this->selectedBooks, $selectedBooks->pluck('id')->all()); 
        if (! empty($missingSelection)) { 
            $this->selectedBooks = array_values(array_diff($this->selectedBooks, $missingSelection)); 
            session()->put('loan_cart', $this->selectedBooks); 
        }

        $detailBook = null; 

        if ($this->detailBookId) { 
            $detailBook = Buku::with(['author', 'kategori', 'penerbit'])->find($this->detailBookId); 

            if (! $detailBook) { 
                $this->clearDetail(); 
            } else {
                $detailBook->append(['cover_depan_url', 'cover_belakang_url']);
            }
        }

        return view('livewire.siswa.list-buku', [ 
            'books' => $books, 
            'detailBook' => $detailBook, 
            'selectedBooksInfo' => $selectedBooks, 
        ]);
    } 

    private function generateUniqueCode(): string
    {
        do {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT); 
        } while (Peminjaman::where('kode', $code)->exists()); 

        return $code; 
    }
}
