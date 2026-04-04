<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('novel_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_story_id')->constrained('novel_stories')->cascadeOnDelete();
            $table->unsignedTinyInteger('chapter_number');
            $table->string('title')->nullable();

            // Outline stage
            $table->enum('outline_status', ['pending', 'generating', 'ready', 'approved', 'failed'])->default('pending');
            $table->text('outline_prompt_notes')->nullable();
            $table->text('outline_content')->nullable();
            $table->timestamp('approved_outline_at')->nullable();
            $table->foreignId('approved_outline_by')->nullable()->constrained('admin_users')->nullOnDelete();

            // Content stage
            $table->enum('content_status', ['pending', 'generating', 'ready', 'approved', 'revision_requested', 'failed'])->default('pending');
            $table->text('content_prompt_notes')->nullable();
            $table->longText('content_draft')->nullable();
            $table->text('content_revision_note')->nullable();
            $table->timestamp('approved_content_at')->nullable();
            $table->foreignId('approved_content_by')->nullable()->constrained('admin_users')->nullOnDelete();

            // Token tracking
            $table->unsignedInteger('outline_input_tokens')->default(0);
            $table->unsignedInteger('outline_output_tokens')->default(0);
            $table->unsignedInteger('content_input_tokens')->default(0);
            $table->unsignedInteger('content_output_tokens')->default(0);
            $table->unsignedTinyInteger('content_generation_count')->default(0);

            $table->timestamps();

            $table->unique(['novel_story_id', 'chapter_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novel_chapters');
    }
};
