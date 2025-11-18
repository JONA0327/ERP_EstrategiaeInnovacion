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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nombre'); // Nombre corporativo (copiado de users.name)
            $table->string('correo')->index(); // Correo corporativo (copiado de users.email)
            $table->string('area')->nullable();
            $table->string('posicion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo_personal')->nullable();
            $table->string('foto_path')->nullable(); // ruta de almacenamiento de foto opcional
            $table->text('direccion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
