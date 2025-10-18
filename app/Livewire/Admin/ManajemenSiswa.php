<?php // Komponen Livewire untuk fitur manajemen siswa

namespace App\Livewire\Admin; // Namespace untuk komponen admin

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
    public $nip = ''; // NIP opsional
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

        'nip.string' => 'NIP harus berupa teks.',
        'nip.max' => 'NIP maksimal :max karakter.',

        'foto.image' => 'File foto harus berupa gambar.',
        'foto.max' => 'Ukuran foto maksimal :max kilobyte.',
    ];

    // Dipicu ketika dropdown jumlah data per halaman berubah
    public function updatedPerPage(): void
    {
        $this->perPage = max(1, (int) $this->perPage); // Menjaga nilai perPage minimal 1
        $this->resetPage(); // Kembali ke halaman pertama saat jumlah per halaman berubah
    }

    // Kumpulan rules validasi dinamis
    protected function rules(): array
    {
        $passwordRules = $this->siswa_id ? ['nullable'] : ['required']; // Password wajib diisi saat create
        $passwordRules[] = 'min:8'; // Minimal 8 karakter

        $passwordConfirmationRules = $this->password ? ['same:password'] : ['nullable']; // Konfirmasi hanya dicek jika password diisi

        return [
            'nama' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email_user')->ignore($this->user_id),
            ],
            'phone_number' => ['required', 'string', 'max:20'],
            'password' => $passwordRules,
            'password_confirmation' => $passwordConfirmationRules,
            'alamat' => ['nullable', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:laki-laki,perempuan'],
            'nisn' => [
                'required',
                'digits_between:8,20',
                Rule::unique('siswa', 'nisn')->ignore($this->siswa_id),
            ],
            'nis' => [
                'required',
                'digits_between:4,20',
                Rule::unique('siswa', 'nis')->ignore($this->siswa_id),
            ],
            'nip' => ['nullable', 'string', 'max:30'],
            'foto' => ['nullable', 'image', 'max:1024'],
        ];
    }

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
        $nip = $this->nip ? trim($this->nip) : null;
        $alamat = $this->alamat ? trim($this->alamat) : null;

        DB::transaction(function () use ($roleId, $imagePath, $nama, $email, $phone, $nisn, $nis, $nip, $alamat) {
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
                    'nip' => $nip,
                    'alamat' => $alamat,
                    'jenis_kelamin' => $this->jenis_kelamin,
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
                    'nip' => $nip,
                    'alamat' => $alamat,
                    'jenis_kelamin' => $this->jenis_kelamin,
                    'foto' => $imagePath,
                ]);

                $this->siswa_id = $siswa->id;
                $this->user_id = $user->id;
            }
        });

        $this->resetForm(); // Bersihkan form setelah simpan
        session()->flash('message', 'Data siswa berhasil disimpan.');
        $this->dispatch('close-modal', id: 'modal-form'); // Tutup modal via JS
    }

    // Muat data siswa untuk mode edit
    public function edit(int $id): void
    {
        $this->resetValidation(); // Bersihkan error lama
        $siswa = Siswa::with('user')->findOrFail($id); // Ambil data siswa beserta user

        $this->siswa_id = $siswa->id;
        $this->user_id = $siswa->user->id ?? null;
        $this->nama = $siswa->user->nama_user ?? '';
        $this->email = $siswa->user->email_user ?? '';
        $this->phone_number = $siswa->user->phone_number ?? '';
        $this->nisn = $siswa->nisn;
        $this->nis = $siswa->nis;
        $this->nip = $siswa->nip;
        $this->alamat = $siswa->alamat;
        $this->jenis_kelamin = $siswa->jenis_kelamin;
        $this->existingFoto = $siswa->foto;
        $this->password = null;
        $this->password_confirmation = null;
    }

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
        $siswa = Siswa::with('user')->findOrFail($id);

        DB::transaction(function () use ($siswa) {
            if ($siswa->foto) {
                Storage::disk('public')->delete($siswa->foto);
            }

            if ($siswa->user) {
                $siswa->user->delete();
            } else {
                $siswa->delete();
            }
        });

        session()->flash('message', 'Data siswa berhasil dihapus.');
        $this->resetForm();
    }

    #[Computed]
    public function listSiswa() // Data untuk tabel dengan pagination
    {
        return Siswa::with('user')->orderByDesc('created_at')->paginate($this->perPage);
    }

    public function render() // Render view Livewire
    {
        return view('livewire.admin.manajemen-siswa'); // Render tampilan Livewire
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
            'nip', // Kosongkan NIP
            'foto', // Reset file upload
            'existingFoto', // Hapus referensi foto lama
        ]);

        $this->jenis_kelamin = 'laki-laki'; // Default pilihan gender
        $this->resetErrorBag(); // Hapus pesan kesalahan sebelumnya
        $this->resetValidation(); // Bersihkan status validasi
    }
}
