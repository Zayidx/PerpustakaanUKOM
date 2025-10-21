<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:Administrator,Petugas'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
        Route::get('/manajemen-siswa', \App\Livewire\Admin\ManajemenSiswa::class)->name('manajemen-siswa');
        Route::get('/manajemen-kelas', \App\Livewire\Admin\ManajemenKelas::class)->name('manajemen-kelas');
        Route::get('/manajemen-jurusan', \App\Livewire\Admin\ManajemenJurusan::class)->name('manajemen-jurusan');
        Route::get('/manajemen-admin', \App\Livewire\Admin\ManajemenPetugas::class)->name('manajemen-admin');
        Route::get('/manajemen-guru', \App\Livewire\Admin\ManajemenGuru::class)->name('manajemen-guru');
    });
