<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('operaciones_logisticas', function (Blueprint $table) {
            // Agregar campos de status automático
            $table->enum('status_calculado', ['In Process', 'Done', 'Out of Metric'])->default('In Process')->after('status_enum');
            $table->enum('color_status', ['verde', 'amarillo', 'rojo', 'sin_fecha'])->default('sin_fecha')->after('status_calculado');
            $table->integer('dias_transcurridos_calculados')->nullable()->after('color_status');
            $table->timestamp('fecha_ultimo_calculo')->nullable()->after('dias_transcurridos_calculados');
        });

        // Limpiar datos NULL no necesarios y actualizar con valores por defecto
        DB::statement("UPDATE operaciones_logisticas SET status_calculado = 'In Process' WHERE status_calculado IS NULL");
        DB::statement("UPDATE operaciones_logisticas SET color_status = 'sin_fecha' WHERE color_status IS NULL");
        
        // Actualizar registros existentes que tengan fecha_arribo_aduana
        DB::statement("
            UPDATE operaciones_logisticas 
            SET dias_transcurridos_calculados = DATEDIFF(NOW(), fecha_arribo_aduana),
                fecha_ultimo_calculo = NOW()
            WHERE fecha_arribo_aduana IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operaciones_logisticas', function (Blueprint $table) {
            $table->dropColumn([
                'status_calculado',
                'color_status', 
                'dias_transcurridos_calculados',
                'fecha_ultimo_calculo'
            ]);
        });
    }
};
