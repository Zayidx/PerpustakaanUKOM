<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\Concerns\HandlesAlerts;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Jurusan;

class ManajemenJurusan extends Component
{
    use HandlesAlerts;
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
        $this->resetValidation();

        $jurusan = Jurusan::findOrFail($id);

        $this->jurusan_id = $jurusan->id;
        $this->nama_jurusan = $jurusan->nama_jurusan;
        $this->deskripsi = $jurusan->deskripsi;
    }

    public function store(): void
    {
        $this->validate();

        $payload = [
            'nama_jurusan' => trim($this->nama_jurusan),
            'deskripsi' => trim($this->deskripsi),
        ];

        if ($this->jurusan_id) {
            $jurusan = Jurusan::findOrFail($this->jurusan_id);
            $jurusan->update($payload);
        } else {
            $jurusan = Jurusan::create($payload);
            $this->jurusan_id = $jurusan->id;
        }

        $this->resetForm();
        $this->flashSuccess('Data jurusan berhasil disimpan.');
        $this->dispatch('close-modal', id: 'modal-form-jurusan');
    }

    public function delete(int $id): void
    {
        $jurusan = Jurusan::withCount('siswa')->findOrFail($id);

        if ($jurusan->siswa_count > 0) {
            $this->flashError('Jurusan masih memiliki siswa aktif dan tidak dapat dihapus.');
            return;
        }

        $jurusan->delete();

        $this->flashSuccess('Data jurusan berhasil dihapus.');
        $this->resetForm();
        $this->resetPage();
    }

    #[Computed]
    public function listJurusan()
    {
        [$sortField, $sortDirection] = $this->resolveSort();

        return Jurusan::query()
            ->when($this->search !== '', function ($query) {
                $searchTerm = '%'.$this->search.'%';

                $query->where(function ($query) use ($searchTerm) {
                    $query->where('nama_jurusan', 'like', $searchTerm)
                        ->orWhere('deskripsi', 'like', $searchTerm);
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.super-admin.manajemen-jurusan');
    }

    protected function rules(): array
    {
        return [
            'nama_jurusan' => [
                'required',
                'string',
                'max:120',
                Rule::unique('jurusan', 'nama_jurusan')->ignore($this->jurusan_id),
            ],
            'deskripsi' => ['required', 'string', 'max:255'],
        ];
    }

    private function normalizeSort(string $value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'nama_jurusan_asc';
    }

    private function resolveSort(): array
    {
        return match ($this->sort) {
            'nama_jurusan_desc' => ['nama_jurusan', 'desc'],
            'created_at_desc' => ['created_at', 'desc'],
            'created_at_asc' => ['created_at', 'asc'],
            default => ['nama_jurusan', 'asc'],
        };
    }

    private function normalizePerPage($value): int
    {
        $value = (int) $value;

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0];
    }

    private function resetForm(): void
    {
        $this->reset([
            'jurusan_id',
            'nama_jurusan',
            'deskripsi',
        ]);

        $this->resetErrorBag();
        $this->resetValidation();
    }
}
