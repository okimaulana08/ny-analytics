<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_triggers', function (Blueprint $table) {
            $table->string('trigger_type')->change(); // loosen SQLite enum → plain text to allow 'pending_payment'
            $table->string('condition')->nullable()->after('trigger_type');
        });

        // Backfill existing rows
        DB::table('email_triggers')
            ->where('trigger_type', 'expiry_reminder')
            ->whereNull('condition')
            ->update(['condition' => 'before_expiry']);

        DB::table('email_triggers')
            ->where('trigger_type', 're_engagement')
            ->whereNull('condition')
            ->update(['condition' => 're_engagement']);

        DB::table('email_triggers')
            ->where('trigger_type', 'welcome_payment')
            ->whereNull('condition')
            ->update(['condition' => 'welcome_payment']);
    }

    public function down(): void
    {
        Schema::table('email_triggers', function (Blueprint $table) {
            $table->dropColumn('condition');
        });
    }
};
