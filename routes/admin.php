<?php

use Illuminate\Support\Facades\Route;

Route::get('/admin/dashboard', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');