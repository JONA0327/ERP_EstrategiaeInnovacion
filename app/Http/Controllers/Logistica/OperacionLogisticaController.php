<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\Cliente;
use App\Models\Logistica\AgenteAduanal;
use App\Models\Logistica\Transporte;
use App\Models\Logistica\PostOperacion;
use App\Models\Logistica\PostOperacionOperacion;
use App\Models\Logistica\HistoricoMatrizSgm;
use App\Models\Empleado;

class OperacionLogisticaController extends Controller
{
    public function index()
    {
        // *** VERIFICACIÓN AUTOMÁTICA DE STATUS AL CONSULTAR ***
        $this->verificarYActualizarStatusOperaciones();

        $operaciones = OperacionLogistica::with(['ejecutivo', 'cliente', 'agenteAduanal', 'transporte', 'postOperacion'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Obtener datos para los selects del modal
        // Filtrar clientes por ejecutivo asignado (solo mostrar los del ejecutivo logueado)
        $usuarioActual = auth()->user();
        $empleadoActual = null;

        // Buscar el empleado actual en la tabla empleados
        if ($usuarioActual) {
            $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                ->first();
        }

        // Si es administrador, mostrar todos los clientes, si no, solo los asignados
        if ($usuarioActual && $usuarioActual->hasRole('admin')) {
            $clientes = Cliente::with('ejecutivoAsignado')->orderBy('cliente')->get();
        } elseif ($empleadoActual) {
            $clientes = Cliente::where('ejecutivo_asignado_id', $empleadoActual->id)
                ->orWhereNull('ejecutivo_asignado_id')
                ->orderBy('cliente')->get();
        } else {
            $clientes = Cliente::whereNull('ejecutivo_asignado_id')
                ->orderBy('cliente')->get();
        }

        $agentesAduanales = AgenteAduanal::orderBy('agente_aduanal')->get();
        // Solo empleados del área de logística
        $empleados = Empleado::where(function($query) {
                $query->where('area', 'like', '%Logística%')
                      ->orWhere('area', 'like', '%Logistica%')
                      ->orWhere('area', 'like', '%LOGÍSTICA%')
                      ->orWhere('area', 'like', '%LOGISTICA%');
            })
            ->orderBy('nombre')
            ->get();
        $transportes = Transporte::orderBy('transporte')->get();

        return view('Logistica.matriz-seguimiento', compact('operaciones', 'clientes', 'agentesAduanales', 'empleados', 'transportes'));
    }

    public function catalogos()
    {
        // Obtener todos los datos para las pestañas
        $clientes = Cliente::with('ejecutivoAsignado')->orderBy('cliente')->paginate(15, ['*'], 'clientes_page');
        $agentesAduanales = AgenteAduanal::orderBy('agente_aduanal')->paginate(15, ['*'], 'agentes_page');
        $transportes = Transporte::orderBy('transporte')->paginate(15, ['*'], 'transportes_page');

        // Agregar aduanas
        $aduanas = \App\Models\Logistica\Aduana::orderBy('aduana')->orderBy('seccion')->paginate(15, ['*'], 'aduanas_page');

        // Solo empleados del área de logística
        $ejecutivos = Empleado::where(function($query) {
                $query->where('area', 'like', '%Logística%')
                      ->orWhere('area', 'like', '%Logistica%')
                      ->orWhere('area', 'like', '%LOGÍSTICA%')
                      ->orWhere('area', 'like', '%LOGISTICA%');
            })
            ->orderBy('nombre')
            ->paginate(15, ['*'], 'ejecutivos_page');

        // Obtener todos los ejecutivos para el select de asignación
        $todosEjecutivos = Empleado::where(function($query) {
                $query->where('area', 'like', '%Logística%')
                      ->orWhere('area', 'like', '%Logistica%')
                      ->orWhere('area', 'like', '%LOGÍSTICA%')
                      ->orWhere('area', 'like', '%LOGISTICA%');
            })
            ->orderBy('nombre')
            ->get();

        return view('Logistica.catalogos', compact('clientes', 'agentesAduanales', 'transportes', 'ejecutivos', 'todosEjecutivos', 'aduanas'));
    }

    public function create()
    {
        // Obtener datos para los selects
        $clientes = Cliente::orderBy('cliente')->get();
        $agentesAduanales = AgenteAduanal::orderBy('agente_aduanal')->get();
        $ejecutivos = Empleado::where('area', 'LIKE', '%Logist%')->orderBy('nombre')->get();

        return response()->json([
            'clientes' => $clientes,
            'agentesAduanales' => $agentesAduanales,
            'ejecutivos' => $ejecutivos,
            'tiposOperacion' => ['Aerea', 'Terrestre', 'Maritima', 'Ferrocarril'],
            'operaciones' => ['Exportacion', 'Importacion'],
            'statusOptions' => ['In Process', 'Done', 'Out of Metric']
        ]);
    }

    public function store(Request $request)
    {
        // VALIDACIÓN SEGÚN FLUJO CORPORATIVO - Solo campos obligatorios al crear
        $request->validate([
            // === CAMPOS OBLIGATORIOS AL INICIO (12 máximo) ===

            // A. Información Básica
            'operacion' => 'required|in:EXPORTACION,IMPORTACION',
            'tipo_operacion_enum' => 'required|in:Terrestre,Aerea,Maritima,Ferrocarril',

            // B. Cliente y Ejecutivo
            'cliente' => 'required|string|max:255',
            'ejecutivo' => 'required|string|max:255',

            // C. Fecha Inicial (la única obligatoria)
            'fecha_embarque' => 'required|date',

            // D. Información Inicial Adicional
            'proveedor_o_cliente' => 'required|string|max:255',
            'no_factura' => 'required|string|max:255',
            'clave' => 'required|string|max:100',
            'referencia_interna' => 'required|string|max:255',
            'aduana' => 'required|string|max:255',
            'agente_aduanal' => 'required|string|max:255',

            // === CAMPOS OPCIONALES (se llenan después) ===
            'referencia_aa' => 'nullable|string|max:255',
            'no_pedimento' => 'nullable|string|max:255',
            'transporte' => 'nullable|string|max:255',
            'guia_bl' => 'nullable|string|max:255',
            'fecha_arribo_aduana' => 'nullable|date',
            'fecha_modulacion' => 'nullable|date',
            'fecha_arribo_planta' => 'nullable|date',
            'target' => 'nullable|integer|min:0',
            'resultado' => 'nullable|integer|min:0',
            'dias_transito' => 'nullable|integer|min:0',
        ]);

        // Crear la operación - el status se calcula automáticamente en el modelo
        $operacion = OperacionLogistica::create([
            'ejecutivo' => $request->ejecutivo,
            'cliente' => $request->cliente,
            'agente_aduanal' => $request->agente_aduanal,
            'transporte' => $request->transporte,
            'operacion' => $request->operacion,
            'proveedor_o_cliente' => $request->proveedor_o_cliente,
            'fecha_embarque' => $request->fecha_embarque,
            'no_factura' => $request->no_factura,
            'tipo_operacion_enum' => $request->tipo_operacion_enum,
            'clave' => $request->clave,
            'referencia_interna' => $request->referencia_interna,
            'aduana' => $request->aduana,
            'referencia_aa' => $request->referencia_aa,
            'no_pedimento' => $request->no_pedimento,
            'fecha_arribo_aduana' => $request->fecha_arribo_aduana,
            'guia_bl' => $request->guia_bl,
            'fecha_modulacion' => $request->fecha_modulacion,
            'fecha_arribo_planta' => $request->fecha_arribo_planta,
            'resultado' => $request->resultado,
            'comentarios' => $request->comentarios,
            // Target se calcula automáticamente basado en tipo_operacion_enum
            'target' => null, // Se calculará automáticamente
            // Status manual inicial
            'status_manual' => 'In Process',
            // status_calculado y color_status se calculan automáticamente en el modelo
        ]);

        // Calcular target automáticamente basado en el tipo de operación
        $targetCalculado = $operacion->calcularTargetAutomatico();
        if ($targetCalculado !== null) {
            $operacion->target = $targetCalculado;
        }

        // *** GUARDAR PRIMERO, LUEGO CALCULAR STATUS ***
        $operacion->save();

        // Ahora calcular status (ya que created_at existe) y generar historial inicial
        $resultado = $operacion->calcularStatusPorDias();
        $operacion->generarHistorialCambioStatus(
            $resultado,
            false, // No es acción manual
            null
        );
        $operacion->saveQuietly(); // Guardar sin disparar eventos otra vez

        return response()->json([
            'success' => true,
            'message' => 'Operación creada exitosamente',
            'operacion' => $operacion->load(['ejecutivo', 'cliente', 'agenteAduanal', 'transporte'])
        ]);
    }

    public function getTransportesPorTipo(Request $request)
    {
        $transportes = Transporte::where('tipo_operacion', $request->tipo)
            ->orderBy('transporte')
            ->get();

        return response()->json($transportes);
    }

    public function storeCliente(Request $request)
    {
        try {
            \Log::info('Datos recibidos para cliente:', $request->all());

            $request->validate([
                'cliente' => 'required|string|max:255|unique:clientes,cliente'
            ]);

            $cliente = Cliente::create([
                'cliente' => $request->cliente,
                'ejecutivo_asignado_id' => $request->ejecutivo_asignado_id ?? null
            ]);

            \Log::info('Cliente creado:', $cliente->toArray());

            return response()->json([
                'success' => true,
                'cliente' => $cliente->load('ejecutivoAsignado'),
                'message' => 'Cliente creado exitosamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación en storeCliente:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error en storeCliente:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeAgente(Request $request)
    {
        try {
            \Log::info('Datos recibidos para agente:', $request->all());

            $request->validate([
                'agente_aduanal' => 'required|string|max:255|unique:agentes_aduanales,agente_aduanal'
            ]);

            $agente = AgenteAduanal::create([
                'agente_aduanal' => $request->agente_aduanal
            ]);

            \Log::info('Agente creado:', $agente->toArray());

            return response()->json([
                'success' => true,
                'agente' => $agente,
                'message' => 'Agente aduanal creado exitosamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación en storeAgente:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error en storeAgente:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el agente aduanal: ' . $e->getMessage()
            ], 500);
        }
    }

    // Métodos de actualización
    public function updateCliente(Request $request, $id)
    {
        try {
            $cliente = Cliente::findOrFail($id);

            $request->validate([
                'cliente' => 'required|string|max:255|unique:clientes,cliente,' . $id
            ]);

            $cliente->update([
                'cliente' => $request->cliente,
                'ejecutivo_asignado_id' => $request->ejecutivo_asignado_id
            ]);

            return response()->json([
                'success' => true,
                'cliente' => $cliente->load('ejecutivoAsignado'),
                'message' => 'Cliente actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateAgente(Request $request, $id)
    {
        try {
            $agente = AgenteAduanal::findOrFail($id);

            $request->validate([
                'agente_aduanal' => 'required|string|max:255|unique:agentes_aduanales,agente_aduanal,' . $id
            ]);

            $agente->update([
                'agente_aduanal' => $request->agente_aduanal
            ]);

            return response()->json([
                'success' => true,
                'agente' => $agente,
                'message' => 'Agente aduanal actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el agente aduanal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateTransporte(Request $request, $id)
    {
        try {
            $transporte = Transporte::findOrFail($id);

            $request->validate([
                'transporte' => 'required|string|max:255'
            ]);

            $transporte->update([
                'transporte' => $request->transporte
            ]);

            return response()->json([
                'success' => true,
                'transporte' => $transporte,
                'message' => 'Transporte actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el transporte: ' . $e->getMessage()
            ], 500);
        }
    }

    // Métodos de eliminación
    public function destroyCliente($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);

            // Verificar si tiene operaciones asociadas
            if ($cliente->operaciones()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el cliente porque tiene operaciones asociadas'
                ], 400);
            }

            $cliente->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cliente eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyAgente($id)
    {
        try {
            $agente = AgenteAduanal::findOrFail($id);

            // Verificar si tiene operaciones asociadas
            if ($agente->operaciones()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el agente aduanal porque tiene operaciones asociadas'
                ], 400);
            }

            $agente->delete();

            return response()->json([
                'success' => true,
                'message' => 'Agente aduanal eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el agente aduanal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyTransporte($id)
    {
        try {
            $transporte = Transporte::findOrFail($id);

            // Verificar si tiene operaciones asociadas
            if ($transporte->operaciones()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el transporte porque tiene operaciones asociadas'
                ], 400);
            }

            $transporte->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transporte eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el transporte: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeTransporte(Request $request)
    {
        try {
            \Log::info('Datos recibidos para transporte:', $request->all());

            $request->validate([
                'transporte' => 'required|string|max:255',
                'tipo_operacion' => 'required|in:Aerea,Terrestre,Maritima,Ferrocarril'
            ]);

            $transporte = Transporte::create([
                'transporte' => $request->transporte,
                'tipo_operacion' => $request->tipo_operacion
            ]);

            \Log::info('Transporte creado:', $transporte->toArray());

            return response()->json([
                'success' => true,
                'transporte' => $transporte,
                'message' => 'Transporte creado exitosamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación en storeTransporte:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error en storeTransporte:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el transporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar múltiples clientes a un ejecutivo
     */
    public function asignarClientesEjecutivo(Request $request)
    {
        try {
            // Verificar que el usuario tenga permisos de administrador
            $usuarioActual = auth()->user();
            if (!$usuarioActual || !$usuarioActual->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta acción'
                ], 403);
            }

            $request->validate([
                'cliente_ids' => 'required|array',
                'cliente_ids.*' => 'exists:clientes,id',
                'ejecutivo_id' => 'required|exists:empleados,id'
            ]);

            // Actualizar los clientes seleccionados
            Cliente::whereIn('id', $request->cliente_ids)
                ->update(['ejecutivo_asignado_id' => $request->ejecutivo_id]);

            $ejecutivo = Empleado::find($request->ejecutivo_id);
            $cantidadClientes = count($request->cliente_ids);

            return response()->json([
                'success' => true,
                'message' => "Se asignaron {$cantidadClientes} clientes al ejecutivo {$ejecutivo->nombre}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener clientes filtrados por ejecutivo para los dropdowns
     */
    public function getClientesPorEjecutivo(Request $request)
    {
        try {
            $usuarioActual = auth()->user();
            $empleadoActual = null;

            if ($usuarioActual) {
                $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                    ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                    ->first();
            }

            // Si es administrador, obtener todos los clientes
            if ($usuarioActual && $usuarioActual->hasRole('admin')) {
                $clientes = Cliente::with('ejecutivoAsignado')->orderBy('cliente')->get();
            } elseif ($empleadoActual) {
                // Si no es admin, solo los clientes asignados a él
                $clientes = Cliente::where('ejecutivo_asignado_id', $empleadoActual->id)
                    ->orWhereNull('ejecutivo_asignado_id')
                    ->orderBy('cliente')->get();
            } else {
                $clientes = collect([]);
            }

            return response()->json([
                'success' => true,
                'clientes' => $clientes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar historial inicial cuando se crea una operación
     */
    private function generarHistorialInicial($operacion)
    {
        try {
            // Crear registro inicial del historial
            HistoricoMatrizSgm::create([
                'operacion_logistica_id' => $operacion->id,
                'fecha_arribo_aduana' => $operacion->fecha_arribo_aduana,
                'fecha_registro' => now()->format('Y-m-d'),
                'dias_transcurridos' => $operacion->dias_transcurridos_calculados ?? 0,
                'target_dias' => $operacion->target ?? 0,
                'color_status' => $operacion->color_status ?? 'sin_fecha',
                'operacion_status' => $operacion->status_calculado ?? 'In Process',
                'observaciones' => 'Operación creada - Estado inicial'
            ]);

            \Log::info("Historial inicial generado para operación ID: {$operacion->id}");

        } catch (\Exception $e) {
            \Log::error("Error generando historial inicial: " . $e->getMessage());
        }
    }

    /**
     * Obtener el historial de una operación
     * Ahora incluye todas las operaciones del mismo cliente y No Ped si están disponibles
     */
    public function obtenerHistorial($id)
    {
        try {
            $operacion = OperacionLogistica::with([
                'historicoMatrizSgm'
            ])->findOrFail($id);

            // Obtener historial de la operación específica
            $historialRecords = $operacion->historicoMatrizSgm()
                ->orderBy('created_at', 'desc')
                ->get();

            // Si no hay historial, generar uno inicial
            if ($historialRecords->isEmpty()) {
                $this->generarHistorialInicial($operacion);
                // Volver a cargar el historial
                $historialRecords = $operacion->fresh()->historicoMatrizSgm()
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            $historial = $historialRecords->map(function ($registro) {
                    return [
                        'id' => $registro->id,
                        'fecha_registro' => $registro->fecha_registro ?
                            (is_string($registro->fecha_registro) ? $registro->fecha_registro : $registro->fecha_registro->format('d/m/Y')) : null,
                        'fecha_arribo_aduana' => $registro->fecha_arribo_aduana ?
                            (is_string($registro->fecha_arribo_aduana) ? $registro->fecha_arribo_aduana : $registro->fecha_arribo_aduana->format('d/m/Y')) : null,
                        'dias_transcurridos' => $registro->dias_transcurridos ?? 0,
                        'target_dias' => $registro->target_dias ?? 0,
                        'color_status' => $registro->color_status ?? 'sin_fecha',
                        'operacion_status' => $registro->operacion_status ?? 'In Process',
                        'observaciones' => $registro->observaciones ?? '',
                        'created_at' => $registro->created_at ? $registro->created_at->format('d/m/Y H:i:s') : ''
                    ];
                });

            // Buscar otras operaciones del mismo cliente y No Ped para historial completo
            $operacionesRelacionadas = [];
            if ($operacion->cliente && $operacion->no_pedimento) {
                $operacionesRelacionadas = OperacionLogistica::where('cliente', $operacion->cliente)
                    ->where('no_pedimento', $operacion->no_pedimento)
                    ->where('id', '!=', $operacion->id)
                    ->with('historicoMatrizSgm')
                    ->get();
            } else if ($operacion->cliente) {
                // Si no hay No Ped, buscar por cliente
                $operacionesRelacionadas = OperacionLogistica::where('cliente', $operacion->cliente)
                    ->where('id', '!=', $operacion->id)
                    ->with('historicoMatrizSgm')
                    ->orderBy('created_at', 'desc')
                    ->limit(5) // Limitar a las 5 más recientes
                    ->get();
            }

            $response = [
                'success' => true,
                'historial' => $historial->toArray(),
                'operacion' => [
                    'id' => $operacion->id,
                    'operacion' => $operacion->operacion ?? 'Sin nombre',
                    'status' => $operacion->status_calculado ?? $operacion->status ?? 'In Process',
                    'cliente' => $operacion->cliente ?? 'Sin cliente',
                    'no_pedimento' => $operacion->no_pedimento ?? 'Sin No Ped',
                ],
                'operaciones_relacionadas' => $operacionesRelacionadas->map(function($op) {
                    return [
                        'id' => $op->id,
                        'operacion' => $op->operacion ?? 'Sin nombre',
                        'no_pedimento' => $op->no_pedimento ?? 'Sin No Ped',
                        'status' => $op->status_calculado ?? 'In Process',
                        'fecha_creacion' => $op->created_at ? $op->created_at->format('d/m/Y H:i') : '',
                        'historial_count' => $op->historicoMatrizSgm->count()
                    ];
                }),
                'message' => $historial->count() > 0 ? 'Historial cargado correctamente' : 'Historial generado automáticamente'
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error("Error en obtenerHistorial: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar solo el status manual de una operación (solo se puede cambiar a 'Done')
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $operacion = OperacionLogistica::findOrFail($id);

            $request->validate([
                'status' => 'required|in:Done'
            ]);

            // NUEVA LÓGICA: Solo actualizar el status MANUAL, no el automático
            $operacion->status_manual = 'Done';
            $operacion->fecha_status_manual = now();

            // Recalcular el status automático (que tomará en cuenta el status manual)
            $resultado = $operacion->calcularStatusPorDias();

            // Generar historial específico para acción manual
            $operacion->generarHistorialCambioStatus(
                $resultado,
                true, // Es acción manual
                'Operación marcada como completada manualmente por el usuario'
            );

            // Guardar cambios
            $operacion->save();

            return response()->json([
                'success' => true,
                'message' => 'Operación marcada como completada exitosamente',
                'operacion' => $operacion->load(['ejecutivo', 'cliente', 'agenteAduanal', 'transporte', 'postOperacion'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una operación
     */
    public function destroy($id)
    {
        try {
            $operacion = OperacionLogistica::findOrFail($id);

            // Eliminar registros del historial primero (por integridad referencial)
            $operacion->historicoMatrizSgm()->delete();

            // Eliminar la operación
            $operacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Operación eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la operación: ' . $e->getMessage()
            ], 500);
        }
    }

    // =================================
    // MÉTODOS PARA POST-OPERACIONES
    // =================================

    /**
     * Listar todas las post-operaciones
     */
    public function indexPostOperaciones()
    {
        try {
            $postOperaciones = PostOperacion::with('operacionLogistica')
                ->orderBy('created_at', 'desc')
                ->get();

            $postOperacionesData = $postOperaciones->map(function($postOp) {
                return [
                    'id' => $postOp->id,
                    'nombre' => $postOp->nombre,
                    'descripcion' => $postOp->descripcion,
                    'status' => $postOp->status ?? 'Pendiente',
                    'operacion_relacionada' => $postOp->operacionLogistica
                        ? ($postOp->operacionLogistica->operacion ?? 'Operación #' . $postOp->operacionLogistica->id)
                        : 'Sin operación específica',
                    'fecha_creacion' => $postOp->created_at ? $postOp->created_at->format('d/m/Y') : '-',
                    'fecha_completado' => $postOp->fecha_completado ? $postOp->fecha_completado->format('d/m/Y H:i') : null
                ];
            });

            return response()->json([
                'success' => true,
                'postOperaciones' => $postOperacionesData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar post-operaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva post-operación
     */
    public function storePostOperacion(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'operacion_logistica_id' => 'nullable|exists:operaciones_logisticas,id'
            ]);

            $postOperacion = PostOperacion::create([
                'nombre' => $validatedData['nombre'],
                'descripcion' => $validatedData['descripcion'] ?? null,
                'operacion_logistica_id' => $validatedData['operacion_logistica_id'] ?? null,
                'status' => 'Pendiente',
                'fecha_creacion' => now()
            ]);

            return response()->json([
                'success' => true,
                'postOperacion' => $postOperacion,
                'message' => 'Post-operación creada exitosamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos: ' . implode(', ', $e->validator->errors()->all())
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear post-operación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar post-operación como completada
     */
    public function markPostOperacionDone($id)
    {
        try {
            $postOperacion = PostOperacion::findOrFail($id);

            $postOperacion->update([
                'status' => 'Completado',
                'fecha_completado' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post-operación marcada como completada'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar post-operación como completada: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar post-operación
     */
    public function destroyPostOperacion($id)
    {
        try {
            $postOperacion = PostOperacion::findOrFail($id);
            $postOperacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post-operación eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar post-operación: ' . $e->getMessage()
            ], 500);
        }
    }

    // =================================
    // MÉTODOS PARA POST-OPERACIONES POR OPERACIÓN
    // =================================

    /**
     * Obtener post-operaciones de una operación específica
     */
    public function getPostOperacionesByOperacion($operacionId)
    {
        try {
            // Obtener información de la operación
            $operacion = OperacionLogistica::find($operacionId);

            if (!$operacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Operación no encontrada'
                ], 404);
            }

            // Obtener TODAS las post-operaciones globales (plantillas)
            $postOperacionesGlobales = PostOperacion::where('status', 'Plantilla')
                ->orderBy('created_at', 'desc')
                ->get();

            // Obtener las asignaciones específicas de esta operación
            $asignacionesEspecificas = PostOperacionOperacion::where('operacion_logistica_id', $operacionId)
                ->with('postOperacion')
                ->get()
                ->keyBy('post_operacion_id'); // Indexar por ID de post-operación para búsqueda rápida

            // Combinar datos: todas las plantillas + estados específicos si existen
            $postOperacionesData = $postOperacionesGlobales->map(function($postOpGlobal) use ($asignacionesEspecificas, $operacion) {
                $asignacion = $asignacionesEspecificas->get($postOpGlobal->id);

                return [
                    'id_global' => $postOpGlobal->id,
                    'id_asignacion' => $asignacion ? $asignacion->id : null,
                    'nombre' => $postOpGlobal->nombre,
                    'descripcion' => $postOpGlobal->descripcion,
                    'status' => $asignacion ? $asignacion->status : 'Pendiente',
                    'fecha_creacion' => $postOpGlobal->created_at ? $postOpGlobal->created_at->format('d/m/Y H:i') : '-',
                    'fecha_asignacion' => $asignacion && $asignacion->fecha_asignacion ? $asignacion->fecha_asignacion->format('d/m/Y H:i') : null,
                    'fecha_completado' => $asignacion && $asignacion->fecha_completado ? $asignacion->fecha_completado->format('d/m/Y H:i') : null,
                    'notas_especificas' => $asignacion ? $asignacion->notas_especificas : null,
                    'es_plantilla' => !$asignacion, // true si no está asignada específicamente
                ];
            });

            return response()->json([
                'success' => true,
                'postOperaciones' => $postOperacionesData,
                'operacion_info' => [
                    'id' => $operacion->id,
                    'no_pedimento' => $operacion->no_pedimento
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar post-operaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar estado de post-operación (Completado/No Aplica)
     */
    public function updatePostOperacionEstado(Request $request, $id)
    {
        try {
            $postOperacion = PostOperacion::findOrFail($id);

            $validatedData = $request->validate([
                'estado' => 'required|in:Completado,No Aplica,Pendiente'
            ]);

            $postOperacion->update([
                'status' => $validatedData['estado'],
                'fecha_completado' => $validatedData['estado'] !== 'Pendiente' ? now() : null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    // =================================
    // MÉTODOS PARA POST-OPERACIONES GLOBALES
    // =================================

    /**
     * Listar post-operaciones globales (plantillas)
     */
    public function indexPostOperacionesGlobales()
    {
        try {
            $postOperaciones = PostOperacion::whereNull('operacion_logistica_id')
                ->orderBy('nombre')
                ->get();

            $postOperacionesData = $postOperaciones->map(function($postOp) {
                return [
                    'id' => $postOp->id,
                    'nombre' => $postOp->nombre,
                    'descripcion' => $postOp->descripcion,
                    'fecha_creacion' => $postOp->created_at ? $postOp->created_at->format('d/m/Y') : '-'
                ];
            });

            return response()->json([
                'success' => true,
                'postOperaciones' => $postOperacionesData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar post-operaciones globales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear post-operación global (plantilla)
     */
    public function storePostOperacionGlobal(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string'
            ]);

            $postOperacion = PostOperacion::create([
                'nombre' => $validatedData['nombre'],
                'descripcion' => $validatedData['descripcion'] ?? null,
                'operacion_logistica_id' => null, // Global
                'status' => 'Plantilla',
                'fecha_creacion' => now()
            ]);

            return response()->json([
                'success' => true,
                'postOperacion' => $postOperacion,
                'message' => 'Post-operación global creada exitosamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos: ' . implode(', ', $e->validator->errors()->all())
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear post-operación global: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar post-operación global
     */
    public function destroyPostOperacionGlobal($id)
    {
        try {
            $postOperacion = PostOperacion::whereNull('operacion_logistica_id')->findOrFail($id);
            $postOperacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post-operación global eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar post-operación global: ' . $e->getMessage()
            ], 500);
        }
    }

    // =================================
    // MÉTODOS PARA COMENTARIOS
    // =================================

    /**
     * Obtener comentarios de una operación
     */
    public function getComentariosByOperacion($operacionId)
    {
        try {
            // Primero obtenemos la operación
            $operacion = OperacionLogistica::findOrFail($operacionId);

            // Por ahora usamos el campo comentarios de la operación
            // En el futuro se puede crear una tabla separada de comentarios
            $comentarios = [];
            if ($operacion->comentarios) {
                $comentarios[] = [
                    'id' => 1,
                    'texto' => $operacion->comentarios,
                    'autor' => 'Usuario',
                    'fecha' => $operacion->updated_at ? $operacion->updated_at->format('d/m/Y H:i') : 'Sin fecha'
                ];
            }

            return response()->json([
                'success' => true,
                'comentarios' => $comentarios
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar comentarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear comentario
     */
    public function storeComentario(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'comentario' => 'required|string',
                'operacion_logistica_id' => 'required|exists:operaciones_logisticas,id'
            ]);

            $operacion = OperacionLogistica::findOrFail($validatedData['operacion_logistica_id']);

            // Por ahora guardamos en el campo comentarios de la operación
            // Concatenamos si ya hay comentarios previos
            $comentarioExistente = $operacion->comentarios;
            $nuevoComentario = $validatedData['comentario'];

            if ($comentarioExistente) {
                $nuevoComentario = $comentarioExistente . "\n---\n" . $nuevoComentario . " (" . now()->format('d/m/Y H:i') . ")";
            } else {
                $nuevoComentario = $nuevoComentario . " (" . now()->format('d/m/Y H:i') . ")";
            }

            $operacion->update([
                'comentarios' => $nuevoComentario
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comentario guardado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar comentario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar comentario
     */
    public function updateComentario(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'comentario' => 'required|string'
            ]);

            // Para simplicidad, actualizamos directamente el campo comentarios
            // En una implementación más compleja se usaría una tabla separada
            $operacion = OperacionLogistica::where('id', $request->operacion_logistica_id)->first();

            if ($operacion) {
                $operacion->update([
                    'comentarios' => $validatedData['comentario'] . " (Editado: " . now()->format('d/m/Y H:i') . ")"
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Comentario actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar comentario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar estados de múltiples post-operaciones asociadas a una operación
     * Usa tabla pivot para mantener limpia la separación entre plantillas y asignaciones
     */
    public function actualizarEstadosPostOperaciones(Request $request, $operacionId)
    {
        try {
            $validatedData = $request->validate([
                'cambios' => 'required|array',
                'no_pedimento' => 'nullable|string'
            ]);

            $cambios = $validatedData['cambios'];

            $actualizados = 0;
            $creados = 0;

            foreach ($cambios as $postOpId => $cambioData) {
                $estado = $cambioData['estado'];
                $esPlantilla = $cambioData['es_plantilla'] ?? false;
                $idGlobal = $cambioData['id_global'] ?? null;

                // Usar ID global si es plantilla, sino usar el ID directo
                $postOperacionId = $esPlantilla ? $idGlobal : $postOpId;

                if (!$postOperacionId) {
                    continue;
                }

                // Verificar que la post-operación global existe
                $postOperacionGlobal = PostOperacion::find($postOperacionId);
                if (!$postOperacionGlobal) {
                    continue;
                }

                // Buscar si ya existe una asignación para esta operación
                $asignacionExistente = PostOperacionOperacion::where('post_operacion_id', $postOperacionId)
                    ->where('operacion_logistica_id', $operacionId)
                    ->first();

                if ($estado === 'Pendiente') {
                    // Si se marca como pendiente y existe asignación, eliminarla
                    if ($asignacionExistente) {
                        $asignacionExistente->delete();
                        $actualizados++;
                    }
                } else {
                    // Crear o actualizar asignación para estados Completado/No Aplica
                    if ($asignacionExistente) {
                        // Actualizar asignación existente
                        $asignacionExistente->status = $estado;
                        $asignacionExistente->fecha_completado = $estado === 'Completado' ? now() : null;
                        $asignacionExistente->save();
                        $actualizados++;
                    } else {
                        // Crear nueva asignación
                        PostOperacionOperacion::create([
                            'post_operacion_id' => $postOperacionId,
                            'operacion_logistica_id' => $operacionId,
                            'status' => $estado,
                            'fecha_asignacion' => now(),
                            'fecha_completado' => $estado === 'Completado' ? now() : null
                        ]);
                        $creados++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Operación completada: {$creados} asignaciones creadas, {$actualizados} actualizadas",
                'creados' => $creados,
                'actualizados' => $actualizados
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar post-operaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalcular automáticamente todos los status de las operaciones usando nueva lógica
     */
    public function recalcularStatus()
    {
        try {
            $operaciones = OperacionLogistica::all();
            $actualizadas = 0;
            $historialesGenerados = 0;

            foreach ($operaciones as $operacion) {
                // Usar la nueva lógica de cálculo por días
                $resultado = $operacion->actualizarStatusAutomaticamente(false); // No guardar aún

                if ($resultado['cambio']) {
                    $operacion->save(); // Guardar cambios
                    $actualizadas++;
                    $historialesGenerados++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Se recalcularon {$actualizadas} operaciones exitosamente. Se generaron {$historialesGenerados} registros de historial.",
                'actualizadas' => $actualizadas,
                'historiales_generados' => $historialesGenerados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recalcular status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular el status de una operación basado en sus fechas
     */
    private function calcularStatusOperacion($operacion)
    {
        if ($operacion->fecha_arribo_planta) {
            return 'Entregado';
        } elseif ($operacion->fecha_modulacion) {
            return 'Modulado';
        } elseif ($operacion->fecha_arribo_aduana) {
            return 'En Aduana';
        } elseif ($operacion->fecha_embarque) {
            return 'En Tránsito';
        } else {
            return 'Pendiente';
        }
    }

    /**
     * Calcular días transcurridos: fecha embarque vs fecha arribo a planta (o fecha actual si no hay arribo)
     */
    private function calcularDiasTranscurridos($operacion)
    {
        if (!$operacion->fecha_embarque) {
            return 0;
        }

        $fechaInicio = \Carbon\Carbon::parse($operacion->fecha_embarque);
        $fechaFin = $operacion->fecha_arribo_planta
            ? \Carbon\Carbon::parse($operacion->fecha_arribo_planta)
            : \Carbon\Carbon::now();

        return $fechaInicio->diffInDays($fechaFin);
    }

    /**
     * Determinar color del status basado en target y días transcurridos
     */
    private function determinarColorStatus($operacion, $status, $diasTranscurridos)
    {
        if ($status === 'Entregado') {
            // Si ya se entregó, verificar si fue dentro del target
            $target = $operacion->target ?? $operacion->dias_transito ?? 30;
            return $diasTranscurridos <= $target ? 'green' : 'red';
        }

        // Para operaciones en curso
        $target = $operacion->target ?? $operacion->dias_transito ?? 30;

        if ($diasTranscurridos > $target) {
            return 'red'; // Fuera de métrica
        } elseif ($diasTranscurridos >= ($target * 0.8)) {
            return 'yellow'; // Cerca del límite
        } else {
            return 'green'; // Dentro de métrica
        }
    }

    /**
     * Verificar y actualizar automáticamente el status de operaciones al consultar
     * Solo actualiza operaciones que han cambiado desde la última verificación
     */
    private function verificarYActualizarStatusOperaciones()
    {
        try {
            // Verificar todas las operaciones activas (no Done)
            $operacionesActivas = OperacionLogistica::where('status_calculado', '!=', 'Done')
                ->where(function($query) {
                    // Verificar operaciones que no se han calculado hoy o que nunca se han calculado
                    $query->whereNull('fecha_ultimo_calculo')
                          ->orWhere('fecha_ultimo_calculo', '<', now()->startOfDay());
                })
                ->get();

            $actualizadas = 0;

            foreach ($operacionesActivas as $operacion) {
                $resultado = $operacion->actualizarStatusAutomaticamente(true);
                if ($resultado['cambio']) {
                    $actualizadas++;
                }
            }

            // Log para monitoreo (opcional)
            if ($actualizadas > 0) {
                \Log::info("Verificación automática: {$actualizadas} operaciones actualizadas");
            }

        } catch (\Exception $e) {
            \Log::error("Error en verificación automática de status: " . $e->getMessage());
        }
    }
}
