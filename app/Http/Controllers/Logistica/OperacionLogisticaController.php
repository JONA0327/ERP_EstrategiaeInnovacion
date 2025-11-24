<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\Cliente;
use App\Models\Logistica\AgenteAduanal;
use App\Models\Logistica\Transporte;
use App\Models\Logistica\PostOperacion;
use App\Models\Logistica\HistoricoMatrizSgm;
use App\Models\Empleado;

class OperacionLogisticaController extends Controller
{
    public function index()
    {
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

        return view('Logistica.catalogos', compact('clientes', 'agentesAduanales', 'transportes', 'ejecutivos', 'todosEjecutivos'));
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
        $request->validate([
            'ejecutivo' => 'required|string|max:255',
            'cliente' => 'required|string|max:255',
            'operacion' => 'required|in:EXPORTACION,IMPORTACION',
            'tipo_operacion_enum' => 'required|in:Terrestre,Aerea,Maritima,Ferrocarril',
            'fecha_embarque' => 'required|date',
            'proveedor_o_cliente' => 'nullable|string|max:255',
            'no_factura' => 'nullable|string|max:255',
            'clave' => 'nullable|string|max:100',
            'referencia_interna' => 'nullable|string|max:255',
            'aduana' => 'nullable|string|max:255',
            'agente_aduanal' => 'nullable|string|max:255',
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
            // Target se calcula automáticamente basado en tipo_operacion_enum
            'target' => null, // Se calculará automáticamente
            // status_calculado y color_status se calculan automáticamente en el modelo
        ]);

        // Calcular target automáticamente basado en el tipo de operación
        $targetCalculado = $operacion->calcularTargetAutomatico();
        if ($targetCalculado !== null) {
            $operacion->target = $targetCalculado;
        }

        // Calcular días en tránsito
        $operacion->calcularDiasTransito();
        $operacion->save();

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
     * Obtener el historial de una operación
     */
    public function obtenerHistorial($id)
    {
        try {
            $operacion = OperacionLogistica::with([
                'ejecutivo', 
                'cliente', 
                'agenteAduanal', 
                'transporte', 
                'postOperacion',
                'historicoMatrizSgm'
            ])->findOrFail($id);

            $historial = $operacion->historicoMatrizSgm()
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($registro) {
                    return [
                        'id' => $registro->id,
                        'fecha_registro' => $registro->fecha_registro ? $registro->fecha_registro->format('d/m/Y') : null,
                        'fecha_arribo_aduana' => $registro->fecha_arribo_aduana ? $registro->fecha_arribo_aduana->format('d/m/Y') : null,
                        'dias_transcurridos' => $registro->dias_transcurridos,
                        'target_dias' => $registro->target_dias,
                        'color_status' => $registro->color_status,
                        'operacion_status' => $registro->operacion_status,
                        'observaciones' => $registro->observaciones,
                        'created_at' => $registro->created_at->format('d/m/Y H:i:s')
                    ];
                });

            return response()->json([
                'success' => true,
                'historial' => $historial,
                'operacion' => [
                    'id' => $operacion->id,
                    'operacion' => $operacion->operacion,
                    'status' => $operacion->status,
                    'cliente' => $operacion->cliente ? [
                        'id' => $operacion->cliente->id,
                        'cliente' => $operacion->cliente->cliente
                    ] : null,
                    'post_operacion' => $operacion->postOperacion ? [
                        'id' => $operacion->postOperacion->id,
                        'post_operacion' => $operacion->postOperacion->post_operacion,
                        'status' => $operacion->postOperacion->status
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar solo el status de una operación (solo se puede cambiar a 'Done')
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $operacion = OperacionLogistica::findOrFail($id);
            
            $request->validate([
                'status_calculado' => 'required|in:Done'
            ]);

            // Solo permitir cambio a 'Done'
            $operacion->status_calculado = 'Done';
            $operacion->save(); // Esto disparará el cálculo automático en el modelo
            
            // Crear registro en historial
            HistoricoMatrizSgm::create([
                'operacion_logistica_id' => $operacion->id,
                'fecha_registro' => now(),
                'fecha_arribo_aduana' => $operacion->fecha_arribo_aduana,
                'dias_transcurridos' => $operacion->dias_transcurridos_calculados,
                'target_dias' => $operacion->target,
                'color_status' => $operacion->color_status,
                'operacion_status' => $operacion->status_calculado,
                'observaciones' => 'Status actualizado a Done por ' . auth()->user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status actualizado exitosamente',
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
}
