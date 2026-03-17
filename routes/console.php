<?php

use Illuminate\Support\Facades\Schedule;

// Notify pending transactions - every 5 minutes
Schedule::command('notify:pending')
    ->everyFiveMinutes()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Notify paid transactions - every 5 minutes
Schedule::command('notify:paid')
    ->everyFiveMinutes()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Daily summary - at 23:55 Jakarta time
Schedule::command('notify:daily-summary')
    ->dailyAt('23:55')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));
