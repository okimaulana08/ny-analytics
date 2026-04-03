<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaTriggerTemplate extends Model
{
    protected $connection = 'sqlite';

    protected $fillable = [
        'wa_trigger_id',
        'body',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function trigger(): BelongsTo
    {
        return $this->belongsTo(WaTrigger::class, 'wa_trigger_id');
    }

    /** @param array<string, string> $params */
    public function render(array $params): string
    {
        $body = $this->body;
        foreach ($params as $key => $value) {
            $body = str_replace("{{$key}}", $value, $body);
        }

        return $body;
    }
}
