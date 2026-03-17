<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaNotification extends Model
{
    protected $connection = 'sqlite';
    protected $table      = 'wa_notifications';
    public    $timestamps = false;

    protected $fillable = ['transaction_id', 'type', 'sent_at'];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
