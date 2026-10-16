<?php

use Database\Seeders\MaterialHandlingSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Content migration: the scraper conveyor now leads with the sharp Hardox chain photo. The seeder
 * is an idempotent upsert, so re-running it carries the new gallery order to the live database.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('public_products')) {
            return;
        }

        (new MaterialHandlingSeeder)->run();
    }

    public function down(): void
    {
        // Content only; the original seeder migration owns removal.
    }
};
