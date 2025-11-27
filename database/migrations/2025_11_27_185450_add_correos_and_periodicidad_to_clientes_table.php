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
        Schema::table('clientes', function (Blueprint $table) {
            $table->json('correos')->nullable()->after('cliente')->comment('JSON array de correos del cliente');
            $table->string('periodicidad_reporte')->nullable()->after('correos')->comment('Periodicidad de reportes: Diario, Semanal, etc.');
            $table->timestamp('fecha_carga_excel')->nullable()->after('periodicidad_reporte')->comment('Fecha cuando se cargó desde Excel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['correos', 'periodicidad_reporte', 'fecha_carga_excel']);
        });
    }
};
