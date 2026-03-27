<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyRevenueCost extends Model
{
    protected $connection = 'sqlite';

    protected $fillable = ['date', 'marketing_cost'];

    protected function casts(): array
    {
        return [
            'marketing_cost' => 'integer',
        ];
    }
}
