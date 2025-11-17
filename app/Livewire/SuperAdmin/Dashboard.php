<?php

namespace App\Livewire\SuperAdmin;

use App\Models\AdminPerpus;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\Peminjaman;
use App\Models\Siswa;
use App\Models\SuperAdmin;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{

    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Super Admin Dashboard')]
    public function render()
    {
        $totalStudents = Siswa::count();
        $totalAdminPerpus = AdminPerpus::count();
        $totalSuperAdmins = SuperAdmin::count();
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

        return view('livewire.super-admin.dashboard', [
            'stats' => [
                'students' => $totalStudents,
                'staff' => $totalAdminPerpus + $totalSuperAdmins,
                'admin_perpus' => $totalAdminPerpus,
                'super_admins' => $totalSuperAdmins,
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
    } 
}
