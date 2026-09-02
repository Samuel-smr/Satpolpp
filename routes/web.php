<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PelaporController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

// Landing / Role Pelapor (masyarakat)
Route::get('/', [PelaporController::class, 'index'])->name('pelapor.index');
Route::get('/lapor', [PelaporController::class, 'lapor'])->name('pelapor.lapor');
Route::get('/status', [PelaporController::class, 'status'])->name('pelapor.status');
Route::get('/feedback', [PelaporController::class, 'feedback'])->name('pelapor.feedback');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Routes (Tampilan Sementara / Prototyping)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/pengaduan', [AdminController::class, 'pengaduan'])->name('pengaduan');
    Route::get('/kegiatan', [AdminController::class, 'kegiatan'])->name('kegiatan');
    Route::get('/feedback', [AdminController::class, 'feedback'])->name('feedback');
    Route::get('/arsip', [AdminController::class, 'arsip'])->name('arsip');
    Route::get('/aset', [AdminController::class, 'aset'])->name('aset');
    Route::get('/peta', [AdminController::class, 'peta'])->name('peta');
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
});

// Super Admin Routes (Tampilan Sementara / Prototyping)
Route::prefix('super-admin')->name('super_admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/user', [SuperAdminController::class, 'user'])->name('user');
    Route::get('/organisasi', [SuperAdminController::class, 'organisasi'])->name('organisasi');
    Route::get('/petugas', [SuperAdminController::class, 'petugas'])->name('petugas');
    Route::get('/evaluasi', [SuperAdminController::class, 'evaluasi'])->name('evaluasi');
    Route::get('/monitoring', [SuperAdminController::class, 'monitoring'])->name('monitoring');
});
