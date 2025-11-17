<?php

namespace App\Livewire\AdminPerpus;

use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{
    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Admin Perpus Dashboard')]
    public function render()
    {
        $adminPerpusId = Auth::user()?->adminPerpus?->id;

        $stats = [
            'pending' => 0,
            'active' => 0,
            'due_soon' => 0,
            'students_served' => 0,
        ];

        $pendingList = collect();
        $dueSoonList = collect();
        $recentActivities = collect();

        if ($adminPerpusId) {
            $stats['pending'] = Peminjaman::where('admin_perpus_id', $adminPerpusId)->where('status', 'pending')->count();
            $stats['active'] = Peminjaman::where('admin_perpus_id', $adminPerpusId)->where('status', 'accepted')->count();
            $stats['due_soon'] = Peminjaman::where('admin_perpus_id', $adminPerpusId)
                ->where('status', 'accepted')
                ->whereNotNull('due_at')
                ->whereBetween('due_at', [now(), now()->addDays(3)])
                ->count();
            $stats['students_served'] = Peminjaman::where('admin_perpus_id', $adminPerpusId)
                ->distinct('siswa_id')
                ->count('siswa_id');

            $pendingList = Peminjaman::with(['siswa.user'])
                ->where('admin_perpus_id', $adminPerpusId)
                ->where('status', 'pending')
                ->orderBy('created_at')
                ->take(5)
                ->get();

            $dueSoonList = Peminjaman::with(['siswa.user'])
                ->where('admin_perpus_id', $adminPerpusId)
                ->where('status', 'accepted')
                ->whereNotNull('due_at')
                ->whereBetween('due_at', [now(), now()->addDays(7)])
                ->orderBy('due_at')
                ->take(5)
                ->get();

            $recentActivities = Peminjaman::with(['siswa.user'])
                ->where('admin_perpus_id', $adminPerpusId)
                ->latest('updated_at')
                ->take(6)
                ->get();
        }

        return view('livewire.admin-perpus.dashboard', [
            'stats' => $stats,
            'pendingList' => $pendingList,
            'dueSoonList' => $dueSoonList,
            'recentActivities' => $recentActivities,
        ]);
    } 
}
