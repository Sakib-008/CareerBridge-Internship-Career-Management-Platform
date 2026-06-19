<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

// ─── Public Routes ─────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ─── Auth Routes ───────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ─── Student Routes ────────────────────────────────────────────────────────
Route::middleware(['auth', 'active', 'student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', fn() => view('student.dashboard'))->name('dashboard');
    });

// ─── Company Routes ────────────────────────────────────────────────────────
Route::middleware(['auth', 'active', 'company'])
    ->prefix('company')
    ->name('company.')
    ->group(function () {
        Route::get('/dashboard', fn() => view('company.dashboard'))->name('dashboard');
    });

// ─── Admin Routes ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'active', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
    });