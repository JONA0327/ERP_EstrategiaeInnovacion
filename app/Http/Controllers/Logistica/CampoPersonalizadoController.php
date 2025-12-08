<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use App\Models\Logistica\CampoPersonalizadoMatriz;
use App\Models\Logistica\ValorCampoPersonalizado;
use App\Models\Empleado;
use Illuminate\Http\Request;

class CampoPersonalizadoController extends Controller
{
    /**
     * Listar todos los campos personalizados
     */
    public function index()
    {
        $campos = CampoPersonalizadoMatriz::with('ejecutivos')
            ->ordenado()
            ->get();
        
        return response()->json($campos);
    }

    /**
     * Obtener ejecutivos de logística disponibles para asignación
     */
    public function ejecutivos()
    {
        $ejecutivos = Empleado::select('id', 'nombre')
            ->where('area', 'LIKE', '%Logist%')
            ->orderBy('nombre')
            ->get();
        
        return response()->json($ejecutivos);
    }

    /**
     * Crear un nuevo campo personalizado
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:texto,fecha',
            'mostrar_despues_de' => 'nullable|string|max:50',
            'ejecutivos' => 'array',
            'ejecutivos.*' => 'exists:empleados,id',
        ]);

        $maxOrden = CampoPersonalizadoMatriz::max('orden') ?? 0;

        $campo = CampoPersonalizadoMatriz::create([
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'mostrar_despues_de' => $request->mostrar_despues_de,
            'activo' => true,
            'orden' => $maxOrden + 1,
        ]);

        if ($request->has('ejecutivos') && is_array($request->ejecutivos)) {
            $campo->ejecutivos()->sync($request->ejecutivos);
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Campo creado exitosamente',
            'campo' => $campo->load('ejecutivos'),
        ]);
    }

    /**
     * Actualizar un campo personalizado
     */
    public function update(Request $request, $id)
    {
        $campo = CampoPersonalizadoMatriz::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:texto,fecha',
            'mostrar_despues_de' => 'nullable|string|max:50',
            'activo' => 'boolean',
            'ejecutivos' => 'array',
            'ejecutivos.*' => 'exists:empleados,id',
        ]);

        $campo->update([
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'mostrar_despues_de' => $request->mostrar_despues_de,
            'activo' => $request->boolean('activo', true),
        ]);

        if ($request->has('ejecutivos')) {
            $campo->ejecutivos()->sync($request->ejecutivos ?? []);
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Campo actualizado exitosamente',
            'campo' => $campo->load('ejecutivos'),
        ]);
    }

    /**
     * Eliminar un campo personalizado
     */
    public function destroy($id)
    {
        $campo = CampoPersonalizadoMatriz::findOrFail($id);
        
        // Eliminar los valores asociados
        ValorCampoPersonalizado::where('campo_personalizado_id', $id)->delete();
        
        // Eliminar las asignaciones de ejecutivos
        $campo->ejecutivos()->detach();
        
        // Eliminar el campo
        $campo->delete();

        return response()->json([
            'success' => true,
            'mensaje' => 'Campo eliminado exitosamente',
        ]);
    }

    /**
     * Obtener campos personalizados para un ejecutivo específico
     */
    public function camposPorEjecutivo($ejecutivoId)
    {
        $campos = CampoPersonalizadoMatriz::activos()
            ->ordenado()
            ->whereHas('ejecutivos', function($q) use ($ejecutivoId) {
                $q->where('empleados.id', $ejecutivoId);
            })
            ->get();

        return response()->json($campos);
    }

    /**
     * Guardar valor de un campo personalizado para una operación
     */
    public function guardarValor(Request $request)
    {
        $request->validate([
            'operacion_id' => 'required|exists:operaciones_logisticas,id',
            'campo_id' => 'required|exists:campos_personalizados_matriz,id',
            'valor' => 'nullable|string',
        ]);

        $valorCampo = ValorCampoPersonalizado::updateOrCreate(
            [
                'operacion_logistica_id' => $request->operacion_id,
                'campo_personalizado_id' => $request->campo_id,
            ],
            [
                'valor' => $request->valor,
            ]
        );

        return response()->json([
            'success' => true,
            'mensaje' => 'Valor guardado exitosamente',
            'valor' => $valorCampo,
        ]);
    }

    /**
     * Obtener valores de campos personalizados para una operación
     */
    public function valoresPorOperacion($operacionId)
    {
        $valores = ValorCampoPersonalizado::where('operacion_logistica_id', $operacionId)
            ->with('campo')
            ->get()
            ->keyBy('campo_personalizado_id');

        return response()->json($valores);
    }
}
