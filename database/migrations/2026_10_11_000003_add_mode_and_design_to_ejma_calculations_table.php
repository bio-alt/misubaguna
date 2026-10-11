<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ejma_calculations', function (Blueprint $table) {
            // Earlier rows are check-mode calculations.
            $table->string('mode')->nullable()->default('check')->after('assembly_type');
            $table->json('design')->nullable()->after('results');
        });
    }

    public function down(): void
    {
        Schema::table('ejma_calculations', function (Blueprint $table) {
            $table->dropColumn(['mode', 'design']);
        });
    }
};
