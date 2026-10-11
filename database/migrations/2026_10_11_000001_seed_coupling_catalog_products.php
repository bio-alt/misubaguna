<?php

use App\Support\CouplingCatalog;
use Database\Seeders\CouplingCatalogSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Content migration: the deploy runs migrations but not seeders, so the coupling
 * hub and family pages reach the live database through here.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new CouplingCatalogSeeder)->run();
    }

    public function down(): void
    {
        $slugs = array_column(CouplingCatalog::families(), 'slug');
        DB::table('public_products')->whereIn('slug', $slugs)->delete();
    }
};
