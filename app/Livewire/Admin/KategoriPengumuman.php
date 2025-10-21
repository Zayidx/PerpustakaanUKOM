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
class KategoriPengumuman extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public int $perPage = 10;
    public array $perPageOptions = [10, 25, 50];

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

    public function updatedSearch($value): void
    {
        $this->search = trim((string) $value);
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = in_array((int) $value, $this->perPageOptions, true)
            ? (int) $value
            : $this->perPageOptions[0];

        $this->resetPage();
    }

    public function render()
    {
        $kategori = KategoriPengumumanModel::query()
            ->withCount('pengumuman')
            ->when($this->search !== '', function ($query) {
                $query->where('nama', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.admin.kategori-pengumuman', [
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
}
