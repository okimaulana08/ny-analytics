<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Admin Novelya',
                'email' => 'admin@novelya.id',
                'password' => Hash::make('secret123'),
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
