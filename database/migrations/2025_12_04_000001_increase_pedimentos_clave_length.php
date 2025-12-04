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
            // Aumentar el tamaño de la columna clave para soportar números de pedimento largos
            $table->string('clave', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedimentos', function (Blueprint $table) {
            // Revertir al tamaño original
            $table->string('clave', 10)->change();
        });
    }
};