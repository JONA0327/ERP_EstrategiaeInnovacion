<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use App\Models\Logistica\CampoPersonalizadoMatriz;
use App\Models\Logistica\ValorCampoPersonalizado;
use App\Models\Logistica\ColumnaVisibleEjecutivo;
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
     * Obtener tipos de campos disponibles
     */
    public function tipos()
    {
        return response()->json(CampoPersonalizadoMatriz::getTipos());
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
     * Obtener campos adicionales del ejecutivo actual
     * Incluye campos personalizados asignados y columnas opcionales activadas
     */
    public function camposAdicionales()
    {
        $usuario = auth()->user();
        $empleado = null;
        $esAdmin = false;
        
        if ($usuario) {
            $esAdmin = $usuario->hasRole('admin');
            $empleado = Empleado::where('correo', $usuario->email)
                ->orWhere('nombre', 'like', '%' . $usuario->name . '%')
                ->first();
        }
        
        // Si no hay empleado y no es admin, devolver vacío
        if (!$empleado && !$esAdmin) {
            return response()->json([
                'ejecutivo_nombre' => null,
                'campos_personalizados' => [],
                'columnas_opcionales' => [],
                'tiene_campos_adicionales' => false
            ]);
        }
        
        $ejecutivoNombre = $empleado ? $empleado->nombre : 'Administrador';
        
        // Obtener campos personalizados
        $camposPersonalizados = [];
        if ($esAdmin) {
            // Admin ve todos los campos activos
            $camposPersonalizados = CampoPersonalizadoMatriz::where('activo', true)
                ->ordenado()
                ->get()
                ->toArray();
        } elseif ($empleado) {
            // Usuario normal ve solo campos asignados a él
            $camposPersonalizados = CampoPersonalizadoMatriz::where('activo', true)
                ->whereHas('ejecutivos', function($q) use ($empleado) {
                    $q->where('empleados.id', $empleado->id);
                })
                ->ordenado()
                ->get()
                ->toArray();
        }
        
        // Obtener columnas opcionales visibles del ejecutivo
        $columnasOpcionales = [];
        if ($empleado) {
            $columnasVisibles = ColumnaVisibleEjecutivo::getColumnasVisiblesParaEjecutivo($empleado->id);
            $idioma = ColumnaVisibleEjecutivo::getIdiomaEjecutivo($empleado->id);
            
            // Filtrar solo las columnas que son opcionales (no predeterminadas)
            $todasOpcionales = ColumnaVisibleEjecutivo::$columnasOpcionales;
            
            foreach ($columnasVisibles as $columna) {
                if (isset($todasOpcionales[$columna])) {
                    $columnasOpcionales[] = [
                        'clave' => $columna,
                        'nombre' => $todasOpcionales[$columna][$idioma] ?? $todasOpcionales[$columna]['es'],
                        'nombre_es' => $todasOpcionales[$columna]['es'],
                        'nombre_en' => $todasOpcionales[$columna]['en']
                    ];
                }
            }
        }
        
        $tieneCamposAdicionales = !empty($camposPersonalizados) || !empty($columnasOpcionales);
        
        return response()->json([
            'ejecutivo_nombre' => $ejecutivoNombre,
            'campos_personalizados' => $camposPersonalizados,
            'columnas_opcionales' => $columnasOpcionales,
            'tiene_campos_adicionales' => $tieneCamposAdicionales
        ]);
    }

    /**
     * Crear un nuevo campo personalizado
     */
    public function store(Request $request)
    {
        $tiposValidos = array_keys(CampoPersonalizadoMatriz::getTipos());
        
        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:' . implode(',', $tiposValidos),
            'opciones' => 'nullable|array',
            'configuracion' => 'nullable|array',
            'requerido' => 'boolean',
            'mostrar_despues_de' => 'nullable|string|max:50',
            'ejecutivos' => 'array',
            'ejecutivos.*' => 'exists:empleados,id',
        ]);

        // Validar que selector y multiple tengan opciones
        if (in_array($request->tipo, ['selector', 'multiple'])) {
            if (empty($request->opciones) || count($request->opciones) < 1) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Los campos de tipo selector o múltiple requieren al menos una opción.',
                ], 422);
            }
        }

        $maxOrden = CampoPersonalizadoMatriz::max('orden') ?? 0;

        $campo = CampoPersonalizadoMatriz::create([
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'opciones' => $request->opciones,
            'configuracion' => $request->configuracion,
            'requerido' => $request->boolean('requerido', false),
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
        $tiposValidos = array_keys(CampoPersonalizadoMatriz::getTipos());

        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:' . implode(',', $tiposValidos),
            'opciones' => 'nullable|array',
            'configuracion' => 'nullable|array',
            'requerido' => 'boolean',
            'mostrar_despues_de' => 'nullable|string|max:50',
            'activo' => 'boolean',
            'ejecutivos' => 'array',
            'ejecutivos.*' => 'exists:empleados,id',
        ]);

        // Validar que selector y multiple tengan opciones
        if (in_array($request->tipo, ['selector', 'multiple'])) {
            if (empty($request->opciones) || count($request->opciones) < 1) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Los campos de tipo selector o múltiple requieren al menos una opción.',
                ], 422);
            }
        }

        $campo->update([
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'opciones' => $request->opciones,
            'configuracion' => $request->configuracion,
            'requerido' => $request->boolean('requerido', false),
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

    /**
     * Obtener configuración de columnas para todos los ejecutivos
     */
    public function getColumnasConfig()
    {
        $ejecutivos = Empleado::select('id', 'nombre')
            ->where('area', 'LIKE', '%Logist%')
            ->orderBy('nombre')
            ->get();

        $configuracion = [];
        foreach ($ejecutivos as $ejecutivo) {
            $columnasVisibles = ColumnaVisibleEjecutivo::where('empleado_id', $ejecutivo->id)
                ->where('visible', true)
                ->pluck('columna')
                ->toArray();
            
            // Obtener columnas predeterminadas ocultas
            $columnasPredeterminadasOcultas = ColumnaVisibleEjecutivo::getColumnasPredeterminadasOcultas($ejecutivo->id);
            
            $idioma = ColumnaVisibleEjecutivo::getIdiomaEjecutivo($ejecutivo->id);
            
            $configuracion[$ejecutivo->id] = [
                'ejecutivo' => $ejecutivo,
                'columnas_visibles' => $columnasVisibles,
                'columnas_predeterminadas_ocultas' => $columnasPredeterminadasOcultas,
                'idioma' => $idioma
            ];
        }

        return response()->json([
            'ejecutivos' => $ejecutivos,
            'columnas_predeterminadas_es' => ColumnaVisibleEjecutivo::getColumnasPredeterminadasConNombres('es'),
            'columnas_predeterminadas_en' => ColumnaVisibleEjecutivo::getColumnasPredeterminadasConNombres('en'),
            'columnas_opcionales_es' => ColumnaVisibleEjecutivo::getColumnasOpcionalesConNombres('es'),
            'columnas_opcionales_en' => ColumnaVisibleEjecutivo::getColumnasOpcionalesConNombres('en'),
            'configuracion' => $configuracion
        ]);
    }

    /**
     * Guardar configuración de columnas para un ejecutivo
     */
    public function guardarColumnasConfig(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'columnas_opcionales' => 'array',
            'columnas_opcionales.*' => 'string',
            'columnas_predeterminadas' => 'array',
            'columnas_predeterminadas.*' => 'string',
            'idioma' => 'nullable|in:es,en'
        ]);

        ColumnaVisibleEjecutivo::guardarConfiguracion(
            $request->empleado_id,
            $request->columnas_opcionales ?? [],
            $request->idioma,
            $request->columnas_predeterminadas
        );

        return response()->json([
            'success' => true,
            'mensaje' => 'Configuración guardada exitosamente'
        ]);
    }

    /**
     * Obtener columnas visibles para un ejecutivo específico
     */
    public function getColumnasEjecutivo($empleadoId)
    {
        $columnasVisibles = ColumnaVisibleEjecutivo::getColumnasVisiblesParaEjecutivo($empleadoId);
        $columnasPredeterminadasOcultas = ColumnaVisibleEjecutivo::getColumnasPredeterminadasOcultas($empleadoId);
        $idioma = ColumnaVisibleEjecutivo::getIdiomaEjecutivo($empleadoId);
        
        return response()->json([
            'columnas_visibles' => $columnasVisibles,
            'columnas_predeterminadas_ocultas' => $columnasPredeterminadasOcultas,
            'idioma' => $idioma,
            'nombres_columnas' => ColumnaVisibleEjecutivo::getTodasLasColumnasConNombres($idioma)
        ]);
    }

    /**
     * Guardar idioma de nombres de columnas para un ejecutivo
     */
    public function guardarIdiomaEjecutivo(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'idioma' => 'required|in:es,en'
        ]);

        ColumnaVisibleEjecutivo::guardarIdiomaEjecutivo(
            $request->empleado_id,
            $request->idioma
        );

        return response()->json([
            'success' => true,
            'mensaje' => 'Idioma guardado exitosamente',
            'nombres_columnas' => ColumnaVisibleEjecutivo::getTodasLasColumnasConNombres($request->idioma)
        ]);
    }

    /**
     * Guardar orden de columnas para un ejecutivo
     */
    public function guardarOrdenColumnas(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'orden_columnas' => 'required|array',
            'orden_columnas.*.columna' => 'required|string',
            'orden_columnas.*.orden' => 'required|integer',
            'orden_columnas.*.visible' => 'required|boolean'
        ]);

        // Guardar orden de columnas normales
        ColumnaVisibleEjecutivo::guardarConfiguracionCompleta(
            $request->empleado_id,
            $request->orden_columnas,
            $request->idioma ?? null
        );

        // Guardar orden de campos personalizados si se enviaron
        if ($request->has('orden_campos_personalizados') && is_array($request->orden_campos_personalizados)) {
            foreach ($request->orden_campos_personalizados as $campoOrden) {
                CampoPersonalizadoMatriz::where('id', $campoOrden['campo_id'])
                    ->update(['orden' => $campoOrden['orden']]);
            }
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Orden de columnas guardado exitosamente'
        ]);
    }

    /**
     * Obtener columnas ordenadas para un ejecutivo
     */
    public function getColumnasOrdenadas($empleadoId)
    {
        $idioma = ColumnaVisibleEjecutivo::getIdiomaEjecutivo($empleadoId);
        $columnasOrdenadas = ColumnaVisibleEjecutivo::getColumnasOrdenadasParaEjecutivo($empleadoId, $idioma, true);
        
        return response()->json([
            'success' => true,
            'columnas' => $columnasOrdenadas,
            'idioma' => $idioma
        ]);
    }
}
