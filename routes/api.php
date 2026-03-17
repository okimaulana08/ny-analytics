<?php

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {
    // Manual trigger endpoints
    Route::post('/notify/pending',       [NotificationController::class, 'triggerPending']);
    Route::post('/notify/paid',          [NotificationController::class, 'triggerPaid']);
    Route::post('/notify/daily-summary', [NotificationController::class, 'triggerDailySummary']);

    // Discovery and monitoring
    Route::get('/groups/find',           [NotificationController::class, 'findGroup']);
    Route::get('/scheduler/status',      [NotificationController::class, 'schedulerStatus']);
});
