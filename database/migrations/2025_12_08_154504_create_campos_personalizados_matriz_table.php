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
        // Tabla para definir los campos personalizados
        Schema::create('campos_personalizados_matriz', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100); // Nombre del campo que se mostrará
            $table->enum('tipo', ['texto', 'fecha']); // Tipo de campo
            $table->boolean('activo')->default(true); // Si el campo está activo
            $table->integer('orden')->default(0); // Orden de visualización
            $table->timestamps();
        });

        // Tabla pivote para asignar campos a ejecutivos específicos
        Schema::create('campo_personalizado_ejecutivo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campo_personalizado_id');
            $table->unsignedBigInteger('empleado_id');
            $table->timestamps();

            $table->foreign('campo_personalizado_id')
                  ->references('id')
                  ->on('campos_personalizados_matriz')
                  ->onDelete('cascade');

            $table->foreign('empleado_id')
                  ->references('id')
                  ->on('empleados')
                  ->onDelete('cascade');

            $table->unique(['campo_personalizado_id', 'empleado_id'], 'campo_ejecutivo_unique');
        });

        // Tabla para guardar los valores de los campos personalizados por operación
        Schema::create('valores_campos_personalizados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operacion_logistica_id');
            $table->unsignedBigInteger('campo_personalizado_id');
            $table->text('valor')->nullable(); // Valor del campo (texto o fecha en formato string)
            $table->timestamps();

            $table->foreign('operacion_logistica_id')
                  ->references('id')
                  ->on('operaciones_logisticas')
                  ->onDelete('cascade');

            $table->foreign('campo_personalizado_id')
                  ->references('id')
                  ->on('campos_personalizados_matriz')
                  ->onDelete('cascade');

            $table->unique(['operacion_logistica_id', 'campo_personalizado_id'], 'valor_campo_operacion_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valores_campos_personalizados');
        Schema::dropIfExists('campo_personalizado_ejecutivo');
        Schema::dropIfExists('campos_personalizados_matriz');
    }
};
