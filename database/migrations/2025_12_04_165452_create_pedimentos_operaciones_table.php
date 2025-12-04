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
        Schema::create('pedimentos_operaciones', function (Blueprint $table) {
            $table->id();
            $table->string('no_pedimento'); // Número de pedimento individual
            $table->string('clave', 10); // Clave de operación (A1, A3, etc.)
            $table->unsignedBigInteger('operacion_logistica_id'); // ID de la operación
            $table->enum('estado_pago', ['pendiente', 'pagado'])->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->decimal('monto', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Índices
            $table->index(['clave', 'estado_pago']);
            $table->index('no_pedimento');
            $table->index('operacion_logistica_id');
            
            // Clave única para evitar duplicados
            $table->unique(['no_pedimento', 'operacion_logistica_id'], 'unique_pedimento_operacion');

            // Relación con operaciones_logisticas
            $table->foreign('operacion_logistica_id')
                  ->references('id')
                  ->on('operaciones_logisticas')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedimentos_operaciones');
    }
};
