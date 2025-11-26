<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\Concerns\HandlesAlerts;
use App\Livewire\Concerns\HandlesImageUploads;
use App\Models\Author;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ManajemenAuthor extends Component
{
    use HandlesAlerts;
    use HandlesImageUploads;
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Manajemen Author')]
    #[Url(except: "")]
    #[Layout('components.layouts.dashboard-layouts')]
    public $perPage = 5;
    public string $search = '';
    public string $sort = 'created_at_desc';
    public array $sortOptions = [
        'created_at_desc' => 'Terbaru',
        'created_at_asc' => 'Terlama',
        'nama_author_asc' => 'Nama A-Z',
        'nama_author_desc' => 'Nama Z-A',
    ];

    public $authorId;
    public $nama_author = '';
    public $email_author = '';
    public $no_telp = '';
    public $alamat = '';
    public $foto;
    public $existingFoto = '';
    public $editMode = false;

    protected $messages = [
        'nama_author.required' => 'Nama author wajib diisi.',
        'nama_author.unique' => 'Nama author sudah digunakan.',
        'email_author.email' => 'Format email tidak valid.',
        'foto.image' => 'File harus berupa gambar.',
        'foto.mimes' => 'Format foto harus JPG atau PNG.',
        'foto.max' => 'Ukuran foto maksimal 2MB.',
    ];

    protected function rules(): array
    {
        return [
            'nama_author' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('authors', 'nama_author')->ignore($this->authorId), 
            ],
            'email_author' => ['nullable', 'email', 'max:255'], 
            'no_telp' => ['nullable', 'string', 'max:20'], 
            'alamat' => ['nullable', 'string', 'max:255'], 
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], 
        ];
    } 

    public function mount(): void
    {
        $this->sort = $this->normalizeSort($this->sort);
        $this->search = trim((string) $this->search);
    }

    public function updatedPerPage(): void
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

    public function create(): void
    {
        // Mulai flow tambah baru: bersihkan form dan validasi
        $this->resetForm(); 
        $this->editMode = false; 
        $this->resetValidation(); 
    } 

    public function store(): void
    {
        // Simpan atau perbarui author dan tangani upload foto (opsional) dalam satu transaksi
        $uploadDirectory = 'img/author'; 
        $this->validate(); 

        $imagePath = $this->existingFoto; 
        if ($this->foto instanceof TemporaryUploadedFile) { 
            $imagePath = $this->storeImageAndReturnName(
                $this->foto,
                $uploadDirectory,
                $imagePath
            ); 
        }
        $imagePath = $this->onlyFilename($uploadDirectory, $imagePath); 

        $payload = [
            'nama_author' => trim($this->nama_author), 
            'email_author' => $this->email_author ? trim($this->email_author) : null, 
            'no_telp' => $this->no_telp ? trim($this->no_telp) : null, 
            'alamat' => $this->alamat ? trim($this->alamat) : null, 
            'foto' => $imagePath, 
        ];

        DB::transaction(function () use ($payload) { 
            if ($this->authorId) { 
                $author = Author::findOrFail($this->authorId); 
                $author->update($payload); 
            } else { 
                $author = Author::create($payload); 
                $this->authorId = $author->id; 
            }
        });

        $this->flashSuccess($this->editMode ? 'Data author berhasil diperbarui.' : 'Data author berhasil disimpan.');

        $this->dispatch('close-modal', id: 'modal-form'); 
        $this->resetForm(); 
    } 

    public function edit(int $id): void
    {
        // Muat data author terpilih ke form untuk proses edit
        $this->resetValidation(); 

        $author = Author::findOrFail($id); 

        $this->editMode = true; 
        $this->authorId = $author->id; 
        $this->nama_author = $author->nama_author; 
        $this->email_author = $author->email_author; 
        $this->no_telp = $author->no_telp; 
        $this->alamat = $author->alamat; 
        $this->existingFoto = $author->foto; 
        $this->foto = null; 
    } 

    public function delete(int $id): void
    {
        // Hapus author beserta file foto (jika ada) di dalam transaksi
        $author = Author::findOrFail($id); 

        DB::transaction(function () use ($author) { 
            if ($author->foto) { 
                $this->deleteImage('img/author', $author->foto); 
            }

            $author->delete(); 
        });

        $this->flashSuccess('Data author berhasil dihapus.');
        $this->resetForm(); 
    } 

    public function updatedFoto(): void
    {
        if ($this->foto instanceof TemporaryUploadedFile) { 
            $this->validateOnly('foto'); 
        }
    } 

    #[Computed]
    public function getListAuthorProperty()
    {
        // Data untuk tabel: terapkan pencarian, urutan, dan pagination
        [$sortField, $sortDirection] = $this->resolveSort();

        return Author::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('nama_author', 'like', $term)
                        ->orWhere('email_author', 'like', $term)
                        ->orWhere('no_telp', 'like', $term);
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($this->perPage); 
    } 

    public function render()
    {
        return view('livewire.super-admin.manajemen-author', [
            'listAuthor' => $this->listAuthor, 
        ]);
    } 

    private function normalizeSort($value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'created_at_desc';
    }

    private function resolveSort(): array
    {
        return match ($this->sort) {
            'created_at_asc' => ['created_at', 'asc'],
            'nama_author_asc' => ['nama_author', 'asc'],
            'nama_author_desc' => ['nama_author', 'desc'],
            default => ['created_at', 'desc'],
        };
    }

    private function resetForm(): void
    {
        $this->reset([
            'authorId',
            'nama_author',
            'email_author',
            'no_telp',
            'alamat',
            'foto',
            'existingFoto',
            'editMode',
        ]); 
        $this->resetErrorBag(); 
        $this->resetValidation(); 
    } 
}
