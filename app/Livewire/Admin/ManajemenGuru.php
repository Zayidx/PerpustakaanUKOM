<?php

namespace App\Livewire\Admin;

use App\Models\RoleData;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ManajemenGuru extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Manajemen Guru')]
    #[Url(except: "")]
    #[Layout('components.layouts.dashboard-layouts')]
    public $perPage = 5;
    public array $perPageOptions = [5, 10, 25];
    public string $search = '';
    public string $genderFilter = 'all';
    public array $genderOptions = [
        'all' => 'Semua Gender',
        'Laki-laki' => 'Laki-laki',
        'Perempuan' => 'Perempuan',
    ];
    public string $sort = 'created_at_desc';
    public array $sortOptions = [
        'created_at_desc' => 'Terbaru',
        'created_at_asc' => 'Terlama',
        'nama_user_asc' => 'Nama A-Z',
        'nama_user_desc' => 'Nama Z-A',
        'mata_pelajaran_asc' => 'Mapel A-Z',
        'mata_pelajaran_desc' => 'Mapel Z-A',
    ];

    public $guru_id;
    public $user_id;
    public $nama_user = '';
    public $email_user = '';
    public $phone_number = '';
    public $password;
    public $password_confirmation;
    public $nip = '';
    public $jenis_kelamin = 'Laki-laki';
    public $mata_pelajaran = '';
    public $alamat = ''; // ✅ Tambahan baru
    public $foto;
    public $existingFoto = '';

    protected $messages = [
        'nama_user.required' => 'Nama guru wajib diisi.',
        'email_user.required' => 'Email wajib diisi.',
        'email_user.email' => 'Format email tidak valid.',
        'email_user.unique' => 'Email sudah digunakan.',
        'phone_number.required' => 'Nomor telepon wajib diisi.',
        'nip.required' => 'NIP wajib diisi.',
        'nip.unique' => 'NIP sudah terdaftar.',
        'mata_pelajaran.required' => 'Mata pelajaran wajib diisi.',
        'alamat.required' => 'Alamat wajib diisi.', // ✅ Tambahan baru
        'foto.image' => 'File harus berupa gambar.',
        'foto.max' => 'Ukuran foto maksimal 2MB.',
    ];

    protected function rules(): array
    {
        $passwordRules = $this->guru_id ? ['nullable', 'min:8'] : ['required', 'min:8']; // Password opsional saat edit, wajib saat create
        $passwordConfirmationRules = $this->password ? ['same:password'] : ['nullable']; // Konfirmasi password hanya dicek jika password diisi

        return [
            'nama_user' => ['required', 'string', 'max:255'], // Nama wajib diisi maksimal 255 karakter
            'email_user' => ['required', 'email', Rule::unique('users', 'email_user')->ignore($this->user_id)], // Email wajib dan harus unik, abaikan saat edit
            'phone_number' => ['required', 'string', 'max:20'], // Nomor telepon wajib diisi maksimal 20 karakter
            'password' => $passwordRules, // Aturan password berbeda saat create dan edit
            'password_confirmation' => $passwordConfirmationRules, // Aturan konfirmasi password
            'nip' => ['required', 'string', 'max:30', Rule::unique('guru', 'nip')->ignore($this->guru_id)], // NIP wajib dan unik, abaikan saat edit
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'], // Jenis kelamin wajib dan harus valid
            'mata_pelajaran' => ['required', 'string', 'max:100'], // Mata pelajaran wajib diisi maksimal 100 karakter
            'alamat' => ['required', 'string', 'max:255'], // Alamat wajib diisi maksimal 255 karakter // ✅ Tambahan baru
            'foto' => ['nullable', 'image', 'max:2048'], // Foto opsional, harus berupa gambar maks 2MB
        ];
    } // Aturan validasi untuk form guru

    public function mount(): void
    {
        $this->perPage = $this->normalizePerPage($this->perPage); // Pastikan nilai perPage valid
        $this->genderFilter = $this->normalizeGender($this->genderFilter); // Pastikan filter gender valid
        $this->sort = $this->normalizeSort($this->sort); // Pastikan opsi sort valid
        $this->search = trim((string) $this->search); // Normalisasi kata kunci pencarian
    } // Inisialisasi komponen dengan nilai valid

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

    public function updatedGenderFilter($value): void
    {
        $this->genderFilter = $this->normalizeGender($value); // Pastikan filter gender valid
        $this->resetPage(); // Reset pagination ke halaman pertama saat filter gender berubah
    } // Atur filter gender dan reset pagination

    public function updatedSort($value): void
    {
        $this->sort = $this->normalizeSort($value); // Pastikan opsi sort valid
        $this->resetPage(); // Reset pagination ke halaman pertama saat sorting berubah
    } // Atur opsi sorting dan reset pagination

    public function create()
    {
        $this->resetForm(); // Reset form ke kondisi awal
        $this->resetValidation(); // Hapus pesan validasi sebelumnya
    } // Reset form untuk membuat guru baru

    public function store()
    {
        $this->validate(); // Jalankan validasi pada input

        $roleId = RoleData::where('nama_role', 'Guru')->value('id'); // Ambil ID role guru
        if (!$roleId) { // Jika role guru tidak ditemukan
            session()->flash('message', 'Role Guru belum dikonfigurasi.');
            return;
        }

        $imagePath = $this->existingFoto; // Gunakan foto lama sebagai default
        if ($this->foto instanceof TemporaryUploadedFile) { // Jika ada upload foto baru
            Storage::disk('public')->makeDirectory('admin/foto-guru'); // Buat direktori jika belum ada
            if ($this->existingFoto) { // Hapus foto lama jika ada
                Storage::disk('public')->delete($this->existingFoto);
            }
            $imagePath = $this->foto->store('admin/foto-guru', 'public'); // Simpan foto baru
        }

        DB::transaction(function () use ($roleId, $imagePath) { // Jalankan dalam transaksi database
            if ($this->guru_id) { // Jika dalam mode edit
                // Edit guru
                $guru = Guru::with('user')->findOrFail($this->guru_id); // Ambil data guru dan user
                $user = $guru->user; // Ambil data user terkait

                $user->update([ // Update data user
                    'nama_user' => $this->nama_user, // Nama user
                    'email_user' => strtolower(trim($this->email_user)), // Email user (normalisasi ke lowercase)
                    'phone_number' => $this->phone_number, // Nomor telepon
                    'role_id' => $roleId, // Role ID
                    'password' => $this->password ? Hash::make($this->password) : $user->password, // Password (hanya jika diisi)
                ]);

                $guru->update([ // Update data guru
                    'nip' => $this->nip, // NIP guru
                    'jenis_kelamin' => $this->jenis_kelamin, // Jenis kelamin guru
                    'mata_pelajaran' => $this->mata_pelajaran, // Mata pelajaran guru
                    'alamat' => $this->alamat, // Alamat guru // ✅ Tambahan baru
                    'foto' => $imagePath, // Foto guru
                ]);
            } else { // Jika dalam mode create
                // Tambah baru
                $user = User::create([ // Buat user baru
                    'nama_user' => $this->nama_user, // Nama user
                    'email_user' => strtolower(trim($this->email_user)), // Email user (normalisasi ke lowercase)
                    'phone_number' => $this->phone_number, // Nomor telepon
                    'password' => Hash::make($this->password), // Password (di-hash)
                    'role_id' => $roleId, // Role ID
                ]);

                $guru = Guru::create([ // Buat data guru baru
                    'user_id' => $user->id, // ID user terkait
                    'nip' => $this->nip, // NIP guru
                    'jenis_kelamin' => $this->jenis_kelamin, // Jenis kelamin guru
                    'mata_pelajaran' => $this->mata_pelajaran, // Mata pelajaran guru
                    'alamat' => $this->alamat, // Alamat guru // ✅ Tambahan baru
                    'foto' => $imagePath, // Foto guru
                ]);
            }
        });

        $this->resetForm(); // Reset form setelah disimpan
        session()->flash('message', 'Data guru berhasil disimpan.'); // Tampilkan pesan sukses
        $this->dispatch('close-modal', id: 'modal-form'); // Kirim event untuk menutup modal
    } // Simpan data guru baru atau perbarui yang sudah ada

    public function edit(int $id)
    {
        $this->resetValidation(); // Hapus pesan validasi sebelumnya
        $guru = Guru::with('user')->findOrFail($id); // Ambil data guru beserta user terkait

        $this->guru_id = $guru->id; // Set ID guru yang akan diedit
        $this->user_id = $guru->user->id ?? null; // Set ID user terkait
        $this->nama_user = $guru->user->nama_user ?? ''; // Muat nama user
        $this->email_user = $guru->user->email_user ?? ''; // Muat email user
        $this->phone_number = $guru->user->phone_number ?? ''; // Muat nomor telepon user
        $this->nip = $guru->nip; // Muat NIP guru
        $this->jenis_kelamin = $guru->jenis_kelamin; // Muat jenis kelamin guru
        $this->mata_pelajaran = $guru->mata_pelajaran; // Muat mata pelajaran guru
        $this->alamat = $guru->alamat ?? ''; // Muat alamat guru // ✅ Tambahan baru
        $this->existingFoto = $guru->foto; // Muat path foto guru
        $this->password = null; // Reset password
        $this->password_confirmation = null; // Reset konfirmasi password
    } // Muat data guru untuk mode edit

    public function delete(int $id)
    {
        $guru = Guru::with('user')->findOrFail($id); // Ambil data guru beserta user terkait

        DB::transaction(function () use ($guru) { // Jalankan dalam transaksi database
            if ($guru->foto) { // Jika guru memiliki foto
                Storage::disk('public')->delete($guru->foto); // Hapus file foto dari storage
            }

            if ($guru->user) { // Jika guru memiliki user terkait
                $guru->user->delete(); // Hapus data user terlebih dahulu
            } else { // Jika tidak ada user terkait
                $guru->delete(); // Hapus data guru
            }
        });

        session()->flash('message', 'Data guru berhasil dihapus.'); // Tampilkan pesan sukses
        $this->resetForm(); // Reset form setelah penghapusan
    } // Hapus data guru

    #[Computed]
    public function listGuru()
    {
        [$sortField, $sortDirection] = $this->resolveSort(); // Ambil field dan arah sorting

        $query = Guru::query()
            ->with('user') // Muat relasi user
            ->when($this->search !== '', function ($query) { // Jika ada kata kunci pencarian
                $term = '%' . $this->search . '%';

                $query->where(function ($query) use ($term) { // Cari berdasarkan berbagai field
                    $query->where('guru.nip', 'like', $term) // Cari berdasarkan NIP
                        ->orWhere('guru.mata_pelajaran', 'like', $term) // Cari berdasarkan mata pelajaran
                        ->orWhere('guru.alamat', 'like', $term) // Cari berdasarkan alamat
                        ->orWhereHas('user', function ($userQuery) use ($term) { // Cari juga di tabel user
                            $userQuery->where('nama_user', 'like', $term) // Cari berdasarkan nama user
                                ->orWhere('email_user', 'like', $term) // Cari berdasarkan email
                                ->orWhere('phone_number', 'like', $term); // Cari berdasarkan nomor telepon
                        });
                });
            })
            ->when($this->genderFilter !== 'all', function ($query) { // Jika ada filter gender
                $query->where('guru.jenis_kelamin', $this->genderFilter); // Filter berdasarkan jenis kelamin
            });

        if ($sortField === 'users.nama_user') { // Jika sorting berdasarkan nama user
            $query->leftJoin('users', 'users.id', '=', 'guru.user_id') // Join ke tabel users
                ->select('guru.*') // Ambil semua field dari guru
                ->orderBy('users.nama_user', $sortDirection); // Urutkan berdasarkan nama user
        } else {
            $query->orderBy($sortField, $sortDirection); // Urutkan berdasarkan field biasa
        }

        return $query->paginate($this->perPage); // Kembalikan hasil dengan pagination
    } // Ambil daftar guru dengan pencarian, filter, dan pagination

    public function render()
    {
        return view('livewire.admin.manajemen-guru');
    } // Render tampilan komponen

    private function resetForm(): void
    {
        $this->reset([
            'guru_id',
            'user_id',
            'nama_user',
            'email_user',
            'phone_number',
            'password',
            'password_confirmation',
            'nip',
            'jenis_kelamin',
            'mata_pelajaran',
            'alamat', 
            'foto',
            'existingFoto',
        ]); // Reset semua properti form ke nilai awal
        $this->jenis_kelamin = 'Laki-laki'; // Set default jenis kelamin
         $this->resetErrorBag(); // Hapus pesan error sebelumnya
        $this->resetValidation(); // Hapus status validasi
    } // Reset form ke kondisi awal

    private function normalizeGender(string $value): string
    {
        return array_key_exists($value, $this->genderOptions) ? $value : 'all'; // Kembalikan nilai valid atau default
    } // Pastikan nilai gender valid sesuai opsi yang tersedia

    private function normalizeSort(string $value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'created_at_desc'; // Kembalikan nilai sort valid atau default
    } // Pastikan nilai sort valid sesuai opsi yang tersedia

    private function resolveSort(): array
    {
        return match ($this->sort) { // Kembalikan field dan arah sorting berdasarkan opsi
            'created_at_asc' => ['guru.created_at', 'asc'], // Urutkan berdasarkan created_at ascending
            'nama_user_asc' => ['users.nama_user', 'asc'], // Urutkan berdasarkan nama user ascending
            'nama_user_desc' => ['users.nama_user', 'desc'], // Urutkan berdasarkan nama user descending
            'mata_pelajaran_asc' => ['guru.mata_pelajaran', 'asc'], // Urutkan berdasarkan mata pelajaran ascending
            'mata_pelajaran_desc' => ['guru.mata_pelajaran', 'desc'], // Urutkan berdasarkan mata pelajaran descending
            default => ['guru.created_at', 'desc'], // Default: urutkan berdasarkan created_at descending
        };
    } // Ambil field dan arah sorting

    private function normalizePerPage($value): int
    {
        $value = (int) $value; // Konversi nilai ke integer

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0]; // Kembalikan nilai valid atau default
    } // Pastikan nilai perPage valid sesuai opsi yang tersedia
}
