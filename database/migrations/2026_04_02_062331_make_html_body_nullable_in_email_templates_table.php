<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlite';

    public function up(): void
    {
        Schema::connection('sqlite')->table('email_templates', function (Blueprint $table) {
            $table->longText('html_body')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::connection('sqlite')->table('email_templates', function (Blueprint $table) {
            $table->longText('html_body')->nullable(false)->change();
        });
    }
};
