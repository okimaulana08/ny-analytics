<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlite';

    public function up(): void
    {
        Schema::connection('sqlite')->create('email_campaign_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->enum('status', ['sent', 'delivered', 'opened', 'clicked', 'bounced', 'failed', 'unsubscribed'])->default('sent');
            $table->string('brevo_message_id')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['email_campaign_id', 'recipient_email']);
        });
    }

    public function down(): void
    {
        Schema::connection('sqlite')->dropIfExists('email_campaign_logs');
    }
};
