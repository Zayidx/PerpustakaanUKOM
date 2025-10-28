<?php

namespace App\Livewire\Admin;

use App\Models\PetugasPerpus;
use App\Models\RoleData;
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
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManajemenPetugas extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    #[Title('Halaman Manajemen Admin')]
    #[Url(except: "")]
    #[Layout('components.layouts.dashboard-layouts')]
    public int $perPage = 5;
    public array $perPageOptions = [5, 10, 25];
    public string $search = '';
    public string $genderFilter = 'all';
    public string $sort = 'created_at_desc';
    public array $sortOptions = [
        'created_at_desc' => 'Terbaru',
        'created_at_asc' => 'Terlama',
        'nama_user_asc' => 'Nama A-Z',
        'nama_user_desc' => 'Nama Z-A',
    ];
    public array $genderOptions = [
        'all' => 'Semua Gender',
        'laki-laki' => 'Laki-laki',
        'perempuan' => 'Perempuan',
    ];

    public ?int $petugas_id = null;
    public ?int $user_id = null;
    public string $nama = '';
    public string $email = '';
    public string $phone_number = '';
    public ?string $password = null;
    public ?string $password_confirmation = null;
    public string $alamat = '';
    public string $jenis_kelamin = 'laki-laki';
    public string $nip = '';
    public $foto = null;
    public string $existingFoto = '';

    protected array $messages = [
        'nama.required' => 'Nama admin wajib diisi.',
        'nama.string' => 'Nama admin harus berupa teks.',
        'nama.max' => 'Nama admin maksimal :max karakter.',

        'email.required' => 'Email admin wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.max' => 'Email maksimal :max karakter.',
        'email.unique' => 'Email tersebut sudah terdaftar.',

        'phone_number.required' => 'Nomor telepon wajib diisi.',
        'phone_number.string' => 'Nomor telepon harus berupa teks.',
        'phone_number.max' => 'Nomor telepon maksimal :max karakter.',

        'password.required' => 'Password wajib diisi.',
        'password.min' => 'Password minimal :min karakter.',
        'password_confirmation.same' => 'Konfirmasi password harus sama dengan password.',

        'alamat.string' => 'Alamat harus berupa teks.',
        'alamat.max' => 'Alamat maksimal :max karakter.',

        'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',

        'nip.required' => 'NIP wajib diisi.',
        'nip.string' => 'NIP harus berupa teks.',
        'nip.max' => 'NIP maksimal :max karakter.',
        'nip.unique' => 'NIP tersebut sudah terdaftar.',

        'foto.image' => 'File foto harus berupa gambar.',
        'foto.max' => 'Ukuran foto maksimal :max kilobyte.',
    ];

    public function mount(): void
    {
        $this->perPage = $this->normalizePerPage($this->perPage);
        $this->genderFilter = $this->normalizeGender($this->genderFilter);
        $this->sort = $this->normalizeSort($this->sort);
        $this->search = trim((string) $this->search);
    }

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

    public function updatedGenderFilter($value): void
    {
        $this->genderFilter = $this->normalizeGender($value);
        $this->resetPage();
    }

    public function updatedSort($value): void
    {
        $this->sort = $this->normalizeSort($value);
        $this->resetPage();
    }

    protected function rules(): array
    {
        $passwordRules = $this->petugas_id ? ['nullable'] : ['required']; // Password opsional saat edit, wajib saat create
        $passwordRules[] = 'min:8'; // Minimal 8 karakter

        $passwordConfirmationRules = $this->password ? ['same:password'] : ['nullable']; // Konfirmasi hanya dicek jika password diisi

        return [
            'nama' => ['required', 'string', 'max:255'], // Nama wajib diisi maksimal 255 karakter
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email_user')->ignore($this->user_id), // Email harus unik, abaikan saat edit
            ],
            'phone_number' => ['required', 'string', 'max:20'], // Nomor telepon wajib diisi maksimal 20 karakter
            'password' => $passwordRules, // Aturan password berbeda saat create dan edit
            'password_confirmation' => $passwordConfirmationRules, // Aturan konfirmasi password
            'alamat' => ['nullable', 'string', 'max:255'], // Alamat opsional maksimal 255 karakter
            'jenis_kelamin' => ['required', 'in:laki-laki,perempuan'], // Jenis kelamin wajib dan harus valid
            'nip' => [
                'required',
                'string',
                'max:30',
                Rule::unique('petugas', 'nip')->ignore($this->petugas_id), // NIP harus unik, abaikan saat edit
            ],
            'foto' => ['nullable', 'image', 'max:1024'], // Foto opsional, harus berupa gambar maks 1MB
        ];
    } // Aturan validasi untuk form petugas

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

    public function create(): void
    {
        $this->resetForm(); // Reset form ke kondisi awal
        $this->resetValidation(); // Hapus pesan validasi sebelumnya
    } // Reset form untuk membuat petugas baru

    public function store(): void
    {
        if ($this->password === '') { // Jika password diisi sebagai string kosong
            $this->password = null; // Ubah ke null
        }

        if ($this->password_confirmation === '') { // Jika konfirmasi password diisi sebagai string kosong
            $this->password_confirmation = null; // Ubah ke null
        }

        $this->validate(); // Jalankan validasi pada input

        $roleId = RoleData::firstOrCreate( // Ambil atau buat role Admin
            ['nama_role' => 'Admin'], // Kondisi pencarian
            [ // Data yang akan disimpan jika tidak ditemukan
                'deskripsi_role' => 'Pengelola operasional perpustakaan.',
                'icon_role' => 'bi-person-badge',
            ]
        )->id; // Dapatkan ID role

        $imagePath = $this->existingFoto; // Gunakan foto lama sebagai default
        if ($this->foto instanceof TemporaryUploadedFile) { // Jika ada upload foto baru
            Storage::disk('public')->makeDirectory('admin/foto-petugas'); // Buat direktori jika belum ada
            if ($this->existingFoto) { // Hapus foto lama jika ada
                Storage::disk('public')->delete($this->existingFoto);
            }
            $imagePath = $this->foto->store('admin/foto-petugas', 'public'); // Simpan foto baru
        }

        $nama = trim($this->nama); // Normalisasi nama
        $email = strtolower(trim($this->email)); // Normalisasi email ke lowercase
        $phone = trim($this->phone_number); // Normalisasi nomor telepon
        $nip = trim($this->nip); // Normalisasi NIP
        $alamat = $this->alamat ? trim($this->alamat) : null; // Normalisasi alamat jika ada

        DB::transaction(function () use ($roleId, $imagePath, $nama, $email, $phone, $nip, $alamat) { // Jalankan dalam transaksi database
            if ($this->petugas_id) { // Jika dalam mode edit
                $petugas = PetugasPerpus::with('user')->findOrFail($this->petugas_id); // Ambil data petugas beserta user
                $user = $petugas->user; // Ambil data user terkait
                $user->nama_user = $nama; // Update nama user
                $user->email_user = $email; // Update email user
                $user->phone_number = $phone; // Update nomor telepon user
                if ($this->password) { // Jika password diisi
                    $user->password = Hash::make($this->password); // Hash dan update password
                }
                $user->role_id = $roleId; // Update role ID
                $user->save(); // Simpan perubahan user

                $petugas->update([ // Update data petugas
                    'nip' => $nip, // NIP petugas
                    'alamat' => $alamat, // Alamat petugas
                    'jenis_kelamin' => $this->jenis_kelamin, // Jenis kelamin petugas
                    'foto' => $imagePath, // Foto petugas
                ]);

                return;
            }

            $user = User::create([ // Buat user baru
                'nama_user' => $nama, // Nama user
                'email_user' => $email, // Email user
                'phone_number' => $phone, // Nomor telepon user
                'password' => Hash::make($this->password), // Password yang di-hash
                'role_id' => $roleId, // Role ID
            ]);

            $petugas = PetugasPerpus::create([ // Buat data petugas baru
                'user_id' => $user->id, // ID user terkait
                'nip' => $nip, // NIP petugas
                'alamat' => $alamat, // Alamat petugas
                'jenis_kelamin' => $this->jenis_kelamin, // Jenis kelamin petugas
                'foto' => $imagePath, // Foto petugas
            ]);

            $this->petugas_id = $petugas->id; // Simpan ID petugas baru
            $this->user_id = $user->id; // Simpan ID user baru
        });

        $this->resetForm(); // Reset form setelah disimpan
        session()->flash('message', 'Data admin berhasil disimpan.'); // Tampilkan pesan sukses
        $this->dispatch('close-modal', id: 'modal-form'); // Kirim event untuk menutup modal
    } // Simpan data petugas baru atau perbarui yang sudah ada

    public function edit(int $id): void
    {
        $this->resetValidation(); // Hapus pesan validasi sebelumnya
        $petugas = PetugasPerpus::with('user')->findOrFail($id); // Ambil data petugas beserta user terkait

        $this->petugas_id = $petugas->id; // Set ID petugas yang akan diedit
        $this->user_id = $petugas->user->id ?? null; // Set ID user terkait
        $this->nama = $petugas->user->nama_user ?? ''; // Muat nama user
        $this->email = $petugas->user->email_user ?? ''; // Muat email user
        $this->phone_number = $petugas->user->phone_number ?? ''; // Muat nomor telepon user
        $this->nip = $petugas->nip; // Muat NIP petugas
        $this->alamat = $petugas->alamat ?? ''; // Muat alamat petugas
        $this->jenis_kelamin = $petugas->jenis_kelamin; // Muat jenis kelamin petugas
        $this->existingFoto = $petugas->foto ?? ''; // Muat path foto petugas
        $this->password = null; // Reset password
        $this->password_confirmation = null; // Reset konfirmasi password
    } // Muat data petugas untuk mode edit

    public function updatedFoto(): void
    {
        if ($this->foto) { // Jika ada file foto yang diupload
            $this->validateOnly('foto'); // Validasi hanya field foto
        }
    } // Validasi file foto saat diupload

    public function delete(int $id): void
    {
        $petugas = PetugasPerpus::with('user')->findOrFail($id); // Ambil data petugas beserta user terkait

        DB::transaction(function () use ($petugas) { // Jalankan dalam transaksi database
            if ($petugas->foto) { // Jika petugas memiliki foto
                Storage::disk('public')->delete($petugas->foto); // Hapus file foto dari storage
            }

            if ($petugas->user) { // Jika petugas memiliki user terkait
                $petugas->user->delete(); // Hapus data user terlebih dahulu
                return;
            }

            $petugas->delete(); // Hapus data petugas jika tidak ada user terkait
        });

        session()->flash('message', 'Data admin berhasil dihapus.'); // Tampilkan pesan sukses
        $this->resetForm(); // Reset form setelah penghapusan
    } // Hapus data petugas

    #[Computed]
    public function listPetugasPerpus()
    {
        [$sortField, $sortDirection] = $this->resolveSort(); // Ambil field dan arah sorting

        $query = PetugasPerpus::query()
            ->with('user') // Muat relasi user
            ->when($this->search !== '', function ($query) { // Jika ada kata kunci pencarian
                $term = '%' . $this->search . '%';

                $query->where(function ($query) use ($term) { // Cari berdasarkan berbagai field
                    $query->where('petugas.nip', 'like', $term) // Cari berdasarkan NIP
                        ->orWhere('petugas.alamat', 'like', $term) // Cari berdasarkan alamat
                        ->orWhereHas('user', function ($userQuery) use ($term) { // Cari juga di tabel user
                            $userQuery->where('nama_user', 'like', $term) // Cari berdasarkan nama user
                                ->orWhere('email_user', 'like', $term) // Cari berdasarkan email
                                ->orWhere('phone_number', 'like', $term); // Cari berdasarkan nomor telepon
                        });
                });
            })
            ->when($this->genderFilter !== 'all', function ($query) { // Jika ada filter gender
                $query->where('petugas.jenis_kelamin', $this->genderFilter); // Filter berdasarkan jenis kelamin
            });

        if ($sortField === 'users.nama_user') { // Jika sorting berdasarkan nama user
            $query->leftJoin('users', 'users.id', '=', 'petugas.user_id') // Join ke tabel users
                ->select('petugas.*') // Pilih semua field dari petugas
                ->orderBy('users.nama_user', $sortDirection); // Urutkan berdasarkan nama user
        } else {
            $query->orderBy($sortField, $sortDirection); // Urutkan berdasarkan field biasa
        }

        return $query->paginate($this->perPage); // Kembalikan hasil dengan pagination
    } // Ambil daftar petugas dengan pencarian, filter, dan pagination

    public function render()
    {
        return view('livewire.admin.manajemen-petugas');
    } // Render tampilan komponen

    private function resetForm(): void
    {
        $this->reset([
            'petugas_id',
            'user_id',
            'nama',
            'email',
            'phone_number',
            'password',
            'password_confirmation',
            'alamat',
            'jenis_kelamin',
            'nip',
            'foto',
            'existingFoto',
        ]); // Reset semua properti form ke nilai awal

        $this->jenis_kelamin = 'laki-laki'; // Set default jenis kelamin
        $this->resetErrorBag(); // Hapus pesan error sebelumnya
        $this->resetValidation(); // Hapus status validasi
    } // Reset form ke kondisi awal

    private function normalizeGender(string $value): string
    {
        return array_key_exists($value, $this->genderOptions) ? $value : 'all'; // Kembalikan nilai gender valid atau default
    } // Pastikan nilai gender valid sesuai opsi yang tersedia

    private function normalizeSort(string $value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'created_at_desc'; // Kembalikan nilai sort valid atau default
    } // Pastikan nilai sort valid sesuai opsi yang tersedia

    private function resolveSort(): array
    {
        return match ($this->sort) { // Kembalikan field dan arah sorting berdasarkan opsi
            'created_at_asc' => ['petugas.created_at', 'asc'], // Urutkan berdasarkan created_at ascending
            'nama_user_asc' => ['users.nama_user', 'asc'], // Urutkan berdasarkan nama user ascending
            'nama_user_desc' => ['users.nama_user', 'desc'], // Urutkan berdasarkan nama user descending
            default => ['petugas.created_at', 'desc'], // Default: urutkan berdasarkan created_at descending
        };
    } // Ambil field dan arah sorting

    private function normalizePerPage($value): int
    {
        $value = (int) $value; // Konversi nilai ke integer

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0]; // Kembalikan nilai valid atau default
    } // Pastikan nilai perPage valid sesuai opsi yang tersedia
}
