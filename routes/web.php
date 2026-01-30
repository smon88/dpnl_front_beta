<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminSocketTokenController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

// LOGIN ADMIN con 2FA
Route::get('/admin/login', [AdminAuthController::class, 'show'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('admin.login.submit');

// OTP (paso 2 del login)
Route::get('/admin/login/otp', [AdminAuthController::class, 'showOtpForm'])->name('admin.login.otp');
Route::post('/admin/login/otp', [AdminAuthController::class, 'verifyOtp'])
    ->middleware('throttle:3,1')
    ->name('admin.login.otp.submit');

Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// ADMIN dashboard protegido por sesión
Route::middleware(['admin.session'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/profile', [AdminDashboardController::class, 'profile'])->name('admin.profile');
    Route::put('/admin/profile', [AdminDashboardController::class, 'updateProfile'])->name('admin.profile.update');
    Route::get('/admin/traffic', [AdminDashboardController::class, 'traffic'])->name('admin.traffic');
    Route::get('/admin/tools', [AdminDashboardController::class, 'tools'])->name('admin.tools');
    Route::get('/admin/records', [AdminDashboardController::class, 'records'])->name('admin.records');
    Route::get('/admin/settings', [AdminDashboardController::class, 'settings'])->name('admin.settings');
    // token para socket admin (lo usa el dashboard)
    Route::get('/admin/socket-token', [AdminSocketTokenController::class, 'issue'])->name('admin.socket.token');

    // Gestión de usuarios (solo admin)
    Route::get('/admin/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});