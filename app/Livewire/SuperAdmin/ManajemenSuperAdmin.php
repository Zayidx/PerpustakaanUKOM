<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\Concerns\HandlesAlerts;
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
use App\Models\SuperAdmin;
use App\Models\RoleData;
use App\Models\User;

class ManajemenSuperAdmin extends Component
{
    use HandlesAlerts;
    use WithFileUploads;
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    #[Title('Halaman Manajemen Super Admin')]
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

    public ?int $super_admin_id = null;
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
        $passwordRules = $this->super_admin_id ? ['nullable'] : ['required'];
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
                Rule::unique('super_admins', 'nip')->ignore($this->super_admin_id),
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
            ['nama_role' => 'SuperAdmin'],
            [
                'deskripsi_role' => 'Pengelola operasional perpustakaan.',
                'icon_role' => 'bi-person-badge',
            ]
        )->id;

        $imagePath = $this->existingFoto;

        if ($this->foto instanceof TemporaryUploadedFile) {
            Storage::disk('public')->makeDirectory('super-admin/foto-super-admins');

            if ($this->existingFoto) {
                Storage::disk('public')->delete($this->existingFoto);
            }

            $imagePath = $this->foto->store('super-admin/foto-super-admins', 'public');
        }

        $nama = trim($this->nama);
        $email = strtolower(trim($this->email));
        $phone = trim($this->phone_number);
        $nip = trim($this->nip);
        $alamat = $this->alamat ? trim($this->alamat) : null;

        DB::transaction(function () use ($roleId, $imagePath, $nama, $email, $phone, $nip, $alamat) {
            if ($this->super_admin_id) {
                $super_admins = SuperAdmin::with('user')->findOrFail($this->super_admin_id);
                $user = $super_admins->user;

                $user->nama_user = $nama;
                $user->email_user = $email;
                $user->phone_number = $phone;

                if ($this->password) {
                    $user->password = Hash::make($this->password);
                }

                $user->role_id = $roleId;
                $user->save();

                $super_admins->update([
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

            $super_admins = SuperAdmin::create([
                'user_id' => $user->id,
                'nip' => $nip,
                'alamat' => $alamat,
                'jenis_kelamin' => $this->jenis_kelamin,
                'foto' => $imagePath,
            ]);

            $this->super_admin_id = $super_admins->id;
            $this->user_id = $user->id;
        });

        $this->resetForm();
        $this->flashSuccess('Data Super Admin berhasil disimpan.');
        $this->dispatch('close-modal', id: 'modal-form');
    }

    public function edit(int $id): void
    {
        $this->resetValidation();

        $super_admins = SuperAdmin::with('user')->findOrFail($id);

        $this->super_admin_id = $super_admins->id;
        $this->user_id = $super_admins->user->id ?? null;
        $this->nama = $super_admins->user->nama_user ?? '';
        $this->email = $super_admins->user->email_user ?? '';
        $this->phone_number = $super_admins->user->phone_number ?? '';
        $this->nip = $super_admins->nip;
        $this->alamat = $super_admins->alamat ?? '';
        $this->jenis_kelamin = $super_admins->jenis_kelamin;
        $this->existingFoto = $super_admins->foto ?? '';
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
        $super_admins = SuperAdmin::with('user')->findOrFail($id);

        DB::transaction(function () use ($super_admins) {
            if ($super_admins->foto) {
                Storage::disk('public')->delete($super_admins->foto);
            }

            if ($super_admins->user) {
                $super_admins->user->delete();
                return;
            }

            $super_admins->delete();
        });

        $this->flashSuccess('Data Super Admin berhasil dihapus.');
        $this->resetForm();
    }

    #[Computed]
    public function listSuperAdmins()
    {
        [$sortField, $sortDirection] = $this->resolveSort();

        $query = SuperAdmin::query()
            ->with('user')
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('super_admins.nip', 'like', $term)
                        ->orWhere('super_admins.alamat', 'like', $term)
                        ->orWhereHas('user', function ($userQuery) use ($term) {
                            $userQuery->where('nama_user', 'like', $term)
                                ->orWhere('email_user', 'like', $term)
                                ->orWhere('phone_number', 'like', $term);
                        });
                });
            })
            ->when($this->genderFilter !== 'all', function ($query) {
                $query->where('super_admins.jenis_kelamin', $this->genderFilter);
            });

        if ($sortField === 'users.nama_user') {
            $query->leftJoin('users', 'users.id', '=', 'super_admins.user_id')
                ->select('super_admins.*')
                ->orderBy('users.nama_user', $sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.super-admin.manajemen-super-admin');
    }

    private function resetForm(): void
    {
        $this->reset([
            'super_admin_id',
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

    private function normalizeGender(string $value): string
    {
        return array_key_exists($value, $this->genderOptions) ? $value : 'all';
    }

    private function normalizeSort(string $value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'created_at_desc';
    }

    
    private function resolveSort(): array
    {
        return match ($this->sort) {
            'created_at_asc' => ['super_admins.created_at', 'asc'],
            'nama_user_asc' => ['users.nama_user', 'asc'],
            'nama_user_desc' => ['users.nama_user', 'desc'],
            default => ['super_admins.created_at', 'desc'],
        };
    }

    private function normalizePerPage($value): int
    {
        $value = (int) $value;

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0];
    }
}
