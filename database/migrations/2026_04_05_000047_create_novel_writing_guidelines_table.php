<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('novel_writing_guidelines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('genre')->default('drama_rumah_tangga');
            $table->boolean('is_active')->default(true);
            $table->string('language_style')->nullable();
            $table->string('narrative_pov')->default('first_person');
            $table->longText('content_guidelines')->nullable();
            $table->json('character_archetypes')->nullable();
            $table->text('plot_structure_notes')->nullable();
            $table->text('forbidden_content')->nullable();
            $table->unsignedSmallInteger('target_chapter_word_count')->default(2000);
            $table->text('system_prompt_prefix')->nullable();
            $table->foreignId('created_by')->constrained('admin_users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novel_writing_guidelines');
    }
};
