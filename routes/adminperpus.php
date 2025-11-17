<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:AdminPerpus'])
    ->prefix('admin-perpus')
    ->name('adminperpus.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\AdminPerpus\Dashboard::class)->name('dashboard');
        Route::get('/peminjaman', \App\Livewire\AdminPerpus\ManajemenPeminjaman::class)->name('peminjaman');
        Route::get('/scan-peminjaman', \App\Livewire\AdminPerpus\ScanPeminjaman::class)->name('scan-peminjaman');
        Route::get('/scan-pengembalian', \App\Livewire\AdminPerpus\ScanPengembalian::class)->name('scan-pengembalian');
    });
