<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\InternshipController as BrowseInternshipController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentProfileController;
use App\Http\Controllers\Student\StudentSkillController;
use App\Http\Controllers\Student\ApplicationController as StudentApplicationController;
use App\Http\Controllers\Company\CompanyDashboardController;
use App\Http\Controllers\Company\CompanyProfileController;
use App\Http\Controllers\Company\InternshipController as CompanyInternshipController;
use App\Http\Controllers\Company\ApplicationController as CompanyApplicationController;
use App\Http\Controllers\Company\InterviewController as CompanyInterviewController;
use App\Http\Controllers\Student\InterviewController as StudentInterviewController;
use App\Http\Controllers\Student\RecommendationController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSkillController;


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

// ─── Public/Student Internship Browsing (accessible to logged-in students) ──
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/internships',      [BrowseInternshipController::class, 'index'])->name('internships.index');
    Route::get('/internships/{id}', [BrowseInternshipController::class, 'show'])->name('internships.show');
});

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
        Route::get('/applications', [StudentApplicationController::class, 'index'])->name('applications');
        Route::get('/internships/{internshipId}/apply',  [StudentApplicationController::class, 'create'])->name('applications.create');
        Route::post('/internships/{internshipId}/apply', [StudentApplicationController::class, 'store'])->name('applications.store');
        Route::delete('/applications/{applicationId}',   [StudentApplicationController::class, 'destroy'])->name('applications.destroy');
        Route::get('/interviews',       [StudentInterviewController::class, 'index'])->name('interviews');
        Route::get('/recommendations',  [RecommendationController::class, 'index'])->name('recommendations');
    });

// Company Routes 
Route::middleware(['auth', 'active', 'company'])
    ->prefix('company')
    ->name('company.')
    ->group(function () {

        Route::get('/dashboard', [CompanyDashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', [CompanyProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [CompanyProfileController::class, 'update'])->name('profile.update');

        Route::get('/internships',                 [CompanyInternshipController::class, 'index'])->name('internships');
        Route::get('/internships/create',           [CompanyInternshipController::class, 'create'])->name('internships.create');
        Route::post('/internships',                 [CompanyInternshipController::class, 'store'])->name('internships.store');
        Route::get('/internships/{id}/edit',        [CompanyInternshipController::class, 'edit'])->name('internships.edit');
        Route::put('/internships/{id}',             [CompanyInternshipController::class, 'update'])->name('internships.update');
        Route::patch('/internships/{id}/status',    [CompanyInternshipController::class, 'updateStatus'])->name('internships.status');
        Route::delete('/internships/{id}',          [CompanyInternshipController::class, 'destroy'])->name('internships.destroy');
        Route::get('/applications',         [CompanyApplicationController::class, 'index'])->name('applications');
        Route::get('/applications/{id}',    [CompanyApplicationController::class, 'show'])->name('applications.show');
        Route::patch('/applications/{id}/status', [CompanyApplicationController::class, 'updateStatus'])->name('applications.status');

                Route::get('/applications/{applicationId}/interview/create',
            [CompanyInterviewController::class, 'create'])->name('interviews.create');
        Route::post('/applications/{applicationId}/interview',
            [CompanyInterviewController::class, 'store'])->name('interviews.store');
        Route::delete('/applications/{applicationId}/interview',
            [CompanyInterviewController::class, 'destroy'])->name('interviews.destroy');
            Route::get('/interviews', function () {
    $company = Auth::user()->company;
    $internshipIds = $company->internships()->get()->pluck('INTERNSHIP_ID')->toArray();

    $interviews = \App\Models\Application::with(['student', 'internship', 'interview'])
        ->whereIn('INTERNSHIP_ID', $internshipIds)
        ->where('STATUS', 'Interview')
        ->whereHas('interview')
        ->orderBy('UPDATED_AT', 'desc')
        ->get();

    return view('company.interviews.index', compact('interviews'));
})->name('interviews.list');
    });

// ─── Admin Routes ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'active', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // User management
        Route::get('/users', [AdminUserController::class, 'index'])->name('users');
        Route::patch('/users/{userId}/toggle', [AdminUserController::class, 'toggleActive'])
            ->name('users.toggle');

        // Reports
        Route::get('/reports/applications', [AdminReportController::class, 'applicationSummary'])
            ->name('reports.applications');
        Route::get('/reports/placement', [AdminReportController::class, 'studentPlacement'])
            ->name('reports.placement');
        Route::get('/reports/skills', [AdminReportController::class, 'skillDemand'])
            ->name('reports.skills');
        Route::get('/reports/companies', [AdminReportController::class, 'companyActivity'])
            ->name('reports.companies');

        // Skill catalog
        Route::get('/skills',    [AdminSkillController::class, 'index'])->name('skills');
        Route::post('/skills',   [AdminSkillController::class, 'store'])->name('skills.store');
        Route::delete('/skills/{skillId}', [AdminSkillController::class, 'destroy'])
            ->name('skills.destroy');
    });