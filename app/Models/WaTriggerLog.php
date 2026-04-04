<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaTriggerLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'wa_trigger_id',
        'user_id',
        'phone',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function trigger(): BelongsTo
    {
        return $this->belongsTo(WaTrigger::class, 'wa_trigger_id');
    }
}
