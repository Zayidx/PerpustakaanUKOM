<?php

namespace App\Livewire\Admin;

use App\Models\Jurusan;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenJurusan extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Halaman Manajemen Jurusan')]
    #[Url(except: "")]
    #[Layout('components.layouts.dashboard-layouts')]
    public $perPage = 5;
    public array $perPageOptions = [5, 10, 25];
    public $search = '';
    public string $sort = 'nama_jurusan_asc';
    public array $sortOptions = [
        'nama_jurusan_asc' => 'Nama Jurusan A-Z',
        'nama_jurusan_desc' => 'Nama Jurusan Z-A',
        'created_at_desc' => 'Terbaru',
        'created_at_asc' => 'Terlama',
    ];

    public $jurusan_id;
    public $nama_jurusan = '';
    public $deskripsi = '';

    protected $messages = [
        'nama_jurusan.required' => 'Nama jurusan wajib diisi.',
        'nama_jurusan.string' => 'Nama jurusan harus berupa teks.',
        'nama_jurusan.max' => 'Nama jurusan maksimal :max karakter.',
        'nama_jurusan.unique' => 'Nama jurusan tersebut sudah digunakan.',

        'deskripsi.required' => 'Deskripsi jurusan wajib diisi.',
        'deskripsi.string' => 'Deskripsi harus berupa teks.',
        'deskripsi.max' => 'Deskripsi maksimal :max karakter.',
    ];

    public function updatedPerPage($value): void
    {
        $this->perPage = $this->normalizePerPage($value);
        $this->resetPage();
    }

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
        $this->resetForm();
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $this->resetValidation(); // Hapus pesan validasi sebelumnya

        $jurusan = Jurusan::findOrFail($id); // Ambil data jurusan berdasarkan ID

        $this->jurusan_id = $jurusan->id; // Set ID jurusan yang akan diedit
        $this->nama_jurusan = $jurusan->nama_jurusan; // Muat nama jurusan
        $this->deskripsi = $jurusan->deskripsi; // Muat deskripsi jurusan
    } // Muat data jurusan untuk mode edit

    public function store(): void
    {
        $this->validate(); // Jalankan validasi pada input

        $payload = [
            'nama_jurusan' => trim($this->nama_jurusan), // Normalisasi nama jurusan
            'deskripsi' => trim($this->deskripsi), // Normalisasi deskripsi
        ];

        if ($this->jurusan_id) { // Jika dalam mode edit
            $jurusan = Jurusan::findOrFail($this->jurusan_id); // Ambil jurusan yang akan diupdate
            $jurusan->update($payload); // Update data jurusan
        } else { // Jika dalam mode create
            $jurusan = Jurusan::create($payload); // Buat jurusan baru
            $this->jurusan_id = $jurusan->id; // Simpan ID jurusan baru
        }

        $this->resetForm(); // Reset form setelah disimpan
        session()->flash('message', 'Data jurusan berhasil disimpan.'); // Tampilkan pesan sukses
        $this->dispatch('close-modal', id: 'modal-form-jurusan'); // Kirim event untuk menutup modal
    } // Simpan data jurusan baru atau perbarui yang sudah ada

    public function delete(int $id): void
    {
        $jurusan = Jurusan::withCount('siswa')->findOrFail($id); // Ambil data jurusan beserta jumlah siswa

        if ($jurusan->siswa_count > 0) { // Cek apakah jurusan masih memiliki siswa aktif
            session()->flash('message', 'Jurusan masih memiliki siswa aktif dan tidak dapat dihapus.');
            return;
        }

        $jurusan->delete(); // Hapus data jurusan dari database

        session()->flash('message', 'Data jurusan berhasil dihapus.'); // Tampilkan pesan sukses
        $this->resetForm(); // Reset form setelah penghapusan
        $this->resetPage(); // Reset pagination
    } // Hapus data jurusan jika tidak memiliki siswa aktif

    #[Computed]
    public function listJurusan()
    {
        [$sortField, $sortDirection] = $this->resolveSort(); // Ambil field dan arah sorting

        return Jurusan::query()
            ->when($this->search !== '', function ($query) { // Jika ada kata kunci pencarian
                $searchTerm = '%' . $this->search . '%';

                $query->where(function ($query) use ($searchTerm) { // Cari berdasarkan nama jurusan atau deskripsi
                    $query->where('nama_jurusan', 'like', $searchTerm) // Cari berdasarkan nama jurusan
                        ->orWhere('deskripsi', 'like', $searchTerm); // Cari berdasarkan deskripsi
                });
            })
            ->orderBy($sortField, $sortDirection) // Terapkan sorting
            ->paginate($this->perPage); // Terapkan pagination
    } // Ambil daftar jurusan dengan pencarian, sorting, dan pagination

    public function render()
    {
        return view('livewire.admin.manajemen-jurusan');
    } // Render tampilan komponen

    protected function rules(): array
    {
        return [
            'nama_jurusan' => [
                'required', // Nama jurusan wajib diisi
                'string', // Harus berupa teks
                'max:120', // Maksimal 120 karakter
                Rule::unique('jurusan', 'nama_jurusan')->ignore($this->jurusan_id), // Harus unik, abaikan saat edit
            ],
            'deskripsi' => ['required', 'string', 'max:255'], // Deskripsi wajib, harus string, maksimal 255 karakter
        ];
    } // Aturan validasi untuk form jurusan

    public function updatedPerPage($value): void
    {
        $this->perPage = $this->normalizePerPage($value); // Pastikan nilai perPage valid sesuai opsi
        $this->resetPage(); // Reset pagination ke halaman pertama saat jumlah per halaman berubah
    } // Atur jumlah item per halaman dan reset pagination

    public function updatedSearch(): void
    {
        $this->search = trim((string) $this->search); // Hapus spasi di awal/akhir kata kunci pencarian
        $this->resetPage(); // Reset pagination ke halaman pertama saat pencarian berubah
    } // Hapus spasi pada input pencarian dan reset pagination

    public function updatedSort($value): void
    {
        $this->sort = $this->normalizeSort($value); // Pastikan nilai sort valid
        $this->resetPage(); // Reset pagination ke halaman pertama saat sorting berubah
    } // Atur opsi sorting dan reset pagination

    public function create(): void
    {
        $this->resetForm(); // Reset form ke kondisi awal
        $this->resetValidation(); // Hapus pesan validasi sebelumnya
    } // Reset form untuk membuat jurusan baru

    private function normalizeSort(string $value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'nama_jurusan_asc'; // Kembalikan nilai sort valid atau default
    } // Pastikan nilai sort valid sesuai opsi yang tersedia

    private function resolveSort(): array
    {
        return match ($this->sort) { // Kembalikan field dan arah sorting berdasarkan opsi
            'nama_jurusan_desc' => ['nama_jurusan', 'desc'], // Urutkan berdasarkan nama jurusan descending
            'created_at_desc' => ['created_at', 'desc'], // Urutkan berdasarkan created_at descending
            'created_at_asc' => ['created_at', 'asc'], // Urutkan berdasarkan created_at ascending
            default => ['nama_jurusan', 'asc'], // Default: urutkan berdasarkan nama jurusan ascending
        };
    } // Ambil field dan arah sorting

    private function normalizePerPage($value): int
    {
        $value = (int) $value; // Konversi nilai ke integer

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0]; // Kembalikan nilai valid atau default
    } // Pastikan nilai perPage valid sesuai opsi yang tersedia

    private function resetForm(): void
    {
        $this->reset([
            'jurusan_id',
            'nama_jurusan',
            'deskripsi',
        ]); // Reset semua properti form ke nilai awal

        $this->resetErrorBag(); // Hapus pesan error sebelumnya
        $this->resetValidation(); // Hapus status validasi
    } // Reset form ke kondisi awal
}
