<?php

namespace App\Livewire\SuperAdmin;

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
                Rule::unique('kategori_buku', 'nama_kategori_buku')->ignore($this->kategori_id) 
            ],
            'deskripsi_kategori_buku' => ['required', 'string'], 
        ];
    } 

    public function mount(): void
    {
        $this->sort = $this->normalizeSort($this->sort);
        $this->search = trim((string) $this->search);
    }

    public function updatedPerPage()
    {
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

    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->resetValidation();
    } 

    public function store()
    {
        $this->validate(); 

        if ($this->editMode && $this->kategori_id) {
            $kategori = KategoriBuku::findOrFail($this->kategori_id); 

            $kategori->update([
                'nama_kategori_buku' => $this->nama_kategori_buku,
                'deskripsi_kategori_buku' => $this->deskripsi_kategori_buku,
            ]);

            session()->flash('message', 'Kategori buku berhasil diperbarui.');
        } else {
            KategoriBuku::create([ 
                'nama_kategori_buku' => $this->nama_kategori_buku,
                'deskripsi_kategori_buku' => $this->deskripsi_kategori_buku,
            ]);

            session()->flash('message', 'Kategori buku berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->dispatch('close-modal', id: 'modal-form'); 
    } 

    public function edit($id)
    {
        $this->resetValidation();

        $kategori = KategoriBuku::findOrFail((int) $id); 

        $this->editMode = true; 
        $this->kategori_id = $kategori->id;
        $this->nama_kategori_buku = $kategori->nama_kategori_buku;
        $this->deskripsi_kategori_buku = $kategori->deskripsi_kategori_buku;
    } 

    public function delete($id)
    {
        $kategori = KategoriBuku::findOrFail((int) $id); 
        $kategori->delete(); 

        session()->flash('message', 'Kategori buku berhasil dihapus.');
        $this->resetForm();
    } 

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
            ->paginate($this->perPage); 
    } 

        public function render()
    {
        
        
        
        return view('livewire.super-admin.kategori-buku', [
            'listKategoriBuku' => $this->listKategoriBuku,
            'listAuthors' => Author::orderBy('nama_author', 'asc')->get(), 
        ]);
    } 

    private function resetForm(): void
    {
        $this->reset([
            'kategori_id',
            'nama_kategori_buku',
            'deskripsi_kategori_buku',
            'editMode',
        ]);
        $this->resetErrorBag(); 
        $this->resetValidation(); 
    } 

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
