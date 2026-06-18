<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('public_products')) {
            return;
        }

        Schema::create('public_products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('url_path')->unique();
            $table->string('name');
            $table->string('product_group')->nullable();
            $table->string('product_subgroup')->nullable();
            $table->string('headline')->nullable();
            $table->text('summary')->nullable();
            $table->longText('content_html')->nullable();
            $table->json('key_features')->nullable();
            $table->json('applications')->nullable();
            $table->json('materials')->nullable();
            $table->json('technical_specs')->nullable();
            $table->json('gallery')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image_path')->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->boolean('sitemap_include')->default(true);
            $table->decimal('sitemap_priority', 2, 1)->default(0.5);
            $table->string('sitemap_changefreq')->default('monthly');
            $table->longText('schema_json')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_products');
    }
};
