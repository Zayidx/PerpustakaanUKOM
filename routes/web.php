<?php

use App\Livewire\Auth\Login;
use App\Models\Pengumuman as PengumumanModel;
use App\Models\Acara;
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

// Route Role Admin
require __DIR__.'/admin.php';

require __DIR__.'/guru.php';

require __DIR__.'/siswa.php';

Route::get('/login', Login::class)->middleware('redirect.authenticated')->name('login');

Route::get('/pengumuman', \App\Livewire\Pengumuman::class)->name('landing.pengumuman');
Route::get('/pengumuman/{slug}', \App\Livewire\DetailPengumuman::class)->name('landing.pengumuman.detail');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');
