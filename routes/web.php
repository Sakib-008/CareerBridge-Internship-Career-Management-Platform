<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\InternshipController as BrowseInternshipController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentProfileController;
use App\Http\Controllers\Student\StudentSkillController;
use App\Http\Controllers\Student\ApplicationController as StudentApplicationController;
use App\Http\Controllers\Student\InterviewController as StudentInterviewController;
use App\Http\Controllers\Student\RecommendationController;
use App\Http\Controllers\Company\CompanyDashboardController;
use App\Http\Controllers\Company\CompanyProfileController;
use App\Http\Controllers\Company\InternshipController as CompanyInternshipController;
use App\Http\Controllers\Company\ApplicationController as CompanyApplicationController;
use App\Http\Controllers\Company\InterviewController as CompanyInterviewController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSkillController;

// ─── Root ──────────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ─── Guest-only routes (logged-in users are redirected away) ──────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login'])->name('login.post');
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// ─── Logout (auth required) ────────────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ─── Public internship browsing (any authenticated user) ──────────────────────
Route::middleware(['auth', 'active', 'prevent_back'])->group(function () {
    Route::get('/internships',      [BrowseInternshipController::class, 'index'])->name('internships.index');
    Route::get('/internships/{id}', [BrowseInternshipController::class, 'show'])->name('internships.show');
});

// ─── Student Routes ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'active', 'student', 'prevent_back'])
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

        Route::get('/applications',                        [StudentApplicationController::class, 'index'])->name('applications');
        Route::get('/internships/{internshipId}/apply',    [StudentApplicationController::class, 'create'])->name('applications.create');
        Route::post('/internships/{internshipId}/apply',   [StudentApplicationController::class, 'store'])->name('applications.store');
        Route::delete('/applications/{applicationId}',     [StudentApplicationController::class, 'destroy'])->name('applications.destroy');

        Route::get('/interviews',     [StudentInterviewController::class, 'index'])->name('interviews');
        Route::get('/recommendations',[RecommendationController::class, 'index'])->name('recommendations');
    });

// ─── Company Routes ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'active', 'company', 'prevent_back'])
    ->prefix('company')
    ->name('company.')
    ->group(function () {

        Route::get('/dashboard', [CompanyDashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', [CompanyProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [CompanyProfileController::class, 'update'])->name('profile.update');

        Route::get('/internships',              [CompanyInternshipController::class, 'index'])->name('internships');
        Route::get('/internships/create',       [CompanyInternshipController::class, 'create'])->name('internships.create');
        Route::post('/internships',             [CompanyInternshipController::class, 'store'])->name('internships.store');
        Route::get('/internships/{id}/edit',    [CompanyInternshipController::class, 'edit'])->name('internships.edit');
        Route::put('/internships/{id}',         [CompanyInternshipController::class, 'update'])->name('internships.update');
        Route::patch('/internships/{id}/status',[CompanyInternshipController::class, 'updateStatus'])->name('internships.status');
        Route::delete('/internships/{id}',      [CompanyInternshipController::class, 'destroy'])->name('internships.destroy');

        Route::get('/applications',             [CompanyApplicationController::class, 'index'])->name('applications');
        Route::get('/applications/{id}',        [CompanyApplicationController::class, 'show'])->name('applications.show');
        Route::patch('/applications/{id}/status',[CompanyApplicationController::class, 'updateStatus'])->name('applications.status');

        Route::get('/applications/{applicationId}/interview/create', [CompanyInterviewController::class, 'create'])->name('interviews.create');
        Route::post('/applications/{applicationId}/interview',       [CompanyInterviewController::class, 'store'])->name('interviews.store');
        Route::delete('/applications/{applicationId}/interview',     [CompanyInterviewController::class, 'destroy'])->name('interviews.destroy');

        Route::get('/interviews', function () {
            $companyId = DB::select(
                "SELECT COMPANY_ID FROM COMPANIES WHERE USER_ID = :user_id AND ROWNUM = 1",
                ['user_id' => Auth::id()]
            )[0]->company_id;

            $internshipIds = array_column(
                DB::select(
                    "SELECT INTERNSHIP_ID FROM INTERNSHIPS WHERE COMPANY_ID = :company_id",
                    ['company_id' => $companyId]
                ), 'internship_id'
            );

            $interviews = collect([]);

            if (!empty($internshipIds)) {
                $placeholders = implode(',', array_fill(0, count($internshipIds), '?'));
                $rows = DB::select(
                    "SELECT a.APPLICATION_ID, a.STATUS,
                            s.FIRST_NAME, s.LAST_NAME,
                            i.TITLE AS INTERNSHIP_TITLE,
                            iv.INTERVIEW_ID, iv.SCHEDULED_DATE, iv.SCHEDULED_TIME,
                            iv.INTERVIEW_MODE, iv.LOCATION_OR_LINK
                     FROM APPLICATIONS a
                     INNER JOIN STUDENTS   s  ON a.STUDENT_ID    = s.STUDENT_ID
                     INNER JOIN INTERNSHIPS i  ON a.INTERNSHIP_ID = i.INTERNSHIP_ID
                     INNER JOIN INTERVIEWS  iv ON a.APPLICATION_ID = iv.APPLICATION_ID
                     WHERE a.INTERNSHIP_ID IN ($placeholders)
                     ORDER BY iv.SCHEDULED_DATE ASC",
                    $internshipIds
                );

                $interviews = collect(array_map(fn($r) => (object)[
                    'APPLICATION_ID' => $r->application_id,
                    'STATUS'         => $r->status,
                    'student'  => (object)['FIRST_NAME' => $r->first_name, 'LAST_NAME' => $r->last_name],
                    'internship' => (object)['TITLE' => $r->internship_title],
                    'interview' => (object)[
                        'INTERVIEW_ID'     => $r->interview_id,
                        'SCHEDULED_DATE'   => $r->scheduled_date,
                        'SCHEDULED_TIME'   => $r->scheduled_time,
                        'INTERVIEW_MODE'   => $r->interview_mode,
                        'LOCATION_OR_LINK' => $r->location_or_link,
                    ],
                ], $rows));
            }

            return view('company.interviews.index', compact('interviews'));
        })->name('interviews.list');
    });

// ─── Admin Routes ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'active', 'admin', 'prevent_back'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard',  [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/recommendations/regenerate',
            [AdminDashboardController::class, 'regenerateRecommendations'])
            ->name('recommendations.regenerate');

        Route::get('/users',                    [AdminUserController::class, 'index'])->name('users');
        Route::patch('/users/{userId}/toggle',  [AdminUserController::class, 'toggleActive'])->name('users.toggle');

        Route::get('/reports/applications', [AdminReportController::class, 'applicationSummary'])->name('reports.applications');
        Route::get('/reports/placement',    [AdminReportController::class, 'studentPlacement'])->name('reports.placement');
        Route::get('/reports/skills',       [AdminReportController::class, 'skillDemand'])->name('reports.skills');
        Route::get('/reports/companies',    [AdminReportController::class, 'companyActivity'])->name('reports.companies');

        Route::get('/skills',              [AdminSkillController::class, 'index'])->name('skills');
        Route::post('/skills',             [AdminSkillController::class, 'store'])->name('skills.store');
        Route::delete('/skills/{skillId}', [AdminSkillController::class, 'destroy'])->name('skills.destroy');
    });