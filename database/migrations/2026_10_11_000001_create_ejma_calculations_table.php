<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ejma_calculations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('bellows_type');
            $table->string('verdict');
            $table->json('inputs');
            $table->json('results');
            $table->json('contact');
            $table->string('ip_address')->nullable();
            $table->timestamp('emailed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ejma_calculations');
    }
};
