<?php

namespace App\Livewire\SuperAdmin;

use App\Models\RoleData;
use App\Models\AdminPerpus;
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

class ManajemenAdminPerpus extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Manajemen Admin Perpus')]
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

    public $admin_perpus_id;
    public $user_id;
    public $nama_user = '';
    public $email_user = '';
    public $phone_number = '';
    public $password;
    public $password_confirmation;
    public $nip = '';
    public $jenis_kelamin = 'Laki-laki';
    public $mata_pelajaran = '';
    public $alamat = ''; 
    public $foto;
    public $existingFoto = '';

    protected $messages = [
        'nama_user.required' => 'Nama Admin Perpus wajib diisi.',
        'email_user.required' => 'Email wajib diisi.',
        'email_user.email' => 'Format email tidak valid.',
        'email_user.unique' => 'Email sudah digunakan.',
        'phone_number.required' => 'Nomor telepon wajib diisi.',
        'nip.required' => 'NIP wajib diisi.',
        'nip.unique' => 'NIP sudah terdaftar.',
        'mata_pelajaran.required' => 'Mata pelajaran wajib diisi.',
        'alamat.required' => 'Alamat wajib diisi.', 
        'foto.image' => 'File harus berupa gambar.',
        'foto.max' => 'Ukuran foto maksimal 2MB.',
    ];

    protected function rules(): array
    {
        $passwordRules = $this->admin_perpus_id ? ['nullable', 'min:8'] : ['required', 'min:8']; 
        $passwordConfirmationRules = $this->password ? ['same:password'] : ['nullable']; 

        return [
            'nama_user' => ['required', 'string', 'max:255'], 
            'email_user' => ['required', 'email', Rule::unique('users', 'email_user')->ignore($this->user_id)], 
            'phone_number' => ['required', 'string', 'max:20'], 
            'password' => $passwordRules, 
            'password_confirmation' => $passwordConfirmationRules, 
            'nip' => ['required', 'string', 'max:30', Rule::unique('admin_perpus', 'nip')->ignore($this->admin_perpus_id)], 
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'], 
            'mata_pelajaran' => ['required', 'string', 'max:100'], 
            'alamat' => ['required', 'string', 'max:255'], 
            'foto' => ['nullable', 'image', 'max:2048'], 
        ];
    } 

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

    public function create()
    {
        $this->resetForm(); 
        $this->resetValidation(); 
    } 

    public function store()
    {
        $this->validate(); 

        $roleId = RoleData::where('nama_role', 'AdminPerpus')->value('id'); 
        if (!$roleId) { 
            session()->flash('message', 'Role Admin Perpus belum dikonfigurasi.');
            return;
        }

        $imagePath = $this->existingFoto; 
        if ($this->foto instanceof TemporaryUploadedFile) { 
            Storage::disk('public')->makeDirectory('admin/foto-admin-perpus'); 
            if ($this->existingFoto) { 
                Storage::disk('public')->delete($this->existingFoto);
            }
            $imagePath = $this->foto->store('admin/foto-admin-perpus', 'public'); 
        }

        DB::transaction(function () use ($roleId, $imagePath) { 
            if ($this->admin_perpus_id) { 
                
                $adminPerpus = AdminPerpus::with('user')->findOrFail($this->admin_perpus_id); 
                $user = $adminPerpus->user; 

                $user->update([ 
                    'nama_user' => $this->nama_user, 
                    'email_user' => strtolower(trim($this->email_user)), 
                    'phone_number' => $this->phone_number, 
                    'role_id' => $roleId, 
                    'password' => $this->password ? Hash::make($this->password) : $user->password, 
                ]);

                $adminPerpus->update([ 
                    'nip' => $this->nip, 
                    'jenis_kelamin' => $this->jenis_kelamin, 
                    'mata_pelajaran' => $this->mata_pelajaran, 
                    'alamat' => $this->alamat, 
                    'foto' => $imagePath, 
                ]);
            } else { 
                
                $user = User::create([ 
                    'nama_user' => $this->nama_user, 
                    'email_user' => strtolower(trim($this->email_user)), 
                    'phone_number' => $this->phone_number, 
                    'password' => Hash::make($this->password), 
                    'role_id' => $roleId, 
                ]);

                $adminPerpus = AdminPerpus::create([ 
                    'user_id' => $user->id, 
                    'nip' => $this->nip, 
                    'jenis_kelamin' => $this->jenis_kelamin, 
                    'mata_pelajaran' => $this->mata_pelajaran, 
                    'alamat' => $this->alamat, 
                    'foto' => $imagePath, 
                ]);
            }
        });

        $this->resetForm(); 
        session()->flash('message', 'Data Admin Perpus berhasil disimpan.'); 
        $this->dispatch('close-modal', id: 'modal-form'); 
    } 

    public function edit(int $id)
    {
        $this->resetValidation(); 
        $adminPerpus = AdminPerpus::with('user')->findOrFail($id); 

        $this->admin_perpus_id = $adminPerpus->id; 
        $this->user_id = $adminPerpus->user->id ?? null; 
        $this->nama_user = $adminPerpus->user->nama_user ?? ''; 
        $this->email_user = $adminPerpus->user->email_user ?? ''; 
        $this->phone_number = $adminPerpus->user->phone_number ?? ''; 
        $this->nip = $adminPerpus->nip; 
        $this->jenis_kelamin = $adminPerpus->jenis_kelamin; 
        $this->mata_pelajaran = $adminPerpus->mata_pelajaran; 
        $this->alamat = $adminPerpus->alamat ?? ''; 
        $this->existingFoto = $adminPerpus->foto; 
        $this->password = null; 
        $this->password_confirmation = null; 
    } 

    public function delete(int $id)
    {
        $adminPerpus = AdminPerpus::with('user')->findOrFail($id); 

        DB::transaction(function () use ($adminPerpus) { 
            if ($adminPerpus->foto) { 
                Storage::disk('public')->delete($adminPerpus->foto); 
            }

            if ($adminPerpus->user) { 
                $adminPerpus->user->delete(); 
            } else { 
                $adminPerpus->delete(); 
            }
        });

        session()->flash('message', 'Data Admin Perpus berhasil dihapus.'); 
        $this->resetForm(); 
    } 

    #[Computed]
    public function listAdminPerpus()
    {
        [$sortField, $sortDirection] = $this->resolveSort(); 

        $query = AdminPerpus::query()
            ->with('user') 
            ->when($this->search !== '', function ($query) { 
                $term = '%' . $this->search . '%';

                $query->where(function ($query) use ($term) { 
                    $query->where('admin_perpus.nip', 'like', $term) 
                        ->orWhere('admin_perpus.mata_pelajaran', 'like', $term) 
                        ->orWhere('admin_perpus.alamat', 'like', $term) 
                        ->orWhereHas('user', function ($userQuery) use ($term) { 
                            $userQuery->where('nama_user', 'like', $term) 
                                ->orWhere('email_user', 'like', $term) 
                                ->orWhere('phone_number', 'like', $term); 
                        });
                });
            })
            ->when($this->genderFilter !== 'all', function ($query) { 
                $query->where('admin_perpus.jenis_kelamin', $this->genderFilter); 
            });

        if ($sortField === 'users.nama_user') { 
            $query->leftJoin('users', 'users.id', '=', 'admin_perpus.user_id') 
                ->select('admin_perpus.*') 
                ->orderBy('users.nama_user', $sortDirection); 
        } else {
            $query->orderBy($sortField, $sortDirection); 
        }

        return $query->paginate($this->perPage); 
    } 

    public function render()
    {
        return view('livewire.super-admin.manajemen-admin-perpus');
    } 

    private function resetForm(): void
    {
        $this->reset([
            'admin_perpus_id',
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
            'created_at_asc' => ['admin_perpus.created_at', 'asc'], 
            'nama_user_asc' => ['users.nama_user', 'asc'], 
            'nama_user_desc' => ['users.nama_user', 'desc'], 
            'mata_pelajaran_asc' => ['admin_perpus.mata_pelajaran', 'asc'], 
            'mata_pelajaran_desc' => ['admin_perpus.mata_pelajaran', 'desc'], 
            default => ['admin_perpus.created_at', 'desc'], 
        };
    } 

    private function normalizePerPage($value): int
    {
        $value = (int) $value; 

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0]; 
    } 
}
