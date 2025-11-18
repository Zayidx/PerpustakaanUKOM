<?php

namespace App\Livewire\Siswa;

use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class ListPeminjaman extends Component
{
    use WithPagination;

    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Peminjaman Saya')]
    public string $statusFilter = 'all';

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
    ];

    public function updatingStatusFilter(): void
    {
        $this->resetPage(); 
    } 

    public function render()
    {
        $user = Auth::user(); 
        $siswaId = $user?->siswa?->id; 

        abort_if(! $siswaId, 403, 'Akun tidak memiliki data siswa.'); 

        $loans = Peminjaman::query() 
            ->with(['items.buku']) 
            ->where('siswa_id', $siswaId) 
            ->when($this->statusFilter !== 'all', function ($query) { 
                $query->where('status', $this->statusFilter); 
            })
            ->latest('created_at') 
            ->paginate(10); 

        return view('livewire.siswa.list-peminjaman', [
            'loans' => $loans,
        ]);
    } 
}
