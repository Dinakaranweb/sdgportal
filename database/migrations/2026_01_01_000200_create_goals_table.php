<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->nullable();
            $table->string('title');
            $table->string('short_title')->nullable();
            $table->string('slug')->unique();
            $table->string('statement')->nullable();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('icon')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('color', 20)->default('#1f6feb');
            $table->string('color_dark', 20)->nullable();
            $table->string('color_light', 20)->nullable();
            $table->string('text_color', 20)->default('#ffffff');
            $table->json('theme')->nullable();
            $table->string('layout')->default('standard');
            $table->string('external_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_in_menu')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
