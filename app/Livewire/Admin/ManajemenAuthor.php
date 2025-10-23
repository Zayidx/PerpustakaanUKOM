<?php

namespace App\Livewire\Admin;

use App\Models\Author;
use Illuminate\Support\Facades\DB;
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

class ManajemenAuthor extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Manajemen Author')]
    #[Url(except: "")]
    #[Layout('components.layouts.dashboard-layouts')]
    public $perPage = 5;

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
            'foto' => ['nullable', 'image', 'max:2048'],
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

        $imagePath = $this->existingFoto;
        if ($this->foto instanceof TemporaryUploadedFile) {
            Storage::disk('public')->makeDirectory('admin/foto-author');
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $this->foto->store('admin/foto-author', 'public');
        }

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

        session()->flash(
            'message',
            $this->editMode ? 'Data author berhasil diperbarui.' : 'Data author berhasil disimpan.'
        );

        $this->dispatch('close-modal', id: 'modal-form');
        $this->resetForm();
    }

    public function edit(int $id): void
    {
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
        $author = Author::findOrFail($id);

        DB::transaction(function () use ($author) {
            if ($author->foto) {
                Storage::disk('public')->delete($author->foto);
            }

            $author->delete();
        });

        session()->flash('message', 'Data author berhasil dihapus.');
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
        return Author::orderByDesc('created_at')->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.admin.manajemen-author', [
            'listAuthor' => $this->listAuthor,
        ]);
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
