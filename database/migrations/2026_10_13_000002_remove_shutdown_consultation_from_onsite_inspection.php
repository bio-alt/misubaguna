<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Shutdown consultation is no longer offered. Drop it from the On-Site Inspection summary
 * (the services list, the service page hero and llms.txt all read this field).
 */
return new class extends Migration
{
    private const OLD = 'On-site technical evaluation, root cause failure analysis, NDT inspection, and shutdown consultation for critical plant equipment.';

    private const NEW = 'On-site technical evaluation, root cause failure analysis and NDT inspection for critical plant equipment.';

    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('public_services')) {
            return;
        }

        DB::table('public_services')
            ->where('slug', 'onsite-inspection')
            ->where('summary', self::OLD)
            ->update(['summary' => self::NEW, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('public_services')
            ->where('slug', 'onsite-inspection')
            ->where('summary', self::NEW)
            ->update(['summary' => self::OLD, 'updated_at' => now()]);
    }
};
