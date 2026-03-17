<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlite';

    public function up(): void
    {
        Schema::connection('sqlite')->create('wa_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id', 36)->index();
            $table->enum('type', ['pending', 'paid']);
            $table->timestamp('sent_at')->useCurrent();

            $table->unique(['transaction_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::connection('sqlite')->dropIfExists('wa_notifications');
    }
};
