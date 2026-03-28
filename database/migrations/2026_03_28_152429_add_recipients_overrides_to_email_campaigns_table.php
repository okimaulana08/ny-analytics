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
        Schema::connection('sqlite')->table('email_campaigns', function (Blueprint $table) {
            $table->json('extra_recipients')->nullable();
            $table->json('excluded_emails')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('sqlite')->table('email_campaigns', function (Blueprint $table) {
            $table->dropColumn(['extra_recipients', 'excluded_emails']);
        });
    }
};
