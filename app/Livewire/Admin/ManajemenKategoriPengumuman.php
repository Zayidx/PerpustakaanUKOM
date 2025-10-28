<?php

namespace App\Livewire\Admin;

use App\Models\KategoriPengumuman as KategoriPengumumanModel;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.dashboard-layouts')]
#[Title('Kategori Pengumuman')]
class ManajemenKategoriPengumuman extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public int $perPage = 10;
    public array $perPageOptions = [10, 25, 50];
    public string $sort = 'created_desc';
    public array $sortOptions = [
        'created_desc' => 'Terbaru',
        'created_asc' => 'Terlama',
        'nama_asc' => 'Nama A-Z',
        'nama_desc' => 'Nama Z-A',
        'pengumuman_desc' => 'Pengumuman Terbanyak',
        'pengumuman_asc' => 'Pengumuman Tersedikit',
    ];

    public ?int $kategoriId = null;
    public string $nama = '';
    public ?string $deskripsi = null;

    protected $messages = [
        'nama.required' => 'Nama kategori wajib diisi.',
        'nama.max' => 'Nama kategori maksimal 255 karakter.',
        'nama.unique' => 'Nama kategori ini sudah digunakan.',
        'deskripsi.max' => 'Deskripsi maksimal 500 karakter.',
    ];

    protected function rules(): array
    {
        return [
            'nama' => [
                'required', // Nama kategori wajib diisi
                'string', // Harus berupa teks
                'max:255', // Maksimal 255 karakter
                Rule::unique('kategori_pengumuman', 'nama')->ignore($this->kategoriId), // Harus unik, abaikan saat edit
            ],
            'deskripsi' => ['nullable', 'string', 'max:500'], // Deskripsi opsional, harus string, maksimal 500 karakter
        ];
    } // Aturan validasi untuk form kategori pengumuman

    public function mount(): void
    {
        $this->perPage = $this->normalizePerPage($this->perPage); // Pastikan nilai perPage valid
        $this->sort = $this->normalizeSort($this->sort); // Pastikan opsi sort valid
        $this->search = trim((string) $this->search); // Normalisasi kata kunci pencarian
    } // Inisialisasi komponen dengan nilai valid

    public function updatedSearch($value): void
    {
        $this->search = trim((string) $value); // Hapus spasi di awal/akhir kata kunci pencarian
        $this->resetPage(); // Reset pagination ke halaman pertama saat pencarian berubah
    } // Hapus spasi pada input pencarian dan reset pagination

    public function updatedPerPage($value): void
    {
        $this->perPage = $this->normalizePerPage($value); // Pastikan nilai perPage valid sesuai opsi

        $this->resetPage(); // Reset pagination ke halaman pertama saat jumlah per halaman berubah
    } // Atur jumlah item per halaman dan reset pagination

    public function updatedSort($value): void
    {
        $this->sort = $this->normalizeSort($value); // Pastikan opsi sort valid
        $this->resetPage(); // Reset pagination ke halaman pertama saat sorting berubah
    } // Atur opsi sorting dan reset pagination

    public function render()
    {
        [$sortField, $sortDirection] = $this->resolveSort(); // Ambil field dan arah sorting

        $kategori = KategoriPengumumanModel::query()
            ->withCount('pengumuman') // Muat jumlah pengumuman per kategori
            ->when($this->search !== '', function ($query) { // Jika ada kata kunci pencarian
                $query->where('nama', 'like', '%' . $this->search . '%'); // Cari berdasarkan nama kategori
            })
            ->orderBy($sortField, $sortDirection) // Terapkan sorting
            ->paginate($this->perPage); // Terapkan pagination

        return view('livewire.admin.manajemen-kategori-pengumuman', [ // Render tampilan komponen
            'kategoriList' => $kategori, // Kirim daftar kategori ke view
        ]);
    } // Render tampilan komponen dengan data kategori

    public function create(): void
    {
        $this->resetForm(); // Reset form ke kondisi awal
    } // Reset form untuk membuat kategori baru

    public function edit(int $id): void
    {
        $this->resetValidation(); // Hapus pesan validasi sebelumnya

        $kategori = KategoriPengumumanModel::findOrFail($id); // Ambil data kategori berdasarkan ID

        $this->kategoriId = $kategori->id; // Set ID kategori yang akan diedit
        $this->nama = $kategori->nama; // Muat nama kategori
        $this->deskripsi = $kategori->deskripsi; // Muat deskripsi kategori
    } // Muat data kategori untuk mode edit

    public function save(): void
    {
        $data = $this->validate(); // Jalankan validasi pada input
        $isUpdate = (bool) $this->kategoriId; // Cek apakah dalam mode update

        KategoriPengumumanModel::updateOrCreate( // Buat atau update data kategori
            ['id' => $this->kategoriId], // Kondisi pencarian
            [ // Data yang akan disimpan
                'nama' => $data['nama'], // Nama kategori
                'deskripsi' => $data['deskripsi'], // Deskripsi kategori
            ]
        );

        session()->flash('message', $isUpdate ? 'Kategori pengumuman berhasil diperbarui.' : 'Kategori pengumuman baru berhasil dibuat.'); // Tampilkan pesan sukses

        $this->dispatch('close-modal', id: 'modal-kategori'); // Kirim event untuk menutup modal
        $this->resetForm(); // Reset form setelah disimpan
    } // Simpan data kategori pengumuman baru atau perbarui yang sudah ada

    public function delete(int $id): void
    {
        $kategori = KategoriPengumumanModel::withCount('pengumuman')->findOrFail($id); // Ambil data kategori beserta jumlah pengumuman

        if ($kategori->pengumuman_count > 0) { // Cek apakah kategori masih digunakan oleh pengumuman
            session()->flash('message', 'Kategori tidak dapat dihapus karena masih digunakan oleh pengumuman.');
            return;
        }

        $kategori->delete(); // Hapus data kategori dari database
        session()->flash('message', 'Kategori pengumuman berhasil dihapus.'); // Tampilkan pesan sukses
    } // Hapus data kategori jika tidak digunakan oleh pengumuman

    private function resetForm(): void
    {
        $this->reset(['kategoriId', 'nama', 'deskripsi']); // Reset semua properti form ke nilai awal
        $this->resetValidation(); // Hapus status validasi
    } // Reset form ke kondisi awal

    private function normalizePerPage($value): int
    {
        $value = (int) $value; // Konversi nilai ke integer

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0]; // Kembalikan nilai valid atau default
    } // Pastikan nilai perPage valid sesuai opsi yang tersedia

    private function normalizeSort(string $value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'created_desc'; // Kembalikan nilai sort valid atau default
    } // Pastikan nilai sort valid sesuai opsi yang tersedia

    private function resolveSort(): array
    {
        return match ($this->sort) { // Kembalikan field dan arah sorting berdasarkan opsi
            'created_asc' => ['created_at', 'asc'], // Urutkan berdasarkan created_at ascending
            'created_desc' => ['created_at', 'desc'], // Urutkan berdasarkan created_at descending
            'nama_asc' => ['nama', 'asc'], // Urutkan berdasarkan nama ascending
            'nama_desc' => ['nama', 'desc'], // Urutkan berdasarkan nama descending
            'pengumuman_asc' => ['pengumuman_count', 'asc'], // Urutkan berdasarkan jumlah pengumuman ascending
            'pengumuman_desc' => ['pengumuman_count', 'desc'], // Urutkan berdasarkan jumlah pengumuman descending
            default => ['created_at', 'desc'], // Default: urutkan berdasarkan created_at descending
        };
    } // Ambil field dan arah sorting
}
