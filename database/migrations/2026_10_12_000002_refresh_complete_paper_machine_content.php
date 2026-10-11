<?php

use Database\Seeders\PaperPulpContentSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Complete Paper Machine page: fuller meta description, pocket-ventilated dryers, QCS and
 * pope reel detail, more board grades, and a spare & wear parts section.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('public_products')) {
            return;
        }

        (new PaperPulpContentSeeder)->applyOne('/product/paper-machine-line/');
    }

    public function down(): void
    {
        // Content refresh; nothing to undo.
    }
};
