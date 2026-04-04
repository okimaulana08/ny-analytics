<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('novel_stories', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('genre')->default('drama_rumah_tangga');
            $table->foreignId('novel_writing_guideline_id')->nullable()->constrained('novel_writing_guidelines')->nullOnDelete();
            $table->enum('status', [
                'draft',
                'overview_pending',
                'overview_ready',
                'overview_approved',
                'outline_pending',
                'outline_ready',
                'outline_approved',
                'content_in_progress',
                'content_complete',
                'published',
            ])->default('draft');
            $table->text('overview_prompt_notes')->nullable();
            $table->longText('overview_ai_raw')->nullable();
            $table->string('title_draft')->nullable();
            $table->string('theme')->nullable();
            $table->text('synopsis')->nullable();
            $table->json('characters')->nullable();
            $table->text('general_overview')->nullable();
            $table->json('plot_points')->nullable();
            $table->unsignedTinyInteger('total_chapters_planned')->default(20);
            $table->timestamp('approved_overview_at')->nullable();
            $table->foreignId('approved_overview_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamp('approved_outline_at')->nullable();
            $table->foreignId('approved_outline_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->unsignedInteger('total_input_tokens')->default(0);
            $table->unsignedInteger('total_output_tokens')->default(0);
            $table->foreignId('created_by')->constrained('admin_users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novel_stories');
    }
};
