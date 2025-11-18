<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:Siswa'])
    ->prefix('siswa')
    ->name('siswa.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Siswa\Dashboard::class)->name('dashboard');
        Route::get('/buku', \App\Livewire\Siswa\ListBuku::class)->name('buku');
        Route::get('/peminjaman', \App\Livewire\Siswa\ListPeminjaman::class)->name('peminjaman');
        Route::get('/peminjaman/kode/{kode}', \App\Livewire\Siswa\KodePinjaman::class)->name('kode-peminjaman');
        Route::get('/pengembalian/kode/{kode}', \App\Livewire\Siswa\KodePengembalian::class)->name('kode-pengembalian');
    });
