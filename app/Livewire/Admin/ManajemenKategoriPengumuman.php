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
                'required',
                'string',
                'max:255',
                Rule::unique('kategori_pengumuman', 'nama')->ignore($this->kategoriId),
            ],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function mount(): void
    {
        $this->perPage = $this->normalizePerPage($this->perPage);
        $this->sort = $this->normalizeSort($this->sort);
        $this->search = trim((string) $this->search);
    }

    public function updatedSearch($value): void
    {
        $this->search = trim((string) $value);
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = $this->normalizePerPage($value);

        $this->resetPage();
    }

    public function updatedSort($value): void
    {
        $this->sort = $this->normalizeSort($value);
        $this->resetPage();
    }

    public function render()
    {
        [$sortField, $sortDirection] = $this->resolveSort();

        $kategori = KategoriPengumumanModel::query()
            ->withCount('pengumuman')
            ->when($this->search !== '', function ($query) {
                $query->where('nama', 'like', '%' . $this->search . '%');
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.manajemen-kategori-pengumuman', [
            'kategoriList' => $kategori,
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $this->resetValidation();

        $kategori = KategoriPengumumanModel::findOrFail($id);

        $this->kategoriId = $kategori->id;
        $this->nama = $kategori->nama;
        $this->deskripsi = $kategori->deskripsi;
    }

    public function save(): void
    {
        $data = $this->validate();
        $isUpdate = (bool) $this->kategoriId;

        KategoriPengumumanModel::updateOrCreate(
            ['id' => $this->kategoriId],
            [
                'nama' => $data['nama'],
                'deskripsi' => $data['deskripsi'],
            ]
        );

        session()->flash('message', $isUpdate ? 'Kategori pengumuman berhasil diperbarui.' : 'Kategori pengumuman baru berhasil dibuat.');

        $this->dispatch('close-modal', id: 'modal-kategori');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $kategori = KategoriPengumumanModel::withCount('pengumuman')->findOrFail($id);

        if ($kategori->pengumuman_count > 0) {
            session()->flash('message', 'Kategori tidak dapat dihapus karena masih digunakan oleh pengumuman.');
            return;
        }

        $kategori->delete();
        session()->flash('message', 'Kategori pengumuman berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->reset(['kategoriId', 'nama', 'deskripsi']);
        $this->resetValidation();
    }

    private function normalizePerPage($value): int
    {
        $value = (int) $value;

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0];
    }

    private function normalizeSort(string $value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'created_desc';
    }

    private function resolveSort(): array
    {
        return match ($this->sort) {
            'created_asc' => ['created_at', 'asc'],
            'created_desc' => ['created_at', 'desc'],
            'nama_asc' => ['nama', 'asc'],
            'nama_desc' => ['nama', 'desc'],
            'pengumuman_asc' => ['pengumuman_count', 'asc'],
            'pengumuman_desc' => ['pengumuman_count', 'desc'],
            default => ['created_at', 'desc'],
        };
    }
}
