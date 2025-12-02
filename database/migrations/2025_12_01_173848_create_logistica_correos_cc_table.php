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
        Schema::create('logistica_correos_cc', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->comment('Nombre del contacto');
            $table->string('email')->unique()->comment('Correo electrónico');
            $table->enum('tipo', ['administrador', 'supervisor', 'notificacion'])->default('notificacion')->comment('Tipo de correo CC');
            $table->text('descripcion')->nullable()->comment('Descripción del propósito del correo');
            $table->boolean('activo')->default(true)->comment('Si el correo está activo para recibir copias');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistica_correos_cc');
    }
};
