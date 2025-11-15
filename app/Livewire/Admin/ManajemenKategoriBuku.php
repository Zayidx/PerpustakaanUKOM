<?php

namespace App\Livewire\Admin;

use App\Models\KategoriBuku;
use App\Models\Author; 
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenKategoriBuku extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Manajemen Kategori Buku')]
    #[Url(except: "")]
    #[Layout('components.layouts.dashboard-layouts')]

    public $perPage = 5;
    public string $search = '';
    public string $sort = 'created_at_desc';
    public array $sortOptions = [
        'created_at_desc' => 'Terbaru',
        'created_at_asc' => 'Terlama',
        'nama_kategori_buku_asc' => 'Nama A-Z',
        'nama_kategori_buku_desc' => 'Nama Z-A',
    ];

    public $kategori_id;
    public $nama_kategori_buku;
    public $deskripsi_kategori_buku;

    public $editMode = false;

    protected $messages = [
        'nama_kategori_buku.required' => 'Nama kategori wajib diisi.',
        'nama_kategori_buku.unique' => 'Nama kategori sudah digunakan.',
        'deskripsi_kategori_buku.required' => 'Deskripsi kategori wajib diisi.',
    ];

    protected function rules(): array
    {
        return [
            'nama_kategori_buku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori_buku', 'nama_kategori_buku')->ignore($this->kategori_id) // Validasi unik, abaikan ID saat edit
            ],
            'deskripsi_kategori_buku' => ['required', 'string'], // Deskripsi wajib diisi
        ];
    } // Aturan validasi untuk form kategori buku

    public function mount(): void
    {
        $this->sort = $this->normalizeSort($this->sort);
        $this->search = trim((string) $this->search);
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    } // Reset pagination ke halaman pertama saat jumlah item per halaman berubah

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

    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->resetValidation();
    } // Reset form untuk membuat kategori baru

    public function store()
    {
        $this->validate(); // Jalankan validasi sebelum menyimpan

        if ($this->editMode && $this->kategori_id) {
            $kategori = KategoriBuku::findOrFail($this->kategori_id); // Ambil data kategori yang akan diedit

            $kategori->update([
                'nama_kategori_buku' => $this->nama_kategori_buku,
                'deskripsi_kategori_buku' => $this->deskripsi_kategori_buku,
            ]);

            session()->flash('message', 'Kategori buku berhasil diperbarui.');
        } else {
            KategoriBuku::create([ // Buat kategori baru
                'nama_kategori_buku' => $this->nama_kategori_buku,
                'deskripsi_kategori_buku' => $this->deskripsi_kategori_buku,
            ]);

            session()->flash('message', 'Kategori buku berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->dispatch('close-modal', id: 'modal-form'); // Kirim event untuk menutup modal
    } // Simpan data kategori buku baru atau perbarui data kategori yang sudah ada

    public function edit($id)
    {
        $this->resetValidation();

        $kategori = KategoriBuku::findOrFail((int) $id); // Ambil data kategori berdasarkan ID

        $this->editMode = true; // Aktifkan mode edit
        $this->kategori_id = $kategori->id;
        $this->nama_kategori_buku = $kategori->nama_kategori_buku;
        $this->deskripsi_kategori_buku = $kategori->deskripsi_kategori_buku;
    } // Ambil data kategori berdasarkan ID untuk dimuat ke form edit

    public function delete($id)
    {
        $kategori = KategoriBuku::findOrFail((int) $id); // Ambil data kategori berdasarkan ID
        $kategori->delete(); // Hapus dari database

        session()->flash('message', 'Kategori buku berhasil dihapus.');
        $this->resetForm();
    } // Hapus data kategori buku berdasarkan ID

    #[Computed]
    public function listKategoriBuku()
    {
        [$sortField, $sortDirection] = $this->resolveSort();

        return KategoriBuku::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('nama_kategori_buku', 'like', $term)
                        ->orWhere('deskripsi_kategori_buku', 'like', $term);
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($this->perPage); // Ambil data kategori sesuai sort
    } // Kembalikan daftar kategori buku yang telah diurutkan dan dipaginasi

        public function render()
    {
        // Kirim kedua variabel ke view:
        // - listKategoriBuku untuk tabel/pagination
        // - listAuthors untuk dropdown Penulis jika view butuh
        return view('livewire.admin.kategori-buku', [
            'listKategoriBuku' => $this->listKategoriBuku,
            'listAuthors' => Author::orderBy('nama_author', 'asc')->get(), // Ambil semua author untuk dropdown
        ]);
    } // Tampilkan tampilan komponen Livewire

    private function resetForm(): void
    {
        $this->reset([
            'kategori_id',
            'nama_kategori_buku',
            'deskripsi_kategori_buku',
            'editMode',
        ]);
        $this->resetErrorBag(); // Hapus pesan error yang mungkin ada
        $this->resetValidation(); // Reset status validasi
    } // Reset semua properti form ke nilai awal

    private function normalizeSort($value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'created_at_desc';
    }

    private function resolveSort(): array
    {
        return match ($this->sort) {
            'created_at_asc' => ['created_at', 'asc'],
            'nama_kategori_buku_asc' => ['nama_kategori_buku', 'asc'],
            'nama_kategori_buku_desc' => ['nama_kategori_buku', 'desc'],
            default => ['created_at', 'desc'],
        };
    }
}
