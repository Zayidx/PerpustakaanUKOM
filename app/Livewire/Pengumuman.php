<?php

namespace App\Livewire;

use App\Models\KategoriPengumuman;
use App\Models\Pengumuman as PengumumanModel;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Pengumuman extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public ?int $categoryId = null;
    public int $perPage = 6;
    public array $perPageOptions = [6, 12, 18];

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryId' => ['except' => null, 'as' => 'kategori'],
        'perPage' => ['except' => 6],
    ];

    public function updatedPerPage($value): void
    {
        $this->perPage = $this->normalizePerPage($value); // Pastikan nilai perPage valid sesuai opsi
        $this->resetPage(); // Reset pagination ke halaman pertama saat jumlah per halaman berubah
    } // Atur jumlah item per halaman dan reset pagination

    public function updatedCategoryId($value): void
    {
        $this->categoryId = $value ? (int) $value : null; // Konversi ID kategori ke integer atau null jika kosong
        $this->resetPage(); // Reset pagination ke halaman pertama saat filter kategori berubah
    } // Atur ID kategori filter dan reset pagination

    public function updatedSearch(): void
    {
        $this->search = trim((string) $this->search); // Hapus spasi di awal/akhir kata kunci pencarian
        $this->resetPage(); // Reset pagination ke halaman pertama saat pencarian berubah
    } // Hapus spasi pada input pencarian dan reset pagination

    public function render()
    {
        $announcements = PengumumanModel::query() // Query pengumuman yang dipublikasikan
            ->with(['kategori', 'admin']) // Muat relasi kategori dan admin
            ->where('status', 'published') // Filter hanya pengumuman yang dipublikasikan
            ->when($this->search !== '', function ($query) { // Jika ada kata kunci pencarian
                $term = '%' . $this->search . '%'; // Format untuk pencarian like

                $query->where(function ($inner) use ($term) { // Cari berdasarkan judul atau konten
                    $inner->where('judul', 'like', $term) // Cari di judul
                        ->orWhere('konten', 'like', $term); // Atau cari di konten
                });
            })
            ->when($this->categoryId, function ($query) { // Jika ada filter kategori
                $query->where('kategori_pengumuman_id', $this->categoryId); // Filter berdasarkan ID kategori
            })
            ->latest('published_at') // Urutkan berdasarkan tanggal publikasi terbaru
            ->paginate($this->perPage); // Terapkan pagination

        return view('livewire.pengumuman', [ // Render view dengan data
            'announcements' => $announcements, // Daftar pengumuman
            'categories' => KategoriPengumuman::orderBy('nama')->get(), // Daftar kategori pengumuman
        ]);
    } // Render tampilan komponen dengan daftar pengumuman

    private function normalizePerPage($value): int
    {
        $value = (int) $value; // Konversi nilai ke integer

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0]; // Kembalikan nilai valid atau default
    } // Pastikan nilai perPage valid sesuai opsi yang tersedia
}
