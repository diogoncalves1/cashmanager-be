<?php

use App\Http\Controllers\Backend\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin', 'auth'])->get('admin', [HomeController::class, 'index'])->name('admin.index');

require __DIR__ . '/auth.php';
