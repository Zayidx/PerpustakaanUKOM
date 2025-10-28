<?php

namespace App\Livewire\Siswa;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    /** @var array<int> */
    public array $selectedBooks = [];

    public ?int $detailBookId = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->selectedBooks = array_values(array_filter( // Muat daftar buku yang dipilih dari session
            session()->get('loan_cart', []), // Ambil dari session 'loan_cart'
            fn ($id) => is_numeric($id) // Hanya ambil ID numerik
        )); // Reset indeks array
    } // Muat daftar buku yang dipilih dari session saat komponen dimuat

    public function updatingSearch(): void
    {
        $this->resetPage(); // Reset pagination ke halaman pertama saat pencarian berubah
    } // Reset pagination saat input pencarian berubah

    public function toggleSelection(int $bookId): void
    {
        $book = Buku::query()->select(['id', 'stok', 'nama_buku'])->find($bookId); // Ambil info buku

        if (! $book) { // Cek apakah buku ditemukan
            $this->addError('selection', 'Buku tidak ditemukan.');
            return;
        }

        if ($book->stok < 1) { // Cek apakah stok buku habis
            $this->addError('selection', "Stok buku {$book->nama_buku} habis.");
            $this->selectedBooks = array_values(array_diff($this->selectedBooks, [$bookId])); // Hapus dari pilihan
            session()->put('loan_cart', $this->selectedBooks); // Simpan ke session
            return;
        }

        if (in_array($bookId, $this->selectedBooks, true)) { // Jika sudah dipilih, hapus
            $this->selectedBooks = array_values(array_diff($this->selectedBooks, [$bookId])); // Hapus dari array
        } else { // Jika belum dipilih, tambahkan
            $this->selectedBooks[] = $bookId; // Tambahkan ke array
        }

        session()->put('loan_cart', $this->selectedBooks); // Simpan ke session
    } // Toggle pemilihan buku ke dalam keranjang peminjaman

    public function showDetail(int $bookId): void
    {
        $this->detailBookId = $bookId; // Set ID buku yang akan ditampilkan detailnya
        $this->dispatch('show-detail-modal'); // Kirim event untuk menampilkan modal detail
    } // Tampilkan detail buku dalam modal

    public function clearDetail(): void
    {
        $this->detailBookId = null; // Reset ID buku detail
        $this->dispatch('hide-detail-modal'); // Kirim event untuk menyembunyikan modal detail
    } // Sembunyikan modal detail buku

    #[On('detail-modal-hidden')]
    public function handleDetailModalHidden(): void
    {
        $this->detailBookId = null; // Reset ID buku detail saat modal ditutup
    } // Tangani event saat modal detail disembunyikan

    public function removeFromSelection(int $bookId): void
    {
        $this->selectedBooks = array_values(array_diff($this->selectedBooks, [$bookId])); // Hapus ID buku dari array
        session()->put('loan_cart', $this->selectedBooks); // Simpan ke session
    } // Hapus buku dari daftar pilihan

    public function clearSelection(): void
    {
        $this->selectedBooks = []; // Kosongkan array pilihan buku
        session()->forget('loan_cart'); // Hapus data dari session
        $this->dispatch('hide-loan-modal'); // Kirim event untuk menyembunyikan modal peminjaman
    } // Kosongkan semua pilihan buku dan sembunyikan modal

    public function generateLoanCode()
    {
        $user = Auth::user(); // Ambil user yang sedang login

        if (! $user?->siswa) { // Cek apakah user memiliki data siswa
            $this->addError('selection', 'Akun tidak memiliki data siswa.');
            return null;
        }

        if (empty($this->selectedBooks)) { // Cek apakah ada buku yang dipilih
            $this->addError('selection', 'Pilih minimal satu buku sebelum membuat kode peminjaman.');
            return null;
        }

        $bookIds = array_values(array_unique(array_map('intval', $this->selectedBooks))); // Validasi ID buku

        try {
            $loan = DB::transaction(function () use ($bookIds, $user) { // Jalankan dalam transaksi database
                $books = Buku::query() // Ambil data buku dengan lock untuk mencegah race condition
                    ->whereIn('id', $bookIds) // Filter berdasarkan ID yang dipilih
                    ->lockForUpdate() // Kunci baris untuk mencegah perubahan bersamaan
                    ->get(); // Ambil semua buku

                if ($books->count() !== count($bookIds)) { // Cek apakah semua buku ditemukan
                    throw ValidationException::withMessages([
                        'selection' => 'Beberapa buku tidak ditemukan. Muat ulang halaman dan coba lagi.',
                    ]);
                }

                $outOfStock = $books->filter(fn ($book) => $book->stok < 1); // Cari buku yang stoknya habis
                if ($outOfStock->isNotEmpty()) { // Jika ada buku yang stoknya habis
                    throw ValidationException::withMessages([
                        'selection' => 'Stok buku berikut habis: '.$outOfStock->pluck('nama_buku')->join(', '),
                    ]);
                }

                $loan = Peminjaman::create([ // Buat record peminjaman baru
                    'kode' => $this->generateUniqueCode(), // Generate kode unik
                    'siswa_id' => $user->siswa->id, // ID siswa peminjam
                    'status' => 'pending', // Status awal pending
                    'metadata' => [ // Metadata tambahan
                        'book_ids' => $books->pluck('id')->all(), // ID buku yang dipinjam
                        'generated_by' => $user->id, // ID user yang membuat
                    ],
                ]);

                foreach ($books as $book) { // Untuk setiap buku yang dipinjam
                    PeminjamanItem::create([ // Buat record item peminjaman
                        'peminjaman_id' => $loan->id, // ID peminjaman
                        'buku_id' => $book->id, // ID buku
                        'quantity' => 1, // Jumlah yang dipinjam
                    ]);

                    $book->decrement('stok'); // Kurangi stok buku
                }

                return $loan; // Kembalikan data peminjaman
            });
        } catch (ValidationException $exception) { // Tangani error validasi
            $this->setErrorBag($exception->validator->errors()); // Set error ke bag komponen
            return null;
        }

        session()->forget('loan_cart'); // Hapus data keranjang dari session
        $this->selectedBooks = []; // Kosongkan array pilihan
        $this->dispatch('hide-loan-modal'); // Sembunyikan modal peminjaman

        return $this->redirectRoute('siswa.kode-peminjaman', ['kode' => $loan->kode], navigate: true); // Redirect ke halaman kode peminjaman
    } // Generate kode peminjaman untuk buku yang dipilih

    public function render()
    {
        $books = Buku::query() // Query utama untuk daftar buku
            ->with(['author', 'kategori', 'penerbit']) // Muat relasi untuk tampilan
            ->when($this->search !== '', function ($query) { // Jika ada pencarian
                $query->where(function ($inner) { // Cari berdasarkan berbagai field
                    $inner->where('nama_buku', 'like', '%'.$this->search.'%') // Cari di nama buku
                        ->orWhereHas('author', function ($author) { // Cari di nama author
                            $author->where('nama_author', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('kategori', function ($kategori) { // Cari di nama kategori
                            $kategori->where('nama_kategori_buku', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('penerbit', function ($penerbit) { // Cari di nama penerbit
                            $penerbit->where('nama_penerbit', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->orderBy('nama_buku') // Urutkan berdasarkan nama buku
            ->paginate(12); // Pagination 12 buku per halaman

        $books->setCollection( // Tambahkan URL cover ke setiap buku
            $books->getCollection()->map(function (Buku $book) { // Untuk setiap buku dalam koleksi
                return $book->append(['cover_depan_url', 'cover_belakang_url']); // Tambahkan URL cover
            })
        );

        $selectedBooks = Buku::query() // Ambil buku yang dipilih
            ->with(['author', 'kategori']) // Muat relasi yang diperlukan
            ->whereIn('id', $this->selectedBooks) // Filter berdasarkan ID yang dipilih
            ->get() // Ambil data
            ->sortBy(fn ($book) => array_search($book->id, $this->selectedBooks, true) ?? PHP_INT_MAX); // Urutkan sesuai dengan urutan pemilihan

        $selectedBooks = $selectedBooks->map(function (Buku $book) { // Tambahkan URL cover ke buku yang dipilih
            return $book->append(['cover_depan_url', 'cover_belakang_url']); // Tambahkan URL cover
        })->values(); // Reset indeks array

        $missingSelection = array_diff($this->selectedBooks, $selectedBooks->pluck('id')->all()); // Cek apakah ada buku yang dipilih tapi tidak ditemukan
        if (! empty($missingSelection)) { // Jika ada buku yang hilang dari seleksi
            $this->selectedBooks = array_values(array_diff($this->selectedBooks, $missingSelection)); // Hapus dari array seleksi
            session()->put('loan_cart', $this->selectedBooks); // Simpan ke session
        }

        $detailBook = null; // Inisialisasi buku detail

        if ($this->detailBookId) { // Jika ada ID buku detail
            $detailBook = Buku::with(['author', 'kategori', 'penerbit'])->find($this->detailBookId); // Ambil data buku detail

            if (! $detailBook) { // Jika buku detail tidak ditemukan
                $this->clearDetail(); // Bersihkan detail
            } else {
                $detailBook->append(['cover_depan_url', 'cover_belakang_url']); // Tambahkan URL cover
            }
        }

        return view('livewire.siswa.list-buku', [ // Render view dengan data
            'books' => $books, // Daftar buku
            'detailBook' => $detailBook, // Buku yang ditampilkan detailnya
            'selectedBooksInfo' => $selectedBooks, // Informasi buku yang dipilih
        ]);
    } // Render tampilan komponen dengan daftar buku

    private function generateUniqueCode(): string
    {
        do {
            $code = 'PINJ-'.Str::upper(Str::random(8)); // Generate kode dalam format PINJ-XXXXXX
        } while (Peminjaman::where('kode', $code)->exists()); // Ulangi hingga menemukan kode yang unik

        return $code; // Kembalikan kode unik
    } // Generate kode peminjaman unik
}
