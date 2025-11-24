<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Logistica\Cliente;
use App\Models\Empleado;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener el primer ejecutivo de logística disponible
        $ejecutivoLogistica = Empleado::where(function($query) {
                $query->where('area', 'like', '%Logística%')
                      ->orWhere('area', 'like', '%Logistica%')
                      ->orWhere('area', 'like', '%LOGÍSTICA%')
                      ->orWhere('area', 'like', '%LOGISTICA%');
            })->first();

        // Solo asignar si existe un ejecutivo de logística
        if ($ejecutivoLogistica) {
            // Asignar ejecutivo a clientes existentes que no tienen ejecutivo asignado
            Cliente::whereNull('ejecutivo_asignado_id')
                ->update(['ejecutivo_asignado_id' => $ejecutivoLogistica->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover todas las asignaciones de ejecutivos
        Cliente::whereNotNull('ejecutivo_asignado_id')
            ->update(['ejecutivo_asignado_id' => null]);
    }
};
