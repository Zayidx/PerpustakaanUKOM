<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:Administrator,Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
        Route::get('/manajemen-siswa', \App\Livewire\Admin\ManajemenSiswa::class)->name('manajemen-siswa');
        Route::get('/manajemen-kelas', \App\Livewire\Admin\ManajemenKelas::class)->name('manajemen-kelas');
        Route::get('/manajemen-jurusan', \App\Livewire\Admin\ManajemenJurusan::class)->name('manajemen-jurusan');
        Route::get('/manajemen-admin', \App\Livewire\Admin\ManajemenPetugas::class)->name('manajemen-admin');
        Route::get('/manajemen-guru', \App\Livewire\Admin\ManajemenGuru::class)->name('manajemen-guru');
        Route::get('/manajemen-acara', \App\Livewire\Admin\ManajemenAcara::class)->name('manajemen-acara');
        Route::get('/manajemen-acara/kategori', \App\Livewire\Admin\KategoriAcara::class)->name('kategori-acara');
        Route::get('/pengumuman', \App\Livewire\Admin\ManajemenPengumuman::class)->name('pengumuman');
        Route::get('/pengumuman/kategori', \App\Livewire\Admin\ManajemenKategoriPengumuman::class)->name('kategori-pengumuman');
        Route::get('/manajemen-author', \App\Livewire\Admin\ManajemenAuthor::class)->name('manajemen-author');
        Route::get('/kategori-buku', \App\Livewire\Admin\ManajemenKategoriBuku::class)->name('kategori-buku');
        Route::get('/penerbit-buku', \App\Livewire\Admin\PenerbitBuku::class)->name('penerbit-buku');
        Route::get('/buku', \App\Livewire\Admin\ManajemenBuku::class)->name('buku');
    });
