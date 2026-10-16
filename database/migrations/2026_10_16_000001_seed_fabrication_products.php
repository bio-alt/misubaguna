<?php

use Database\Seeders\FabricationSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Content migration: the deploy runs migrations but not seeders, so the Fabrication
 * category and its product pages reach the live database through here.
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
        DB::table('public_products')->whereIn('slug', FabricationSeeder::slugs())->delete();
        DB::table('public_product_categories')->where('slug', FabricationSeeder::CATEGORY_SLUG)->delete();
    }
};
