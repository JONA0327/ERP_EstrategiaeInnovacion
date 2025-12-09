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
        Schema::table('operaciones_logisticas', function (Blueprint $table) {
            // Tipo de Carga (FCL/LCL) - después de no_factura
            $table->string('tipo_carga', 100)->nullable()->after('no_factura');
            // Tipo de Incoterm - después de tipo_carga (seguido de factura)
            $table->string('tipo_incoterm', 50)->nullable()->after('tipo_carga');
            // Puerto de Salida - después de guia_bl
            $table->string('puerto_salida', 150)->nullable()->after('guia_bl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operaciones_logisticas', function (Blueprint $table) {
            $table->dropColumn(['tipo_carga', 'tipo_incoterm', 'puerto_salida']);
        });
    }
};
