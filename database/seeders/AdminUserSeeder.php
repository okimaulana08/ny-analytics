<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Admin Novelya',
                'email' => 'admin@novelya.id',
                'password' => 'secret123', // plain — setPasswordAttribute mutator handles hashing
                'is_active' => true,
            ],
        ];

        foreach ($admins as $admin) {
            AdminUser::firstOrCreate(
                ['email' => $admin['email']],
                $admin
            );
        }
    }
}
