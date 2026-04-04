<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class AdminUser extends Model
{
    public const SUPER_ADMIN_EMAIL = 'admin@novelya.id';

    protected $table = 'admin_users';

    protected $fillable = ['name', 'email', 'password', 'is_active', 'avatar_color', 'last_login_at'];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->email === self::SUPER_ADMIN_EMAIL;
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function checkPassword(string $plain): bool
    {
        return Hash::check($plain, $this->password);
    }
}
