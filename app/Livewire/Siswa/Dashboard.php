<?php

namespace App\Livewire\Siswa;

use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{

    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Siswa Dashboard')]
    public function render()
    {
        $siswaId = Auth::user()?->siswa?->id;

        $stats = [
            'active' => 0,
            'pending' => 0,
            'overdue' => 0,
            'total' => 0,
        ];

        $currentLoans = collect();
        $recentHistory = collect();

        if ($siswaId) {
            $stats['active'] = Peminjaman::where('siswa_id', $siswaId)->where('status', 'accepted')->count();
            $stats['pending'] = Peminjaman::where('siswa_id', $siswaId)->where('status', 'pending')->count();
            $stats['overdue'] = Peminjaman::where('siswa_id', $siswaId)
                ->where('status', 'accepted')
                ->whereNotNull('due_at')
                ->where('due_at', '<', now())
                ->count();
            $stats['total'] = Peminjaman::where('siswa_id', $siswaId)->count();

            $currentLoans = Peminjaman::with(['items.buku'])
                ->where('siswa_id', $siswaId)
                ->where('status', 'accepted')
                ->orderBy('due_at')
                ->take(4)
                ->get();

            $recentHistory = Peminjaman::with(['items.buku'])
                ->where('siswa_id', $siswaId)
                ->latest('created_at')
                ->take(5)
                ->get();
        }

        return view('livewire.siswa.dashboard', [
            'stats' => $stats,
            'currentLoans' => $currentLoans,
            'recentHistory' => $recentHistory,
        ]);
    } // Render tampilan komponen dashboard siswa
}
