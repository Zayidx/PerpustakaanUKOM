<?php

use Illuminate\Support\Facades\Route;

Route::get('/admin/dashboard', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');
Route::get('/admin/manajemen-siswa', \App\Livewire\Admin\ManajemenSiswa::class)->name('admin.manajemen-siswa');
Route::get('/admin/manajemen-kelas', \App\Livewire\Admin\ManajemenKelas::class)->name('admin.manajemen-kelas');
Route::get('/admin/manajemen-jurusan', \App\Livewire\Admin\ManajemenJurusan::class)->name('admin.manajemen-jurusan');
