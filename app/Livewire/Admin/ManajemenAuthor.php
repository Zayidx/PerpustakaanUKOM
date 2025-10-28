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
                'required', // Nama author wajib diisi
                'string', // Harus berupa teks
                'max:255', // Maksimal 255 karakter
                Rule::unique('authors', 'nama_author')->ignore($this->authorId), // Harus unik, abaikan saat edit
            ],
            'email_author' => ['nullable', 'email', 'max:255'], // Email opsional, validasi format email
            'no_telp' => ['nullable', 'string', 'max:20'], // Nomor telepon opsional, maksimal 20 karakter
            'alamat' => ['nullable', 'string', 'max:255'], // Alamat opsional, maksimal 255 karakter
            'foto' => ['nullable', 'image', 'max:2048'], // Foto opsional, harus berupa gambar maks 2MB
        ];
    } // Aturan validasi untuk form author

    public function updatedPerPage(): void
    {
        $this->resetPage(); // Reset pagination ke halaman pertama saat jumlah item per halaman berubah
    } // Reset pagination saat jumlah item per halaman berubah

    public function create(): void
    {
        $this->resetForm(); // Reset form ke kondisi awal
        $this->editMode = false; // Nonaktifkan mode edit
        $this->resetValidation(); // Hapus pesan validasi sebelumnya
    } // Reset form untuk membuat author baru

    public function store(): void
    {
        $this->validate(); // Jalankan validasi pada input

        $imagePath = $this->existingFoto; // Gunakan foto lama sebagai default
        if ($this->foto instanceof TemporaryUploadedFile) { // Jika ada upload foto baru
            Storage::disk('public')->makeDirectory('admin/foto-author'); // Buat direktori jika belum ada
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath); // Hapus foto lama
            }
            $imagePath = $this->foto->store('admin/foto-author', 'public'); // Simpan foto baru
        }

        $payload = [
            'nama_author' => trim($this->nama_author), // Normalisasi nama author
            'email_author' => $this->email_author ? trim($this->email_author) : null, // Normalisasi email jika ada
            'no_telp' => $this->no_telp ? trim($this->no_telp) : null, // Normalisasi nomor telepon jika ada
            'alamat' => $this->alamat ? trim($this->alamat) : null, // Normalisasi alamat jika ada
            'foto' => $imagePath, // Path foto yang akan disimpan
        ];

        DB::transaction(function () use ($payload) { // Jalankan dalam transaksi database
            if ($this->authorId) { // Jika dalam mode edit
                $author = Author::findOrFail($this->authorId); // Ambil author yang akan diupdate
                $author->update($payload); // Update data author
            } else { // Jika dalam mode create
                $author = Author::create($payload); // Buat author baru
                $this->authorId = $author->id; // Simpan ID author baru
            }
        });

        session()->flash(
            'message',
            $this->editMode ? 'Data author berhasil diperbarui.' : 'Data author berhasil disimpan.' // Tampilkan pesan sukses
        );

        $this->dispatch('close-modal', id: 'modal-form'); // Kirim event untuk menutup modal
        $this->resetForm(); // Reset form setelah disimpan
    } // Simpan data author baru atau perbarui yang sudah ada

    public function edit(int $id): void
    {
        $this->resetValidation(); // Hapus pesan validasi sebelumnya

        $author = Author::findOrFail($id); // Ambil data author berdasarkan ID

        $this->editMode = true; // Aktifkan mode edit
        $this->authorId = $author->id; // Set ID author yang akan diedit
        $this->nama_author = $author->nama_author; // Muat nama author
        $this->email_author = $author->email_author; // Muat email author
        $this->no_telp = $author->no_telp; // Muat nomor telepon author
        $this->alamat = $author->alamat; // Muat alamat author
        $this->existingFoto = $author->foto; // Muat path foto author
        $this->foto = null; // Reset upload foto
    } // Muat data author untuk mode edit

    public function delete(int $id): void
    {
        $author = Author::findOrFail($id); // Ambil data author berdasarkan ID

        DB::transaction(function () use ($author) { // Jalankan dalam transaksi database
            if ($author->foto) { // Jika author memiliki foto
                Storage::disk('public')->delete($author->foto); // Hapus file foto dari storage
            }

            $author->delete(); // Hapus data author dari database
        });

        session()->flash('message', 'Data author berhasil dihapus.'); // Tampilkan pesan sukses
        $this->resetForm(); // Reset form setelah penghapusan
    } // Hapus data author

    public function updatedFoto(): void
    {
        if ($this->foto instanceof TemporaryUploadedFile) { // Jika ada file yang diupload
            $this->validateOnly('foto'); // Validasi hanya field foto
        }
    } // Validasi file foto saat diupload

    #[Computed]
    public function getListAuthorProperty()
    {
        return Author::orderByDesc('created_at')->paginate($this->perPage); // Ambil data author dan urutkan terbaru dulu
    } // Ambil daftar author dengan pagination

    public function render()
    {
        return view('livewire.admin.manajemen-author', [
            'listAuthor' => $this->listAuthor, // Kirim daftar author ke view
        ]);
    } // Render tampilan komponen dengan data author

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
        ]); // Reset semua properti form ke nilai awal
        $this->resetErrorBag(); // Hapus pesan error
        $this->resetValidation(); // Hapus status validasi
    } // Reset form ke kondisi awal
}
