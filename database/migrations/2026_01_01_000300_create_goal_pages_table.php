<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goal_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('goal_pages')->cascadeOnDelete();
            $table->string('type')->default('submenu');
            $table->string('code', 30)->nullable();
            $table->string('title');
            $table->string('slug');
            $table->string('path', 500)->index();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('color', 20)->nullable();
            $table->string('layout')->default('standard');
            $table->string('external_url')->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('show_in_menu')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();

            $table->unique(['goal_id', 'path'], 'goal_pages_goal_path_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_pages');
    }
};
