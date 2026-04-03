<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlite';

    public function up(): void
    {
        Schema::connection('sqlite')->create('scheduled_email_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('report_type', ['revenue_summary', 'top_content', 'churn_alert', 'engagement_summary']);
            $table->enum('frequency', ['weekly', 'monthly']);
            $table->tinyInteger('day_of_week')->nullable()->comment('0=Sun…6=Sat, for weekly');
            $table->tinyInteger('day_of_month')->nullable()->comment('1-31, for monthly');
            $table->json('recipients')->comment('[{email, name}]');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->foreignId('created_by')->constrained('admin_users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('sqlite')->dropIfExists('scheduled_email_reports');
    }
};
