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
        Schema::create('post_operacion_operacion', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->unsignedBigInteger('post_operacion_id')->comment('ID de la post-operación global');
            $table->unsignedBigInteger('operacion_logistica_id')->comment('ID de la operación logística');
            
            // Estado específico para esta relación
            $table->enum('status', ['Pendiente', 'Completado', 'No Aplica'])->default('Pendiente');
            
            // Fechas de control
            $table->timestamp('fecha_asignacion')->default(now());
            $table->timestamp('fecha_completado')->nullable();
            
            // Campos adicionales
            $table->text('notas_especificas')->nullable()->comment('Notas específicas para esta asignación');
            
            $table->timestamps();
            
            // Foreign keys con eliminación en cascada
            $table->foreign('post_operacion_id')
                  ->references('id')
                  ->on('post_operaciones')
                  ->onDelete('cascade');
                  
            $table->foreign('operacion_logistica_id')
                  ->references('id') 
                  ->on('operaciones_logisticas')
                  ->onDelete('cascade');
            
            // Índice único para evitar duplicados
            $table->unique(['post_operacion_id', 'operacion_logistica_id'], 'unique_post_op_operacion');
            
            // Índices para optimizar consultas
            $table->index(['operacion_logistica_id', 'status']);
            $table->index(['post_operacion_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_operacion_operacion');
    }
};
