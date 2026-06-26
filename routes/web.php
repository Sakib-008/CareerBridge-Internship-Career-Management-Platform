<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentProfileController;
use App\Http\Controllers\Student\StudentSkillController;
use App\Http\Controllers\Company\CompanyDashboardController;
use App\Http\Controllers\Company\CompanyProfileController;
use App\Http\Controllers\Company\InternshipController;

// Public Routes
Route::get('/', fn() => redirect()->route('login'));

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// Student Routes
Route::middleware(['auth', 'active', 'student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile',       [StudentProfileController::class, 'show'])->name('profile');
        Route::put('/profile',       [StudentProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/cv',   [StudentProfileController::class, 'uploadCv'])->name('profile.cv.upload');
        Route::delete('/profile/cv', [StudentProfileController::class, 'deleteCv'])->name('profile.cv.delete');

        Route::get('/skills',                     [StudentSkillController::class, 'index'])->name('skills');
        Route::post('/skills',                    [StudentSkillController::class, 'store'])->name('skills.store');
        Route::put('/skills/{studentSkillId}',    [StudentSkillController::class, 'update'])->name('skills.update');
        Route::delete('/skills/{studentSkillId}', [StudentSkillController::class, 'destroy'])->name('skills.destroy');
    });

// Company Routes 
Route::middleware(['auth', 'active', 'company'])
    ->prefix('company')
    ->name('company.')
    ->group(function () {

        Route::get('/dashboard', [CompanyDashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', [CompanyProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [CompanyProfileController::class, 'update'])->name('profile.update');

        Route::get('/internships',                 [InternshipController::class, 'index'])->name('internships');
        Route::get('/internships/create',           [InternshipController::class, 'create'])->name('internships.create');
        Route::post('/internships',                 [InternshipController::class, 'store'])->name('internships.store');
        Route::get('/internships/{id}/edit',        [InternshipController::class, 'edit'])->name('internships.edit');
        Route::put('/internships/{id}',             [InternshipController::class, 'update'])->name('internships.update');
        Route::patch('/internships/{id}/status',    [InternshipController::class, 'updateStatus'])->name('internships.status');
        Route::delete('/internships/{id}',          [InternshipController::class, 'destroy'])->name('internships.destroy');
    });

// Admin Routes 
Route::middleware(['auth', 'active', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
    });