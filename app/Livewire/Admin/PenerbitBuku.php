<?php

namespace App\Livewire\Admin;

use App\Models\Penerbit;
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

class PenerbitBuku extends Component
{
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
        'logo.max' => 'Ukuran logo maksimal 2MB.',
    ];

    protected function rules(): array
    {
        return [
            'nama_penerbit' => [
                'required', // Nama penerbit wajib diisi
                'string', // Harus berupa teks
                'max:255', // Maksimal 255 karakter
                Rule::unique('penerbit', 'nama_penerbit')->ignore($this->penerbitId), // Harus unik, abaikan saat edit
            ],
            'deskripsi' => ['required', 'string'], // Deskripsi wajib dan harus berupa string
            'tahun_hakcipta' => ['required', 'integer'], // Tahun hak cipta wajib dan harus integer
            'logo' => $this->editMode
                ? ['nullable', 'image', 'max:2048'] // Logo opsional saat edit, harus gambar maks 2MB
                : ['required', 'image', 'max:2048'], // Logo wajib saat create, harus gambar maks 2MB
        ];
    } // Aturan validasi untuk form penerbit

    public function mount(): void
    {
        $this->sort = $this->normalizeSort($this->sort);
        $this->search = trim((string) $this->search);
    }

    public function updatedPerPage(): void
    {
        $this->resetPage(); // Reset pagination ke halaman pertama saat jumlah item per halaman berubah
    } // Reset pagination saat jumlah item per halaman berubah

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
        $this->resetForm(); // Reset form ke kondisi awal
        $this->editMode = false; // Nonaktifkan mode edit
        $this->resetValidation(); // Hapus pesan validasi sebelumnya
    } // Reset form untuk membuat penerbit baru

    public function store(): void
    {
        $this->validate(); // Jalankan validasi pada input

        $logoPath = $this->existingLogo; // Gunakan logo lama sebagai default
        if ($this->logo instanceof TemporaryUploadedFile) { // Jika ada upload logo baru
            Storage::disk('public')->makeDirectory('admin/logo-penerbit'); // Buat direktori jika belum ada
            if ($logoPath) { // Hapus logo lama jika ada
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $this->logo->store('admin/logo-penerbit', 'public'); // Simpan logo baru
        }

        $payload = [
            'nama_penerbit' => trim($this->nama_penerbit), // Normalisasi nama penerbit
            'deskripsi' => trim($this->deskripsi), // Normalisasi deskripsi
            'tahun_hakcipta' => (int) $this->tahun_hakcipta, // Konversi tahun hak cipta ke integer
            'logo' => $logoPath, // Path logo yang akan disimpan
        ];

        DB::transaction(function () use ($payload) { // Jalankan dalam transaksi database
            if ($this->penerbitId) { // Jika dalam mode edit
                $penerbit = Penerbit::findOrFail($this->penerbitId); // Ambil penerbit yang akan diupdate
                $penerbit->update($payload); // Update data penerbit
            } else { // Jika dalam mode create
                $penerbit = Penerbit::create($payload); // Buat penerbit baru
                $this->penerbitId = $penerbit->id; // Simpan ID penerbit baru
            }
        });

        session()->flash(
            'message',
            $this->editMode ? 'Penerbit berhasil diperbarui.' : 'Penerbit berhasil ditambahkan.' // Tampilkan pesan sukses
        );

        $this->dispatch('close-modal', id: 'modal-form'); // Kirim event untuk menutup modal
        $this->resetForm(); // Reset form setelah disimpan
    } // Simpan data penerbit baru atau perbarui yang sudah ada

    public function edit(int $id): void
    {
        $this->resetValidation(); // Hapus pesan validasi sebelumnya
        $penerbit = Penerbit::findOrFail($id); // Ambil data penerbit berdasarkan ID

        $this->editMode = true; // Aktifkan mode edit
        $this->penerbitId = $penerbit->id; // Set ID penerbit yang akan diedit
        $this->nama_penerbit = $penerbit->nama_penerbit; // Muat nama penerbit
        $this->deskripsi = $penerbit->deskripsi; // Muat deskripsi penerbit
        $this->tahun_hakcipta = $penerbit->tahun_hakcipta; // Muat tahun hak cipta
        $this->existingLogo = $penerbit->logo; // Muat path logo penerbit
        $this->logo = null; // Reset upload logo
    } // Muat data penerbit untuk mode edit

    public function delete(int $id): void
    {
        $penerbit = Penerbit::findOrFail($id); // Ambil data penerbit berdasarkan ID

        if ($penerbit->logo) { // Jika penerbit memiliki logo
            Storage::disk('public')->delete($penerbit->logo); // Hapus file logo dari storage
        }

        $penerbit->delete(); // Hapus data penerbit dari database

        session()->flash('message', 'Penerbit berhasil dihapus.'); // Tampilkan pesan sukses
        $this->resetForm(); // Reset form setelah penghapusan
    } // Hapus data penerbit

    public function updatedLogo(): void
    {
        if ($this->logo instanceof TemporaryUploadedFile) { // Jika ada file logo yang diupload
            $this->validateOnly('logo'); // Validasi hanya field logo
        }
    } // Validasi file logo saat diupload

    #[Computed]
    public function listPenerbit()
    {
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
            ->paginate($this->perPage); // Ambil data penerbit sesuai pilihan sort
    } // Ambil daftar penerbit dengan pagination

    public function render()
    {
        return view('livewire.admin.penerbit');
    } // Render tampilan komponen

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
        ]); // Reset semua properti form ke nilai awal
        $this->resetErrorBag(); // Hapus pesan error
        $this->resetValidation(); // Hapus status validasi
    } // Reset form ke kondisi awal

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
