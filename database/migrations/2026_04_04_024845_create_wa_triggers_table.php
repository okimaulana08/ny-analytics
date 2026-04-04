<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['pending_payment', 'expiry_reminder']);
            $table->unsignedSmallInteger('delay_value')->default(30);
            $table->enum('delay_unit', ['minutes', 'hours', 'days'])->default('minutes');
            $table->unsignedSmallInteger('cooldown_hours')->default(24);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_triggers');
    }
};
