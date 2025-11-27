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
        Schema::table('pedimentos', function (Blueprint $table) {
            $table->string('categoria', 100)->nullable()->after('id');
            $table->string('subcategoria', 100)->nullable()->after('categoria');
            
            // Agregar índices para búsquedas más rápidas
            $table->index(['categoria', 'clave']);
            $table->index('subcategoria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedimentos', function (Blueprint $table) {
            $table->dropIndex(['categoria', 'clave']);
            $table->dropIndex(['subcategoria']);
            $table->dropColumn(['categoria', 'subcategoria']);
        });
    }
};