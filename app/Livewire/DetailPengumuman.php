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
        // Ambil pengumuman terbit berdasarkan slug.
        $this->announcement = Pengumuman::query() 
            ->where('slug', $slug) 
            ->where('status', 'published') 
            ->with(['kategori', 'admin']) 
            ->firstOrFail(); 

        // Ambil pengumuman lain (prioritaskan kategori yang sama).
        $this->otherAnnouncements = Pengumuman::query() 
            ->where('status', 'published') 
            ->where('id', '!=', $this->announcement->id) 
            ->orderByRaw('CASE WHEN kategori_pengumuman_id = ? THEN 1 ELSE 2 END', [$this->announcement->kategori_pengumuman_id]) 
            ->latest('published_at') 
            ->take(5) 
            ->with(['kategori', 'admin']) 
            ->get(); 
    } 

    public function render()
    {
        return view('livewire.detail-pengumuman')
            ->title($this->announcement->judul . ' - ' . config('app.name', 'Laravel')); 
    } 
}
