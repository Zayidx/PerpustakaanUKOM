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

    #[Title('Halaman Manajemen Petugas Perpus')]
    #[Url(except: "")]
    #[Layout('components.layouts.dashboard-layouts')]
    public int $perPage = 5;

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
        'nama.required' => 'Nama petugas wajib diisi.',
        'nama.string' => 'Nama petugas harus berupa teks.',
        'nama.max' => 'Nama petugas maksimal :max karakter.',

        'email.required' => 'Email petugas wajib diisi.',
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

    public function updatedPerPage(): void
    {
        $this->perPage = max(1, (int) $this->perPage);
        $this->resetPage();
    }

    protected function rules(): array
    {
        $passwordRules = $this->petugas_id ? ['nullable'] : ['required'];
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
            'nip' => [
                'required',
                'string',
                'max:30',
                Rule::unique('petugas', 'nip')->ignore($this->petugas_id),
            ],
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

        $roleId = RoleData::firstOrCreate(
            ['nama_role' => 'Petugas'],
            [
                'deskripsi_role' => 'Pengelola operasional perpustakaan.',
                'icon_role' => 'bi-person-badge',
            ]
        )->id;

        $imagePath = $this->existingFoto;
        if ($this->foto instanceof TemporaryUploadedFile) {
            Storage::disk('public')->makeDirectory('admin/foto-petugas');
            if ($this->existingFoto) {
                Storage::disk('public')->delete($this->existingFoto);
            }
            $imagePath = $this->foto->store('admin/foto-petugas', 'public');
        }

        $nama = trim($this->nama);
        $email = strtolower(trim($this->email));
        $phone = trim($this->phone_number);
        $nip = trim($this->nip);
        $alamat = $this->alamat ? trim($this->alamat) : null;

        DB::transaction(function () use ($roleId, $imagePath, $nama, $email, $phone, $nip, $alamat) {
            if ($this->petugas_id) {
                $petugas = PetugasPerpus::with('user')->findOrFail($this->petugas_id);
                $user = $petugas->user;
                $user->nama_user = $nama;
                $user->email_user = $email;
                $user->phone_number = $phone;
                if ($this->password) {
                    $user->password = Hash::make($this->password);
                }
                $user->role_id = $roleId;
                $user->save();

                $petugas->update([
                    'nip' => $nip,
                    'alamat' => $alamat,
                    'jenis_kelamin' => $this->jenis_kelamin,
                    'foto' => $imagePath,
                ]);

                return;
            }

            $user = User::create([
                'nama_user' => $nama,
                'email_user' => $email,
                'phone_number' => $phone,
                'password' => Hash::make($this->password),
                'role_id' => $roleId,
            ]);

            $petugas = PetugasPerpus::create([
                'user_id' => $user->id,
                'nip' => $nip,
                'alamat' => $alamat,
                'jenis_kelamin' => $this->jenis_kelamin,
                'foto' => $imagePath,
            ]);

            $this->petugas_id = $petugas->id;
            $this->user_id = $user->id;
        });

        $this->resetForm();
        session()->flash('message', 'Data petugas berhasil disimpan.');
        $this->dispatch('close-modal', id: 'modal-form');
    }

    public function edit(int $id): void
    {
        $this->resetValidation();
        $petugas = PetugasPerpus::with('user')->findOrFail($id);

        $this->petugas_id = $petugas->id;
        $this->user_id = $petugas->user->id ?? null;
        $this->nama = $petugas->user->nama_user ?? '';
        $this->email = $petugas->user->email_user ?? '';
        $this->phone_number = $petugas->user->phone_number ?? '';
        $this->nip = $petugas->nip;
        $this->alamat = $petugas->alamat ?? '';
        $this->jenis_kelamin = $petugas->jenis_kelamin;
        $this->existingFoto = $petugas->foto ?? '';
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
        $petugas = PetugasPerpus::with('user')->findOrFail($id);

        DB::transaction(function () use ($petugas) {
            if ($petugas->foto) {
                Storage::disk('public')->delete($petugas->foto);
            }

            if ($petugas->user) {
                $petugas->user->delete();
                return;
            }

            $petugas->delete();
        });

        session()->flash('message', 'Data petugas berhasil dihapus.');
        $this->resetForm();
    }

    #[Computed]
    public function listPetugasPerpus()
    {
        return PetugasPerpus::with('user')
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.admin.manajemen-petugas');
    }

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
        ]);

        $this->jenis_kelamin = 'laki-laki';
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
