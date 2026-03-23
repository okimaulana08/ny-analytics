<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class AdminUser extends Model
{
    protected $connection = 'sqlite';
    protected $table      = 'admin_users';

    protected $fillable = ['name', 'email', 'password', 'is_active'];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function checkPassword(string $plain): bool
    {
        return Hash::check($plain, $this->password);
    }
}
