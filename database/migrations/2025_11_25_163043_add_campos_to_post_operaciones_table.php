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
        Schema::table('post_operaciones', function (Blueprint $table) {
            // Agregar campos nuevos
            $table->string('nombre')->nullable()->after('id');
            $table->text('descripcion')->nullable()->after('nombre');
            $table->unsignedBigInteger('operacion_logistica_id')->nullable()->after('descripcion');
            $table->timestamp('fecha_creacion')->nullable()->after('status');
            $table->timestamp('fecha_completado')->nullable()->after('fecha_creacion');
            
            // Actualizar enum de status para incluir valores más descriptivos
            $table->string('status')->default('Pendiente')->change();
            
            // Índices
            $table->foreign('operacion_logistica_id')->references('id')->on('operaciones_logisticas')->onDelete('set null');
            $table->index('status');
            $table->index('operacion_logistica_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_operaciones', function (Blueprint $table) {
            // Eliminar foreign key primero
            $table->dropForeign(['operacion_logistica_id']);
            
            // Eliminar índices
            $table->dropIndex(['status']);
            $table->dropIndex(['operacion_logistica_id']);
            
            // Eliminar campos
            $table->dropColumn([
                'nombre',
                'descripcion',
                'operacion_logistica_id',
                'fecha_creacion',
                'fecha_completado'
            ]);
        });
    }
};
