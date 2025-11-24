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
        Schema::create('historico_matriz_sgm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operacion_logistica_id')->constrained('operaciones_logisticas')->onDelete('cascade');
            $table->date('fecha_arribo_aduana')->nullable();
            $table->date('fecha_registro');
            $table->integer('dias_transcurridos');
            $table->integer('target_dias');
            $table->enum('color_status', ['verde', 'amarillo', 'rojo']);
            $table->enum('operacion_status', ['In Process', 'Done', 'Out of Metric']);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->index(['operacion_logistica_id', 'fecha_registro']);
            $table->index('color_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historico_matriz_sgm');
    }
};
