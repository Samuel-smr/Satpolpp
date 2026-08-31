<?php

use App\Http\Controllers\PelaporController;
use Illuminate\Support\Facades\Route;

// Landing / Role Pelapor (masyarakat)
Route::get('/', [PelaporController::class, 'index'])->name('pelapor.index');
Route::get('/lapor', [PelaporController::class, 'lapor'])->name('pelapor.lapor');
Route::get('/status', [PelaporController::class, 'status'])->name('pelapor.status');
Route::get('/feedback', [PelaporController::class, 'feedback'])->name('pelapor.feedback');
