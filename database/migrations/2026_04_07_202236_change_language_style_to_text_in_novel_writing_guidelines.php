<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('novel_writing_guidelines', function (Blueprint $table) {
            $table->text('language_style')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('novel_writing_guidelines', function (Blueprint $table) {
            $table->string('language_style')->nullable()->change();
        });
    }
};
