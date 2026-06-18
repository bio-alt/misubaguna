<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('public_media_assets')) {
            return;
        }

        Schema::create('public_media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('title')->nullable();
            $table->text('alt_text')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_media_assets');
    }
};
