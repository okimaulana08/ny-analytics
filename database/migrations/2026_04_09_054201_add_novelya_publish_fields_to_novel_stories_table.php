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
        Schema::table('novel_stories', function (Blueprint $table) {
            $table->char('novelya_story_id', 36)->nullable()->after('status');
            $table->char('novelya_author_id', 36)->nullable()->after('novelya_story_id');
            $table->char('novelya_category_id', 36)->nullable()->after('novelya_author_id');
            $table->string('novelya_cover_path')->nullable()->after('novelya_category_id');
            $table->timestamp('published_to_novelya_at')->nullable()->after('novelya_cover_path');
            $table->text('novelya_publish_error')->nullable()->after('published_to_novelya_at');
            $table->unsignedSmallInteger('novelya_chapters_published')->default(0)->after('novelya_publish_error');
        });
    }

    public function down(): void
    {
        Schema::table('novel_stories', function (Blueprint $table) {
            $table->dropColumn([
                'novelya_story_id',
                'novelya_author_id',
                'novelya_category_id',
                'novelya_cover_path',
                'published_to_novelya_at',
                'novelya_publish_error',
                'novelya_chapters_published',
            ]);
        });
    }
};
