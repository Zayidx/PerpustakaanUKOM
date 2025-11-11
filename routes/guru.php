<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:Guru'])
    ->prefix('guru')
    ->name('guru.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Guru\Dashboard::class)->name('dashboard');
        Route::get('/peminjaman', \App\Livewire\Guru\ManajemenPeminjaman::class)->name('peminjaman');
        Route::get('/scan-peminjaman', \App\Livewire\Guru\ScanPeminjaman::class)->name('scan-peminjaman');
        Route::get('/scan-pengembalian', \App\Livewire\Guru\ScanPengembalian::class)->name('scan-pengembalian');
    });
