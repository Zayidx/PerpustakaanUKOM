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
        $passwordRules = $this->guru_id ? ['nullable', 'min:8'] : ['required', 'min:8'];
        $passwordConfirmationRules = $this->password ? ['same:password'] : ['nullable'];

        return [
            'nama_user' => ['required', 'string', 'max:255'],
            'email_user' => ['required', 'email', Rule::unique('users', 'email_user')->ignore($this->user_id)],
            'phone_number' => ['required', 'string', 'max:20'],
            'password' => $passwordRules,
            'password_confirmation' => $passwordConfirmationRules,
            'nip' => ['required', 'string', 'max:30', Rule::unique('guru', 'nip')->ignore($this->guru_id)],
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'],
            'mata_pelajaran' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string', 'max:255'], // ✅ Tambahan baru
            'foto' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
        $this->resetValidation();
    }

    public function store()
    {
        $this->validate();

        $roleId = RoleData::where('nama_role', 'Guru')->value('id');
        if (!$roleId) {
            session()->flash('message', 'Role Guru belum dikonfigurasi.');
            return;
        }

        $imagePath = $this->existingFoto;
        if ($this->foto instanceof TemporaryUploadedFile) {
            Storage::disk('public')->makeDirectory('admin/foto-guru');
            if ($this->existingFoto) {
                Storage::disk('public')->delete($this->existingFoto);
            }
            $imagePath = $this->foto->store('admin/foto-guru', 'public');
        }

        DB::transaction(function () use ($roleId, $imagePath) {
            if ($this->guru_id) {
                // Edit guru
                $guru = Guru::with('user')->findOrFail($this->guru_id);
                $user = $guru->user;

                $user->update([
                    'nama_user' => $this->nama_user,
                    'email_user' => strtolower(trim($this->email_user)),
                    'phone_number' => $this->phone_number,
                    'role_id' => $roleId,
                    'password' => $this->password ? Hash::make($this->password) : $user->password,
                ]);

                $guru->update([
                    'nip' => $this->nip,
                    'jenis_kelamin' => $this->jenis_kelamin,
                    'mata_pelajaran' => $this->mata_pelajaran,
                    'alamat' => $this->alamat, // ✅ Tambahan baru
                    'foto' => $imagePath,
                ]);
            } else {
                // Tambah baru
                $user = User::create([
                    'nama_user' => $this->nama_user,
                    'email_user' => strtolower(trim($this->email_user)),
                    'phone_number' => $this->phone_number,
                    'password' => Hash::make($this->password),
                    'role_id' => $roleId,
                ]);

                $guru = Guru::create([
                    'user_id' => $user->id,
                    'nip' => $this->nip,
                    'jenis_kelamin' => $this->jenis_kelamin,
                    'mata_pelajaran' => $this->mata_pelajaran,
                    'alamat' => $this->alamat, // ✅ Tambahan baru
                    'foto' => $imagePath,
                ]);
            }
        });

        $this->resetForm();
        session()->flash('message', 'Data guru berhasil disimpan.');
        $this->dispatch('close-modal', id: 'modal-form');
    }

    public function edit(int $id)
    {
        $this->resetValidation();
        $guru = Guru::with('user')->findOrFail($id);

        $this->guru_id = $guru->id;
        $this->user_id = $guru->user->id ?? null;
        $this->nama_user = $guru->user->nama_user ?? '';
        $this->email_user = $guru->user->email_user ?? '';
        $this->phone_number = $guru->user->phone_number ?? '';
        $this->nip = $guru->nip;
        $this->jenis_kelamin = $guru->jenis_kelamin;
        $this->mata_pelajaran = $guru->mata_pelajaran;
        $this->alamat = $guru->alamat ?? ''; // ✅ Tambahan baru
        $this->existingFoto = $guru->foto;
        $this->password = null;
        $this->password_confirmation = null;
    }

    public function delete(int $id)
    {
        $guru = Guru::with('user')->findOrFail($id);

        DB::transaction(function () use ($guru) {
            if ($guru->foto) {
                Storage::disk('public')->delete($guru->foto);
            }

            if ($guru->user) {
                $guru->user->delete();
            } else {
                $guru->delete();
            }
        });

        session()->flash('message', 'Data guru berhasil dihapus.');
        $this->resetForm();
    }

    #[Computed]
    public function listGuru()
    {
        return Guru::with('user')->orderByDesc('created_at')->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.admin.manajemen-guru');
    }

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
        ]);
        $this->jenis_kelamin = 'Laki-laki';
         $this->resetErrorBag(); 
        $this->resetValidation();
    }
}
