<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlite';

    public function up(): void
    {
        Schema::connection('sqlite')->create('wa_trigger_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wa_trigger_id')->constrained('wa_triggers')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('sqlite')->dropIfExists('wa_trigger_templates');
    }
};
