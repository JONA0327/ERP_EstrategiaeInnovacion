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
        Schema::table('operaciones_logisticas', function (Blueprint $table) {
            // Agregar relaciones con las nuevas tablas
            $table->foreignId('cliente_id')->nullable()->after('ejecutivo_empleado_id')->constrained('clientes')->onDelete('set null');
            $table->foreignId('agente_aduanal_id')->nullable()->after('agente_aduanal')->constrained('agentes_aduanales')->onDelete('set null');
            $table->foreignId('transporte_id')->nullable()->after('transporte')->constrained('transportes')->onDelete('set null');
            
            // Cambiar campos existentes por enums más específicos
            $table->enum('operacion_tipo', ['Exportacion', 'Importacion'])->nullable()->after('operacion');
            $table->enum('tipo_operacion_enum', ['Aerea', 'Terrestre', 'Maritima', 'Ferrocarril'])->nullable()->after('tipo_operacion');
            $table->enum('status_enum', ['In Process', 'Done', 'Out of Metric'])->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operaciones_logisticas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cliente_id');
            $table->dropConstrainedForeignId('agente_aduanal_id');
            $table->dropConstrainedForeignId('transporte_id');
            $table->dropColumn(['operacion_tipo', 'tipo_operacion_enum', 'status_enum']);
        });
    }
};
