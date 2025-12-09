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
        Schema::create('columnas_visibles_ejecutivo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empleado_id');
            $table->string('columna', 50); // nombre de la columna: tipo_carga, tipo_incoterm, puerto_salida
            $table->boolean('visible')->default(false);
            $table->timestamps();
            
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('cascade');
            $table->unique(['empleado_id', 'columna']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('columnas_visibles_ejecutivo');
    }
};
