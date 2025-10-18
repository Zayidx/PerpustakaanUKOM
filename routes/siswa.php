<?php

use Illuminate\Support\Facades\Route;

Route::get('/siswa/dashboard', \App\Livewire\Siswa\Dashboard::class)->name('siswa.dashboard');
