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

    // Persist filters in the query string so pages can be shared/bookmarked.
    protected $queryString = [
        'search' => ['except' => ''],
        'categoryId' => ['except' => null, 'as' => 'kategori'],
        'perPage' => ['except' => 6],
    ];

    public function updatedPerPage($value): void
    {
        $this->perPage = $this->normalizePerPage($value);
        $this->resetPage();
    } 

    public function updatedCategoryId($value): void
    {
        $this->categoryId = $value ? (int) $value : null;
        $this->resetPage();
    } 

    public function updatedSearch(): void
    {
        $this->search = trim((string) $this->search);
        $this->resetPage();
    } 

    public function render()
    {
        // Ambil daftar pengumuman terbit dengan filter pencarian dan kategori.
        $announcements = PengumumanModel::query()
            ->with(['kategori', 'admin'])
            ->where('status', 'published')
            ->when($this->search !== '', function ($query) {
                $term = '%' . $this->search . '%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('judul', 'like', $term)
                        ->orWhere('konten', 'like', $term);
                });
            })
            ->when($this->categoryId, function ($query) {
                $query->where('kategori_pengumuman_id', $this->categoryId);
            })
            ->latest('published_at')
            ->paginate($this->perPage);

        return view('livewire.pengumuman', [
            'announcements' => $announcements,
            'categories' => KategoriPengumuman::orderBy('nama')->get(),
        ]);
    } 

    private function normalizePerPage($value): int
    {
        $value = (int) $value;

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0];
    } 
}
