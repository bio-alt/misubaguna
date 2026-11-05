<?php

use Database\Seeders\FilterPressContentSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Content migration: the deploy runs migrations but not seeders, so the Filter Press Plates
 * category hub and its five plate-type pages reach the live database through here.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new FilterPressContentSeeder)->run();
    }

    public function down(): void
    {
        $slugs = [
            'recessed-chamber-filter-plate',
            'membrane-filter-plate',
            'cgr-gasketed-filter-plate',
            'plate-and-frame-filter-plate',
            'special-material-filter-plate',
        ];

        DB::table('public_products')->whereIn('slug', $slugs)->delete();
        DB::table('public_product_categories')->where('slug', 'filter-press-plates')->delete();
    }
};
