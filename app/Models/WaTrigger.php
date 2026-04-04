<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaTrigger extends Model
{
    public const TYPE_PENDING_PAYMENT = 'pending_payment';

    public const TYPE_EXPIRY_REMINDER = 'expiry_reminder';

    public const COND_INVOICE_ACTIVE = 'invoice_active';

    public const COND_INVOICE_EXPIRED = 'invoice_expired';

    public const COND_BEFORE_EXPIRY = 'before_expiry';

    public const COND_AFTER_EXPIRY = 'after_expiry';

    protected $connection = 'sqlite';

    protected $fillable = [
        'name',
        'type',
        'condition',
        'delay_value',
        'delay_unit',
        'cooldown_hours',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'delay_value' => 'integer',
            'cooldown_hours' => 'integer',
        ];
    }

    public function templates(): HasMany
    {
        return $this->hasMany(WaTriggerTemplate::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WaTriggerLog::class);
    }

    public function delayInMinutes(): int
    {
        return match ($this->delay_unit) {
            'hours' => $this->delay_value * 60,
            'days' => $this->delay_value * 60 * 24,
            default => $this->delay_value,
        };
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_PENDING_PAYMENT => 'Pending Payment',
            self::TYPE_EXPIRY_REMINDER => 'Expiry Reminder',
            default => $this->type,
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

    public function delayLabel(): string
    {
        $unit = match ($this->delay_unit) {
            'minutes' => 'menit',
            'hours' => 'jam',
            'days' => 'hari',
            default => $this->delay_unit,
        };

        return "{$this->delay_value} {$unit}";
    }

    /** @return array<string, string> placeholder => description */
    public function availablePlaceholders(): array
    {
        return match ($this->condition ?? ($this->type === self::TYPE_PENDING_PAYMENT ? self::COND_INVOICE_ACTIVE : self::COND_BEFORE_EXPIRY)) {
            self::COND_INVOICE_ACTIVE => [
                '{name}' => 'Nama user',
                '{plan_name}' => 'Nama paket',
                '{amount}' => 'Nominal transaksi',
                '{invoice_link}' => 'Link pembayaran invoice',
                '{minutes_ago}' => 'Berapa menit lalu transaksi dibuat',
            ],
            self::COND_INVOICE_EXPIRED => [
                '{name}' => 'Nama user',
                '{plan_name}' => 'Nama paket',
                '{amount}' => 'Nominal transaksi',
                '{subscription_url}' => 'URL halaman pilih paket',
                '{minutes_ago}' => 'Berapa menit lalu transaksi dibuat',
            ],
            self::COND_BEFORE_EXPIRY => [
                '{name}' => 'Nama user',
                '{plan_name}' => 'Nama paket',
                '{expired_at}' => 'Tanggal & waktu berakhir',
                '{days_left}' => 'Sisa hari sebelum berakhir',
            ],
            self::COND_AFTER_EXPIRY => [
                '{name}' => 'Nama user',
                '{plan_name}' => 'Nama paket',
                '{expired_at}' => 'Tanggal & waktu berakhir',
            ],
            default => [
                '{name}' => 'Nama user',
                '{plan_name}' => 'Nama paket',
            ],
        };
    }
}
