<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_applications', function (Blueprint $table) {
            $table->id();
            $table->string('position');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->text('cover_letter')->nullable();
            $table->string('cv_path');
            $table->string('cv_original_name');
            $table->string('ip_address')->nullable();
            $table->timestamp('emailed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_applications');
    }
};
