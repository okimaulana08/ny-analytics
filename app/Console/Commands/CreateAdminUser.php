<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use Illuminate\Console\Command;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create
                            {--name= : Nama admin}
                            {--email= : Email admin}
                            {--password= : Password admin}';

    protected $description = 'Buat akun admin baru';

    public function handle(): int
    {
        $name     = $this->option('name')     ?? $this->ask('Nama');
        $email    = $this->option('email')    ?? $this->ask('Email');
        $password = $this->option('password') ?? $this->secret('Password');

        if (AdminUser::where('email', $email)->exists()) {
            $this->error("Email '{$email}' sudah terdaftar.");
            return 1;
        }

        AdminUser::create([
            'name'      => $name,
            'email'     => $email,
            'password'  => $password,
            'is_active' => true,
        ]);

        $this->info("Admin '{$name}' ({$email}) berhasil dibuat.");
        return 0;
    }
}
