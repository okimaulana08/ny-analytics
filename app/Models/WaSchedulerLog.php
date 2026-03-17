<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaSchedulerLog extends Model
{
    protected $connection = 'sqlite';
    protected $table      = 'wa_scheduler_logs';
    public    $timestamps = false;

    protected $fillable = [
        'scheduler_name',
        'status',
        'message',
        'notifications_sent',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];
}
