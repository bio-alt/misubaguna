<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 16)->index();
            $table->string('reference', 20)->nullable()->unique();
            $table->string('product_slug')->nullable();
            $table->string('product_name')->nullable();
            $table->string('item', 160)->nullable();
            $table->string('name');
            $table->string('company');
            $table->string('email');
            $table->string('phone');
            $table->string('location', 160)->nullable();
            $table->string('category', 80)->nullable();
            $table->json('answers')->nullable();
            $table->text('message')->nullable();
            $table->json('files')->nullable();
            $table->string('locale', 5)->default('en');
            $table->string('source_url', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('emailed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
