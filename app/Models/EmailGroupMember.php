<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailGroupMember extends Model
{
    protected $table = 'email_group_members';

    protected $fillable = ['email_group_id', 'email', 'name'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(EmailGroup::class, 'email_group_id');
    }
}
