<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaTrigger extends Model
{
    public const TYPE_PENDING_PAYMENT = 'pending_payment';

    public const TYPE_EXPIRY_REMINDER = 'expiry_reminder';

    protected $connection = 'sqlite';

    protected $fillable = [
        'name',
        'type',
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
}
