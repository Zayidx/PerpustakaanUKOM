<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:SuperAdmin'])
    ->prefix('super-admin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\SuperAdmin\Dashboard::class)->name('dashboard');
        Route::get('/manajemen-siswa', \App\Livewire\SuperAdmin\ManajemenSiswa::class)->name('manajemen-siswa');
        Route::get('/manajemen-kelas', \App\Livewire\SuperAdmin\ManajemenKelas::class)->name('manajemen-kelas');
        Route::get('/manajemen-jurusan', \App\Livewire\SuperAdmin\ManajemenJurusan::class)->name('manajemen-jurusan');
        Route::get('/manajemen-super-admin', \App\Livewire\SuperAdmin\ManajemenSuperAdmin::class)->name('manajemen-super-admin');
        Route::get('/manajemen-admin-perpus', \App\Livewire\SuperAdmin\ManajemenAdminPerpus::class)->name('manajemen-admin-perpus');
        Route::get('/manajemen-acara', \App\Livewire\SuperAdmin\ManajemenAcara::class)->name('manajemen-acara');
        Route::get('/manajemen-acara/kategori', \App\Livewire\SuperAdmin\KategoriAcara::class)->name('kategori-acara');
        Route::get('/pengumuman', \App\Livewire\SuperAdmin\ManajemenPengumuman::class)->name('pengumuman');
        Route::get('/pengumuman/kategori', \App\Livewire\SuperAdmin\ManajemenKategoriPengumuman::class)->name('kategori-pengumuman');
        Route::get('/manajemen-author', \App\Livewire\SuperAdmin\ManajemenAuthor::class)->name('manajemen-author');
        Route::get('/kategori-buku', \App\Livewire\SuperAdmin\ManajemenKategoriBuku::class)->name('kategori-buku');
        Route::get('/penerbit-buku', \App\Livewire\SuperAdmin\PenerbitBuku::class)->name('penerbit-buku');
        Route::get('/buku', \App\Livewire\SuperAdmin\ManajemenBuku::class)->name('buku');
    });
