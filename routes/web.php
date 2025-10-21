<?php

use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route Role Admin
require __DIR__.'/admin.php';

require __DIR__.'/guru.php';

require __DIR__.'/siswa.php';

Route::get('/login', Login::class)->middleware('redirect.authenticated')->name('login');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');
