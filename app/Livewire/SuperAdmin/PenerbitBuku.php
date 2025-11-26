<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\Concerns\HandlesAlerts;
use App\Livewire\Concerns\HandlesImageUploads;
use App\Models\Penerbit;
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

class PenerbitBuku extends Component
{
    use HandlesAlerts;
    use HandlesImageUploads;
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    #[Title('Manajemen Penerbit Buku')]
    #[Url(except: "")]
    #[Layout('components.layouts.dashboard-layouts')]
    public $perPage = 5;
    public string $search = '';
    public string $sort = 'created_at_desc';
    public array $sortOptions = [
        'created_at_desc' => 'Terbaru',
        'created_at_asc' => 'Terlama',
        'nama_penerbit_asc' => 'Nama A-Z',
        'nama_penerbit_desc' => 'Nama Z-A',
    ];

    public $penerbitId;
    public $nama_penerbit = '';
    public $deskripsi = '';
    public $logo;
    public $tahun_hakcipta;
    public $existingLogo = '';

    public $editMode = false;

    protected $messages = [
        'nama_penerbit.required' => 'Nama penerbit wajib diisi.',
        'nama_penerbit.unique' => 'Nama penerbit sudah digunakan.',
        'deskripsi.required' => 'Deskripsi wajib diisi.',
        'tahun_hakcipta.required' => 'Tahun hak cipta wajib diisi.',
        'tahun_hakcipta.integer' => 'Tahun hak cipta harus berupa angka.',
        'logo.required' => 'Logo wajib diunggah.',
        'logo.image' => 'Logo harus berupa file gambar.',
        'logo.mimes' => 'Format logo harus JPG atau PNG.',
        'logo.max' => 'Ukuran logo maksimal 2MB.',
    ];

    protected function rules(): array
    {
        return [
            'nama_penerbit' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('penerbit', 'nama_penerbit')->ignore($this->penerbitId), 
            ],
            'deskripsi' => ['required', 'string'], 
            'tahun_hakcipta' => ['required', 'integer'], 
            'logo' => $this->editMode
                ? ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'] 
                : ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], 
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
        // Mulai flow tambah penerbit: reset form dan status validasi
        $this->resetForm(); 
        $this->editMode = false; 
        $this->resetValidation(); 
    } 

    public function store(): void
    {
        // Simpan atau perbarui penerbit beserta upload logo (opsional)
        $uploadDirectory = 'admin/logo-penerbit'; 
        $this->validate(); 

        $logoPath = $this->existingLogo; 
        if ($this->logo instanceof TemporaryUploadedFile) { 
            $logoPath = $this->storeImageAndReturnName(
                $this->logo,
                $uploadDirectory,
                $logoPath
            ); 
        }
        $logoPath = $this->onlyFilename($uploadDirectory, $logoPath); 

        $payload = [
            'nama_penerbit' => trim($this->nama_penerbit), 
            'deskripsi' => trim($this->deskripsi), 
            'tahun_hakcipta' => (int) $this->tahun_hakcipta, 
            'logo' => $logoPath, 
        ];

        DB::transaction(function () use ($payload) { 
            if ($this->penerbitId) { 
                $penerbit = Penerbit::findOrFail($this->penerbitId); 
                $penerbit->update($payload); 
            } else { 
                $penerbit = Penerbit::create($payload); 
                $this->penerbitId = $penerbit->id; 
            }
        });

        $this->flashSuccess($this->editMode ? 'Penerbit berhasil diperbarui.' : 'Penerbit berhasil ditambahkan.');

        $this->dispatch('close-modal', id: 'modal-form'); 
        $this->resetForm(); 
    } 

    public function edit(int $id): void
    {
        // Muat data penerbit terpilih ke form edit
        $this->resetValidation(); 
        $penerbit = Penerbit::findOrFail($id); 

        $this->editMode = true; 
        $this->penerbitId = $penerbit->id; 
        $this->nama_penerbit = $penerbit->nama_penerbit; 
        $this->deskripsi = $penerbit->deskripsi; 
        $this->tahun_hakcipta = $penerbit->tahun_hakcipta; 
        $this->existingLogo = $penerbit->logo; 
        $this->logo = null; 
    } 

    public function delete(int $id): void
    {
        // Hapus penerbit dan logo jika tidak dipakai lagi
        $penerbit = Penerbit::findOrFail($id); 

        if ($penerbit->logo) { 
            $this->deleteImage('admin/logo-penerbit', $penerbit->logo); 
        }

        $penerbit->delete();

        $this->flashSuccess('Penerbit berhasil dihapus.');
        $this->resetForm(); 
    } 

    public function updatedLogo(): void
    {
        if ($this->logo instanceof TemporaryUploadedFile) { 
            $this->validateOnly('logo'); 
        }
    } 

    #[Computed]
    public function listPenerbit()
    {
        // Data tabel: pencarian, urutan, dan pagination
        [$sortField, $sortDirection] = $this->resolveSort();

        return Penerbit::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('nama_penerbit', 'like', $term)
                        ->orWhere('deskripsi', 'like', $term)
                        ->orWhere('tahun_hakcipta', 'like', $term);
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($this->perPage); 
    } 

    public function render()
    {
        return view('livewire.super-admin.penerbit');
    } 

    private function resetForm(): void
    {
        $this->reset([
            'penerbitId',
            'nama_penerbit',
            'deskripsi',
            'logo',
            'tahun_hakcipta',
            'existingLogo',
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
            'nama_penerbit_asc' => ['nama_penerbit', 'asc'],
            'nama_penerbit_desc' => ['nama_penerbit', 'desc'],
            default => ['created_at', 'desc'],
        };
    }
}
