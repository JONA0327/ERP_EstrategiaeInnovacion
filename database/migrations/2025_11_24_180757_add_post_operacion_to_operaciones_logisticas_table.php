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
            // Agregar relación con post operaciones
            $table->foreignId('post_operacion_id')->nullable()->constrained('post_operaciones')->onDelete('set null');
            $table->enum('post_operacion_status', ['In Process', 'Done', 'Out of Metric'])->default('In Process');
            
            // Eliminar campos que no se usan (según análisis del modal)
            $table->dropColumn([
                'pendientes_pos_operaciones', // Ya no se usa, se reemplaza por post_operacion_id
                'comentarios' // Se puede mover a historico si es necesario
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operaciones_logisticas', function (Blueprint $table) {
            // Revertir cambios
            $table->dropForeign(['post_operacion_id']);
            $table->dropColumn(['post_operacion_id', 'post_operacion_status']);
            
            // Restaurar campos eliminados
            $table->boolean('pendientes_pos_operaciones')->default(false);
            $table->text('comentarios')->nullable();
        });
    }
};
