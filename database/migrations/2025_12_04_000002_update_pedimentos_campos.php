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
            // Cambiar fecha_vencimiento por fecha_tentativa_pago
            $table->dropColumn('fecha_vencimiento');
            $table->date('fecha_tentativa_pago')->nullable()->after('fecha_pago');
            
            // Agregar campo moneda
            $table->string('moneda', 10)->default('USD')->after('monto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedimentos', function (Blueprint $table) {
            $table->dropColumn(['fecha_tentativa_pago', 'moneda']);
            $table->datetime('fecha_vencimiento')->nullable();
        });
    }
};