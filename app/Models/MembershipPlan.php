<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    protected $connection  = 'novel';
    protected $table       = 'membership_plans';
    protected $keyType     = 'string';
    public    $incrementing = false;
    protected $guarded     = [];
}
