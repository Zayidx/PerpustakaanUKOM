<?php

namespace App\Livewire\Admin;

use App\Models\KategoriPengumuman;
use App\Models\Pengumuman as PengumumanModel;
use App\Models\RoleData;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.dashboard-layouts')]
#[Title('Manajemen Pengumuman')]
class Pengumuman extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $statusFilter = 'all';
    public int $perPage = 10;
    public array $perPageOptions = [10, 25, 50];
    public array $statusOptions = [
        'all' => 'Semua Status',
        'draft' => 'Draft',
        'published' => 'Published',
    ];

    public ?int $pengumumanId = null;
    public string $judul = '';
    public ?int $kategori_pengumuman_id = null;
    public ?int $owner_id = null;
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
        'owner_id.required' => 'Penanggung jawab wajib dipilih.',
        'owner_id.exists' => 'Penanggung jawab tidak valid.',
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
            'owner_id' => ['required', 'exists:users,id'],
            'status' => ['required', 'in:draft,published'],
            'thumbnail_url' => ['nullable', 'url'],
            'thumbnail_caption' => ['nullable', 'string', 'max:255'],
            'konten' => ['required', 'string', 'min:20'],
        ];
    }

    public function updatedSearch($value): void
    {
        $this->search = trim((string) $value);
        $this->resetPage();
    }

    public function updatedStatusFilter($value): void
    {
        if (! array_key_exists($value, $this->statusOptions)) {
            $this->statusFilter = 'all';
        }

        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = in_array((int) $value, $this->perPageOptions, true)
            ? (int) $value
            : $this->perPageOptions[0];

        $this->resetPage();
    }

    public function render()
    {
        $pengumuman = PengumumanModel::query()
            ->with(['kategori', 'owner.role'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('judul', 'like', '%' . $this->search . '%')
                        ->orWhere('konten', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.pengumuman', [
            'pengumumanList' => $pengumuman,
            'kategoriOptions' => KategoriPengumuman::orderBy('nama')->get(),
            'ownerOptions' => $this->ownerOptions(),
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('initialize-editor', content: $this->konten);
    }

    public function edit(int $id): void
    {
        $this->resetValidation();

        $pengumuman = PengumumanModel::findOrFail($id);

        $this->pengumumanId = $pengumuman->id;
        $this->judul = $pengumuman->judul;
        $this->kategori_pengumuman_id = $pengumuman->kategori_pengumuman_id;
        $this->owner_id = $pengumuman->owner_id;
        $this->status = $pengumuman->status;
        $this->thumbnail_url = $pengumuman->thumbnail_url;
        $this->thumbnail_caption = $pengumuman->thumbnail_caption;
        $this->konten = $pengumuman->konten;

        $this->dispatch('initialize-editor', content: $this->konten);
    }

    public function save(): void
    {
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
                'owner_id' => $data['owner_id'],
                'thumbnail_url' => $data['thumbnail_url'],
                'thumbnail_caption' => $data['thumbnail_caption'],
                'konten' => $data['konten'],
                'status' => $data['status'],
                'published_at' => $publishedAt,
            ]
        );

        $this->pengumumanId = $pengumuman->id;

        session()->flash('message', $isUpdate ? 'Pengumuman berhasil diperbarui.' : 'Pengumuman baru berhasil dibuat.');

        $this->dispatch('close-modal', id: 'modal-pengumuman');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $pengumuman = PengumumanModel::findOrFail($id);
        $pengumuman->delete();

        session()->flash('message', 'Pengumuman berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->reset([
            'pengumumanId',
            'judul',
            'kategori_pengumuman_id',
            'owner_id',
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

    private function ownerOptions()
    {
        $allowedRoles = RoleData::whereIn('nama_role', ['Administrator', 'Petugas'])
            ->pluck('id');

        return User::query()
            ->with('role')
            ->whereIn('role_id', $allowedRoles)
            ->orderBy('nama_user')
            ->get();
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
}
