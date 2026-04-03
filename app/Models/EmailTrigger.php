<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTrigger extends Model
{
    public const TYPE_EXPIRY_REMINDER = 'expiry_reminder';

    public const TYPE_RE_ENGAGEMENT = 're_engagement';

    public const TYPE_WELCOME_PAYMENT = 'welcome_payment';

    protected $connection = 'sqlite';

    protected $fillable = [
        'name',
        'description',
        'trigger_type',
        'email_template_id',
        'conditions',
        'cooldown_days',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(EmailTriggerLog::class);
    }

    public function typeLabel(): string
    {
        return match ($this->trigger_type) {
            self::TYPE_EXPIRY_REMINDER => 'Reminder Expiry',
            self::TYPE_RE_ENGAGEMENT => 'Re-engagement',
            self::TYPE_WELCOME_PAYMENT => 'Welcome Pembayaran',
            default => $this->trigger_type,
        };
    }
}
