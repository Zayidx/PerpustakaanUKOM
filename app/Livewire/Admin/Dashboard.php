<?php

namespace App\Livewire\Admin;

use App\Models\Buku;
use App\Models\Guru;
use App\Models\KategoriBuku;
use App\Models\Peminjaman;
use App\Models\Siswa;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{

    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Admin Dashboard')]
    public function render()
    {
        $totalStudents = Siswa::count();
        $totalTeachers = Guru::count();
        $totalBooks = Buku::count();
        $newMembersWeek = Siswa::where('created_at', '>=', now()->subDays(7))->count();

        $activeLoans = Peminjaman::where('status', 'accepted')->count();
        $pendingLoans = Peminjaman::where('status', 'pending')->count();
        $overdueLoans = Peminjaman::where('status', 'accepted')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->count();

        $topCategories = KategoriBuku::withCount('buku')
            ->orderByDesc('buku_count')
            ->take(4)
            ->get();

        $recentLoans = Peminjaman::with(['siswa.user'])
            ->latest('created_at')
            ->take(5)
            ->get();

        $upcomingDue = Peminjaman::with(['siswa.user'])
            ->where('status', 'accepted')
            ->whereNotNull('due_at')
            ->where('due_at', '>=', now()->startOfDay())
            ->orderBy('due_at')
            ->take(5)
            ->get();

        return view('livewire.admin.dashboard', [
            'stats' => [
                'students' => $totalStudents,
                'teachers' => $totalTeachers,
                'books' => $totalBooks,
                'new_members' => $newMembersWeek,
                'active_loans' => $activeLoans,
                'pending_loans' => $pendingLoans,
                'overdue_loans' => $overdueLoans,
            ],
            'topCategories' => $topCategories,
            'recentLoans' => $recentLoans,
            'upcomingDue' => $upcomingDue,
        ]);
    } // Render tampilan komponen dashboard admin
}
