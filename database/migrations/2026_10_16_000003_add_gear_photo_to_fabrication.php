<?php

use Database\Seeders\FabricationSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Content migration: the pinion, worm and rack gear pages now carry the gear photo. The seeder
 * is an idempotent upsert, so re-running it carries the new gallery to the live database.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('public_products')) {
            return;
        }

        (new FabricationSeeder)->run();
    }

    public function down(): void
    {
        // Content only; the original seeder migration owns removal.
    }
};
