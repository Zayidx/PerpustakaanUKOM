<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:Guru'])
    ->prefix('guru')
    ->name('guru.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Guru\Dashboard::class)->name('dashboard');
    });
