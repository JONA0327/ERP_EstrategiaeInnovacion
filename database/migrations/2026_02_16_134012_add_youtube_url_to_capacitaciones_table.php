<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('capacitaciones', function (Blueprint $table) {
            $table->string('youtube_url')->nullable()->after('archivo_path');
            $table->string('archivo_path')->nullable()->change(); // Ahora es opcional si hay YouTube
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('capacitaciones', function (Blueprint $table) {
            $table->dropColumn('youtube_url');
            $table->string('archivo_path')->nullable(false)->change();
        });
    }
};
