<?php

namespace App\Livewire\Admin;

use App\Models\Author;
use App\Models\Buku as BukuModel;
use App\Models\KategoriBuku;
use App\Models\Penerbit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ManajemenBuku extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Manajemen Buku')]
    #[Url(except: "")]
    #[Layout('components.layouts.dashboard-layouts')]
    public $perPage = 5;
    public string $search = '';
    public string $sort = 'created_at_desc';
    public array $sortOptions = [
        'created_at_desc' => 'Terbaru',
        'created_at_asc' => 'Terlama',
        'nama_buku_asc' => 'Judul A-Z',
        'nama_buku_desc' => 'Judul Z-A',
    ];

    public $bukuId;
    public $nama_buku = '';
    public $author_id;
    public $kategori_id;
    public $penerbit_id;
    public $deskripsi = '';
    public $tanggal_terbit;
    public $cover_depan;
    public $cover_belakang;
    public $existingCoverDepan = '';
    public $existingCoverBelakang = '';
    public $existingCoverDepanUrl = '';
    public $existingCoverBelakangUrl = '';
    public $editMode = false;
    public $stok = 0;

    protected $messages = [
        'nama_buku.required' => 'Nama buku wajib diisi.',
        'nama_buku.unique' => 'Nama buku sudah digunakan.',
        'author_id.required' => 'Penulis wajib dipilih.',
        'kategori_id.required' => 'Kategori wajib dipilih.',
        'penerbit_id.required' => 'Penerbit wajib dipilih.',
        'deskripsi.required' => 'Deskripsi wajib diisi.',
        'tanggal_terbit.required' => 'Tanggal terbit wajib diisi.',
        'tanggal_terbit.date' => 'Tanggal terbit harus berupa format tanggal yang valid.',
        'stok.required' => 'Stok buku wajib diisi.',
        'stok.integer' => 'Stok buku harus berupa angka.',
        'stok.min' => 'Stok buku tidak boleh kurang dari 0.',
        'cover_depan.image' => 'Cover depan harus berupa file gambar.',
        'cover_depan.max' => 'Ukuran cover depan maksimal 2MB.',
        'cover_belakang.image' => 'Cover belakang harus berupa file gambar.',
        'cover_belakang.max' => 'Ukuran cover belakang maksimal 2MB.',
    ];

    protected function rules(): array
    {
        return [
            'nama_buku' => [
                'required', // Nama buku wajib diisi
                'string', // Harus berupa teks
                'max:255', // Maksimal 255 karakter
                Rule::unique('buku', 'nama_buku')->ignore($this->bukuId), // Harus unik, abaikan saat edit
            ],
            'author_id' => ['required', 'exists:authors,id'], // ID author wajib dan harus ada di tabel authors
            'kategori_id' => ['required', 'exists:kategori_buku,id'], // ID kategori wajib dan harus ada di tabel kategori_buku
            'penerbit_id' => ['required', 'exists:penerbit,id'], // ID penerbit wajib dan harus ada di tabel penerbit
            'deskripsi' => ['required', 'string'], // Deskripsi wajib diisi
            'tanggal_terbit' => ['required', 'date'], // Tanggal terbit wajib dan harus format tanggal valid
            'stok' => ['required', 'integer', 'min:0'], // Stok wajib, harus integer, minimal 0
            'cover_depan' => ['nullable', 'image', 'max:2048'], // Cover depan opsional, harus gambar maks 2MB
            'cover_belakang' => ['nullable', 'image', 'max:2048'], // Cover belakang opsional, harus gambar maks 2MB
        ];
    } // Aturan validasi untuk form buku

    public function mount(): void
    {
        $this->sort = $this->normalizeSort($this->sort);
        $this->search = trim((string) $this->search);
    }

    public function updatedPerPage(): void
    {
        $this->resetPage(); // Reset pagination ke halaman pertama saat jumlah item per halaman berubah
    } // Reset pagination saat jumlah item per halaman berubah

    public function updatedSearch(): void
    {
        $this->search = trim((string) $this->search);
        $this->resetPage();
    }

    public function updatedSort($value): void
    {
        $this->sort = $this->normalizeSort($value);
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm(); // Reset form ke kondisi awal
        $this->editMode = false; // Nonaktifkan mode edit
        $this->resetValidation(); // Hapus pesan validasi sebelumnya
    } // Reset form untuk membuat buku baru

    public function store(): void
    {
        $this->validate(); // Jalankan validasi pada input

        Storage::disk('public')->makeDirectory('admin/cover-buku'); // Buat direktori cover buku jika belum ada

        $coverDepanPath = $this->existingCoverDepan; // Gunakan cover depan lama sebagai default
        if ($this->cover_depan instanceof TemporaryUploadedFile) { // Jika ada upload baru untuk cover depan
            if ($coverDepanPath && $this->shouldDeleteFromStorage($coverDepanPath)) { // Hapus cover lama jika perlu
                Storage::disk('public')->delete($coverDepanPath);
            }
            $coverDepanPath = $this->cover_depan->store('admin/cover-buku', 'public'); // Simpan cover depan baru
        }

        $coverBelakangPath = $this->existingCoverBelakang; // Gunakan cover belakang lama sebagai default
        if ($this->cover_belakang instanceof TemporaryUploadedFile) { // Jika ada upload baru untuk cover belakang
            if ($coverBelakangPath && $this->shouldDeleteFromStorage($coverBelakangPath)) { // Hapus cover belakang lama jika perlu
                Storage::disk('public')->delete($coverBelakangPath);
            }
            $coverBelakangPath = $this->cover_belakang->store('admin/cover-buku', 'public'); // Simpan cover belakang baru
        }

        $payload = [
            'nama_buku' => trim($this->nama_buku), // Normalisasi nama buku
            'author_id' => $this->author_id, // ID author
            'kategori_id' => $this->kategori_id, // ID kategori
            'penerbit_id' => $this->penerbit_id, // ID penerbit
            'deskripsi' => trim($this->deskripsi), // Normalisasi deskripsi
            'tanggal_terbit' => $this->tanggal_terbit, // Tanggal terbit
            'cover_depan' => $coverDepanPath, // Path cover depan
            'cover_belakang' => $coverBelakangPath, // Path cover belakang
            'stok' => (int) $this->stok, // Konversi stok ke integer
        ];

        try {
            if ($this->editMode && $this->bukuId) { // Jika dalam mode edit
                $buku = BukuModel::findOrFail($this->bukuId); // Ambil buku yang akan diupdate
                $buku->update($payload); // Update data buku
                session()->flash('message', 'Data buku berhasil diperbarui.'); // Tampilkan pesan sukses
            } else { // Jika dalam mode create
                $buku = BukuModel::create($payload); // Buat buku baru
                $this->bukuId = $buku->id; // Simpan ID buku baru
                session()->flash('message', 'Data buku berhasil ditambahkan.'); // Tampilkan pesan sukses
            }

            $this->dispatch('close-modal', id: 'modal-form'); // Kirim event untuk menutup modal
            $this->resetForm(); // Reset form setelah disimpan
        } catch (\Throwable $e) { // Jika terjadi error
            Log::error('Gagal menyimpan data buku', [ // Log error ke file
                'error' => $e->getMessage(),
                'nama_buku' => $this->nama_buku,
                'author_id' => $this->author_id,
                'kategori_id' => $this->kategori_id,
                'penerbit_id' => $this->penerbit_id,
            ]);

            session()->flash('message', 'Terjadi kesalahan saat menyimpan data buku.'); // Tampilkan pesan error
        }
    } // Simpan data buku baru atau perbarui yang sudah ada

    public function edit(int $id): void
    {
        $this->resetValidation(); // Hapus pesan validasi sebelumnya

        $buku = BukuModel::findOrFail($id); // Ambil data buku berdasarkan ID

        $this->editMode = true; // Aktifkan mode edit
        $this->bukuId = $buku->id; // Set ID buku yang akan diedit
        $this->nama_buku = $buku->nama_buku; // Muat nama buku
        $this->author_id = $buku->author_id; // Muat ID author
        $this->kategori_id = $buku->kategori_id; // Muat ID kategori
        $this->penerbit_id = $buku->penerbit_id; // Muat ID penerbit
        $this->deskripsi = $buku->deskripsi; // Muat deskripsi
        $this->tanggal_terbit = $buku->tanggal_terbit?->format('Y-m-d'); // Muat tanggal terbit, format: YYYY-MM-DD
        $this->existingCoverDepan = $buku->cover_depan; // Muat path cover depan
        $this->existingCoverBelakang = $buku->cover_belakang; // Muat path cover belakang
        $this->existingCoverDepanUrl = $this->resolveCoverUrl($buku->cover_depan); // Muat URL cover depan untuk preview
        $this->existingCoverBelakangUrl = $this->resolveCoverUrl($buku->cover_belakang); // Muat URL cover belakang untuk preview
        $this->stok = $buku->stok; // Muat jumlah stok
        $this->cover_depan = null; // Reset upload cover depan
        $this->cover_belakang = null; // Reset upload cover belakang
    } // Muat data buku untuk mode edit

    public function delete(int $id): void
    {
        $buku = BukuModel::findOrFail($id); // Ambil data buku berdasarkan ID

        if ($buku->cover_depan && $this->shouldDeleteFromStorage($buku->cover_depan)) { // Jika buku memiliki cover depan yang perlu dihapus
            Storage::disk('public')->delete($buku->cover_depan); // Hapus file cover depan dari storage
        }

        if ($buku->cover_belakang && $this->shouldDeleteFromStorage($buku->cover_belakang)) { // Jika buku memiliki cover belakang yang perlu dihapus
            Storage::disk('public')->delete($buku->cover_belakang); // Hapus file cover belakang dari storage
        }

        $buku->delete(); // Hapus data buku dari database

        session()->flash('message', 'Data buku berhasil dihapus.'); // Tampilkan pesan sukses
        $this->resetForm(); // Reset form setelah penghapusan
    } // Hapus data buku

    public function updatedCoverDepan(): void
    {
        if ($this->cover_depan instanceof TemporaryUploadedFile) { // Jika ada file cover depan yang diupload
            $this->validateOnly('cover_depan'); // Validasi hanya field cover depan
        }
    } // Validasi file cover depan saat diupload

    public function updatedCoverBelakang(): void
    {
        if ($this->cover_belakang instanceof TemporaryUploadedFile) { // Jika ada file cover belakang yang diupload
            $this->validateOnly('cover_belakang'); // Validasi hanya field cover belakang
        }
    } // Validasi file cover belakang saat diupload

    #[Computed]
    public function listBuku()
    {
        [$sortField, $sortDirection] = $this->resolveSort();

        return BukuModel::with(['author', 'kategori', 'penerbit']) // Muat relasi author, kategori, dan penerbit
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('nama_buku', 'like', $term)
                        ->orWhere('deskripsi', 'like', $term)
                        ->orWhereHas('author', function ($authorQuery) use ($term) {
                            $authorQuery->where('nama_author', 'like', $term);
                        })
                        ->orWhereHas('kategori', function ($kategoriQuery) use ($term) {
                            $kategoriQuery->where('nama_kategori_buku', 'like', $term);
                        })
                        ->orWhereHas('penerbit', function ($penerbitQuery) use ($term) {
                            $penerbitQuery->where('nama_penerbit', 'like', $term);
                        });
                });
            })
            ->orderBy($sortField, $sortDirection) // Urutkan berdasarkan pilihan
            ->paginate($this->perPage); // Terapkan pagination
    } // Ambil daftar buku dengan pagination

    public function render()
    {
        return view('livewire.admin.buku', [
            'listBuku' => $this->listBuku, // Kirim daftar buku ke view
            'authors' => Author::orderBy('nama_author', 'asc')->get(), // Kirim daftar author ke view
            'kategori_buku' => KategoriBuku::orderBy('nama_kategori_buku', 'asc')->get(), // Kirim daftar kategori ke view
            'penerbits' => Penerbit::orderBy('nama_penerbit', 'asc')->get(), // Kirim daftar penerbit ke view
        ]);
    } // Render tampilan komponen dengan data buku dan relasinya

    private function resetForm(): void
    {
        $this->reset([
            'bukuId',
            'nama_buku',
            'author_id',
            'kategori_id',
            'penerbit_id',
            'deskripsi',
            'tanggal_terbit',
            'cover_depan',
            'cover_belakang',
            'existingCoverDepan',
            'existingCoverBelakang',
            'existingCoverDepanUrl',
            'existingCoverBelakangUrl',
            'editMode',
            'stok',
        ]); // Reset semua properti form ke nilai awal
        $this->resetErrorBag(); // Hapus pesan error
        $this->resetValidation(); // Hapus status validasi
    } // Reset form ke kondisi awal

    private function resolveCoverUrl(?string $path): ?string
    {
        if (! $path) { // Jika path kosong, kembalikan null
            return null;
        }

        $normalized = ltrim($path, '/'); // Hapus leading slash

        if (Str::startsWith($normalized, ['http://', 'https://'])) { // Jika path berupa URL eksternal
            return $normalized;
        }

        if (Str::startsWith($normalized, 'storage/')) { // Jika path berada di direktori storage
            return asset($normalized);
        }

        $publicPath = public_path($normalized); // Cek apakah file ada di direktori public
        if (is_file($publicPath)) {
            return asset($normalized);
        }

        $storagePath = storage_path('app/public/'.$normalized); // Cek apakah file ada di direktori storage/app/public
        if (is_file($storagePath)) {
            return asset('storage/'.$normalized);
        }

        return null; // Jika file tidak ditemukan
    } // Ambil URL untuk menampilkan cover buku

    private function shouldDeleteFromStorage(?string $path): bool
    {
        if (! $path) { // Jika path kosong, tidak perlu dihapus
            return false;
        }

        $normalized = ltrim($path, '/'); // Hapus leading slash

        return ! Str::startsWith($normalized, 'assets/'); // Jangan hapus file yang ada di direktori assets/
    } // Cek apakah file perlu dihapus dari storage

    private function normalizeSort($value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'created_at_desc';
    }

    private function resolveSort(): array
    {
        return match ($this->sort) {
            'created_at_asc' => ['created_at', 'asc'],
            'nama_buku_asc' => ['nama_buku', 'asc'],
            'nama_buku_desc' => ['nama_buku', 'desc'],
            default => ['created_at', 'desc'],
        };
    }
}
