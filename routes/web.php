<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AdminSocketTokenController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;

Route::get('/', [AdminAuthController::class, 'show']);

// Serve storage files (workaround for Windows symlink issues)
/* Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        abort(404);
    }

    $mimeType = mime_content_type($fullPath);
    return response()->file($fullPath, ['Content-Type' => $mimeType]);
})->where('path', '.*')->name('storage.serve');
 */
// LOGIN ADMIN con 2FA
Route::get('/admin/login', [AdminAuthController::class, 'show'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');

// OTP (paso 2 del login)
Route::get('/admin/login/otp', [AdminAuthController::class, 'showOtpForm'])->name('admin.login.otp');
Route::post('/admin/login/otp', [AdminAuthController::class, 'verifyOtp'])->name('admin.login.otp.submit');
Route::post('/admin/login/otp/resend', [AdminAuthController::class, 'resendOtp'])->name('admin.login.otp.resend');
Route::get('/admin/login/otp/attempts', [AdminAuthController::class, 'getResendAttempts'])->name('admin.login.otp.attempts');

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

    // Gestión de proyectos (solo admin)
    Route::get('/admin/projects', [ProjectController::class, 'index'])->name('admin.projects.index');
    Route::get('/admin/projects/create', [ProjectController::class, 'create'])->name('admin.projects.create');
    Route::post('/admin/projects', [ProjectController::class, 'store'])->name('admin.projects.store');
    Route::get('/admin/projects/{project}', [ProjectController::class, 'show'])->name('admin.projects.show');
    Route::get('/admin/projects/{project}/edit', [ProjectController::class, 'edit'])->name('admin.projects.edit');
    Route::put('/admin/projects/{project}', [ProjectController::class, 'update'])->name('admin.projects.update');
    Route::delete('/admin/projects/{project}', [ProjectController::class, 'destroy'])->name('admin.projects.destroy');

    // Gestión de miembros de proyecto (solo admin)
    Route::post('/admin/projects/{project}/assign', [ProjectController::class, 'assignUser'])->name('admin.projects.assign');
    Route::post('/admin/projects/{project}/users/{user}/approve', [ProjectController::class, 'approveUser'])->name('admin.projects.approve');
    Route::post('/admin/projects/{project}/users/{user}/reject', [ProjectController::class, 'rejectUser'])->name('admin.projects.reject');
    Route::delete('/admin/projects/{project}/users/{user}', [ProjectController::class, 'removeUser'])->name('admin.projects.remove');
    Route::put('/admin/projects/{project}/users/{user}/role', [ProjectController::class, 'updateUserRole'])->name('admin.projects.role');

    // Proyectos para usuarios normales
    Route::get('/projects/available', [ProjectController::class, 'available'])->name('projects.available');
    Route::get('/projects/my', [ProjectController::class, 'myProjects'])->name('projects.my');
    Route::post('/projects/{project}/request', [ProjectController::class, 'requestAccess'])->name('projects.request');

    // Registros personales del usuario
    Route::get('/my-records', [AdminDashboardController::class, 'userRecords'])->name('user.records');
});