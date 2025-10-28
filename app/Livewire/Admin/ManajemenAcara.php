<?php

namespace App\Livewire\Admin;

use App\Models\Acara;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\KategoriAcara;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.dashboard-layouts')]
#[Title('Manajemen Acara')]
class ManajemenAcara extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $sort = 'mulai_at_desc';
    public array $sortOptions = [
        'mulai_at_desc' => 'Jadwal Terbaru',
        'mulai_at_asc' => 'Jadwal Terlama',
        'judul_asc' => 'Judul A-Z',
        'judul_desc' => 'Judul Z-A',
    ];

    public int $perPage = 10;
    public array $perPageOptions = [10, 25, 50];

    public ?int $acaraId = null;
    public string $judul = '';
    public string $lokasi = '';
    public ?int $kategori_acara_id = null;
    public ?string $poster_url = null;
    public ?string $deskripsi = null;
    public string $mulai_at_input = '';
    public ?string $selesai_at_input = null;

    protected $rules = [
        'judul' => ['required', 'string', 'max:255'],
        'lokasi' => ['required', 'string', 'max:255'],
        'kategori_acara_id' => ['required', 'exists:kategori_acara,id'],
        'poster_url' => ['nullable', 'url'],
        'mulai_at_input' => ['required', 'date'],
        'selesai_at_input' => ['nullable', 'date', 'after:mulai_at_input'],
        'deskripsi' => ['nullable', 'string'],
    ];

    protected $messages = [
        'judul.required' => 'Judul acara wajib diisi.',
        'lokasi.required' => 'Lokasi acara wajib diisi.',
        'kategori_acara_id.required' => 'Kategori acara wajib dipilih.',
        'kategori_acara_id.exists' => 'Kategori acara tidak valid.',
        'mulai_at_input.required' => 'Tanggal dan waktu mulai wajib diisi.',
        'selesai_at_input.after' => 'Waktu selesai harus setelah waktu mulai.',
        'poster_url.url' => 'Poster harus berupa tautan yang valid.',
    ];

    public function mount(): void
    {
        $this->perPage = $this->normalizePerPage($this->perPage); // Pastikan nilai perPage valid
        $this->sort = $this->normalizeSort($this->sort); // Pastikan opsi sort valid
    } // Inisialisasi nilai awal komponen

    public function updatedSearch(): void
    {
        $this->search = trim((string) $this->search); // Hapus spasi di awal/akhir kata kunci pencarian
        $this->resetPage(); // Reset pagination ke halaman pertama saat pencarian berubah
    } // Hapus spasi pada input pencarian dan reset pagination

    public function updatedSort($value): void
    {
        $this->sort = $this->normalizeSort((string) $value); // Pastikan nilai sort valid
        $this->resetPage(); // Reset pagination ke halaman pertama saat sorting berubah
    } // Atur opsi sorting dan reset pagination

    public function updatedPerPage($value): void
    {
        $this->perPage = $this->normalizePerPage($value); // Pastikan nilai perPage valid sesuai opsi
        $this->resetPage(); // Reset pagination ke halaman pertama saat jumlah per halaman berubah
    } // Atur jumlah item per halaman dan reset pagination

    public function render()
    {
        [$field, $direction] = $this->resolveSort(); // Ambil field dan arah sorting

        $events = Acara::query()
            ->search($this->search) // Terapkan pencarian
            ->orderBy($field, $direction) // Terapkan sorting
            ->paginate($this->perPage); // Terapkan pagination

        return view('livewire.admin.manajemen-acara', [
            'eventList' => $events, // Kirim daftar acara ke view
            'kategoriOptions' => KategoriAcara::orderBy('nama')->get(), // Kirim opsi kategori ke view
        ]);
    } // Render tampilan komponen dengan data acara

    public function create(): void
    {
        $this->resetForm(); // Reset form ke kondisi awal
        $this->resetValidation(); // Hapus pesan validasi sebelumnya
    } // Reset form untuk membuat acara baru

    public function edit(int $id): void
    {
        $this->resetValidation(); // Hapus pesan validasi sebelumnya

        $event = Acara::findOrFail($id); // Ambil data acara berdasarkan ID

        $this->acaraId = $event->id; // Set ID acara yang akan diedit
        $this->judul = $event->judul; // Muat judul acara
        $this->lokasi = $event->lokasi; // Muat lokasi acara
        $this->kategori_acara_id = $event->kategori_acara_id; // Muat ID kategori acara
        $this->poster_url = $event->poster_url; // Muat URL poster acara
        $this->deskripsi = $event->deskripsi; // Muat deskripsi acara
        $this->mulai_at_input = optional($event->mulai_at)->format('Y-m-d\TH:i') ?? ''; // Format tanggal mulai untuk input
        $this->selesai_at_input = optional($event->selesai_at)->format('Y-m-d\TH:i'); // Format tanggal selesai untuk input
    } // Muat data acara untuk mode edit

    public function save(): void
    {
        $validated = $this->validate(); // Jalankan validasi pada input

        $mulai = Carbon::parse($validated['mulai_at_input']); // Parse tanggal mulai
        $selesai = $validated['selesai_at_input'] ? Carbon::parse($validated['selesai_at_input']) : null; // Parse tanggal selesai jika ada

        $slug = $this->generateSlug($validated['judul']); // Generate slug unik untuk URL

        $data = [
            'judul' => $validated['judul'], // Judul acara
            'slug' => $slug, // Slug unik untuk URL
            'lokasi' => $validated['lokasi'], // Lokasi acara
            'kategori_acara_id' => (int) $validated['kategori_acara_id'], // ID kategori acara
            'poster_url' => $validated['poster_url'] ?: null, // URL poster, atau null jika kosong
            'deskripsi' => $validated['deskripsi'] ?: null, // Deskripsi acara, atau null jika kosong
            'mulai_at' => $mulai, // Tanggal mulai acara
            'selesai_at' => $selesai, // Tanggal selesai acara
            'admin_id' => Auth::id(), // ID admin yang membuat acara
        ];

        Acara::updateOrCreate([ // Buat atau update data acara
            'id' => $this->acaraId,
        ], $data);

        session()->flash('message', $this->acaraId ? 'Acara berhasil diperbarui.' : 'Acara baru berhasil dibuat.'); // Tampilkan pesan sukses

        $this->dispatch('close-modal', id: 'modal-acara'); // Kirim event untuk menutup modal
        $this->resetForm(); // Reset form setelah disimpan
    } // Simpan data acara baru atau perbarui yang sudah ada

    public function delete(int $id): void
    {
        Acara::findOrFail($id)->delete(); // Hapus acara berdasarkan ID

        session()->flash('message', 'Acara berhasil dihapus.'); // Tampilkan pesan sukses
        $this->resetForm(); // Reset form setelah penghapusan
    } // Hapus data acara

    private function resetForm(): void
    {
        $this->reset([
            'acaraId',
            'judul',
            'lokasi',
            'kategori_acara_id',
            'poster_url',
            'deskripsi',
            'mulai_at_input',
            'selesai_at_input',
        ]); // Reset semua properti form ke nilai awal
    } // Reset form ke kondisi awal

    private function normalizePerPage($value): int
    {
        $value = (int) $value; // Konversi nilai ke integer

        return in_array($value, $this->perPageOptions, true) ? $value : $this->perPageOptions[0]; // Kembalikan nilai valid atau default
    } // Pastikan nilai perPage valid sesuai opsi yang tersedia

    private function normalizeSort(string $value): string
    {
        return array_key_exists($value, $this->sortOptions) ? $value : 'mulai_at_desc'; // Kembalikan nilai sort valid atau default
    } // Pastikan nilai sort valid sesuai opsi yang tersedia

    private function resolveSort(): array
    {
        return match ($this->sort) { // Kembalikan field dan arah sorting berdasarkan opsi
            'mulai_at_asc' => ['mulai_at', 'asc'],
            'judul_asc' => ['judul', 'asc'],
            'judul_desc' => ['judul', 'desc'],
            default => ['mulai_at', 'desc'],
        };
    } // Ambil field dan arah sorting

    private function generateSlug(string $judul): string
    {
        $baseSlug = Str::slug($judul) ?: 'acara'; // Generate slug dasar dari judul
        $slug = $baseSlug;
        $counter = 1;

        while (
            Acara::where('slug', $slug) // Cek apakah slug sudah ada di database
                ->when($this->acaraId, fn ($query) => $query->where('id', '!=', $this->acaraId)) // Kecualikan ID saat edit
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter; // Tambahkan angka jika slug sudah digunakan
            $counter++;
        }

        return $slug;
    } // Generate slug unik untuk acara
}
