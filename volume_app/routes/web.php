<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\FingerprintController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Operator\TerminalController;
use App\Http\Controllers\Operator\OccurrenceController;
use App\Http\Controllers\Company\DashboardController as CompanyDashboard;
use App\Http\Controllers\Fiscal\DashboardController as FiscalDashboard;
use App\Http\Controllers\Management\DashboardController as ManagementDashboard;
use App\Http\Controllers\Management\OccurrenceController as ManagementOccurrenceController;
use App\Http\Controllers\ReportController;

// Auth routes
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

// Operator routes
Route::middleware(['auth', 'role:operator,admin'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/terminal', [TerminalController::class, 'index'])->name('terminal');
    Route::post('/biometric-check', [TerminalController::class, 'biometricCheck'])->name('biometric.check');
    Route::get('/search-student', [TerminalController::class, 'searchStudent'])->name('search.student');
    Route::post('/manual-release', [TerminalController::class, 'manualRelease'])->name('manual.release');
    Route::get('/occurrences', [OccurrenceController::class, 'index'])->name('occurrences.index');
    Route::post('/occurrences', [OccurrenceController::class, 'store'])->name('occurrences.store');
    Route::get('/sync-report', [TerminalController::class, 'syncReport'])->name('sync.report');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class)->except(['show', 'destroy']);
    Route::resource('students', StudentController::class)->except(['show', 'destroy']);
    Route::post('/students/{student}/deactivate', [StudentController::class, 'deactivate'])->name('students.deactivate');
    Route::post('/students/{student}/anonymize', [StudentController::class, 'anonymize'])->name('students.anonymize');
    Route::get('/students-import', [StudentController::class, 'importForm'])->name('students.import.form');
    Route::post('/students-import', [StudentController::class, 'import'])->name('students.import');
    Route::post('/students/{student}/fingerprints', [FingerprintController::class, 'store'])->name('fingerprints.store');
    Route::delete('/students/{student}/fingerprints/{fingerprint}', [FingerprintController::class, 'destroy'])->name('fingerprints.destroy');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/school-days', [SettingsController::class, 'updateSchoolDays'])->name('settings.school-days');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.logs');
});

// Company routes
Route::middleware(['auth', 'role:company,admin'])->prefix('company')->name('company.')->group(function () {
    Route::get('/dashboard', [CompanyDashboard::class, 'index'])->name('dashboard');
    Route::get('/api/realtime', [CompanyDashboard::class, 'apiRealtime'])->name('api.realtime');
});

// Fiscal routes
Route::middleware(['auth', 'role:fiscal,admin'])->prefix('fiscal')->name('fiscal.')->group(function () {
    Route::get('/dashboard', [FiscalDashboard::class, 'index'])->name('dashboard');
    Route::post('/validate-period', [FiscalDashboard::class, 'validatePeriod'])->name('validate.period');
    Route::post('/preview-period', [FiscalDashboard::class, 'previewPeriod'])->name('preview.period');
    Route::get('/validation/{validation}', [FiscalDashboard::class, 'showValidation'])->name('validation.show');
});

// Management routes
Route::middleware(['auth', 'role:management,admin'])->prefix('management')->name('management.')->group(function () {
    Route::get('/dashboard', [ManagementDashboard::class, 'index'])->name('dashboard');
    Route::get('/occurrences', [ManagementOccurrenceController::class, 'index'])->name('occurrences');
});

// Report routes (accessible by admin, company, fiscal, management)
Route::middleware(['auth', 'role:admin,company,fiscal,management'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/daily', [ReportController::class, 'daily'])->name('daily');
    Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');
    Route::get('/by-student', [ReportController::class, 'byStudent'])->name('by-student');
    Route::get('/by-operator', [ReportController::class, 'byOperator'])->name('by-operator');
    Route::get('/exceptions', [ReportController::class, 'exceptions'])->name('exceptions');
    Route::get('/payment', [ReportController::class, 'payment'])->name('payment');
});
