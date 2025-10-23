<?php

namespace App\Livewire\Admin;

use App\Models\Author;
use App\Models\Buku as BukuModel;
use App\Models\KategoriBuku;
use App\Models\Penerbit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Manajemen Buku')]
    #[Url(except: "")]
    #[Layout('components.layouts.dashboard-layouts')]
    public $perPage = 5;

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
    public $editMode = false;

    protected $messages = [
        'nama_buku.required' => 'Nama buku wajib diisi.',
        'nama_buku.unique' => 'Nama buku sudah digunakan.',
        'author_id.required' => 'Penulis wajib dipilih.',
        'kategori_id.required' => 'Kategori wajib dipilih.',
        'penerbit_id.required' => 'Penerbit wajib dipilih.',
        'deskripsi.required' => 'Deskripsi wajib diisi.',
        'tanggal_terbit.required' => 'Tanggal terbit wajib diisi.',
        'tanggal_terbit.date' => 'Tanggal terbit harus berupa format tanggal yang valid.',
        'cover_depan.image' => 'Cover depan harus berupa file gambar.',
        'cover_depan.max' => 'Ukuran cover depan maksimal 2MB.',
        'cover_belakang.image' => 'Cover belakang harus berupa file gambar.',
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
            'cover_depan' => ['nullable', 'image', 'max:2048'],
            'cover_belakang' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function updatedPerPage(): void
    {
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

        Storage::disk('public')->makeDirectory('admin/cover-buku');

        $coverDepanPath = $this->existingCoverDepan;
        if ($this->cover_depan instanceof TemporaryUploadedFile) {
            if ($coverDepanPath) {
                Storage::disk('public')->delete($coverDepanPath);
            }
            $coverDepanPath = $this->cover_depan->store('admin/cover-buku', 'public');
        }

        $coverBelakangPath = $this->existingCoverBelakang;
        if ($this->cover_belakang instanceof TemporaryUploadedFile) {
            if ($coverBelakangPath) {
                Storage::disk('public')->delete($coverBelakangPath);
            }
            $coverBelakangPath = $this->cover_belakang->store('admin/cover-buku', 'public');
        }

        $payload = [
            'nama_buku' => trim($this->nama_buku),
            'author_id' => $this->author_id,
            'kategori_id' => $this->kategori_id,
            'penerbit_id' => $this->penerbit_id,
            'deskripsi' => trim($this->deskripsi),
            'tanggal_terbit' => $this->tanggal_terbit,
            'cover_depan' => $coverDepanPath,
            'cover_belakang' => $coverBelakangPath,
        ];

        try {
            if ($this->editMode && $this->bukuId) {
                $buku = BukuModel::findOrFail($this->bukuId);
                $buku->update($payload);
                session()->flash('message', 'Data buku berhasil diperbarui.');
            } else {
                $buku = BukuModel::create($payload);
                $this->bukuId = $buku->id;
                session()->flash('message', 'Data buku berhasil ditambahkan.');
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

            session()->flash('message', 'Terjadi kesalahan saat menyimpan data buku.');
        }
    }

    public function edit(int $id): void
    {
        $this->resetValidation();

        $buku = BukuModel::findOrFail($id);

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
        $this->cover_depan = null;
        $this->cover_belakang = null;
    }

    public function delete(int $id): void
    {
        $buku = BukuModel::findOrFail($id);

        if ($buku->cover_depan) {
            Storage::disk('public')->delete($buku->cover_depan);
        }

        if ($buku->cover_belakang) {
            Storage::disk('public')->delete($buku->cover_belakang);
        }

        $buku->delete();

        session()->flash('message', 'Data buku berhasil dihapus.');
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
        return BukuModel::with(['author', 'kategori', 'penerbit'])
            ->orderBy('nama_buku', 'asc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.admin.buku', [
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
            'editMode',
        ]);
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
