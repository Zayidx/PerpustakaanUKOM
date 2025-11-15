<?php

namespace App\Livewire\Guru;

use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{
     #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Guru Dashboard')]
    public function render()
    {
        $guruId = Auth::user()?->guru?->id;

        $stats = [
            'pending' => 0,
            'active' => 0,
            'due_soon' => 0,
            'students_served' => 0,
        ];

        $pendingList = collect();
        $dueSoonList = collect();
        $recentActivities = collect();

        if ($guruId) {
            $stats['pending'] = Peminjaman::where('guru_id', $guruId)->where('status', 'pending')->count();
            $stats['active'] = Peminjaman::where('guru_id', $guruId)->where('status', 'accepted')->count();
            $stats['due_soon'] = Peminjaman::where('guru_id', $guruId)
                ->where('status', 'accepted')
                ->whereNotNull('due_at')
                ->whereBetween('due_at', [now(), now()->addDays(3)])
                ->count();
            $stats['students_served'] = Peminjaman::where('guru_id', $guruId)
                ->distinct('siswa_id')
                ->count('siswa_id');

            $pendingList = Peminjaman::with(['siswa.user'])
                ->where('guru_id', $guruId)
                ->where('status', 'pending')
                ->orderBy('created_at')
                ->take(5)
                ->get();

            $dueSoonList = Peminjaman::with(['siswa.user'])
                ->where('guru_id', $guruId)
                ->where('status', 'accepted')
                ->whereNotNull('due_at')
                ->whereBetween('due_at', [now(), now()->addDays(7)])
                ->orderBy('due_at')
                ->take(5)
                ->get();

            $recentActivities = Peminjaman::with(['siswa.user'])
                ->where('guru_id', $guruId)
                ->latest('updated_at')
                ->take(6)
                ->get();
        }

        return view('livewire.guru.dashboard', [
            'stats' => $stats,
            'pendingList' => $pendingList,
            'dueSoonList' => $dueSoonList,
            'recentActivities' => $recentActivities,
        ]);
    } // Render tampilan komponen dashboard guru
}
