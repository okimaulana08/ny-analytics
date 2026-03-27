<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AssistantController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\Crm\BroadcastEmailController;
use App\Http\Controllers\Admin\Crm\CampaignHistoryController;
use App\Http\Controllers\Admin\Crm\EmailGroupController;
use App\Http\Controllers\Admin\Crm\EmailTemplateController;
use App\Http\Controllers\Admin\Crm\IndividualEmailController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\BrevoWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.dashboard'));

// Auth
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Brevo webhook (outside admin.auth middleware)
Route::post('/webhooks/brevo', [BrevoWebhookController::class, 'handle'])->name('webhooks.brevo');

// Protected admin routes
Route::middleware('admin.auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::patch('/users/{adminUser}/toggle', [AdminUserController::class, 'toggleActive'])->name('users.toggle');
    Route::delete('/users/{adminUser}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('/reports/subscription', [ReportController::class, 'subscription'])->name('reports.subscription');
    Route::get('/reports/engagement', [ReportController::class, 'engagement'])->name('reports.engagement');
    Route::get('/reports/segments', [ReportController::class, 'segments'])->name('reports.segments');
    Route::get('/reports/transactions', [ReportController::class, 'transactions'])->name('reports.transactions');
    Route::get('/reports/users', [ReportController::class, 'userList'])->name('reports.users');
    Route::get('/reports/realtime', [ReportController::class, 'realtime'])->name('reports.realtime');
    Route::get('/reports/user-activity', [ReportController::class, 'userActivity'])->name('reports.user-activity');
    Route::get('/reports/content', [ReportController::class, 'contentAnalytics'])->name('reports.content');
    Route::get('/reports/acquisition', [ReportController::class, 'acquisition'])->name('reports.acquisition');
    Route::get('/reports/revenue-daily', [ReportController::class, 'revenueDaily'])->name('reports.revenue-daily');
    Route::post('/reports/revenue-daily/cost', [ReportController::class, 'saveMarketingCost'])->name('reports.revenue-daily.cost');
    Route::get('/reports/user-daily', [ReportController::class, 'userDaily'])->name('reports.user-daily');
    Route::get('/reports/user-recommend/{userId}', [ReportController::class, 'userRecommend'])->name('reports.user-recommend');

    Route::prefix('assistant')->name('assistant.')->group(function () {
        Route::get('/stakeholder', [AssistantController::class, 'stakeholder'])->name('stakeholder');
        Route::post('/stakeholder/ai-insight', [AssistantController::class, 'generateAiInsight'])->name('stakeholder.ai');
    });

    // CRM Tools
    Route::prefix('crm')->name('crm.')->group(function () {
        // Broadcast Email
        Route::get('/broadcast', [BroadcastEmailController::class, 'create'])->name('broadcast.create');
        Route::post('/broadcast/preview', [BroadcastEmailController::class, 'preview'])->name('broadcast.preview');
        Route::post('/broadcast', [BroadcastEmailController::class, 'store'])->name('broadcast.store');

        // Individual Email
        Route::get('/individual', [IndividualEmailController::class, 'create'])->name('individual.create');
        Route::post('/individual', [IndividualEmailController::class, 'store'])->name('individual.store');

        // Email Groups
        Route::get('/groups/{group}/preview', [EmailGroupController::class, 'resolvePreview'])->name('groups.resolve-preview');
        Route::resource('groups', EmailGroupController::class)->except(['show']);

        // Email Templates
        Route::get('/templates/{template}/preview', [EmailTemplateController::class, 'preview'])->name('templates.preview');
        Route::resource('templates', EmailTemplateController::class)->except(['show']);

        // Campaign History
        Route::get('/campaigns', [CampaignHistoryController::class, 'index'])->name('campaigns.index');
        Route::get('/campaigns/{campaign}', [CampaignHistoryController::class, 'show'])->name('campaigns.show');
        Route::post('/campaigns/{campaign}/resend', [CampaignHistoryController::class, 'resend'])->name('campaigns.resend');
        Route::delete('/campaigns/{campaign}', [CampaignHistoryController::class, 'destroy'])->name('campaigns.destroy');
    });
});
