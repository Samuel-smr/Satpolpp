<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityEvaluationController;
use App\Http\Controllers\AdminActivityExportAttachmentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompleteReportController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\InternalActivityAttachmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\PelaporController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportAttachmentController;
use App\Http\Controllers\ReportStatusController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

// Landing / Role Pelapor (masyarakat)
Route::get('/', [PelaporController::class, 'index'])->name('pelapor.index');
Route::get('/lapor', [PelaporController::class, 'lapor'])->name('pelapor.lapor');
Route::post('/lapor', [PelaporController::class, 'store'])->middleware('throttle:guest-reports')->name('pelapor.store');
Route::post('/lapor/otp', [PelaporController::class, 'sendOtp'])->middleware('throttle:otp-request')->name('pelapor.otp.send');
Route::post('/lapor/otp/verify', [PelaporController::class, 'verifyOtp'])->middleware('throttle:otp-request')->name('pelapor.otp.verify');
Route::post('/lapor/asisten-ai', AiAssistantController::class)
    ->middleware('throttle:ai-assistant')
    ->block(30, 0)
    ->name('pelapor.ai-assistant');
Route::get('/status', [PelaporController::class, 'status'])->name('pelapor.status');
Route::post('/status/{report:code}/feedback', [PelaporController::class, 'storeFeedback'])->middleware('throttle:guest-feedback')->name('pelapor.feedback.store');
Route::get('/feedback', [PelaporController::class, 'feedback'])->name('pelapor.feedback');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login-ip')->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware(['auth', 'can:access-internal'])->group(function () {
    Route::get('/global-search', GlobalSearchController::class)->middleware('throttle:60,1')->name('global-search');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
});

// Admin Routes (Tampilan Sementara / Prototyping)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:access-admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/pengaduan', [AdminController::class, 'pengaduan'])->name('pengaduan');
    Route::get('/pengaduan/create', [AdminReportController::class, 'create'])->name('pengaduan.create');
    Route::post('/pengaduan', [AdminReportController::class, 'store'])->name('pengaduan.store');
    Route::get('/pengaduan/{report:code}', [AdminController::class, 'pengaduanDetail'])->name('pengaduan.detail');
    Route::delete('/pengaduan/{report:code}', [AdminReportController::class, 'destroy'])->name('pengaduan.destroy');
    Route::patch('/pengaduan/{report:code}/status', [ReportStatusController::class, 'update'])->name('pengaduan.status.update');
    Route::patch('/pengaduan/{report:code}/reject', [ReportStatusController::class, 'reject'])->name('pengaduan.reject');
    Route::post('/pengaduan/{report:code}/complete', [CompleteReportController::class, 'store'])->name('pengaduan.complete.store');
    Route::get('/pengaduan/{report:code}/attachments/{attachment}', [ReportAttachmentController::class, 'show'])->name('pengaduan.attachments.show');
    Route::get('/kegiatan', [AdminController::class, 'kegiatan'])->name('kegiatan');
    Route::get('/kegiatan/{activity}/export-attachments/{attachment}', [AdminActivityExportAttachmentController::class, 'show'])->name('kegiatan.export-attachments.show');
    Route::get('/kegiatan/create', [ActivityController::class, 'create'])->name('kegiatan.create');
    Route::post('/kegiatan', [ActivityController::class, 'store'])->name('kegiatan.store');
    Route::get('/kegiatan/{activity}/edit', [ActivityController::class, 'edit'])->name('kegiatan.edit');
    Route::put('/kegiatan/{activity}', [ActivityController::class, 'update'])->name('kegiatan.update');
    Route::delete('/kegiatan/{activity}', [ActivityController::class, 'destroy'])->name('kegiatan.destroy');
    Route::get('/kegiatan/{activity}', [ActivityController::class, 'show'])->name('kegiatan.show');
    Route::get('/kegiatan/{activity}/attachments/{attachment}', [InternalActivityAttachmentController::class, 'show'])->name('kegiatan.attachments.show');
    Route::delete('/kegiatan/{activity}/attachments/{attachment}', [InternalActivityAttachmentController::class, 'destroy'])->name('kegiatan.attachments.destroy');
    Route::get('/feedback', [AdminController::class, 'feedback'])->name('feedback');
    Route::get('/evaluasi', [ActivityEvaluationController::class, 'index'])->name('evaluasi');
    Route::post('/evaluasi', [ActivityEvaluationController::class, 'store'])->name('evaluasi.store');
    Route::put('/evaluasi/{activity}', [ActivityEvaluationController::class, 'update'])->name('evaluasi.update');
    Route::delete('/evaluasi/{activity}', [ActivityEvaluationController::class, 'destroy'])->name('evaluasi.destroy');
    Route::get('/arsip', [ArchiveController::class, 'index'])->name('arsip');
    Route::post('/arsip', [ArchiveController::class, 'store'])->name('arsip.store');
    Route::put('/arsip/{archive}', [ArchiveController::class, 'update'])->name('arsip.update');
    Route::get('/arsip/{archive}/files/{archiveFile}', [ArchiveController::class, 'show'])->name('arsip.show');
    Route::get('/arsip/{archive}/files/{archiveFile}/download', [ArchiveController::class, 'download'])->name('arsip.download');
    Route::delete('/arsip/{archive}', [ArchiveController::class, 'destroy'])->name('arsip.destroy');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Super Admin Routes (Tampilan Sementara / Prototyping)
Route::prefix('super-admin')->name('super_admin.')->middleware(['auth', 'can:access-super-admin'])->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/kegiatan', [AdminController::class, 'kegiatan'])->name('kegiatan');
    Route::get('/kegiatan/{activity}/export-attachments/{attachment}', [AdminActivityExportAttachmentController::class, 'show'])->name('kegiatan.export-attachments.show');
    Route::get('/kegiatan/create', [ActivityController::class, 'create'])->name('kegiatan.create');
    Route::post('/kegiatan', [ActivityController::class, 'store'])->name('kegiatan.store');
    Route::get('/kegiatan/{activity}/edit', [ActivityController::class, 'edit'])->name('kegiatan.edit');
    Route::put('/kegiatan/{activity}', [ActivityController::class, 'update'])->name('kegiatan.update');
    Route::delete('/kegiatan/{activity}', [ActivityController::class, 'destroy'])->name('kegiatan.destroy');
    Route::get('/kegiatan/{activity}', [ActivityController::class, 'show'])->name('kegiatan.show');
    Route::get('/kegiatan/{activity}/attachments/{attachment}', [InternalActivityAttachmentController::class, 'show'])->name('kegiatan.attachments.show');
    Route::delete('/kegiatan/{activity}/attachments/{attachment}', [InternalActivityAttachmentController::class, 'destroy'])->name('kegiatan.attachments.destroy');
    Route::get('/pengaduan/{report:code}', [AdminController::class, 'pengaduanDetail'])->name('pengaduan.detail');
    Route::get('/pengaduan/{report:code}/attachments/{attachment}', [ReportAttachmentController::class, 'show'])->name('pengaduan.attachments.show');
    Route::get('/user', [SuperAdminController::class, 'user'])->name('user');
    Route::post('/user', [SuperAdminController::class, 'storeUser'])->name('user.store');
    Route::put('/user/{user}', [SuperAdminController::class, 'updateUser'])->name('user.update');
    Route::patch('/user/{user}/status', [SuperAdminController::class, 'toggleUserStatus'])->name('user.status');
    Route::delete('/user/{user}', [SuperAdminController::class, 'destroyUser'])->name('user.destroy');
    Route::get('/petugas', [OfficerController::class, 'index'])->name('petugas');
    Route::post('/petugas', [OfficerController::class, 'store'])->name('petugas.store');
    Route::put('/petugas/{officer}', [OfficerController::class, 'update'])->name('petugas.update');
    Route::patch('/petugas/{officer}/status', [OfficerController::class, 'toggleStatus'])->name('petugas.status');
    Route::delete('/petugas/{officer}', [OfficerController::class, 'destroy'])->name('petugas.destroy');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/monitoring', [SuperAdminController::class, 'monitoring'])->name('monitoring');
});
