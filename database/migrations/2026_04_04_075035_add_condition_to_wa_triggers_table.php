<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlite';

    public function up(): void
    {
        Schema::connection('sqlite')->table('wa_triggers', function (Blueprint $table) {
            $table->string('condition')->nullable()->after('type');
        });

        // Backfill existing rows
        DB::connection('sqlite')->table('wa_triggers')
            ->where('type', 'pending_payment')
            ->whereNull('condition')
            ->update(['condition' => 'invoice_active']);

        DB::connection('sqlite')->table('wa_triggers')
            ->where('type', 'expiry_reminder')
            ->whereNull('condition')
            ->update(['condition' => 'before_expiry']);
    }

    public function down(): void
    {
        Schema::connection('sqlite')->table('wa_triggers', function (Blueprint $table) {
            $table->dropColumn('condition');
        });
    }
};
