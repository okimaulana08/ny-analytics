<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlite';

    public function up(): void
    {
        Schema::connection('sqlite')->create('wa_scheduler_logs', function (Blueprint $table) {
            $table->id();
            $table->string('scheduler_name');
            $table->enum('status', ['success', 'failed', 'skipped']);
            $table->text('message')->nullable();
            $table->integer('notifications_sent')->default(0);
            $table->timestamp('executed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::connection('sqlite')->dropIfExists('wa_scheduler_logs');
    }
};
