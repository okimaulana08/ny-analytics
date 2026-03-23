<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AssistantController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('admin.dashboard'));

// Auth
Route::get('/admin/login',  [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Protected admin routes
Route::middleware('admin.auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/users',              [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create',       [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users',             [AdminUserController::class, 'store'])->name('users.store');
    Route::patch('/users/{adminUser}/toggle', [AdminUserController::class, 'toggleActive'])->name('users.toggle');
    Route::delete('/users/{adminUser}',       [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('/reports/subscription',  [ReportController::class, 'subscription'])->name('reports.subscription');
    Route::get('/reports/engagement',    [ReportController::class, 'engagement'])->name('reports.engagement');
    Route::get('/reports/segments',      [ReportController::class, 'segments'])->name('reports.segments');
    Route::get('/reports/transactions',  [ReportController::class, 'transactions'])->name('reports.transactions');
    Route::get('/reports/users',         [ReportController::class, 'userList'])->name('reports.users');
    Route::get('/reports/realtime',        [ReportController::class, 'realtime'])->name('reports.realtime');
    Route::get('/reports/user-activity',    [ReportController::class, 'userActivity'])->name('reports.user-activity');
    Route::get('/reports/content',          [ReportController::class, 'contentAnalytics'])->name('reports.content');
    Route::get('/reports/acquisition',      [ReportController::class, 'acquisition'])->name('reports.acquisition');

    Route::prefix('assistant')->name('assistant.')->group(function () {
        Route::get('/stakeholder',             [AssistantController::class, 'stakeholder'])->name('stakeholder');
        Route::post('/stakeholder/ai-insight', [AssistantController::class, 'generateAiInsight'])->name('stakeholder.ai');
    });
});
