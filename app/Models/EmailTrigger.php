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

    public const TYPE_PENDING_PAYMENT = 'pending_payment';

    public const COND_INVOICE_ACTIVE = 'invoice_active';

    public const COND_INVOICE_EXPIRED = 'invoice_expired';

    public const COND_BEFORE_EXPIRY = 'before_expiry';

    public const COND_AFTER_EXPIRY = 'after_expiry';

    protected $connection = 'sqlite';

    protected $fillable = [
        'name',
        'description',
        'trigger_type',
        'condition',
        'subject',
        'html_body',
        'preview_text',
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
            self::TYPE_PENDING_PAYMENT => 'Pending Payment',
            default => $this->trigger_type,
        };
    }

    public function conditionLabel(): string
    {
        return match ($this->condition) {
            self::COND_INVOICE_ACTIVE => 'Invoice Aktif',
            self::COND_INVOICE_EXPIRED => 'Invoice Expired',
            self::COND_BEFORE_EXPIRY => 'Sebelum Berakhir',
            self::COND_AFTER_EXPIRY => 'Setelah Berakhir',
            default => $this->condition ?? '-',
        };
    }

    /** @return array<string, string> placeholder => description */
    public function availablePlaceholders(): array
    {
        return match ($this->condition) {
            self::COND_INVOICE_ACTIVE => [
                '{name}' => 'Nama user',
                '{plan_name}' => 'Nama paket',
                '{amount}' => 'Nominal transaksi',
                '{invoice_link}' => 'Link pembayaran invoice',
            ],
            self::COND_INVOICE_EXPIRED => [
                '{name}' => 'Nama user',
                '{plan_name}' => 'Nama paket',
                '{amount}' => 'Nominal transaksi',
                '{subscription_url}' => 'URL halaman pilih paket',
            ],
            self::COND_BEFORE_EXPIRY => [
                '{name}' => 'Nama user',
                '{plan_name}' => 'Nama paket',
                '{expired_at}' => 'Tanggal berakhir',
                '{days_left}' => 'Sisa hari sebelum berakhir',
            ],
            self::COND_AFTER_EXPIRY => [
                '{name}' => 'Nama user',
                '{plan_name}' => 'Nama paket',
                '{expired_at}' => 'Tanggal berakhir',
            ],
            default => [
                '{name}' => 'Nama user',
                '{plan_name}' => 'Nama paket',
            ],
        };
    }
}
