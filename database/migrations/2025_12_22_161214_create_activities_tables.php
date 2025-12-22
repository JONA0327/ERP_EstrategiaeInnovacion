<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Tabla Principal
        Schema::create('activities', function (Blueprint $table) {
            $table->id(); // ID
            $table->foreignId('user_id')->constrained(); // Dueño de la actividad
            
            $table->string('nombre_actividad');
            $table->enum('prioridad', ['Baja', 'Media', 'Alta']);
            
            // Fechas
            $table->date('fecha_inicio')->useCurrent(); // Se pone sola
            $table->date('fecha_compromiso');
            $table->date('fecha_final')->nullable(); // Se llena al completar
            
            // Campos Calculados (Se guardan para facilitar reportes)
            $table->integer('metrico')->nullable(); // (fecha_compromiso - fecha_inicio)
            $table->integer('resultado_dias')->nullable(); // (fecha_final - fecha_inicio)
            $table->decimal('porcentaje', 10, 2)->nullable(); // (resultado_dias / metrico)
            
            // Estatus: Agregamos las variantes que pediste
            $table->string('estatus')->default('En proceso'); 
            // Valores esperados: 'En proceso', 'Completado', 'Retardo', 'Completado con retardo', 'En blanco'
            
            $table->text('comentarios')->nullable();
            
            $table->timestamps();
        });

        // 2. Tabla de Historial (Auditoría)
        Schema::create('activity_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');
            $table->foreignId('user_id')->constrained(); // Quién hizo el cambio
            $table->string('campo_modificado')->nullable(); // Qué campo se tocó
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->timestamp('fecha_cambio')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities_tables');
    }
};
