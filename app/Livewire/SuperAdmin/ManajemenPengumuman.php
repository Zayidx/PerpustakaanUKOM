<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\Concerns\HandlesAlerts;
use App\Models\KategoriPengumuman;
use App\Models\Pengumuman as PengumumanModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.dashboard-layouts')]
#[Title('Manajemen Pengumuman')]
class ManajemenPengumuman extends Component
{
    use HandlesAlerts;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $statusFilter = 'all';
    public int $perPage = 10;
    public array $perPageOptions = [10, 25, 50];
    public string $sort = 'published_desc';
    public array $sortOptions = [
        'published_desc' => 'Publikasi Terbaru',
        'published_asc' => 'Publikasi Terlama',
        'judul_asc' => 'Judul A-Z',
        'judul_desc' => 'Judul Z-A',
        'created_desc' => 'Dibuat Terbaru',
        'created_asc' => 'Dibuat Terlama',
        'admin_name_asc' => 'Penulis A-Z',
        'admin_name_desc' => 'Penulis Z-A',
    ];
    public array $statusOptions = [
        'all' => 'Semua Status',
        'draft' => 'Draft',
        'published' => 'Published',
    ];

    public ?int $pengumumanId = null;
    public string $judul = '';
    public ?int $kategori_pengumuman_id = null;
    public ?int $admin_id = null;
    public string $admin_name = '';
    public string $status = 'draft';
    public ?string $thumbnail_url = null;
    public ?string $thumbnail_caption = null;
    public string $konten = '';

    protected $messages = [
        'judul.required' => 'Judul pengumuman wajib diisi.',
        'judul.min' => 'Judul pengumuman minimal 5 karakter.',
        'judul.max' => 'Judul pengumuman maksimal 255 karakter.',
        'kategori_pengumuman_id.required' => 'Kategori pengumuman wajib dipilih.',
        'kategori_pengumuman_id.exists' => 'Kategori pengumuman tidak valid.',
        'admin_id.required' => 'Admin penulis wajib dipilih.',
        'admin_id.exists' => 'Admin penulis tidak valid.',
        'status.required' => 'Status pengumuman wajib dipilih.',
        'status.in' => 'Status pengumuman tidak valid.',
        'thumbnail_url.url' => 'URL thumbnail tidak valid.',
        'thumbnail_caption.max' => 'Caption thumbnail maksimal 255 karakter.',
        'konten.required' => 'Konten pengumuman tidak boleh kosong.',
        'konten.min' => 'Konten pengumuman minimal 20 karakter.',
    ];

    protected function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'min:5', 'max:255'], 
            'kategori_pengumuman_id' => ['required', 'exists:kategori_pengumuman,id'], 
            'admin_id' => ['required', 'exists:users,id'], 
            'status' => ['required', 'in:draft,published'], 
            'thumbnail_url' => ['nullable', 'url'], 
            'thumbnail_caption' => ['nullable', 'string', 'max:255'], 
            'konten' => ['required', 'string', 'min:20'], 
        ];
    } 

    public function mount(): void
    {
        $this->perPage = $this->normalizePerPage($this->perPage); 
        $this->statusFilter = $this->normalizeStatus($this->statusFilter); 
        $this->sort = $this->normalizeSort($this->sort); 
        $this->search = trim((string) $this->search); 
    } 

    public function updatedSearch($value): void
    {
        $this->search = trim((string) $value); 
        $this->resetPage(); 
    } 

    public function updatedStatusFilter($value): void
    {
        $this->statusFilter = $this->normalizeStatus($value); 
        $this->resetPage(); 
    } 

    public function updatedSort($value): void
    {
        $this->sort = $this->normalizeSort($value); 
        $this->resetPage(); 
    } 

    public function updatedPerPage($value): void
    {
        $this->perPage = $this->normalizePerPage($value); 

        $this->resetPage(); 
    } 

    public function render()
    {
        [$sortField, $sortDirection, $requiresJoin] = $this->resolveSort(); 

        $query = PengumumanModel::query()
            ->with(['kategori', 'admin.role']) 
            ->when($this->search !== '', function ($query) { 
                $query->where(function ($subQuery) { 
                    $subQuery->where('judul', 'like', '%' . $this->search . '%') 
                        ->orWhere('konten', 'like', '%' . $this->search . '%'); 
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) { 
                $query->where('status', $this->statusFilter); 
            });

        if ($requiresJoin) { 
            $query->leftJoin('users', 'users.id', '=', 'pengumuman.admin_id') 
                ->select('pengumuman.*') 
                ->orderBy($sortField, $sortDirection); 
        } else {
            $query->orderBy($sortField, $sortDirection); 
        }

        $pengumuman = $query->paginate($this->perPage); 

        return view('livewire.super-admin.manajemen-pengumuman', [ 
            'pengumumanList' => $pengumuman, 
            'kategoriOptions' => KategoriPengumuman::orderBy('nama')->get(), 
        ]);
    } 

    public function create(): void
    {
        $this->resetForm(); 
        $this->assignCurrentAdmin(); 
        $this->dispatch('initialize-editor', content: $this->konten); 
    } 

    public function edit(int $id): void
    {
        $this->resetValidation(); 

        $pengumuman = PengumumanModel::findOrFail($id); 

        $this->pengumumanId = $pengumuman->id; 
        $this->judul = $pengumuman->judul; 
        $this->kategori_pengumuman_id = $pengumuman->kategori_pengumuman_id; 
        $this->status = $pengumuman->status; 
        $this->thumbnail_url = $pengumuman->thumbnail_url; 
        $this->thumbnail_caption = $pengumuman->thumbnail_caption; 
        $this->konten = $pengumuman->konten; 

        $this->assignCurrentAdmin(); 
        $this->dispatch('initialize-editor', content: $this->konten); 
    } 

    public function save(): void
    {
        $this->ensureAdminAssigned(); 
        $data = $this->validate(); 
        $isUpdate = (bool) $this->pengumumanId; 

        $slug = $this->generateSlug($data['judul']); 
        $publishedAt = $this->status === 'published' 
            ? (PengumumanModel::find($this->pengumumanId)?->published_at ?? now()) 
            : null; 

        $pengumuman = PengumumanModel::updateOrCreate( 
            ['id' => $this->pengumumanId], 
            [ 
                'judul' => $data['judul'], 
                'slug' => $slug, 
                'kategori_pengumuman_id' => $data['kategori_pengumuman_id'], 
                'admin_id' => $data['admin_id'], 
                'thumbnail_url' => $data['thumbnail_url'], 
                'thumbnail_caption' => $data['thumbnail_caption'], 
                'konten' => $data['konten'], 
                'status' => $data['status'], 
                'published_at' => $publishedAt, 
            ]
        );

        $this->pengumumanId = $pengumuman->id; 

        $this->flashSuccess($isUpdate ? 'Pengumuman berhasil diperbarui.' : 'Pengumuman baru berhasil dibuat.');

        $this->dispatch('close-modal', id: 'modal-pengumuman'); 
        $this->resetForm(); 
    } 

    public function delete(int $id): void
    {
        $pengumuman = PengumumanModel::findOrFail($id); 
        $pengumuman->delete(); 

        $this->flashSuccess('Pengumuman berhasil dihapus.');
    } 

    private function resetForm(): void
    {
        $this->reset([
            'pengumumanId',
            'judul',
            'kategori_pengumuman_id',
            'admin_id',
            'admin_name',
            'status',
            'thumbnail_url',
            'thumbnail_caption',
            'konten',
        ]); 

        $this->status = 'draft'; 
        $this->thumbnail_url = null; 
        $this->thumbnail_caption = null; 
        $this->resetValidation(); 
    } 

    private function assignCurrentAdmin(): void
    {
        $user = Auth::user(); 
        $this->admin_id = $user?->id; 
        $this->admin_name = $user?->nama_user ?? 'Admin tidak tersedia'; 
    } 

    private function ensureAdminAssigned(): void
    {
        $this->assignCurrentAdmin(); 
    } 

    private function generateSlug(string $judul): string
    {
        $baseSlug = Str::slug($judul) ?: 'pengumuman'; 
        $slug = $baseSlug;
        $counter = 1;

        while (
            PengumumanModel::where('slug', $slug) 
                ->when($this->pengumumanId, fn ($query) => $query->where('id', '!=', $this->pengumumanId)) 
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter; 
            $counter++;
        }

        return $slug;
    } 

    private function normalizePerPage($value): int
    {
        $value = (int) $value; 

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0]; 
    } 

    private function normalizeStatus(string $value): string
    {
        return array_key_exists($value, $this->statusOptions) ? $value : 'all'; 
    } 

    private function normalizeSort(string $value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'published_desc'; 
    } 

    private function resolveSort(): array
    {
        return match ($this->sort) { 
            'published_asc' => ['pengumuman.published_at', 'asc', false], 
            'published_desc' => ['pengumuman.published_at', 'desc', false], 
            'judul_asc' => ['pengumuman.judul', 'asc', false], 
            'judul_desc' => ['pengumuman.judul', 'desc', false], 
            'created_asc' => ['pengumuman.created_at', 'asc', false], 
            'created_desc' => ['pengumuman.created_at', 'desc', false], 
            'admin_name_asc' => ['users.nama_user', 'asc', true], 
            'admin_name_desc' => ['users.nama_user', 'desc', true], 
            default => ['pengumuman.published_at', 'desc', false], 
        };
    } 
}
