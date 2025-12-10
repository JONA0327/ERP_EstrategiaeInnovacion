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
        Schema::create('incoterms', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique(); // EXW, FOB, CIF, etc.
            $table->string('nombre', 100); // Nombre completo
            $table->text('descripcion')->nullable(); // Descripción detallada
            $table->enum('grupo', ['E', 'F', 'C', 'D']); // Grupo de incoterm
            $table->boolean('aplicable_importacion')->default(true);
            $table->boolean('aplicable_exportacion')->default(true);
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoterms');
    }
};
