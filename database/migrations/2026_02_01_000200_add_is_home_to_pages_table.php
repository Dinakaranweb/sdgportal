<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // Marks the one page whose blocks are rendered on the home page.
            // It has no address of its own, so it is never shown in the menu.
            $table->boolean('is_home')->default(false)->after('show_in_menu')->index();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('is_home');
        });
    }
};
