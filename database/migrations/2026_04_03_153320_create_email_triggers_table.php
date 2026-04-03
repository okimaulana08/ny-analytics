<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlite';

    public function up(): void
    {
        Schema::connection('sqlite')->create('email_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('trigger_type', ['expiry_reminder', 're_engagement', 'welcome_payment']);
            $table->foreignId('email_template_id')->nullable()->constrained()->nullOnDelete();
            $table->json('conditions')->nullable();
            $table->unsignedSmallInteger('cooldown_days')->default(14);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('sqlite')->dropIfExists('email_triggers');
    }
};
