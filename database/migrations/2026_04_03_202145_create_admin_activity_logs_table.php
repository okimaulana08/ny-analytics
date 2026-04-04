<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->string('admin_name');
            $table->string('admin_email');
            $table->string('action', 50)->comment('Login, Create, Update, Delete, Send, Export, Toggle, Generate');
            $table->string('feature')->comment('Human-readable feature name, e.g. Email Templates');
            $table->string('url', 500)->nullable();
            $table->string('http_method', 10)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload')->nullable()->comment('Changed data as JSON');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
