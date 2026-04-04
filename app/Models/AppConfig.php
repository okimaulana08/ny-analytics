<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    protected $table = 'system_configs';

    protected $fillable = [
        'group',
        'label',
        'key',
        'value',
        'type',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $config = static::where('key', $key)->first();

        if (! $config) {
            return $default;
        }

        return match ($config->type) {
            'integer' => (int) $config->value,
            'float' => (float) $config->value,
            'boolean' => filter_var($config->value, FILTER_VALIDATE_BOOLEAN),
            default => $config->value,
        };
    }

    public function typeBadgeStyle(): string
    {
        return match ($this->type) {
            'integer' => 'background:#dbeafe;color:#1e40af',
            'float' => 'background:#ede9fe;color:#5b21b6',
            'boolean' => 'background:#fef3c7;color:#92400e',
            default => 'background:#f1f5f9;color:#475569',
        };
    }
}
