<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/demo-login/{username}', [AuthController::class, 'quickLogin'])->name('login.demo');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Student Evaluation
    Route::get('/evaluation/fill/{subject_teacher_id}', [EvaluationController::class, 'showForm'])->name('evaluation.fill');
    Route::post('/evaluation/submit/{subject_teacher_id}', [EvaluationController::class, 'storeResponse'])->name('evaluation.submit');

    // Admin Pages
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
    Route::get('/admin/recap', [DashboardController::class, 'recap'])->name('admin.recap');

    // Admin User Management
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
});