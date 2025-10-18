<?php

use Illuminate\Support\Facades\Route;

Route::get('/admin/dashboard', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');
Route::get('/admin/manajemen-siswa', \App\Livewire\Admin\ManajemenSiswa::class)->name('admin.manajemen-siswa');