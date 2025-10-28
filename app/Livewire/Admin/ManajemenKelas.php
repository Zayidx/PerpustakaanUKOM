<?php

namespace App\Livewire\Admin;

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
        $this->resetValidation(); // Hapus pesan validasi sebelumnya

        $kelas = Kelas::findOrFail($id); // Ambil data kelas berdasarkan ID

        $this->kelas_id = $kelas->id; // Set ID kelas yang akan diedit
        $this->nama_kelas = $kelas->nama_kelas; // Muat nama kelas
        $this->tingkat = $kelas->tingkat; // Muat tingkat kelas
    } // Muat data kelas untuk mode edit

    public function store(): void
    {
        $this->validate(); // Jalankan validasi pada input

        $payload = [
            'nama_kelas' => trim($this->nama_kelas), // Normalisasi nama kelas
            'tingkat' => trim($this->tingkat), // Normalisasi tingkat kelas
        ];

        if ($this->kelas_id) { // Jika dalam mode edit
            $kelas = Kelas::findOrFail($this->kelas_id); // Ambil kelas yang akan diupdate
            $kelas->update($payload); // Update data kelas
        } else { // Jika dalam mode create
            $kelas = Kelas::create($payload); // Buat kelas baru
            $this->kelas_id = $kelas->id; // Simpan ID kelas baru
        }

        $this->resetForm(); // Reset form setelah disimpan
        session()->flash('message', 'Data kelas berhasil disimpan.'); // Tampilkan pesan sukses
        $this->dispatch('close-modal', id: 'modal-form-kelas'); // Kirim event untuk menutup modal
    } // Simpan data kelas baru atau perbarui yang sudah ada

    public function delete(int $id): void
    {
        $kelas = Kelas::withCount('siswa')->findOrFail($id); // Ambil data kelas beserta jumlah siswa

        if ($kelas->siswa_count > 0) { // Cek apakah kelas masih memiliki siswa aktif
            session()->flash('message', 'Kelas masih memiliki siswa aktif dan tidak dapat dihapus.');
            return;
        }

        $kelas->delete(); // Hapus data kelas dari database

        session()->flash('message', 'Data kelas berhasil dihapus.'); // Tampilkan pesan sukses
        $this->resetForm(); // Reset form setelah penghapusan
        $this->resetPage(); // Reset pagination
    } // Hapus data kelas jika tidak memiliki siswa aktif

    #[Computed]
    public function listKelas()
    {
        [$sortField, $sortDirection] = $this->resolveSort(); // Ambil field dan arah sorting

        return Kelas::query()
            ->when($this->search !== '', function ($query) { // Jika ada kata kunci pencarian
                $searchTerm = '%' . $this->search . '%';

                $query->where(function ($query) use ($searchTerm) { // Cari berdasarkan nama kelas atau tingkat
                    $query->where('nama_kelas', 'like', $searchTerm) // Cari berdasarkan nama kelas
                        ->orWhere('tingkat', 'like', $searchTerm); // Cari berdasarkan tingkat
                });
            })
            ->orderBy($sortField, $sortDirection) // Terapkan sorting
            ->paginate($this->perPage); // Terapkan pagination
    } // Ambil daftar kelas dengan pencarian, sorting, dan pagination

    public function render()
    {
        return view('livewire.admin.manajemen-kelas');
    } // Render tampilan komponen

    private function normalizeSort(string $value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'nama_kelas_asc'; // Kembalikan nilai sort valid atau default
    } // Pastikan nilai sort valid sesuai opsi yang tersedia

    private function resolveSort(): array
    {
        return match ($this->sort) { // Kembalikan field dan arah sorting berdasarkan opsi
            'nama_kelas_desc' => ['nama_kelas', 'desc'], // Urutkan berdasarkan nama kelas descending
            'created_at_desc' => ['created_at', 'desc'], // Urutkan berdasarkan created_at descending
            'created_at_asc' => ['created_at', 'asc'], // Urutkan berdasarkan created_at ascending
            default => ['nama_kelas', 'asc'], // Default: urutkan berdasarkan nama kelas ascending
        };
    } // Ambil field dan arah sorting

    private function normalizePerPage($value): int
    {
        $value = (int) $value; // Konversi nilai ke integer

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0]; // Kembalikan nilai valid atau default
    } // Pastikan nilai perPage valid sesuai opsi yang tersedia

    private function resetForm(): void
    {
        $this->reset([
            'kelas_id',
            'nama_kelas',
            'tingkat',
        ]); // Reset semua properti form ke nilai awal

        $this->resetErrorBag(); // Hapus pesan error sebelumnya
        $this->resetValidation(); // Hapus status validasi
    } // Reset form ke kondisi awal

    public function render()
    {
        return view('livewire.admin.manajemen-kelas');
    }

    protected function rules(): array
    {
        return [
            'nama_kelas' => [
                'required', // Nama kelas wajib diisi
                'string', // Harus berupa teks
                'max:100', // Maksimal 100 karakter
                Rule::unique('kelas', 'nama_kelas')->ignore($this->kelas_id), // Harus unik, abaikan saat edit
            ],
            'tingkat' => ['required', 'string', 'max:50'], // Tingkat wajib, harus string, maksimal 50 karakter
        ];
    } // Aturan validasi untuk form kelas

    public function updatedPerPage($value): void
    {
        $this->perPage = $this->normalizePerPage($value); // Pastikan nilai perPage valid sesuai opsi
        $this->resetPage(); // Reset pagination ke halaman pertama saat jumlah per halaman berubah
    } // Atur jumlah item per halaman dan reset pagination

    public function updatedSearch(): void
    {
        $this->search = trim((string) $this->search); // Hapus spasi di awal/akhir kata kunci pencarian
        $this->resetPage(); // Reset pagination ke halaman pertama saat pencarian berubah
    } // Hapus spasi pada input pencarian dan reset pagination

    public function updatedSort($value): void
    {
        $this->sort = $this->normalizeSort($value); // Pastikan nilai sort valid
        $this->resetPage(); // Reset pagination ke halaman pertama saat sorting berubah
    } // Atur opsi sorting dan reset pagination

    public function create(): void
    {
        $this->resetForm(); // Reset form ke kondisi awal
        $this->resetValidation(); // Hapus pesan validasi sebelumnya
    } // Reset form untuk membuat kelas baru

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
}
