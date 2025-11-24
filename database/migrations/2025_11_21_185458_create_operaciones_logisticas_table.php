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
        Schema::create('operaciones_logisticas', function (Blueprint $table) {
            $table->id(); // No. - (id, automatico)
            
            // Ejecutivo - relacionado a la tabla empleados con área de logística
            $table->foreignId('ejecutivo_empleado_id')->nullable()->constrained('empleados')->onDelete('set null');
            
            // Campos de texto
            $table->string('operacion')->nullable(); // Operación
            $table->string('cliente')->nullable(); // Cliente
            $table->string('proveedor_o_cliente')->nullable(); // Proveedor o Cliente
            $table->string('no_factura')->nullable(); // No. De Factura
            $table->string('tipo_operacion')->nullable(); // T. Operación
            $table->string('clave')->nullable(); // Clave
            $table->string('referencia_interna')->nullable(); // Referencia Interna
            $table->string('aduana')->nullable(); // Aduana
            $table->string('agente_aduanal')->nullable(); // A.A
            $table->string('referencia_aa')->nullable(); // Referencia A.A
            $table->string('no_pedimento')->nullable(); // No Ped
            $table->string('transporte')->nullable(); // Transporte
            $table->string('guia_bl')->nullable(); // Guía //BL
            $table->string('status')->nullable(); // Status
            
            // Campos de fecha
            $table->date('fecha_embarque')->nullable(); // Fecha de Embarque
            $table->date('fecha_arribo_aduana')->nullable(); // Fecha de Arribo a Aduana
            $table->date('fecha_modulacion')->nullable(); // Fecha de Modulación
            $table->date('fecha_arribo_planta')->nullable(); // Fecha de Arribo a Planta
            
            // Campos enteros
            $table->integer('resultado')->nullable(); // Resultado
            $table->integer('target')->nullable(); // Target
            $table->integer('dias_transito')->nullable(); // Días en Tránsito
            
            // Campo boolean
            $table->boolean('pendientes_pos_operaciones')->default(false); // Pendientes Pos-Operaciones
            
            // Comentarios
            $table->text('comentarios')->nullable(); // Comentarios
            
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index('ejecutivo_empleado_id');
            $table->index('status');
            $table->index('fecha_embarque');
            $table->index('tipo_operacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operaciones_logisticas');
    }
};
