<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Kelas;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenKelas extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Halaman Manajemen Kelas')]
    #[Url(except: "")]
    #[Layout('components.layouts.dashboard-layouts')]
    public $perPage = 5;
    public array $perPageOptions = [5, 10, 25];
    public $search = '';
    public string $sort = 'nama_kelas_asc';
    public array $sortOptions = [
        'nama_kelas_asc' => 'Nama Kelas A-Z',
        'nama_kelas_desc' => 'Nama Kelas Z-A',
        'created_at_desc' => 'Terbaru',
        'created_at_asc' => 'Terlama',
    ];

    public $kelas_id;
    public $nama_kelas = '';
    public $tingkat = '';

    protected $messages = [
        'nama_kelas.required' => 'Nama kelas wajib diisi.',
        'nama_kelas.string' => 'Nama kelas harus berupa teks.',
        'nama_kelas.max' => 'Nama kelas maksimal :max karakter.',
        'nama_kelas.unique' => 'Nama kelas tersebut sudah digunakan.',

        'tingkat.required' => 'Tingkat kelas wajib diisi.',
        'tingkat.string' => 'Tingkat kelas harus berupa teks.',
        'tingkat.max' => 'Tingkat kelas maksimal :max karakter.',
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

        $kelas = Kelas::findOrFail($id); 

        $this->kelas_id = $kelas->id; 
        $this->nama_kelas = $kelas->nama_kelas; 
        $this->tingkat = $kelas->tingkat; 
    } 

    public function store(): void
    {
        $this->validate(); 

        $payload = [
            'nama_kelas' => trim($this->nama_kelas), 
            'tingkat' => trim($this->tingkat), 
        ];

        if ($this->kelas_id) { 
            $kelas = Kelas::findOrFail($this->kelas_id); 
            $kelas->update($payload); 
        } else { 
            $kelas = Kelas::create($payload); 
            $this->kelas_id = $kelas->id; 
        }

        $this->resetForm(); 
        session()->flash('message', 'Data kelas berhasil disimpan.'); 
        $this->dispatch('close-modal', id: 'modal-form-kelas'); 
    } 

    public function delete(int $id): void
    {
        $kelas = Kelas::withCount('siswa')->findOrFail($id); 

        if ($kelas->siswa_count > 0) { 
            session()->flash('message', 'Kelas masih memiliki siswa aktif dan tidak dapat dihapus.');
            return;
        }

        $kelas->delete(); 

        session()->flash('message', 'Data kelas berhasil dihapus.'); 
        $this->resetForm(); 
        $this->resetPage(); 
    } 

    #[Computed]
    public function listKelas()
    {
        [$sortField, $sortDirection] = $this->resolveSort(); 

        return Kelas::query()
            ->when($this->search !== '', function ($query) { 
                $searchTerm = '%' . $this->search . '%';

                $query->where(function ($query) use ($searchTerm) { 
                    $query->where('nama_kelas', 'like', $searchTerm) 
                        ->orWhere('tingkat', 'like', $searchTerm); 
                });
            })
            ->orderBy($sortField, $sortDirection) 
            ->paginate($this->perPage); 
    } 

    public function render()
    {
        return view('livewire.super-admin.manajemen-kelas');
    } 

    private function normalizeSort(string $value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'nama_kelas_asc'; 
    } 

    private function resolveSort(): array
    {
        return match ($this->sort) { 
            'nama_kelas_desc' => ['nama_kelas', 'desc'], 
            'created_at_desc' => ['created_at', 'desc'], 
            'created_at_asc' => ['created_at', 'asc'], 
            default => ['nama_kelas', 'asc'], 
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
            'kelas_id',
            'nama_kelas',
            'tingkat',
        ]); 

        $this->resetErrorBag(); 
        $this->resetValidation(); 
    } 

    protected function rules(): array
    {
        return [
            'nama_kelas' => [
                'required', 
                'string', 
                'max:100', 
                Rule::unique('kelas', 'nama_kelas')->ignore($this->kelas_id), 
            ],
            'tingkat' => ['required', 'string', 'max:50'], 
        ];
    } 

}
