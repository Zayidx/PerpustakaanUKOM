<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\Concerns\HandlesAlerts;
use App\Livewire\Concerns\HandlesImageUploads;
use App\Models\Author;
use App\Models\Buku as BukuModel;
use App\Models\KategoriBuku;
use App\Models\Penerbit;
use App\Support\CoverUrlResolver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ManajemenBuku extends Component
{
    use HandlesAlerts;
    use HandlesImageUploads;
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Manajemen Buku')]
    #[Url(except: "")]
    #[Layout('components.layouts.dashboard-layouts')]
    public $perPage = 5;
    public string $search = '';
    public string $sort = 'created_at_desc';
    public array $sortOptions = [
        'created_at_desc' => 'Terbaru',
        'created_at_asc' => 'Terlama',
        'nama_buku_asc' => 'Judul A-Z',
        'nama_buku_desc' => 'Judul Z-A',
    ];

    public $bukuId;
    public $nama_buku = '';
    public $author_id;
    public $kategori_id;
    public $penerbit_id;
    public $deskripsi = '';
    public $tanggal_terbit;
    public $cover_depan;
    public $cover_belakang;
    public $existingCoverDepan = '';
    public $existingCoverBelakang = '';
    public $existingCoverDepanUrl = '';
    public $existingCoverBelakangUrl = '';
    public $editMode = false;
    public $stok = 0;

    protected $messages = [
        'nama_buku.required' => 'Nama buku wajib diisi.',
        'nama_buku.unique' => 'Nama buku sudah digunakan.',
        'author_id.required' => 'Penulis wajib dipilih.',
        'kategori_id.required' => 'Kategori wajib dipilih.',
        'penerbit_id.required' => 'Penerbit wajib dipilih.',
        'deskripsi.required' => 'Deskripsi wajib diisi.',
        'tanggal_terbit.required' => 'Tanggal terbit wajib diisi.',
        'tanggal_terbit.date' => 'Tanggal terbit harus berupa format tanggal yang valid.',
        'stok.required' => 'Stok buku wajib diisi.',
        'stok.integer' => 'Stok buku harus berupa angka.',
        'stok.min' => 'Stok buku tidak boleh kurang dari 0.',
        'cover_depan.image' => 'Cover depan harus berupa file gambar.',
        'cover_depan.mimes' => 'Format cover depan harus JPG atau PNG.',
        'cover_depan.max' => 'Ukuran cover depan maksimal 2MB.',
        'cover_belakang.image' => 'Cover belakang harus berupa file gambar.',
        'cover_belakang.mimes' => 'Format cover belakang harus JPG atau PNG.',
        'cover_belakang.max' => 'Ukuran cover belakang maksimal 2MB.',
    ];

    protected function rules(): array
    {
        return [
            'nama_buku' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('buku', 'nama_buku')->ignore($this->bukuId), 
            ],
            'author_id' => ['required', 'exists:authors,id'], 
            'kategori_id' => ['required', 'exists:kategori_buku,id'], 
            'penerbit_id' => ['required', 'exists:penerbit,id'], 
            'deskripsi' => ['required', 'string'], 
            'tanggal_terbit' => ['required', 'date'], 
            'stok' => ['required', 'integer', 'min:0'], 
            'cover_depan' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], 
            'cover_belakang' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], 
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
        $this->resetForm(); 
        $this->editMode = false; 
        $this->resetValidation(); 
    } 

    public function store(): void
    {
        $this->validate(); 

        $coverDirectory = 'admin/cover-buku'; 

        $coverDepanPath = $this->existingCoverDepan; 
        if ($this->cover_depan instanceof TemporaryUploadedFile) { 
            if ($this->shouldDeleteCover($coverDepanPath, 'cover_depan', $this->bukuId)) { 
                $this->deleteImage($coverDirectory, $coverDepanPath);
            }
            $coverDepanPath = $this->storeImageAndReturnName(
                $this->cover_depan,
                $coverDirectory,
                null,
                false
            ); 
        }

        $coverBelakangPath = $this->existingCoverBelakang; 
        if ($this->cover_belakang instanceof TemporaryUploadedFile) { 
            if ($this->shouldDeleteCover($coverBelakangPath, 'cover_belakang', $this->bukuId)) { 
                $this->deleteImage($coverDirectory, $coverBelakangPath);
            }
            $coverBelakangPath = $this->storeImageAndReturnName(
                $this->cover_belakang,
                $coverDirectory,
                null,
                false
            ); 
        }
        $coverDepanPath = $this->onlyFilename($coverDirectory, $coverDepanPath); 
        $coverBelakangPath = $this->onlyFilename($coverDirectory, $coverBelakangPath); 

        $payload = [
            'nama_buku' => trim($this->nama_buku), 
            'author_id' => $this->author_id, 
            'kategori_id' => $this->kategori_id, 
            'penerbit_id' => $this->penerbit_id, 
            'deskripsi' => trim($this->deskripsi), 
            'tanggal_terbit' => $this->tanggal_terbit, 
            'cover_depan' => $coverDepanPath, 
            'cover_belakang' => $coverBelakangPath, 
            'stok' => (int) $this->stok, 
        ];

        try {
            if ($this->editMode && $this->bukuId) { 
                $buku = BukuModel::findOrFail($this->bukuId); 
                $buku->update($payload); 
                $this->flashSuccess('Data buku berhasil diperbarui.');
            } else { 
                $buku = BukuModel::create($payload); 
                $this->bukuId = $buku->id; 
                $this->flashSuccess('Data buku berhasil ditambahkan.');
            }

            $this->dispatch('close-modal', id: 'modal-form'); 
            $this->resetForm(); 
        } catch (\Throwable $e) { 
            Log::error('Gagal menyimpan data buku', [ 
                'error' => $e->getMessage(),
                'nama_buku' => $this->nama_buku,
                'author_id' => $this->author_id,
                'kategori_id' => $this->kategori_id,
                'penerbit_id' => $this->penerbit_id,
            ]);

            $this->flashError('Terjadi kesalahan saat menyimpan data buku.');
        }
    } 

    public function edit(int $id): void
    {
        $this->resetValidation(); 

        $buku = BukuModel::findOrFail($id); 

        $coverDirectory = 'admin/cover-buku'; 

        $this->editMode = true; 
        $this->bukuId = $buku->id; 
        $this->nama_buku = $buku->nama_buku; 
        $this->author_id = $buku->author_id; 
        $this->kategori_id = $buku->kategori_id; 
        $this->penerbit_id = $buku->penerbit_id; 
        $this->deskripsi = $buku->deskripsi; 
        $this->tanggal_terbit = $buku->tanggal_terbit?->format('Y-m-d'); 
        $this->existingCoverDepan = $buku->cover_depan; 
        $this->existingCoverBelakang = $buku->cover_belakang; 
        $this->existingCoverDepanUrl = CoverUrlResolver::resolve(
            $this->normalizeUploadPath($coverDirectory, $buku->cover_depan)
        ); 
        $this->existingCoverBelakangUrl = CoverUrlResolver::resolve(
            $this->normalizeUploadPath($coverDirectory, $buku->cover_belakang)
        ); 
        $this->stok = $buku->stok; 
        $this->cover_depan = null; 
        $this->cover_belakang = null; 
    } 

    public function delete(int $id): void
    {
        $buku = BukuModel::findOrFail($id); 

        $coverDirectory = 'admin/cover-buku'; 

        if ($buku->cover_depan && $this->shouldDeleteCover($buku->cover_depan, 'cover_depan', $buku->id)) { 
            $this->deleteImage($coverDirectory, $buku->cover_depan); 
        }

        if ($buku->cover_belakang && $this->shouldDeleteCover($buku->cover_belakang, 'cover_belakang', $buku->id)) { 
            $this->deleteImage($coverDirectory, $buku->cover_belakang); 
        }

        $buku->delete(); 

        $this->flashSuccess('Data buku berhasil dihapus.');
        $this->resetForm(); 
    } 

    public function updatedCoverDepan(): void
    {
        if ($this->cover_depan instanceof TemporaryUploadedFile) { 
            $this->validateOnly('cover_depan'); 
        }
    } 

    public function updatedCoverBelakang(): void
    {
        if ($this->cover_belakang instanceof TemporaryUploadedFile) { 
            $this->validateOnly('cover_belakang'); 
        }
    } 

    #[Computed]
    public function listBuku()
    {
        [$sortField, $sortDirection] = $this->resolveSort();

        return BukuModel::with(['author', 'kategori', 'penerbit']) 
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('nama_buku', 'like', $term)
                        ->orWhere('deskripsi', 'like', $term)
                        ->orWhereHas('author', function ($authorQuery) use ($term) {
                            $authorQuery->where('nama_author', 'like', $term);
                        })
                        ->orWhereHas('kategori', function ($kategoriQuery) use ($term) {
                            $kategoriQuery->where('nama_kategori_buku', 'like', $term);
                        })
                        ->orWhereHas('penerbit', function ($penerbitQuery) use ($term) {
                            $penerbitQuery->where('nama_penerbit', 'like', $term);
                        });
                });
            })
            ->orderBy($sortField, $sortDirection) 
            ->paginate($this->perPage); 
    } 

    public function render()
    {
        return view('livewire.super-admin.buku', [
            'listBuku' => $this->listBuku, 
            'authors' => Author::orderBy('nama_author', 'asc')->get(), 
            'kategori_buku' => KategoriBuku::orderBy('nama_kategori_buku', 'asc')->get(), 
            'penerbits' => Penerbit::orderBy('nama_penerbit', 'asc')->get(), 
        ]);
    } 

    private function resetForm(): void
    {
        $this->reset([
            'bukuId',
            'nama_buku',
            'author_id',
            'kategori_id',
            'penerbit_id',
            'deskripsi',
            'tanggal_terbit',
            'cover_depan',
            'cover_belakang',
            'existingCoverDepan',
            'existingCoverBelakang',
            'existingCoverDepanUrl',
            'existingCoverBelakangUrl',
            'editMode',
            'stok',
        ]); 
        $this->resetErrorBag(); 
        $this->resetValidation(); 
    } 

    private function shouldDeleteCover(?string $path, string $column, ?int $excludeId = null): bool
    {
        if (! $path) { 
            return false;
        }

        $normalized = ltrim($path, '/'); 

        if (Str::startsWith($normalized, 'assets/')) {
            return false;
        }

        $fileNames = [
            $normalized,
            basename($normalized),
        ];

        return ! BukuModel::query()
            ->where(function ($query) use ($column, $fileNames) {
                $query->whereIn($column, $fileNames);
            })
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->exists();
    } 

    private function normalizeSort($value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'created_at_desc';
    }

    private function resolveSort(): array
    {
        return match ($this->sort) {
            'created_at_asc' => ['created_at', 'asc'],
            'nama_buku_asc' => ['nama_buku', 'asc'],
            'nama_buku_desc' => ['nama_buku', 'desc'],
            default => ['created_at', 'desc'],
        };
    }
}
