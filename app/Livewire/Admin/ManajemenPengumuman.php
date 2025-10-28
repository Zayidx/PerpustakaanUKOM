<?php

namespace App\Livewire\Admin;

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
            'judul' => ['required', 'string', 'min:5', 'max:255'], // Judul wajib, minimal 5 karakter, maksimal 255 karakter
            'kategori_pengumuman_id' => ['required', 'exists:kategori_pengumuman,id'], // ID kategori wajib dan harus ada di tabel kategori_pengumuman
            'admin_id' => ['required', 'exists:users,id'], // ID admin wajib dan harus ada di tabel users
            'status' => ['required', 'in:draft,published'], // Status wajib dan harus salah satu dari draft/published
            'thumbnail_url' => ['nullable', 'url'], // URL thumbnail opsional, validasi format URL
            'thumbnail_caption' => ['nullable', 'string', 'max:255'], // Caption thumbnail opsional, maksimal 255 karakter
            'konten' => ['required', 'string', 'min:20'], // Konten wajib, minimal 20 karakter
        ];
    } // Aturan validasi untuk form pengumuman

    public function mount(): void
    {
        $this->perPage = $this->normalizePerPage($this->perPage); // Pastikan nilai perPage valid
        $this->statusFilter = $this->normalizeStatus($this->statusFilter); // Pastikan filter status valid
        $this->sort = $this->normalizeSort($this->sort); // Pastikan opsi sort valid
        $this->search = trim((string) $this->search); // Normalisasi kata kunci pencarian
    } // Inisialisasi komponen dengan nilai valid

    public function updatedSearch($value): void
    {
        $this->search = trim((string) $value); // Hapus spasi di awal/akhir kata kunci pencarian
        $this->resetPage(); // Reset pagination ke halaman pertama saat pencarian berubah
    } // Hapus spasi pada input pencarian dan reset pagination

    public function updatedStatusFilter($value): void
    {
        $this->statusFilter = $this->normalizeStatus($value); // Pastikan nilai filter status valid
        $this->resetPage(); // Reset pagination ke halaman pertama saat filter status berubah
    } // Atur filter status dan reset pagination

    public function updatedSort($value): void
    {
        $this->sort = $this->normalizeSort($value); // Pastikan nilai sort valid
        $this->resetPage(); // Reset pagination ke halaman pertama saat sorting berubah
    } // Atur opsi sorting dan reset pagination

    public function updatedPerPage($value): void
    {
        $this->perPage = $this->normalizePerPage($value); // Pastikan nilai perPage valid sesuai opsi

        $this->resetPage(); // Reset pagination ke halaman pertama saat jumlah per halaman berubah
    } // Atur jumlah item per halaman dan reset pagination

    public function render()
    {
        [$sortField, $sortDirection, $requiresJoin] = $this->resolveSort(); // Ambil field, arah sorting, dan info join

        $query = PengumumanModel::query()
            ->with(['kategori', 'admin.role']) // Muat relasi kategori dan role admin
            ->when($this->search !== '', function ($query) { // Jika ada kata kunci pencarian
                $query->where(function ($subQuery) { // Cari berdasarkan judul atau konten
                    $subQuery->where('judul', 'like', '%' . $this->search . '%') // Cari berdasarkan judul
                        ->orWhere('konten', 'like', '%' . $this->search . '%'); // Cari berdasarkan konten
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) { // Jika ada filter status
                $query->where('status', $this->statusFilter); // Filter berdasarkan status
            });

        if ($requiresJoin) { // Jika sorting membutuhkan join ke tabel users
            $query->leftJoin('users', 'users.id', '=', 'pengumuman.admin_id') // Join ke tabel users
                ->select('pengumuman.*') // Pilih semua field dari pengumuman
                ->orderBy($sortField, $sortDirection); // Urutkan berdasarkan field join
        } else {
            $query->orderBy($sortField, $sortDirection); // Urutkan berdasarkan field biasa
        }

        $pengumuman = $query->paginate($this->perPage); // Terapkan pagination

        return view('livewire.admin.manajemen-pengumuman', [ // Render tampilan komponen
            'pengumumanList' => $pengumuman, // Kirim daftar pengumuman ke view
            'kategoriOptions' => KategoriPengumuman::orderBy('nama')->get(), // Kirim opsi kategori ke view
        ]);
    } // Render tampilan komponen dengan data pengumuman

    public function create(): void
    {
        $this->resetForm(); // Reset form ke kondisi awal
        $this->assignCurrentAdmin(); // Tetapkan admin saat ini sebagai penulis
        $this->dispatch('initialize-editor', content: $this->konten); // Kirim event untuk menginisialisasi editor konten
    } // Reset form dan tetapkan admin saat ini untuk membuat pengumuman baru

    public function edit(int $id): void
    {
        $this->resetValidation(); // Hapus pesan validasi sebelumnya

        $pengumuman = PengumumanModel::findOrFail($id); // Ambil data pengumuman berdasarkan ID

        $this->pengumumanId = $pengumuman->id; // Set ID pengumuman yang akan diedit
        $this->judul = $pengumuman->judul; // Muat judul pengumuman
        $this->kategori_pengumuman_id = $pengumuman->kategori_pengumuman_id; // Muat ID kategori pengumuman
        $this->status = $pengumuman->status; // Muat status pengumuman
        $this->thumbnail_url = $pengumuman->thumbnail_url; // Muat URL thumbnail pengumuman
        $this->thumbnail_caption = $pengumuman->thumbnail_caption; // Muat caption thumbnail pengumuman
        $this->konten = $pengumuman->konten; // Muat konten pengumuman

        $this->assignCurrentAdmin(); // Tetapkan admin saat ini sebagai penulis
        $this->dispatch('initialize-editor', content: $this->konten); // Kirim event untuk menginisialisasi editor konten
    } // Muat data pengumuman untuk mode edit

    public function save(): void
    {
        $this->ensureAdminAssigned(); // Pastikan admin sudah ditetapkan sebagai penulis
        $data = $this->validate(); // Jalankan validasi pada input
        $isUpdate = (bool) $this->pengumumanId; // Cek apakah dalam mode update

        $slug = $this->generateSlug($data['judul']); // Generate slug unik berdasarkan judul
        $publishedAt = $this->status === 'published' // Jika status published
            ? (PengumumanModel::find($this->pengumumanId)?->published_at ?? now()) // Gunakan waktu published lama atau sekarang
            : null; // Jika draft, set published_at ke null

        $pengumuman = PengumumanModel::updateOrCreate( // Buat atau update data pengumuman
            ['id' => $this->pengumumanId], // Kondisi pencarian
            [ // Data yang akan disimpan
                'judul' => $data['judul'], // Judul pengumuman
                'slug' => $slug, // Slug unik untuk URL
                'kategori_pengumuman_id' => $data['kategori_pengumuman_id'], // ID kategori pengumuman
                'admin_id' => $data['admin_id'], // ID admin penulis
                'thumbnail_url' => $data['thumbnail_url'], // URL thumbnail
                'thumbnail_caption' => $data['thumbnail_caption'], // Caption thumbnail
                'konten' => $data['konten'], // Konten pengumuman
                'status' => $data['status'], // Status pengumuman
                'published_at' => $publishedAt, // Waktu publikasi
            ]
        );

        $this->pengumumanId = $pengumuman->id; // Simpan ID pengumuman baru

        session()->flash('message', $isUpdate ? 'Pengumuman berhasil diperbarui.' : 'Pengumuman baru berhasil dibuat.'); // Tampilkan pesan sukses

        $this->dispatch('close-modal', id: 'modal-pengumuman'); // Kirim event untuk menutup modal
        $this->resetForm(); // Reset form setelah disimpan
    } // Simpan data pengumuman baru atau perbarui yang sudah ada

    public function delete(int $id): void
    {
        $pengumuman = PengumumanModel::findOrFail($id); // Ambil data pengumuman berdasarkan ID
        $pengumuman->delete(); // Hapus data pengumuman dari database

        session()->flash('message', 'Pengumuman berhasil dihapus.'); // Tampilkan pesan sukses
    } // Hapus data pengumuman

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
        ]); // Reset semua properti form ke nilai awal

        $this->status = 'draft'; // Set default status ke draft
        $this->thumbnail_url = null; // Reset thumbnail URL
        $this->thumbnail_caption = null; // Reset caption thumbnail
        $this->resetValidation(); // Hapus status validasi
    } // Reset form ke kondisi awal

    private function assignCurrentAdmin(): void
    {
        $user = Auth::user(); // Ambil user yang sedang login
        $this->admin_id = $user?->id; // Tetapkan ID admin
        $this->admin_name = $user?->nama_user ?? 'Admin tidak tersedia'; // Tetapkan nama admin
    } // Tetapkan admin saat ini sebagai penulis pengumuman

    private function ensureAdminAssigned(): void
    {
        $this->assignCurrentAdmin(); // Pastikan admin sudah ditetapkan sebagai penulis
    } // Pastikan admin sudah ditetapkan sebelum menyimpan

    private function generateSlug(string $judul): string
    {
        $baseSlug = Str::slug($judul) ?: 'pengumuman'; // Generate slug dasar dari judul
        $slug = $baseSlug;
        $counter = 1;

        while (
            PengumumanModel::where('slug', $slug) // Cek apakah slug sudah ada di database
                ->when($this->pengumumanId, fn ($query) => $query->where('id', '!=', $this->pengumumanId)) // Kecualikan ID saat edit
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter; // Tambahkan angka jika slug sudah digunakan
            $counter++;
        }

        return $slug;
    } // Generate slug unik untuk pengumuman

    private function normalizePerPage($value): int
    {
        $value = (int) $value; // Konversi nilai ke integer

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0]; // Kembalikan nilai valid atau default
    } // Pastikan nilai perPage valid sesuai opsi yang tersedia

    private function normalizeStatus(string $value): string
    {
        return array_key_exists($value, $this->statusOptions) ? $value : 'all'; // Kembalikan nilai status valid atau default
    } // Pastikan nilai status valid sesuai opsi yang tersedia

    private function normalizeSort(string $value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'published_desc'; // Kembalikan nilai sort valid atau default
    } // Pastikan nilai sort valid sesuai opsi yang tersedia

    private function resolveSort(): array
    {
        return match ($this->sort) { // Kembalikan field, arah sorting, dan info join berdasarkan opsi
            'published_asc' => ['pengumuman.published_at', 'asc', false], // Urutkan berdasarkan published_at ascending, tanpa join
            'published_desc' => ['pengumuman.published_at', 'desc', false], // Urutkan berdasarkan published_at descending, tanpa join
            'judul_asc' => ['pengumuman.judul', 'asc', false], // Urutkan berdasarkan judul ascending, tanpa join
            'judul_desc' => ['pengumuman.judul', 'desc', false], // Urutkan berdasarkan judul descending, tanpa join
            'created_asc' => ['pengumuman.created_at', 'asc', false], // Urutkan berdasarkan created_at ascending, tanpa join
            'created_desc' => ['pengumuman.created_at', 'desc', false], // Urutkan berdasarkan created_at descending, tanpa join
            'admin_name_asc' => ['users.nama_user', 'asc', true], // Urutkan berdasarkan nama admin ascending, perlu join
            'admin_name_desc' => ['users.nama_user', 'desc', true], // Urutkan berdasarkan nama admin descending, perlu join
            default => ['pengumuman.published_at', 'desc', false], // Default: urutkan berdasarkan published_at descending, tanpa join
        };
    } // Ambil field, arah sorting, dan info join
}
