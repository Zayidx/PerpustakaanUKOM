<?php

namespace App\Livewire\Admin;

use App\Models\KategoriAcara as KategoriAcaraModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.dashboard-layouts')]
#[Title('Kategori Acara')]
class KategoriAcara extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public int $perPage = 10;
    public array $perPageOptions = [10, 25, 50];

    public ?int $kategoriId = null;
    public string $nama = '';
    public ?string $deskripsi = null;

    protected $rules = [
        'nama' => ['required', 'string', 'max:255'],
        'deskripsi' => ['nullable', 'string', 'max:255'],
    ];

    protected $messages = [
        'nama.required' => 'Nama kategori wajib diisi.',
        'nama.max' => 'Nama kategori maksimal 255 karakter.',
        'deskripsi.max' => 'Deskripsi maksimal 255 karakter.',
    ];

    public function updatedSearch(): void
    {
        $this->search = trim((string) $this->search); // Hapus spasi di awal/akhir kata kunci pencarian
        $this->resetPage(); // Reset pagination ke halaman pertama saat pencarian berubah
    } // Hapus spasi pada input pencarian dan reset pagination

    public function updatedPerPage($value): void
    {
        $this->perPage = $this->normalizePerPage($value); // Pastikan nilai perPage valid sesuai opsi
        $this->resetPage(); // Reset pagination ke halaman pertama saat jumlah per halaman berubah
    } // Atur jumlah item per halaman dan reset pagination

    public function render()
    {
        $categories = KategoriAcaraModel::query()
            ->withCount('acara') // Muat jumlah acara per kategori
            ->when($this->search !== '', function ($query) {
                $query->where('nama', 'like', '%' . $this->search . '%'); // Filter berdasarkan nama kategori
            })
            ->orderBy('nama') // Urutkan berdasarkan nama
            ->paginate($this->perPage); // Terapkan pagination

        return view('livewire.admin.kategori-acara', [
            'kategoriList' => $categories, // Kirim daftar kategori ke view
        ]);
    } // Render tampilan komponen dengan data kategori

    public function create(): void
    {
        $this->resetForm(); // Reset form ke kondisi awal
        $this->resetValidation(); // Hapus pesan validasi sebelumnya
    } // Reset form untuk membuat kategori baru

    public function edit(int $id): void
    {
        $this->resetValidation(); // Hapus pesan validasi sebelumnya

        $kategori = KategoriAcaraModel::findOrFail($id); // Ambil data kategori berdasarkan ID

        $this->kategoriId = $kategori->id; // Set ID kategori yang akan diedit
        $this->nama = $kategori->nama; // Muat nama kategori
        $this->deskripsi = $kategori->deskripsi; // Muat deskripsi kategori
    } // Muat data kategori untuk mode edit

    public function save(): void
    {
        $data = $this->validate(); // Jalankan validasi pada input

        KategoriAcaraModel::updateOrCreate([ // Buat atau update data kategori
            'id' => $this->kategoriId,
        ], $data);

        session()->flash('message', $this->kategoriId ? 'Kategori acara berhasil diperbarui.' : 'Kategori acara baru berhasil dibuat.'); // Tampilkan pesan sukses

        $this->dispatch('close-modal', id: 'modal-kategori-acara'); // Kirim event untuk menutup modal
        $this->resetForm(); // Reset form setelah disimpan
    } // Simpan data kategori acara baru atau perbarui yang sudah ada

    public function delete(int $id): void
    {
        $kategori = KategoriAcaraModel::withCount('acara')->findOrFail($id); // Ambil kategori beserta jumlah acara yang terkait

        if ($kategori->acara_count > 0) { // Cek apakah kategori masih digunakan oleh acara
            session()->flash('message', 'Kategori tidak dapat dihapus karena masih digunakan.');
            return;
        }

        $kategori->delete(); // Hapus kategori jika tidak digunakan
        session()->flash('message', 'Kategori acara berhasil dihapus.');
    } // Hapus data kategori acara jika tidak digunakan

    private function resetForm(): void
    {
        $this->reset([
            'kategoriId',
            'nama',
            'deskripsi',
        ]); // Reset semua properti form ke nilai awal
    } // Reset form ke kondisi awal

    private function normalizePerPage($value): int
    {
        $value = (int) $value; // Konversi nilai ke integer

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0]; // Kembalikan nilai valid atau default
    } // Pastikan nilai perPage valid sesuai opsi yang tersedia
}
