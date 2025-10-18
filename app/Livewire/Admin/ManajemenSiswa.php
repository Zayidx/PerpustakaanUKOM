<?php

namespace App\Livewire\Admin;

use App\Models\RoleData;
use App\Models\Siswa;
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

class ManajemenSiswa extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Halaman Manajemen Siswa')]
    #[Url(except: "")]
    #[Layout('components.layouts.dashboard-layouts')]
    public $perPage = 5;

    public $siswa_id;
    public $user_id;
    public $nama = '';
    public $email = '';
    public $phone_number = '';
    public $password;
    public $password_confirmation;
    public $alamat = '';
    public $jenis_kelamin = 'laki-laki';
    public $nisn = '';
    public $nis = '';
    public $nip = '';
    public $foto;
    public $existingFoto = '';

    protected $messages = [
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

    public function updatedPerPage(): void
    {
        $this->perPage = max(1, (int) $this->perPage);
        $this->resetPage();
    }

    protected function rules(): array
    {
        $passwordRules = $this->siswa_id ? ['nullable'] : ['required'];
        $passwordRules[] = 'min:8';

        $passwordConfirmationRules = $this->password ? ['same:password'] : ['nullable'];

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

    public function create(): void
    {
        $this->resetForm();
        $this->resetValidation();
    }

    public function store(): void
    {
        if ($this->password === '') {
            $this->password = null;
        }

        if ($this->password_confirmation === '') {
            $this->password_confirmation = null;
        }

        $this->validate();

        $roleId = RoleData::where('nama_role', 'Siswa')->value('id');
        if (!$roleId) {
            session()->flash('message', 'Role Siswa belum dikonfigurasi. Silakan tambahkan role terlebih dahulu.');
            return;
        }

        $imagePath = $this->existingFoto;
        if ($this->foto instanceof TemporaryUploadedFile) {
            Storage::disk('public')->makeDirectory('admin/foto-siswa');
            if ($this->existingFoto) {
                Storage::disk('public')->delete($this->existingFoto);
            }
            $imagePath = $this->foto->store('admin/foto-siswa', 'public');
        }

        $nama = trim($this->nama);
        $email = strtolower(trim($this->email));
        $phone = trim($this->phone_number);
        $nisn = trim($this->nisn);
        $nis = trim($this->nis);
        $nip = $this->nip ? trim($this->nip) : null;
        $alamat = $this->alamat ? trim($this->alamat) : null;

        DB::transaction(function () use ($roleId, $imagePath, $nama, $email, $phone, $nisn, $nis, $nip, $alamat) {
            if ($this->siswa_id) {
                $siswa = Siswa::with('user')->findOrFail($this->siswa_id);
                $user = $siswa->user;
                $user->nama_user = $nama;
                $user->email_user = $email;
                $user->phone_number = $phone;
                if ($this->password) {
                    $user->password = Hash::make($this->password);
                }
                $user->role_id = $roleId;
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

        $this->resetForm();
        session()->flash('message', 'Data siswa berhasil disimpan.');
        $this->dispatch('close-modal', id: 'modal-form');
    }

    public function edit(int $id): void
    {
        $this->resetValidation();
        $siswa = Siswa::with('user')->findOrFail($id);

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

    public function updatedFoto(): void
    {
        if ($this->foto) {
            $this->validateOnly('foto');
        }
    }

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
    public function listSiswa()
    {
        return Siswa::with('user')->orderByDesc('created_at')->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.admin.manajemen-siswa');
    }

    private function resetForm(): void
    {
        $this->reset([
            'siswa_id',
            'user_id',
            'nama',
            'email',
            'phone_number',
            'password',
            'password_confirmation',
            'alamat',
            'jenis_kelamin',
            'nisn',
            'nis',
            'nip',
            'foto',
            'existingFoto',
        ]);

        $this->jenis_kelamin = 'laki-laki';
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
