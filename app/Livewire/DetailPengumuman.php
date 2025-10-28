<?php

namespace App\Livewire;

use App\Models\Pengumuman;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class DetailPengumuman extends Component
{
    public Pengumuman $announcement;

    public Collection $otherAnnouncements;

    public function mount(string $slug): void
    {
        $this->announcement = Pengumuman::query() // Ambil pengumuman berdasarkan slug
            ->where('slug', $slug) // Filter berdasarkan slug
            ->where('status', 'published') // Hanya pengumuman yang dipublikasikan
            ->with(['kategori', 'admin']) // Muat relasi kategori dan admin
            ->firstOrFail(); // Ambil pengumuman atau lempar exception jika tidak ditemukan

        $this->otherAnnouncements = Pengumuman::query() // Ambil pengumuman lainnya
            ->where('status', 'published') // Hanya pengumuman yang dipublikasikan
            ->where('id', '!=', $this->announcement->id) // Kecualikan pengumuman saat ini
            ->orderByRaw('CASE WHEN kategori_pengumuman_id = ? THEN 1 ELSE 2 END', [$this->announcement->kategori_pengumuman_id]) // Urutkan berdasarkan kategori yang sama dulu
            ->latest('published_at') // Kemudian urutkan berdasarkan tanggal publikasi terbaru
            ->take(5) // Ambil maksimal 5 pengumuman
            ->with(['kategori', 'admin']) // Muat relasi kategori dan admin
            ->get(); // Ambil data
    } // Muat detail pengumuman dan pengumuman lainnya berdasarkan slug

    public function render()
    {
        return view('livewire.detail-pengumuman')
            ->title($this->announcement->judul . ' - ' . config('app.name', 'Laravel')); // Set judul halaman berdasarkan judul pengumuman
    } // Render tampilan detail pengumuman
}
