<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $connection  = 'novel';
    protected $table       = 'transactions';
    protected $keyType     = 'string';
    public    $incrementing = false;
    protected $guarded     = [];

    protected $casts = [
        'plan_price'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at'      => 'datetime',
        'expired_at'   => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(NovelUser::class, 'user_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'plan_id');
    }
}
