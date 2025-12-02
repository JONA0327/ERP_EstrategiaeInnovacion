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
        Schema::create('operacion_comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operacion_logistica_id')->constrained('operaciones_logisticas')->onDelete('cascade');
            $table->text('comentario');
            $table->string('status_en_momento')->nullable()->comment('Status que tenía la operación cuando se hizo este comentario');
            $table->string('tipo_accion')->default('comentario')->comment('creacion, status_change, comentario, edicion');
            $table->string('usuario_nombre')->nullable()->comment('Nombre del usuario que hizo el comentario');
            $table->integer('usuario_id')->nullable()->comment('ID del usuario que hizo el comentario');
            $table->json('contexto_operacion')->nullable()->comment('Snapshot del estado de la operación en ese momento');
            $table->timestamps();
            
            $table->index(['operacion_logistica_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operacion_comentarios');
    }
};