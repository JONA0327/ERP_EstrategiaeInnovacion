<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\Cliente;
use App\Models\Logistica\AgenteAduanal;
use App\Models\Logistica\Transporte;
use App\Models\Logistica\PostOperacion;
use App\Models\Logistica\PostOperacionOperacion;
use App\Models\Logistica\HistoricoMatrizSgm;
use App\Models\Logistica\Aduana;
use App\Models\Logistica\Pedimento;
use App\Models\Empleado;
use App\Services\WordDocumentService;
use App\Services\ClienteImportService;
use App\Services\PedimentoImportService;

class OperacionLogisticaController extends Controller
{
    public function index()
    {
        // *** VERIFICACIÓN AUTOMÁTICA DE STATUS AL CONSULTAR ***
        $this->verificarYActualizarStatusOperaciones();

        $operaciones = OperacionLogistica::with(['ejecutivo', 'postOperacion'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Obtener datos para los selects del modal
        // Filtrar clientes por ejecutivo asignado (solo mostrar los del ejecutivo logueado)
        $usuarioActual = auth()->user();
        $empleadoActual = null;
        $esAdmin = false;

        // Buscar el empleado actual en la tabla empleados
        if ($usuarioActual) {
            $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                ->first();
            $esAdmin = $usuarioActual->hasRole('admin');
        }

        // Para ejecutivos normales (no admin), solo mostrar sus clientes asignados
        // Para admin, mostrar todos los clientes
        if (!$esAdmin && $empleadoActual) {
            // Solo mostrar clientes asignados específicamente a este ejecutivo
            $clientes = Cliente::where('ejecutivo_asignado_id', $empleadoActual->id)
                ->orderBy('cliente')->get();
        } elseif ($esAdmin) {
            // Administrador ve todos los clientes
            $clientes = Cliente::with('ejecutivoAsignado')->orderBy('cliente')->get();
        } else {
            // Si no es admin y no se encontró el empleado, no mostrar clientes
            $clientes = collect();
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
        $aduanas = \App\Models\Logistica\Aduana::orderBy('aduana')->orderBy('seccion')->get();
        $pedimentos = \App\Models\Logistica\Pedimento::orderBy('clave')->get();

        return view('Logistica.matriz-seguimiento', compact('operaciones', 'clientes', 'agentesAduanales', 'empleados', 'transportes', 'aduanas', 'pedimentos', 'empleadoActual', 'esAdmin'));
    }

    public function catalogos()
    {
        $usuarioActual = auth()->user();
        $empleadoActual = null;
        $esAdmin = false;

        // Buscar el empleado actual
        if ($usuarioActual) {
            $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                ->first();
            $esAdmin = $usuarioActual->hasRole('admin');
        }

        // Mostrar todos los clientes para todos los usuarios
        $clientes = Cliente::with('ejecutivoAsignado')->orderBy('cliente')->paginate(15, ['*'], 'clientes_page');

        $agentesAduanales = AgenteAduanal::orderBy('agente_aduanal')->paginate(15, ['*'], 'agentes_page');
        $transportes = Transporte::orderBy('transporte')->paginate(15, ['*'], 'transportes_page');

        // Agregar aduanas
        $aduanas = \App\Models\Logistica\Aduana::orderBy('aduana')->orderBy('seccion')->paginate(15, ['*'], 'aduanas_page');

        // Agregar pedimentos
        $pedimentos = \App\Models\Logistica\Pedimento::orderBy('clave')->paginate(15, ['*'], 'pedimentos_page');

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

        return view('Logistica.catalogos', compact('clientes', 'agentesAduanales', 'transportes', 'ejecutivos', 'todosEjecutivos', 'aduanas', 'pedimentos', 'empleadoActual', 'esAdmin'));
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

        // Ahora calcular status (ya que created_at existe) y generar historial inicial SIEMPRE
        $resultado = $operacion->calcularStatusPorDias();
        $operacion->generarHistorialCambioStatus(
            $resultado,
            false, // No es acción manual
            'Creación de operación - Registro inicial (tentativo)'
        );
        $operacion->saveQuietly(); // Guardar sin disparar eventos otra vez

        return response()->json([
            'success' => true,
            'message' => 'Operación creada exitosamente',
            'operacion' => $operacion->fresh()
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $operacion = OperacionLogistica::findOrFail($id);

            // Validación
            $request->validate([
                'operacion' => 'required|in:EXPORTACION,IMPORTACION',
                'tipo_operacion_enum' => 'required|in:Terrestre,Aerea,Maritima,Ferrocarril',
                'cliente' => 'required|string|max:255',
                'ejecutivo' => 'required|string|max:255',
                'fecha_embarque' => 'required|date',
                'proveedor_o_cliente' => 'required|string|max:255',
                'no_factura' => 'required|string|max:255',
                'clave' => 'required|string|max:100',
                'referencia_interna' => 'required|string|max:255',
                'aduana' => 'required|string|max:255',
                'agente_aduanal' => 'required|string|max:255',
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
                'status_manual' => 'nullable|in:In Process,Done',
            ]);

            // Guardar el status anterior para el historial
            $statusAnterior = [
                'status_calculado' => $operacion->status_calculado,
                'color_status' => $operacion->color_status,
                'status_manual' => $operacion->status_manual
            ];

            // Actualizar todos los campos
            $operacion->update([
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
                // Solo actualizar status_manual si se envía explícitamente
            ]);
            
            // Actualizar status_manual solo si se envió en el request
            if ($request->has('status_manual')) {
                $operacion->status_manual = $request->status_manual;
            }

            // Recalcular target si cambió el tipo de operación
            $targetCalculado = $operacion->calcularTargetAutomatico();
            if ($targetCalculado !== null) {
                $operacion->target = $targetCalculado;
            }

            $operacion->save();

            // Calcular el nuevo status
            $resultado = $operacion->calcularStatusPorDias();
            
            // SIEMPRE generar historial al editar
            if ($request->has('status_manual') && $request->status_manual !== $statusAnterior['status_manual']) {
                // Si se cambió el status manual (especialmente a Done)
                if ($request->status_manual === 'Done') {
                    $operacion->generarHistorialCambioStatus(
                        $resultado,
                        true,
                        'Marcado como DONE manualmente - Operación completada'
                    );
                } else {
                    $operacion->generarHistorialCambioStatus(
                        $resultado,
                        true,
                        'Cambio manual de status a: ' . $request->status_manual
                    );
                }
            } else {
                // Edición de campos (fechas, datos, etc) - siempre registrar
                $cambios = [];
                if ($request->fecha_arribo_aduana && $request->fecha_arribo_aduana !== $statusAnterior['status_calculado']) {
                    $cambios[] = 'fecha de aduana';
                }
                if ($request->fecha_arribo_planta) {
                    $cambios[] = 'fecha de entrega';
                }
                
                $descripcionCambio = count($cambios) > 0 
                    ? 'Actualización de operación - Cambios en: ' . implode(', ', $cambios)
                    : 'Actualización de operación';
                    
                $operacion->generarHistorialCambioStatus(
                    $resultado,
                    false,
                    $descripcionCambio
                );
            }

            $operacion->saveQuietly();

            return response()->json([
                'success' => true,
                'message' => 'Operación actualizada exitosamente',
                'operacion' => $operacion->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar operación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la operación: ' . $e->getMessage()
            ], 500);
        }
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

            // Convertir el nombre del cliente a mayúsculas
            $nombreCliente = strtoupper($request->cliente);

            $request->validate([
                'cliente' => 'required|string|max:255',
                'ejecutivo_asignado_id' => 'nullable|exists:empleados,id',
                'correos' => 'nullable|string', // Recibido como JSON string
                'periodicidad_reporte' => 'nullable|string|max:50'
            ]);

            // Verificar si el cliente ya existe (en mayúsculas)
            if (Cliente::whereRaw('UPPER(cliente) = ?', [$nombreCliente])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cliente ya existe en el sistema'
                ], 422);
            }

            // Procesar correos si se envían
            $correosArray = null;
            if ($request->correos) {
                $correosArray = json_decode($request->correos, true);
                if (!is_array($correosArray)) {
                    $correosArray = null;
                }
            }

            // Si no se especifica ejecutivo_asignado_id, asignar al usuario actual
            $ejecutivoAsignadoId = $request->ejecutivo_asignado_id;
            if (!$ejecutivoAsignadoId) {
                $usuarioActual = auth()->user();
                if ($usuarioActual) {
                    $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                        ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                        ->first();
                    if ($empleadoActual) {
                        $ejecutivoAsignadoId = $empleadoActual->id;
                    }
                }
            }

            $cliente = Cliente::create([
                'cliente' => $nombreCliente, // Guardar en mayúsculas
                'ejecutivo_asignado_id' => $ejecutivoAsignadoId,
                'correos' => $correosArray,
                'periodicidad_reporte' => $request->periodicidad_reporte ?? 'Diario',
                'fecha_carga_excel' => null // Solo se asigna cuando viene del Excel
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

            // Verificar permisos: solo admin o el ejecutivo asignado puede editar
            $usuarioActual = auth()->user();
            $esAdmin = $usuarioActual && $usuarioActual->hasRole('admin');
            
            if (!$esAdmin) {
                $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                    ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                    ->first();
                
                if (!$empleadoActual || $cliente->ejecutivo_asignado_id != $empleadoActual->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permisos para editar este cliente'
                    ], 403);
                }
            }

            // Convertir nombre a mayúsculas
            $nombreCliente = strtoupper($request->cliente);

            $request->validate([
                'cliente' => 'required|string|max:255',
                'ejecutivo_asignado_id' => 'nullable|exists:empleados,id',
                'correos' => 'nullable|string',
                'periodicidad_reporte' => 'nullable|string|max:50'
            ]);

            // Verificar duplicados (excluyendo el actual)
            if (Cliente::whereRaw('UPPER(cliente) = ?', [$nombreCliente])->where('id', '!=', $id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un cliente con ese nombre'
                ], 422);
            }

            $updateData = [
                'cliente' => $nombreCliente,
                'ejecutivo_asignado_id' => $request->ejecutivo_asignado_id ?? null
            ];

            // Solo actualizar campos opcionales si se envían en la request
            if ($request->has('correos')) {
                $correosArray = null;
                if ($request->correos) {
                    $correosArray = json_decode($request->correos, true);
                    if (!is_array($correosArray)) {
                        $correosArray = null;
                    }
                }
                $updateData['correos'] = $correosArray;
            }
            
            if ($request->has('periodicidad_reporte')) {
                $updateData['periodicidad_reporte'] = $request->periodicidad_reporte;
            }

            $cliente->update($updateData);

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

            // Verificar permisos: solo admin o el ejecutivo asignado puede eliminar
            $usuarioActual = auth()->user();
            $esAdmin = $usuarioActual && $usuarioActual->hasRole('admin');
            
            if (!$esAdmin) {
                $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                    ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                    ->first();
                
                if (!$empleadoActual || $cliente->ejecutivo_asignado_id != $empleadoActual->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permisos para eliminar este cliente'
                    ], 403);
                }
            }

            // Verificar si tiene operaciones asociadas por el campo texto 'cliente'
            $operacionesAsociadas = OperacionLogistica::where('cliente', $cliente->cliente)->count();
            
            if ($operacionesAsociadas > 0) {
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
                    'operacion' => $operacion->operacion,
                    'tipo_operacion_enum' => $operacion->tipo_operacion_enum,
                    'cliente' => $operacion->cliente,
                    'ejecutivo' => $operacion->ejecutivo,
                    'proveedor_o_cliente' => $operacion->proveedor_o_cliente,
                    'no_factura' => $operacion->no_factura,
                    'clave' => $operacion->clave,
                    'referencia_interna' => $operacion->referencia_interna,
                    'fecha_embarque' => $operacion->fecha_embarque ? $operacion->fecha_embarque->format('Y-m-d') : null,
                    'aduana' => $operacion->aduana,
                    'agente_aduanal' => $operacion->agente_aduanal,
                    'transporte' => $operacion->transporte,
                    'fecha_arribo_aduana' => $operacion->fecha_arribo_aduana ? $operacion->fecha_arribo_aduana->format('Y-m-d') : null,
                    'fecha_modulacion' => $operacion->fecha_modulacion ? $operacion->fecha_modulacion->format('Y-m-d') : null,
                    'fecha_arribo_planta' => $operacion->fecha_arribo_planta ? $operacion->fecha_arribo_planta->format('Y-m-d') : null,
                    'no_pedimento' => $operacion->no_pedimento,
                    'referencia_aa' => $operacion->referencia_aa,
                    'guia_bl' => $operacion->guia_bl,
                    'comentarios' => $operacion->comentarios,
                    'status_calculado' => $operacion->status_calculado,
                    'status_manual' => $operacion->status_manual,
                    'color_status' => $operacion->color_status,
                    'target' => $operacion->target,
                    'resultado' => $operacion->resultado,
                    'dias_transito' => $operacion->dias_transito,
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

    /**
     * Generar reporte Word de una operación específica
     */
    public function generarReporteWord($id)
    {
        try {
            $operacion = OperacionLogistica::with([
                'ejecutivo', 
                'cliente', 
                'agenteAduanal', 
                'transporte', 
                'postOperaciones',
                'historial' => function($query) {
                    $query->orderBy('created_at', 'desc')->limit(20);
                }
            ])->findOrFail($id);

            $wordService = new WordDocumentService();
            $wordService->crearReporteOperacion($operacion);
            
            $nombreArchivo = 'reporte_operacion_' . ($operacion->numero_operacion ?? $operacion->id) . '_' . date('Y-m-d_H-i-s') . '.docx';
            
            // Descargar directamente
            $wordService->descargar($nombreArchivo);
            
        } catch (\Exception $e) {
            \Log::error("Error generando reporte Word: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar reporte Word de múltiples operaciones (con filtros)
     */
    public function generarReporteMultiple(Request $request)
    {
        try {
            $query = OperacionLogistica::with(['ejecutivo']);

            // Aplicar filtros si existen
            if ($request->filled('cliente')) {
                $query->where('cliente', 'like', '%' . $request->cliente . '%');
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('fecha_desde')) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            }
            
            if ($request->filled('fecha_hasta')) {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }

            if ($request->filled('ejecutivo_id')) {
                $query->where('ejecutivo_id', $request->ejecutivo_id);
            }

            // Limitar a máximo 100 operaciones para evitar documentos muy grandes
            $operaciones = $query->orderBy('created_at', 'desc')->limit(100)->get();

            if ($operaciones->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron operaciones con los filtros aplicados'
                ], 404);
            }

            $wordService = new WordDocumentService();
            
            $titulo = 'REPORTE DE OPERACIONES LOGÍSTICAS';
            if ($request->filled('cliente')) {
                $titulo .= ' - ' . $request->cliente;
            }
            
            $wordService->crearReporteMultiple($operaciones, $titulo);
            
            $nombreArchivo = 'reporte_operaciones_' . date('Y-m-d_H-i-s') . '.docx';
            
            // Descargar directamente
            $wordService->descargar($nombreArchivo);
            
        } catch (\Exception $e) {
            \Log::error("Error generando reporte múltiple Word: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar reporte Word en servidor (para uso posterior)
     */
    public function guardarReporteWord($id)
    {
        try {
            $operacion = OperacionLogistica::with([
                'ejecutivo', 
                'cliente', 
                'agenteAduanal', 
                'transporte', 
                'postOperaciones',
                'historial' => function($query) {
                    $query->orderBy('created_at', 'desc')->limit(20);
                }
            ])->findOrFail($id);

            $wordService = new WordDocumentService();
            $wordService->crearReporteOperacion($operacion);
            
            $nombreArchivo = 'reporte_operacion_' . ($operacion->numero_operacion ?? $operacion->id) . '_' . date('Y-m-d_H-i-s');
            
            $resultado = $wordService->guardar($nombreArchivo);
            
            return response()->json([
                'success' => true,
                'message' => 'Reporte generado exitosamente',
                'data' => $resultado
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error guardando reporte Word: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar empleados para agregar como ejecutivos
     */
    public function searchEmployees(Request $request)
    {
        try {
            $search = $request->get('search', '');
            
            if (strlen($search) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            // Buscar empleados que no sean ya ejecutivos de logística
            $empleadosEjecutivos = Empleado::where('area', 'Logistica')->pluck('id');
            
            $empleados = Empleado::where(function($query) use ($search) {
                $query->where('nombre', 'like', "%{$search}%")
                      ->orWhere('id_empleado', 'like', "%{$search}%")
                      ->orWhere('correo', 'like', "%{$search}%");
            })
            ->whereNotIn('id', $empleadosEjecutivos)
            ->limit(20)
            ->get();

            return response()->json([
                'success' => true,
                'data' => $empleados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar empleados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar empleado como ejecutivo de logística
     */
    public function addEjecutivo(Request $request)
    {
        try {
            $request->validate([
                'empleado_id' => 'required|exists:empleados,id'
            ]);

            $empleado = Empleado::findOrFail($request->empleado_id);
            
            // Verificar que no sea ya ejecutivo de logística
            if ($empleado->area === 'Logistica') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este empleado ya es ejecutivo de logística'
                ], 422);
            }

            // Actualizar el área del empleado a Logística
            $empleado->update([
                'area' => 'Logistica'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Empleado agregado como ejecutivo de logística exitosamente',
                'empleado' => $empleado
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar ejecutivo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkAduanas()
    {
        try {
            $count = Aduana::count();
            
            return response()->json([
                'success' => true,
                'exists' => $count > 0,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar aduanas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkPedimentos()
    {
        try {
            $count = Pedimento::count();
            
            return response()->json([
                'success' => true,
                'exists' => $count > 0,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar pedimentos: ' . $e->getMessage()
            ], 500);
        }
    }

    // === MÉTODOS DE IMPORTACIÓN DE CLIENTES ===
    
    public function importClientes(Request $request)
    {
        try {
            $request->validate([
                'clientes_file' => 'required|file|mimes:xlsx,xls|max:10240' // 10MB máximo
            ]);
            
            $file = $request->file('clientes_file');
            
            // Crear directorio si no existe
            $uploadsDir = storage_path('app/uploads/clientes');
            if (!file_exists($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
                Log::info("Directorio creado: {$uploadsDir}");
            }
            
            $filename = 'clientes_' . time() . '.' . $file->getClientOriginalExtension();
            $fullPath = $uploadsDir . DIRECTORY_SEPARATOR . $filename;
            
            Log::info("Iniciando importación de clientes desde archivo: {$filename}");
            Log::info("Intentando guardar archivo en: {$fullPath}");
            Log::info("Directorio de destino existe: " . (file_exists($uploadsDir) ? 'Sí' : 'No'));
            Log::info("Archivo original válido: " . ($file->isValid() ? 'Sí' : 'No'));
            
            try {
                // Usar método directo para guardar el archivo
                $saved = $file->move($uploadsDir, $filename);
                
                if ($saved) {
                    Log::info("Archivo guardado exitosamente usando move()");
                    Log::info("¿Archivo existe después de move()? " . (file_exists($fullPath) ? 'Sí' : 'No'));
                    Log::info("Tamaño del archivo guardado: " . (file_exists($fullPath) ? filesize($fullPath) . ' bytes' : 'N/A'));
                } else {
                    throw new \Exception("El método move() retornó false");
                }
            } catch (\Exception $moveException) {
                Log::error("Error al mover archivo: " . $moveException->getMessage());
                throw new \Exception("No se pudo mover el archivo a: {$fullPath}. Error: " . $moveException->getMessage());
            }
            
            // Verificar que el archivo existe antes de procesarlo
            if (!file_exists($fullPath)) {
                throw new \Exception("El archivo no se pudo guardar correctamente. Ruta: {$fullPath}");
            }
            
            $importService = new ClienteImportService();
            $resultados = $importService->importFromExcel($fullPath);
            
            // Preparar la respuesta
            $response = response()->json([
                'success' => true,
                'message' => 'Importación completada exitosamente',
                'resultados' => $resultados
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error en importación de clientes: " . $e->getMessage());
            $response = response()->json([
                'success' => false,
                'message' => 'Error al importar clientes: ' . $e->getMessage()
            ], 500);
        } finally {
            // Limpiar archivo temporal siempre, sin importar si hubo éxito o error
            if (isset($fullPath) && file_exists($fullPath)) {
                unlink($fullPath);
                Log::info("Archivo temporal de clientes eliminado: {$fullPath}");
            } elseif (isset($fullPath)) {
                Log::warning("Archivo temporal de clientes no encontrado para eliminar: {$fullPath}");
            }
        }
        
        return $response ?? response()->json(['success' => false, 'message' => 'Error desconocido'], 500);
    }
    
    public function checkClientes()
    {
        try {
            $count = Cliente::whereNotNull('fecha_carga_excel')->count();
            
            return response()->json([
                'success' => true,
                'exists' => $count > 0,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar clientes: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function deleteAllClientes()
    {
        try {
            // Solo permitir a administradores
            if (!auth()->user() || !auth()->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta acción'
                ], 403);
            }
            
            $count = Cliente::count();
            
            if ($count === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay clientes para eliminar'
                ]);
            }
            
            // Eliminar todos los clientes
            Cliente::truncate();
            
            Log::info("Todos los clientes fueron eliminados por el usuario: " . auth()->user()->name . " (ID: " . auth()->id() . "). Total eliminados: {$count}");
            
            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$count} clientes exitosamente",
                'deleted_count' => $count
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error al eliminar todos los clientes: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    // === MÉTODOS DE IMPORTACIÓN DE PEDIMENTOS ===
    
    public function importPedimentos(Request $request)
    {
        try {
            // Debug: Registrar todos los datos de la request
            Log::info('Datos de la request de pedimentos:', [
                'files' => array_keys($request->allFiles()),
                'has_pedimentos_file' => $request->hasFile('pedimentos_file'),
                'has_file' => $request->hasFile('file'),
                'all_keys' => array_keys($request->all())
            ]);
            
            // Validar que existe uno de los dos posibles nombres de campo
            $request->validate([
                'pedimentos_file' => 'sometimes|file|mimes:xlsx,xls|max:10240',
                'file' => 'sometimes|file|mimes:xlsx,xls|max:10240'
            ]);
            
            // Buscar el archivo en cualquiera de los dos campos
            $file = $request->file('pedimentos_file') ?? $request->file('file');
            
            if (!$file) {
                throw new \Exception('No se ha proporcionado ningún archivo de pedimentos.');
            }
            
            // Crear directorio si no existe
            $uploadsDir = storage_path('app/uploads/pedimentos');
            if (!file_exists($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
                Log::info("Directorio creado: {$uploadsDir}");
            }
            
            $filename = 'pedimentos_' . time() . '.' . $file->getClientOriginalExtension();
            $fullPath = $uploadsDir . DIRECTORY_SEPARATOR . $filename;
            
            Log::info("Iniciando importación de pedimentos desde archivo: {$filename}");
            Log::info("Intentando guardar archivo en: {$fullPath}");
            Log::info("Directorio de destino existe: " . (file_exists($uploadsDir) ? 'Sí' : 'No'));
            Log::info("Archivo original válido: " . ($file->isValid() ? 'Sí' : 'No'));
            
            try {
                // Usar método directo para guardar el archivo
                $saved = $file->move($uploadsDir, $filename);
                
                if ($saved) {
                    Log::info("Archivo guardado exitosamente usando move()");
                    Log::info("¿Archivo existe después de move()? " . (file_exists($fullPath) ? 'Sí' : 'No'));
                    Log::info("Tamaño del archivo guardado: " . (file_exists($fullPath) ? filesize($fullPath) . ' bytes' : 'N/A'));
                } else {
                    throw new \Exception("El método move() retornó false");
                }
            } catch (\Exception $moveException) {
                Log::error("Error al mover archivo: " . $moveException->getMessage());
                throw new \Exception("No se pudo mover el archivo a: {$fullPath}. Error: " . $moveException->getMessage());
            }
            Log::info("Ruta completa del archivo: {$fullPath}");
            
            // Verificar que el archivo existe antes de procesarlo
            if (!file_exists($fullPath)) {
                throw new \Exception("El archivo no se pudo guardar correctamente. Ruta: {$fullPath}");
            }
            
            $importService = new \App\Services\PedimentoImportService();
            $resultados = $importService->import($fullPath);
            
            // Preparar la respuesta
            if ($resultados['success']) {
                $totalImported = $resultados['total_imported'] ?? 0;
                $totalSkipped = $resultados['total_skipped'] ?? 0;
                $message = "Importación completada: {$totalImported} pedimentos importados";
                if ($totalSkipped > 0) {
                    $message .= ", {$totalSkipped} omitidos";
                }
                $response = back()->with('success', $message);
            } else {
                $message = $resultados['message'] ?? 'Error en la importación';
                $response = back()->with('error', $message);
            }
            
        } catch (\Exception $e) {
            Log::error("Error en importación de pedimentos: " . $e->getMessage());
            $response = back()->with('error', 'Error al importar pedimentos: ' . $e->getMessage());
        } finally {
            // Limpiar archivo temporal siempre, sin importar si hubo éxito o error
            if (isset($fullPath) && file_exists($fullPath)) {
                unlink($fullPath);
                Log::info("Archivo temporal eliminado: {$fullPath}");
            } elseif (isset($fullPath)) {
                Log::warning("Archivo temporal no encontrado para eliminar: {$fullPath}");
            }
        }
        
        return $response ?? back()->with('error', 'Error desconocido en la importación');
    }

    /**
     * Vista pública para consulta de operaciones (sin autenticación)
     */
    public function consultaPublica()
    {
        return view('Logistica.consulta-publica');
    }

    /**
     * Búsqueda pública de operación por pedimento o factura
     */
    public function buscarOperacionPublica(Request $request)
    {
        try {
            $request->validate([
                'tipo_busqueda' => 'required|in:pedimento,factura',
                'valor' => 'required|string'
            ]);

            $tipoBusqueda = $request->tipo_busqueda;
            $valor = $request->valor;

            $query = OperacionLogistica::query();

            if ($tipoBusqueda === 'pedimento') {
                $query->where('no_pedimento', $valor);
            } else {
                $query->where('no_factura', $valor);
            }

            $operacion = $query->first();

            if (!$operacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró ninguna operación con ese ' . ($tipoBusqueda === 'pedimento' ? 'número de pedimento' : 'número de factura')
                ]);
            }

            // Cargar relaciones con verificación
            $historial = \App\Models\Logistica\HistoricoMatrizSgm::where('operacion_logistica_id', $operacion->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Obtener post-operaciones a través de la tabla pivot
            $postOperaciones = \App\Models\Logistica\PostOperacionOperacion::where('operacion_logistica_id', $operacion->id)
                ->with('postOperacion')
                ->orderBy('created_at', 'desc')
                ->get();

            // Formatear datos
            $data = [
                'success' => true,
                'operacion' => [
                    'id' => $operacion->id,
                    'operacion' => $operacion->operacion,
                    'cliente' => $operacion->cliente,
                    'ejecutivo' => $operacion->ejecutivo,
                    'tipo_operacion' => $operacion->tipo_operacion_enum,
                    'no_factura' => $operacion->no_factura,
                    'no_pedimento' => $operacion->no_pedimento,
                    'clave' => $operacion->clave,
                    'referencia_interna' => $operacion->referencia_interna,
                    'proveedor_o_cliente' => $operacion->proveedor_o_cliente,
                    'aduana' => $operacion->aduana,
                    'agente_aduanal' => $operacion->agente_aduanal,
                    'transporte' => $operacion->transporte,
                    'fecha_embarque' => $operacion->fecha_embarque?->format('d/m/Y'),
                    'fecha_arribo_aduana' => $operacion->fecha_arribo_aduana?->format('d/m/Y'),
                    'fecha_modulacion' => $operacion->fecha_modulacion?->format('d/m/Y'),
                    'fecha_arribo_planta' => $operacion->fecha_arribo_planta?->format('d/m/Y'),
                    'status_calculado' => $operacion->status_calculado,
                    'status_manual' => $operacion->status_manual,
                    'color_status' => $operacion->color_status,
                    'target' => $operacion->target,
                    'dias_transcurridos' => $operacion->dias_transcurridos_calculados,
                    'comentarios' => $operacion->comentarios,
                ],
                'historial' => $historial->map(function($item) {
                    return [
                        'id' => $item->id,
                        'fecha' => $item->created_at->format('d/m/Y H:i:s'),
                        'status' => $item->operacion_status ?? 'N/A',
                        'color' => $item->color_status ?? 'sin_fecha',
                        'descripcion' => $item->observaciones ?? 'Sin observaciones',
                        'dias_transcurridos' => $item->dias_transcurridos,
                        'target_dias' => $item->target_dias,
                        'fecha_arribo_aduana' => $item->fecha_arribo_aduana?->format('d/m/Y'),
                        'fecha_registro' => $item->fecha_registro?->format('d/m/Y'),
                    ];
                }),
                'post_operaciones' => $postOperaciones->map(function($item) {
                    return [
                        'id' => $item->id,
                        'nombre' => $item->postOperacion->nombre ?? 'N/A',
                        'descripcion' => $item->postOperacion->descripcion ?? '',
                        'status' => $item->status,
                        'fecha_asignacion' => $item->fecha_asignacion?->format('d/m/Y'),
                        'fecha_completado' => $item->fecha_completado?->format('d/m/Y'),
                        'notas_especificas' => $item->notas_especificas,
                    ];
                })
            ];

            return response()->json($data);

        } catch (\Exception $e) {
            \Log::error('Error en búsqueda pública: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar la búsqueda. Por favor, intente nuevamente.'
            ], 500);
        }
    }
}
