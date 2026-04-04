<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_triggers', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('condition');
            $table->longText('html_body')->nullable()->after('subject');
            $table->string('preview_text')->nullable()->after('html_body');
        });
    }

    public function down(): void
    {
        Schema::table('email_triggers', function (Blueprint $table) {
            $table->dropColumn(['subject', 'html_body', 'preview_text']);
        });
    }
};
