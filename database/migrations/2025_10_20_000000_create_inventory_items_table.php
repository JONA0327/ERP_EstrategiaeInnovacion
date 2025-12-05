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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_producto');
            $table->string('identificador')->nullable();
            $table->string('nombre');
            $table->string('categoria');
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('numero_serie')->nullable();
            $table->enum('estado', ['disponible', 'en_uso', 'mantenimiento', 'dañado', 'baja'])->default('disponible');
            $table->boolean('es_funcional')->default(true);
            $table->string('ubicacion')->nullable();
            $table->text('descripcion_general')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('codigo_producto');
            $table->index('estado');
            $table->index('es_funcional');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
