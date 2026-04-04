<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlite';

    public function up(): void
    {
        Schema::connection('sqlite')->table('admin_users', function (Blueprint $table) {
            $table->string('avatar_color')->default('blue')->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('avatar_color');
        });
    }

    public function down(): void
    {
        Schema::connection('sqlite')->table('admin_users', function (Blueprint $table) {
            $table->dropColumn(['avatar_color', 'last_login_at']);
        });
    }
};
