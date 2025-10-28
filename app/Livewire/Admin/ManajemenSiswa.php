<?php // Komponen Livewire untuk fitur manajemen siswa

namespace App\Livewire\Admin; // Namespace untuk komponen admin

use App\Models\Jurusan; // Model untuk tabel jurusan
use App\Models\Kelas; // Model untuk tabel kelas
use App\Models\RoleData; // Model untuk tabel role_data
use App\Models\Siswa; // Model relasi detail siswa
use App\Models\User; // Model user bawaan Laravel
use Illuminate\Support\Facades\DB; // Facade transaksi database
use Illuminate\Support\Facades\Hash; // Facade hashing password
use Illuminate\Support\Facades\Storage; // Facade penyimpanan file
use Illuminate\Validation\Rule; // Rule validasi dinamis
use Livewire\Attributes\Computed; // Attribute untuk properti terhitung
use Livewire\Attributes\Layout; // Attribute layout komponen
use Livewire\Attributes\Title; // Attribute judul halaman
use Livewire\Attributes\Url; // Attribute sinkronisasi URL
use Livewire\Component; // Base class Livewire
use Livewire\WithPagination; // Trait pagination Livewire
use Livewire\WithFileUploads; // Trait upload file Livewire
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile; // Representasi file upload sementara

class ManajemenSiswa extends Component // Komponen Livewire utama untuk CRUD siswa
{ 
    use WithFileUploads; // Mengaktifkan dukungan upload file pada komponen Livewire
    use WithPagination; // Mengaktifkan pagination Livewire untuk tabel siswa

    protected $paginationTheme = 'bootstrap'; // Menggunakan gaya pagination Bootstrap bawaan template

    #[Title('Halaman Manajemen Siswa')]
    #[Url(except: "")]
    #[Layout('components.layouts.dashboard-layouts')]
    public $perPage = 5; // Jumlah data per halaman tabel
    public array $perPageOptions = [5, 10, 25]; // Opsi dropdown per halaman
    public $search = ''; // Kata kunci pencarian
    public $genderFilter = 'all'; // Filter jenis kelamin
    public string $sort = 'created_at_desc'; // Opsi sorting default
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

    public $siswa_id; // ID siswa untuk mode edit
    public $user_id; // ID user terkait siswa
    public $nama = ''; // Nama lengkap siswa
    public $email = ''; // Email siswa
    public $phone_number = ''; // Nomor telepon siswa
    public $password; // Password baru (opsional saat edit)
    public $password_confirmation; // Konfirmasi password
    public $alamat = ''; // Alamat siswa
    public $jenis_kelamin = 'laki-laki'; // Default jenis kelamin
    public $nisn = ''; // Nomor Induk Siswa Nasional
    public $nis = ''; // Nomor Induk Siswa internal
    public $kelas_id; // Kelas terkait siswa
    public $jurusan_id; // Jurusan terkait siswa
    public $foto; // File foto yang baru diupload
    public $existingFoto = ''; // Path foto lama bila ada

    protected $messages = [ // Pesan error kustom berbahasa Indonesia
        'nama.required' => 'Nama siswa wajib diisi.',
        'nama.string' => 'Nama siswa harus berupa teks.',
        'nama.max' => 'Nama siswa maksimal :max karakter.',

        'email.required' => 'Email siswa wajib diisi.',
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

        'nisn.required' => 'NISN wajib diisi.',
        'nisn.digits_between' => 'NISN harus terdiri dari :min sampai :max digit.',
        'nisn.unique' => 'NISN tersebut sudah terdaftar.',

        'nis.required' => 'NIS wajib diisi.',
        'nis.digits_between' => 'NIS harus terdiri dari :min sampai :max digit.',
        'nis.unique' => 'NIS tersebut sudah terdaftar.',

        'foto.image' => 'File foto harus berupa gambar.',
        'foto.max' => 'Ukuran foto maksimal :max kilobyte.',

        'kelas_id.required' => 'Kelas wajib dipilih.',
        'kelas_id.exists' => 'Kelas yang dipilih tidak valid.',

        'jurusan_id.required' => 'Jurusan wajib dipilih.',
        'jurusan_id.exists' => 'Jurusan yang dipilih tidak valid.',
    ];

    public function mount(): void
    {
        $this->perPage = $this->normalizePerPage($this->perPage); // Pastikan nilai awal valid
        $this->sort = $this->normalizeSort($this->sort); // Pastikan opsi sort valid
        $this->genderFilter = $this->normalizeGender($this->genderFilter); // Pastikan filter gender valid
        $this->search = trim((string) $this->search); // Normalisasi kata kunci awal
    } // Inisialisasi komponen dengan nilai valid

    // Dipicu ketika dropdown jumlah data per halaman berubah
    public function updatedPerPage($value): void
    {
        $this->perPage = $this->normalizePerPage($value); // Menjaga nilai sesuai opsi
        $this->resetPage(); // Kembali ke halaman pertama saat jumlah per halaman berubah
    }

    public function updatedSearch(): void
    {
        $this->search = trim((string) $this->search); // Hilangkan spasi berlebih
        $this->resetPage(); // Reset pagination agar pencarian dimulai dari halaman pertama
    }

    public function updatedGenderFilter($value): void
    {
        $this->genderFilter = $this->normalizeGender($value); // Pastikan filter valid
        $this->resetPage(); // Reset pagination setelah filter berubah
    }

    public function updatedSort($value): void
    {
        $this->sort = $this->normalizeSort($value); // Pastikan opsi sort valid
        $this->resetPage(); // Reset pagination setelah sort berubah
    }

        // Kumpulan rules validasi dinamis
        protected function rules(): array
        {
            $passwordRules = $this->siswa_id ? ['nullable'] : ['required']; // Password wajib diisi saat create
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
                'phone_number' => ['required', 'string', 'max:20'], // Nomor telepon wajib diisi
                'password' => $passwordRules, // Aturan password berbeda saat create dan edit
                'password_confirmation' => $passwordConfirmationRules, // Aturan konfirmasi password
                'alamat' => ['nullable', 'string', 'max:255'], // Alamat opsional
                'jenis_kelamin' => ['required', 'in:laki-laki,perempuan'], // Jenis kelamin wajib dan harus valid
                'kelas_id' => ['required', Rule::exists('kelas', 'id')], // ID kelas wajib dan harus ada di tabel kelas
                'jurusan_id' => ['required', Rule::exists('jurusan', 'id')], // ID jurusan wajib dan harus ada di tabel jurusan
                'nisn' => [
                    'required',
                    'digits_between:8,20', // NISN harus 8-20 digit
                    Rule::unique('siswa', 'nisn')->ignore($this->siswa_id), // NISN harus unik, abaikan saat edit
                ],
                'nis' => [
                    'required',
                    'digits_between:4,20', // NIS harus 4-20 digit
                    Rule::unique('siswa', 'nis')->ignore($this->siswa_id), // NIS harus unik, abaikan saat edit
                ],
                'foto' => ['nullable', 'image', 'max:1024'], // Foto opsional, harus berupa gambar maks 1MB
            ];
        } // Aturan validasi untuk form siswa

    // Reset form saat tombol tambah ditekan
    public function create(): void
    {
        $this->resetForm(); // Kosongkan seluruh field
        $this->resetValidation(); // Bersihkan pesan error
    }

        // Simpan atau perbarui data siswa
        public function store(): void
        {
            if ($this->password === '') {
                $this->password = null; // Livewire kadang mengirim string kosong, ubah jadi null
            }

            if ($this->password_confirmation === '') {
                $this->password_confirmation = null; // Samakan perlakuan konfirmasi password
            }

            $this->validate(); // Jalankan validasi semua field

            $roleId = RoleData::where('nama_role', 'Siswa')->value('id'); // Ambil ID role siswa
            if (!$roleId) {
                session()->flash('message', 'Role Siswa belum dikonfigurasi. Silakan tambahkan role terlebih dahulu.');
                return;
            }

            $imagePath = $this->existingFoto;
            if ($this->foto instanceof TemporaryUploadedFile) { // Jika ada upload baru
                Storage::disk('public')->makeDirectory('admin/foto-siswa'); // Pastikan folder ada
                if ($this->existingFoto) { // Hapus foto lama bila ada
                    Storage::disk('public')->delete($this->existingFoto);
                }
                $imagePath = $this->foto->store('admin/foto-siswa', 'public'); // Simpan foto baru
            }

            $nama = trim($this->nama); // Normalisasi input untuk menghindari spasi tak perlu
            $email = strtolower(trim($this->email));
            $phone = trim($this->phone_number);
            $nisn = trim($this->nisn);
            $nis = trim($this->nis);
            $alamat = $this->alamat ? trim($this->alamat) : null;
            $kelasId = (int) $this->kelas_id;
            $jurusanId = (int) $this->jurusan_id;

            DB::transaction(function () use ($roleId, $imagePath, $nama, $email, $phone, $nisn, $nis, $alamat, $kelasId, $jurusanId) {
                if ($this->siswa_id) {
                    $siswa = Siswa::with('user')->findOrFail($this->siswa_id); // Mode edit, cari data lama
                    $user = $siswa->user; // Ambil relasi user
                    $user->nama_user = $nama; // Update nama
                    $user->email_user = $email; // Update email
                    $user->phone_number = $phone; // Update telepon
                    if ($this->password) { // Password hanya diganti jika diisi
                        $user->password = Hash::make($this->password);
                    }
                    $user->role_id = $roleId; // Pastikan role tetap siswa
                    $user->save();

                    $siswa->update([
                        'nisn' => $nisn,
                        'nis' => $nis,
                        'alamat' => $alamat,
                        'jenis_kelamin' => $this->jenis_kelamin,
                        'kelas_id' => $kelasId,
                        'jurusan_id' => $jurusanId,
                        'foto' => $imagePath,
                    ]);
                } else {
                    $user = User::create([
                        'nama_user' => $nama,
                        'email_user' => $email,
                        'phone_number' => $phone,
                        'password' => Hash::make($this->password),
                        'role_id' => $roleId,
                    ]);

                    $siswa = Siswa::create([
                        'user_id' => $user->id,
                        'nisn' => $nisn,
                        'nis' => $nis,
                        'alamat' => $alamat,
                        'jenis_kelamin' => $this->jenis_kelamin,
                        'kelas_id' => $kelasId,
                        'jurusan_id' => $jurusanId,
                        'foto' => $imagePath,
                    ]);

                    $this->siswa_id = $siswa->id;
                    $this->user_id = $user->id;
                }
            });

            $this->resetForm(); // Bersihkan form setelah simpan
            session()->flash('message', 'Data siswa berhasil disimpan.');
            $this->dispatch('close-modal', id: 'modal-form'); // Tutup modal via JS
        } // Simpan atau perbarui data siswa

        // Muat data siswa untuk mode edit
        public function edit(int $id): void
        {
            $this->resetValidation(); // Bersihkan error lama
            $siswa = Siswa::with(['user', 'kelas', 'jurusan'])->findOrFail($id); // Ambil data siswa beserta relasi kunci

            $this->siswa_id = $siswa->id;
            $this->user_id = $siswa->user->id ?? null;
            $this->nama = $siswa->user->nama_user ?? '';
            $this->email = $siswa->user->email_user ?? '';
            $this->phone_number = $siswa->user->phone_number ?? '';
            $this->nisn = $siswa->nisn;
            $this->nis = $siswa->nis;
            $this->alamat = $siswa->alamat;
            $this->jenis_kelamin = $siswa->jenis_kelamin;
            $this->kelas_id = $siswa->kelas_id;
            $this->jurusan_id = $siswa->jurusan_id;
            $this->existingFoto = $siswa->foto;
            $this->password = null;
            $this->password_confirmation = null;
        } // Muat data siswa untuk mode edit

    // Validasi foto setiap kali input berubah
    public function updatedFoto(): void
    {
        if ($this->foto) {
            $this->validateOnly('foto'); // Validasi sehingga error langsung muncul jika file tidak sesuai
        }
    }

        // Hapus siswa beserta user terkait
        public function delete(int $id): void
        {
            $siswa = Siswa::with('user')->findOrFail($id); // Ambil data siswa beserta relasi user

            DB::transaction(function () use ($siswa) { // Jalankan dalam transaksi database
                if ($siswa->foto) {
                    Storage::disk('public')->delete($siswa->foto); // Hapus file foto dari storage
                }

                if ($siswa->user) {
                    $siswa->user->delete(); // Hapus data user terlebih dahulu
                } else {
                    $siswa->delete(); // Hapus data siswa
                }
            });

            session()->flash('message', 'Data siswa berhasil dihapus.');
            $this->resetForm();
        } // Hapus siswa beserta user terkait

    #[Computed]
    public function kelasOptions()
    {
        return Kelas::orderBy('nama_kelas')->get();
    }

    #[Computed]
    public function jurusanOptions()
    {
        return Jurusan::orderBy('nama_jurusan')->get();
    }

        #[Computed]
        public function listSiswa() // Data untuk tabel dengan pagination
        {
            [$sortField, $sortDirection] = $this->resolveSort(); // Ambil field dan arah sorting

            $query = Siswa::query()
                ->with(['user', 'kelas', 'jurusan']) // Muat relasi user, kelas, dan jurusan
                ->when($this->search !== '', function ($query) {
                    $searchTerm = '%' . $this->search . '%';

                    $query->where(function ($query) use ($searchTerm) { // Cari berdasarkan berbagai field
                        $query->where('nisn', 'like', $searchTerm)
                            ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                                $userQuery->where('nama_user', 'like', $searchTerm); // Cari berdasarkan nama user
                            })
                            ->orWhereHas('kelas', function ($kelasQuery) use ($searchTerm) {
                                $kelasQuery->where('nama_kelas', 'like', $searchTerm); // Cari berdasarkan nama kelas
                            })
                            ->orWhereHas('jurusan', function ($jurusanQuery) use ($searchTerm) {
                                $jurusanQuery->where('nama_jurusan', 'like', $searchTerm); // Cari berdasarkan nama jurusan
                            });
                    });
                })
                ->when($this->genderFilter !== 'all', function ($query) {
                    $query->where('jenis_kelamin', $this->genderFilter); // Filter berdasarkan jenis kelamin
                });

            if ($sortField === 'users.nama_user') { // Jika sorting berdasarkan nama user
                $query->leftJoin('users', 'users.id', '=', 'siswa.user_id')
                    ->select('siswa.*')
                    ->orderBy('users.nama_user', $sortDirection);
            } else {
                $query->orderBy($sortField, $sortDirection); // Sorting biasa
            }

            return $query->paginate($this->perPage); // Kembalikan hasil dengan pagination
        } // Ambil data siswa dengan pencarian, filter, dan pagination

    public function render() // Render view Livewire
    {
        return view('livewire.admin.manajemen-siswa'); // Render tampilan Livewire
    }

    private function normalizeGender($value): string
    {
        return array_key_exists($value, $this->genderOptions) ? $value : 'all';
    }

    private function normalizeSort($value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'created_at_desc';
    }

    private function resolveSort(): array
    {
        return match ($this->sort) {
            'created_at_asc' => ['siswa.created_at', 'asc'],
            'nama_user_asc' => ['users.nama_user', 'asc'],
            'nama_user_desc' => ['users.nama_user', 'desc'],
            default => ['siswa.created_at', 'desc'],
        };
    }

    private function normalizePerPage($value): int
    {
        $value = (int) $value;

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0];
    }

        // Membersihkan seluruh state form ke nilai awal
        private function resetForm(): void
        {
            $this->reset([
                'siswa_id', // Reset ID siswa
                'user_id', // Reset ID user
                'nama', // Kosongkan nama
                'email', // Kosongkan email
                'phone_number', // Kosongkan telepon
                'password', // Kosongkan password
                'password_confirmation', // Kosongkan konfirmasi password
                'alamat', // Kosongkan alamat
                'jenis_kelamin', // Akan di-set ulang di bawah
                'nisn', // Kosongkan NISN
                'nis', // Kosongkan NIS
                'kelas_id', // Kosongkan kelas
                'jurusan_id', // Kosongkan jurusan
                'foto', // Reset file upload
                'existingFoto', // Hapus referensi foto lama
            ]);

            $this->jenis_kelamin = 'laki-laki'; // Default pilihan gender
            $this->kelas_id = null;
            $this->jurusan_id = null;
            $this->resetErrorBag(); // Hapus pesan kesalahan sebelumnya
            $this->resetValidation(); // Bersihkan status validasi
        } // Membersihkan seluruh state form ke nilai awal
}
