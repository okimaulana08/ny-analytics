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
        Schema::create('novel_ai_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_story_id')->constrained('novel_stories')->cascadeOnDelete();
            $table->foreignId('novel_chapter_id')->nullable()->constrained('novel_chapters')->nullOnDelete();
            $table->enum('stage', ['overview', 'outline', 'content']);
            $table->string('model_used');
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('estimated_cost_usd', 10, 6)->default(0);
            $table->boolean('was_successful')->default(true);
            $table->text('error_message')->nullable();
            $table->foreignId('triggered_by')->constrained('admin_users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novel_ai_usages');
    }
};
