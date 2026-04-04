<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_trigger_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_trigger_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('user_id')->nullable();
            $table->foreignId('email_campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['email_trigger_id', 'recipient_email', 'sent_at'], 'etl_trigger_email_sent_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_trigger_logs');
    }
};
