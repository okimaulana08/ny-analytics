<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTemplate extends Model
{
    const TYPE_CUSTOM = 'custom';

    const TYPE_STORY_RECOMMENDATION = 'story_recommendation';

    const TYPE_PAYMENT_REMINDER = 'payment_reminder';

    const TYPE_PROMO = 'promo';

    const TYPES = [
        self::TYPE_CUSTOM,
        self::TYPE_STORY_RECOMMENDATION,
        self::TYPE_PAYMENT_REMINDER,
        self::TYPE_PROMO,
    ];

    const TYPE_LABELS = [
        self::TYPE_CUSTOM => 'Custom',
        self::TYPE_STORY_RECOMMENDATION => '3 Cerita Rekomendasi',
        self::TYPE_PAYMENT_REMINDER => 'Pengingat Pembayaran',
        self::TYPE_PROMO => 'Promo',
    ];

    protected $table = 'email_templates';

    protected $fillable = [
        'name', 'subject', 'html_body', 'preview_text',
        'is_active', 'created_by', 'template_type', 'template_settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'template_settings' => 'array',
        ];
    }

    public function isCustom(): bool
    {
        return $this->template_type === self::TYPE_CUSTOM;
    }

    public function isBuiltIn(): bool
    {
        return ! $this->isCustom();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class, 'email_template_id');
    }
}
