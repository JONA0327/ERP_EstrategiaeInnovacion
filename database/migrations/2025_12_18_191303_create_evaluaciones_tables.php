<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla Principal (Cabecera de la evaluación)
        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');
            $table->foreignId('evaluador_id')->constrained('users'); // Quien realizó la evaluación
            $table->string('periodo'); // Ej: "2025 | Enero - Junio"
            $table->decimal('promedio_final', 5, 2)->nullable();
            $table->text('comentarios_generales')->nullable();
            $table->timestamps();
        });

        // 2. Tabla de Detalles (Respuestas individuales por criterio)
        Schema::create('evaluacion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluacion_id')->constrained('evaluaciones')->onDelete('cascade');
            $table->foreignId('criterio_id')->constrained('criterios_evaluacion');
            $table->decimal('calificacion', 5, 2); // 0 a 100 o 0 a 10
            $table->text('observaciones')->nullable(); // Comentarios específicos por pregunta
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluacion_detalles');
        Schema::dropIfExists('evaluaciones');
    }
};