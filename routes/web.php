<?php

use App\Livewire\Auth\Login;
use App\Models\Pengumuman as PengumumanModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $latestAnnouncements = PengumumanModel::query()
        ->where('status', 'published')
        ->latest('published_at')
        ->take(4)
        ->with(['kategori'])
        ->get();

    return view('welcome', [
        'latestAnnouncements' => $latestAnnouncements,
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
