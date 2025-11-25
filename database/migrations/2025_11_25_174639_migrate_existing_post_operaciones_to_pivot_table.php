<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrar datos existentes de post-operaciones específicas a la tabla pivot
        $postOperacionesEspecificas = DB::table('post_operaciones')
            ->where('status', '!=', 'Plantilla')
            ->whereNotNull('operacion_logistica_id')
            ->get();

        foreach ($postOperacionesEspecificas as $postOp) {
            // Buscar si existe una plantilla con el mismo nombre
            $plantilla = DB::table('post_operaciones')
                ->where('nombre', $postOp->nombre)
                ->where('status', 'Plantilla')
                ->whereNull('operacion_logistica_id')
                ->first();

            if ($plantilla) {
                // Crear relación en tabla pivot usando la plantilla existente
                DB::table('post_operacion_operacion')->insert([
                    'post_operacion_id' => $plantilla->id,
                    'operacion_logistica_id' => $postOp->operacion_logistica_id,
                    'status' => $postOp->status,
                    'fecha_asignacion' => $postOp->created_at ?? now(),
                    'fecha_completado' => $postOp->fecha_completado,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Eliminar la post-operación específica duplicada
                DB::table('post_operaciones')->where('id', $postOp->id)->delete();
            } else {
                // Convertir la post-operación específica en plantilla y crear relación
                $estadoOriginal = $postOp->status;
                $operacionId = $postOp->operacion_logistica_id;
                
                DB::table('post_operaciones')
                    ->where('id', $postOp->id)
                    ->update([
                        'status' => 'Plantilla',
                        'operacion_logistica_id' => null,
                        'no_pedimento' => null
                    ]);

                // Crear relación en tabla pivot
                DB::table('post_operacion_operacion')->insert([
                    'post_operacion_id' => $postOp->id,
                    'operacion_logistica_id' => $operacionId,
                    'status' => $estadoOriginal,
                    'fecha_asignacion' => $postOp->created_at ?? now(),
                    'fecha_completado' => $postOp->fecha_completado,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir: mover datos de pivot table de vuelta a post_operaciones
        $asignaciones = DB::table('post_operacion_operacion')
            ->join('post_operaciones', 'post_operacion_operacion.post_operacion_id', '=', 'post_operaciones.id')
            ->select('post_operacion_operacion.*', 'post_operaciones.nombre', 'post_operaciones.descripcion')
            ->get();

        foreach ($asignaciones as $asignacion) {
            // Crear post-operación específica
            DB::table('post_operaciones')->insert([
                'nombre' => $asignacion->nombre,
                'descripcion' => $asignacion->descripcion,
                'status' => $asignacion->status,
                'operacion_logistica_id' => $asignacion->operacion_logistica_id,
                'fecha_creacion' => $asignacion->fecha_asignacion,
                'fecha_completado' => $asignacion->fecha_completado,
                'created_at' => $asignacion->created_at,
                'updated_at' => $asignacion->updated_at
            ]);
        }

        // Limpiar tabla pivot
        DB::table('post_operacion_operacion')->truncate();
    }
};
