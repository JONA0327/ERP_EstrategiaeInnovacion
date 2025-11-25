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
        // Arreglar tabla post_operaciones - hacer campo post_operacion opcional
        Schema::table('post_operaciones', function (Blueprint $table) {
            $table->string('post_operacion')->nullable()->change();
        });

        // Primero eliminar foreign keys de operaciones_logisticas si existen
        Schema::table('operaciones_logisticas', function (Blueprint $table) {
            // Eliminar foreign keys que puedan existir
            $foreignKeys = [
                'operaciones_logisticas_transporte_id_foreign',
                'operaciones_logisticas_agente_aduanal_id_foreign', 
                'operaciones_logisticas_cliente_id_foreign',
                'operaciones_logisticas_ejecutivo_empleado_id_foreign'
            ];
            
            foreach ($foreignKeys as $foreignKey) {
                try {
                    $table->dropForeign($foreignKey);
                } catch (\Exception $e) {
                    // Si no existe la foreign key, continuar
                }
            }
        });

        // Después eliminar las columnas
        Schema::table('operaciones_logisticas', function (Blueprint $table) {
            // Eliminar campos que no se usan
            if (Schema::hasColumn('operaciones_logisticas', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('operaciones_logisticas', 'status_enum')) {
                $table->dropColumn('status_enum');
            }
            if (Schema::hasColumn('operaciones_logisticas', 'transporte_id')) {
                $table->dropColumn('transporte_id');
            }
            if (Schema::hasColumn('operaciones_logisticas', 'agente_aduanal_id')) {
                $table->dropColumn('agente_aduanal_id');
            }
            if (Schema::hasColumn('operaciones_logisticas', 'tipo_operacion')) {
                $table->dropColumn('tipo_operacion');
            }
            if (Schema::hasColumn('operaciones_logisticas', 'operacion_tipo')) {
                $table->dropColumn('operacion_tipo');
            }
            if (Schema::hasColumn('operaciones_logisticas', 'cliente_id')) {
                $table->dropColumn('cliente_id');
            }
            if (Schema::hasColumn('operaciones_logisticas', 'ejecutivo_empleado_id')) {
                $table->dropColumn('ejecutivo_empleado_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios en post_operaciones
        Schema::table('post_operaciones', function (Blueprint $table) {
            $table->string('post_operacion')->nullable(false)->change();
        });

        // Restaurar campos eliminados (solo los principales)
        Schema::table('operaciones_logisticas', function (Blueprint $table) {
            $table->string('status')->nullable();
            $table->enum('status_enum', ['In Process', 'Done', 'Out of Metric'])->nullable();
            $table->unsignedBigInteger('transporte_id')->nullable();
            $table->unsignedBigInteger('agente_aduanal_id')->nullable();
            $table->string('tipo_operacion')->nullable();
            $table->string('operacion_tipo')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('ejecutivo_empleado_id')->nullable();
        });
    }
};
