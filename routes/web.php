<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Logout;
use App\Models\Pengumuman as PengumumanModel;
use App\Models\Acara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $latestAnnouncements = PengumumanModel::query()
        ->where('status', 'published')
        ->latest('published_at')
        ->take(4)
        ->with(['kategori'])
        ->get();

    $upcomingEvents = Acara::query()
        ->where('mulai_at', '>=', now()->startOfDay())
        ->orderBy('mulai_at')
        ->take(4)
        ->get();

    if ($upcomingEvents->count() < 4) {
        $additional = Acara::query()
            ->where('mulai_at', '<', now()->startOfDay())
            ->orderByDesc('mulai_at')
            ->take(4 - $upcomingEvents->count())
            ->get();

        $upcomingEvents = $upcomingEvents->concat($additional)->values();
    }

    return view('welcome', [
        'latestAnnouncements' => $latestAnnouncements,
        'upcomingEvents' => $upcomingEvents,
    ]);
})->name('welcome');

// Route Super Admin
require __DIR__.'/superadmin.php';

// Route Admin Perpus
require __DIR__.'/adminperpus.php';

require __DIR__.'/siswa.php';

Route::get('/login', Login::class)->middleware('redirect.authenticated')->name('login');

Route::get('/pengumuman', \App\Livewire\Pengumuman::class)->name('landing.pengumuman');
Route::get('/pengumuman/{slug}', \App\Livewire\DetailPengumuman::class)->name('landing.pengumuman.detail');

Route::get('/cari-buku', function (Request $request) {
    $search = trim((string) $request->query('q', ''));
    $params = $search !== '' ? ['search' => $search] : [];
    $target = route('siswa.buku', $params);

    if (! Auth::check()) {
        $request->session()->put('url.intended', $target);

        return redirect()->route('login');
    }

    $user = Auth::user()->loadMissing('role');
    $roleName = optional($user->role)->nama_role;

    if ($roleName !== 'Siswa') {
        $dashboard = match ($roleName) {
            'SuperAdmin' => route('superadmin.dashboard'),
            'AdminPerpus' => route('adminperpus.dashboard'),
            default => route('welcome'),
        };

        return redirect()->to($dashboard)->with('error', 'Fitur pencarian buku hanya tersedia untuk akun siswa.');
    }

    return redirect()->to($target);
})->name('landing.book-search');

Route::get('/logout', Logout::class)->middleware('auth')->name('logout');
