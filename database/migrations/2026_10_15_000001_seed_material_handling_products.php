<?php

use Database\Seeders\MaterialHandlingSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Content migration: the deploy runs migrations but not seeders, so the Material Handling
 * category and its product pages reach the live database through here.
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
        DB::table('public_products')->whereIn('slug', MaterialHandlingSeeder::slugs())->delete();
        DB::table('public_product_categories')->where('slug', MaterialHandlingSeeder::CATEGORY_SLUG)->delete();
    }
};
