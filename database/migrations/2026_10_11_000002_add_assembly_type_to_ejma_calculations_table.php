<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ejma_calculations', function (Blueprint $table) {
            // Phase 1 rows are single expansion joints.
            $table->string('assembly_type')->nullable()->default('single')->after('bellows_type');
        });
    }

    public function down(): void
    {
        Schema::table('ejma_calculations', function (Blueprint $table) {
            $table->dropColumn('assembly_type');
        });
    }
};
