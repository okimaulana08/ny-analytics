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

// Automated email triggers - daily at 07:00 Jakarta time
Schedule::command('triggers:run')
    ->dailyAt('07:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Scheduled email reports - daily at 08:00 Jakarta time
Schedule::command('reports:send-scheduled')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// WA triggers - every 5 minutes
Schedule::command('wa:run-triggers')
    ->everyFiveMinutes()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Queue worker for AI novel generation jobs - every minute
Schedule::command('queue:work --queue=default --stop-when-empty --max-time=110 --tries=2 --timeout=300')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/queue-worker.log'));
