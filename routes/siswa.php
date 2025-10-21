<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:Siswa'])
    ->prefix('siswa')
    ->name('siswa.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Siswa\Dashboard::class)->name('dashboard');
    });
