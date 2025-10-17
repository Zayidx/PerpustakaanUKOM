<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route Role Admin
require __DIR__.'/admin.php';

require __DIR__.'/guru.php';

require __DIR__.'/siswa.php';
