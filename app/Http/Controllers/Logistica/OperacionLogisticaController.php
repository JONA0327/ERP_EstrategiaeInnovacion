<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\OperacionComentario;
use App\Models\Logistica\Cliente;
use App\Models\Logistica\AgenteAduanal;
use App\Models\Logistica\Transporte;
use App\Models\Logistica\PostOperacion;
use App\Models\Logistica\PostOperacionOperacion;
use App\Models\Logistica\HistoricoMatrizSgm;
use App\Models\Logistica\Aduana;
use App\Models\Logistica\Pedimento;

use App\Models\Logistica\CampoPersonalizadoMatriz;
use App\Models\Logistica\ValorCampoPersonalizado;
use App\Models\Logistica\ColumnaVisibleEjecutivo;
use App\Models\Empleado;
use App\Services\WordDocumentService;
use App\Services\ClienteImportService;
use App\Services\PedimentoImportService;
use App\Services\ExcelReportService;
use App\Services\ExcelChartService;
use Illuminate\Support\Facades\DB;

class OperacionLogisticaController extends Controller
{
    public function index(Request $request)
    {
        // *** VERIFICACION AUTOMATICA DE STATUS AL CONSULTAR ***
        $this->verificarYActualizarStatusoperaciones();

        // Obtener datos para los selects del modal
        // Filtrar clientes por ejecutivo asignado (solo mostrar los del ejecutivo logueado)
        $usuarioActual = auth()->user();
        $empleadoActual = null;
        $esAdmin = false;
        $modoPreview = false;
        $empleadoPreview = null;

        // Buscar el empleado actual en la tabla empleados
        if ($usuarioActual) {
            $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                ->first();
            $esAdmin = $usuarioActual->hasRole('admin');
        }

        // *** MODO PREVIEW: Admin puede ver como si fuera otro ejecutivo ***
        $previewAs = $request->get('preview_as');
        if ($esAdmin && $previewAs) {
            $empleadoPreview = Empleado::find($previewAs);
            if ($empleadoPreview) {
                $modoPreview = true;
            }
        }

        // Obtener filtros del request
        $filtroCliente = $request->get('cliente');
        $filtroEjecutivo = $request->get('ejecutivo');

        // Base query con relaciones
        $query = OperacionLogistica::with(['ejecutivo', 'postoperacion', 'valoresCamposPersonalizados.campo']);

        // Filtrar operaciones: admin ve todas, usuarios normales solo las suyas
        if ($esAdmin) {
            // Admin ve todas las operaciones
        } elseif ($empleadoActual) {
            // Buscar por nombre completo del ejecutivo
            $query->where('ejecutivo', 'LIKE', '%' . $empleadoActual->nombre . '%');
        } else {
            $query->where('id', 0); // No mostrar nada si no se identifica al usuario
        }

        // Aplicar filtros adicionales
        if ($filtroCliente && $filtroCliente !== 'todos') {
            $query->where('cliente', $filtroCliente);
        }
        if ($filtroEjecutivo && $filtroEjecutivo !== 'todos') {
            $query->where('ejecutivo', $filtroEjecutivo);
        }

        // Obtener operaciones con paginación (10 por página)
        $operaciones = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Obtener lista de ejecutivos únicos para el filtro (solo si es admin)
        $ejecutivosUnicos = [];
        if ($esAdmin) {
            $ejecutivosUnicos = OperacionLogistica::select('ejecutivo')
                ->whereNotNull('ejecutivo')
                ->where('ejecutivo', '!=', '')
                ->distinct()
                ->orderBy('ejecutivo')
                ->pluck('ejecutivo')
                ->toArray();
        }

        // Obtener lista de clientes únicos para el filtro
        $clientesUnicos = [];
        if ($esAdmin) {
            $clientesUnicos = OperacionLogistica::select('cliente')
                ->whereNotNull('cliente')
                ->where('cliente', '!=', '')
                ->distinct()
                ->orderBy('cliente')
                ->pluck('cliente')
                ->toArray();
        } elseif ($empleadoActual) {
            $clientesUnicos = OperacionLogistica::select('cliente')
                ->where('ejecutivo', 'LIKE', '%' . $empleadoActual->nombre . '%')
                ->whereNotNull('cliente')
                ->where('cliente', '!=', '')
                ->distinct()
                ->orderBy('cliente')
                ->pluck('cliente')
                ->toArray();
        }

        // Para ejecutivos normales (no admin), solo mostrar sus clientes asignados
        // Para admin, mostrar todos los clientes
        if (!$esAdmin && $empleadoActual) {
            // Solo mostrar clientes asignados especficamente a este ejecutivo
            $clientes = Cliente::where('ejecutivo_asignado_id', $empleadoActual->id)
                ->orderBy('cliente')->get();
        } elseif ($esAdmin) {
            // Administrador ve todos los clientes
            $clientes = Cliente::with('ejecutivoAsignado')->orderBy('cliente')->get();
        } else {
            // Si no es admin y no se encontr el empleado, no mostrar clientes
            $clientes = collect();
        }

        $agentesAduanales = AgenteAduanal::orderBy('agente_aduanal')->get();
        // Solo empleados del rea de LOGISTICA
        $empleados = Empleado::where(function($query) {
                $query->where('area', 'like', '%LOGISTICA%')
                      ->orWhere('area', 'like', '%Logistica%')
                      ->orWhere('area', 'like', '%LOGISTICA%')
                      ->orWhere('area', 'like', '%LOGISTICA%');
            })
            ->orderBy('nombre')
            ->get();
        $transportes = Transporte::orderBy('transporte')->get();
        $aduanas = \App\Models\Logistica\Aduana::orderBy('aduana')->orderBy('seccion')->get();
        $pedimentos = \App\Models\Logistica\Pedimento::orderBy('clave')->get();

        // Cargar campos personalizados activos con sus ejecutivos
        $camposPersonalizados = CampoPersonalizadoMatriz::with('ejecutivos')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
        
        // DEBUG: Log de campos personalizados cargados
        \Log::info('Campos personalizados cargados:', [
            'total' => $camposPersonalizados->count(),
            'campos' => $camposPersonalizados->map(fn($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'activo' => $c->activo,
                'ejecutivos' => $c->ejecutivos->pluck('id')->toArray()
            ])->toArray()
        ]);

        // Cargar columnas opcionales visibles para el ejecutivo actual
        $columnasOpcionalesVisibles = [];
        $columnasPredeterminadasOcultas = [];
        $idiomaColumnas = 'es'; // Por defecto español
        $columnasOrdenadas = [];
        
        // Determinar qué empleado usar para la configuración de columnas
        $empleadoParaColumnas = $modoPreview ? $empleadoPreview : $empleadoActual;
        
        if ($empleadoParaColumnas) {
            $columnasOpcionalesVisibles = \App\Models\Logistica\ColumnaVisibleEjecutivo::getColumnasVisiblesParaEjecutivo($empleadoParaColumnas->id);
            $columnasPredeterminadasOcultas = \App\Models\Logistica\ColumnaVisibleEjecutivo::getColumnasPredeterminadasOcultas($empleadoParaColumnas->id);
            $idiomaColumnas = \App\Models\Logistica\ColumnaVisibleEjecutivo::getIdiomaEjecutivo($empleadoParaColumnas->id);
            $columnasOrdenadas = \App\Models\Logistica\ColumnaVisibleEjecutivo::getColumnasOrdenadasParaEjecutivo($empleadoParaColumnas->id, $idiomaColumnas);
        }
        
        // Admin ve todas las columnas opcionales y ninguna oculta (solo si NO está en modo preview)
        if ($esAdmin && !$modoPreview) {
            $columnasOpcionalesVisibles = array_keys(\App\Models\Logistica\ColumnaVisibleEjecutivo::$columnasOpcionales);
            $columnasPredeterminadasOcultas = [];
            $columnasOrdenadas = \App\Models\Logistica\ColumnaVisibleEjecutivo::getColumnasOrdenadasParaEjecutivo(0, $idiomaColumnas);
        }
        
        // DEBUG: Log de columnas ordenadas que se enviarán al frontend
        \Log::info('Columnas ordenadas para empleado:', [
            'empleado_id' => $empleadoParaColumnas ? $empleadoParaColumnas->id : 'admin',
            'es_admin' => $esAdmin,
            'modo_preview' => $modoPreview,
            'total_columnas' => count($columnasOrdenadas),
            'columnas' => $columnasOrdenadas
        ]);

        // Obtener nombres de columnas según idioma configurado
        $nombresColumnas = \App\Models\Logistica\ColumnaVisibleEjecutivo::getTodasLasColumnasConNombres($idiomaColumnas);

        return view('Logistica.matriz-seguimiento', compact(
            'operaciones', 
            'clientes', 
            'agentesAduanales', 
            'empleados', 
            'transportes', 
            'aduanas', 
            'pedimentos', 
            'empleadoActual', 
            'esAdmin', 
            'camposPersonalizados',
            'ejecutivosUnicos',
            'clientesUnicos',
            'filtroCliente',
            'filtroEjecutivo',
            'columnasOpcionalesVisibles',
            'columnasPredeterminadasOcultas',
            'idiomaColumnas',
            'nombresColumnas',
            'columnasOrdenadas',
            'modoPreview',
            'empleadoPreview'
        ));
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

        // Solo empleados del rea de LOGISTICA
        $ejecutivos = Empleado::where(function($query) {
                $query->where('area', 'like', '%LOGISTICA%')
                      ->orWhere('area', 'like', '%Logistica%')
                      ->orWhere('area', 'like', '%LOGISTICA%')
                      ->orWhere('area', 'like', '%LOGISTICA%');
            })
            ->orderBy('nombre')
            ->paginate(15, ['*'], 'ejecutivos_page');

        // Obtener todos los ejecutivos para el select de asignacin
        $todosEjecutivos = Empleado::where(function($query) {
                $query->where('area', 'like', '%LOGISTICA%')
                      ->orWhere('area', 'like', '%Logistica%')
                      ->orWhere('area', 'like', '%LOGISTICA%')
                      ->orWhere('area', 'like', '%LOGISTICA%');
            })
            ->orderBy('nombre')
            ->get();

        // Obtener correos CC
        $correosCC = \App\Models\Logistica\LogisticaCorreoCC::orderBy('tipo')->orderBy('nombre')->get();

        return view('Logistica.catalogos', compact('clientes', 'agentesAduanales', 'transportes', 'ejecutivos', 'todosEjecutivos', 'aduanas', 'pedimentos', 'correosCC', 'empleadoActual', 'esAdmin'));
    }

    // Reportes: pgina con export y grfico
    public function reportes(Request $request)
    {
        // Obtener usuario actual y verificar permisos
        $usuarioActual = auth()->user();
        $empleadoActual = null;
        $esAdmin = false;

        if ($usuarioActual) {
            $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                ->first();
            $esAdmin = $usuarioActual->hasRole('admin');
        }

        // Construir query base
        $query = OperacionLogistica::with('ejecutivo');
        $statsQuery = OperacionLogistica::query();

        // Si no es admin, filtrar solo sus operaciones
        if (!$esAdmin && $empleadoActual) {
            $query->where('ejecutivo', $empleadoActual->nombre);
            $statsQuery->where('ejecutivo', $empleadoActual->nombre);
        }

        // Aplicar filtros
        // Filtro por perodo (semanal, mensual, anual)
        if ($request->filled('periodo')) {
            $periodo = $request->periodo;
            if ($periodo === 'semanal') {
                $query->where('created_at', '>=', now()->subWeek());
                $statsQuery->where('created_at', '>=', now()->subWeek());
            } elseif ($periodo === 'mensual') {
                $query->where('created_at', '>=', now()->subMonth());
                $statsQuery->where('created_at', '>=', now()->subMonth());
            } elseif ($periodo === 'anual') {
                $query->where('created_at', '>=', now()->subYear());
                $statsQuery->where('created_at', '>=', now()->subYear());
            }
        }

        // Filtro por mes y ao especficos
        if ($request->filled('mes') && $request->filled('anio')) {
            $query->whereMonth('created_at', $request->mes)
                  ->whereYear('created_at', $request->anio);
            $statsQuery->whereMonth('created_at', $request->mes)
                       ->whereYear('created_at', $request->anio);
        }

        // Filtro por cliente (coincidencia exacta)
        if ($request->filled('cliente')) {
            $query->where('cliente', $request->cliente);
            $statsQuery->where('cliente', $request->cliente);
        }

        // Filtro por status
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'Done') {
                $query->where('status_manual', 'Done');
                $statsQuery->where('status_manual', 'Done');
            } elseif ($status === 'In Process') {
                $query->where(function($q){
                    $q->where(function($qq){
                        $qq->where('status_manual', '!=', 'Done')->orWhereNull('status_manual');
                    })->where('status_calculado', 'In Process');
                });
                $statsQuery->where(function($q){
                    $q->where(function($qq){
                        $qq->where('status_manual', '!=', 'Done')->orWhereNull('status_manual');
                    })->where('status_calculado', 'In Process');
                });
            } elseif ($status === 'Out of Metric') {
                $query->where(function($q){
                    $q->where(function($qq){
                        $qq->where('status_manual', '!=', 'Done')->orWhereNull('status_manual');
                    })->where('status_calculado', 'Out of Metric');
                });
                $statsQuery->where(function($q){
                    $q->where(function($qq){
                        $qq->where('status_manual', '!=', 'Done')->orWhereNull('status_manual');
                    })->where('status_calculado', 'Out of Metric');
                });
            }
        }

        // Filtro por rango de fechas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
            $statsQuery->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
            $statsQuery->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Obtener operaciones con filtros aplicados
        $operaciones = $query->orderByDesc('created_at')->limit(500)->get();

        // Contar por status actual con filtros aplicados
        $enProceso = (clone $statsQuery)->where(function($q){
            $q->where(function($qq){
                $qq->where('status_manual', '!=', 'Done')->orWhereNull('status_manual');
            })->where('status_calculado', 'In Process');
        })->count();

        $fueraMetrica = (clone $statsQuery)->where(function($q){
            $q->where(function($qq){
                $qq->where('status_manual', '!=', 'Done')->orWhereNull('status_manual');
            })->where('status_calculado', 'Out of Metric');
        })->count();

        $done = (clone $statsQuery)->where('status_manual', 'Done')->count();

        $stats = [
            'en_proceso' => $enProceso,
            'fuera_metrica' => $fueraMetrica,
            'done' => $done,
        ];

        // *** DATOS PARA ANLISIS TEMPORAL ***
        $analisisTemporalQuery = clone $statsQuery;
        $datosTemporales = $analisisTemporalQuery->select([
            'id', 'cliente', 'ejecutivo', 'dias_transcurridos_calculados',
            'target', 'status_calculado', 'status_manual', 'color_status',
            'fecha_embarque', 'fecha_arribo_aduana', 'created_at'
        ])->get();

        // Preparar datos de comportamiento por das transcurridos vs target
        $comportamientoTemporal = [];
        $clientes_unicos = [];

        foreach ($datosTemporales as $op) {
            $diasTranscurridos = $op->dias_transcurridos_calculados ?? 0;
            $target = $op->target ?? 3;
            $statusFinal = ($op->status_manual === 'Done') ? 'Done' : $op->status_calculado;
            $retraso = max(0, $diasTranscurridos - $target);

            // Categorizar el estado temporal
            $categoria = 'En Tiempo';
            if ($statusFinal === 'Done') {
                $categoria = $diasTranscurridos <= $target ? 'Completado a Tiempo' : 'Completado con Retraso';
            } elseif ($diasTranscurridos > $target) {
                $categoria = 'Con Retraso';
            } elseif ($diasTranscurridos >= ($target * 0.8)) {
                $categoria = 'En Riesgo';
            }

            $comportamientoTemporal[] = [
                'id' => $op->id,
                'cliente' => $op->cliente,
                'ejecutivo' => $op->ejecutivo,
                'dias_transcurridos' => (int)round($diasTranscurridos),
                'target' => $target,
                'retraso' => $retraso,
                'status' => $statusFinal,
                'categoria' => $categoria,
                'porcentaje_progreso' => min(100, ($diasTranscurridos / max($target, 1)) * 100)
            ];

            // Recopilar clientes nicos
            if (!in_array($op->cliente, $clientes_unicos)) {
                $clientes_unicos[] = $op->cliente;
            }
        }

        // Estadsticas del anlisis temporal
        $statsTemporales = [
            'en_tiempo' => collect($comportamientoTemporal)->where('categoria', 'En Tiempo')->count(),
            'en_riesgo' => collect($comportamientoTemporal)->where('categoria', 'En Riesgo')->count(),
            'con_retraso' => collect($comportamientoTemporal)->where('categoria', 'Con Retraso')->count(),
            'completado_tiempo' => collect($comportamientoTemporal)->where('categoria', 'Completado a Tiempo')->count(),
            'completado_retraso' => collect($comportamientoTemporal)->where('categoria', 'Completado con Retraso')->count(),
            'promedio_dias' => collect($comportamientoTemporal)->avg('dias_transcurridos'),
            'promedio_target' => collect($comportamientoTemporal)->avg('target'),
            'total_operaciones' => count($comportamientoTemporal)
        ];

        // Para el filtro: obtener clientes nicos simples
        $clientes = array_unique(array_filter($clientes_unicos));
        sort($clientes);

        // Para el modal de email: obtener solo clientes asignados al ejecutivo actual
        $clientesEmail = [];
        try {
            if ($empleadoActual && $empleadoActual->nombre) {
                // Obtener clientes que tienen operaciones con este ejecutivo
                $clientesDelEjecutivo = OperacionLogistica::where('ejecutivo', $empleadoActual->nombre)
                    ->whereNotNull('cliente')
                    ->where('cliente', '!=', '')
                    ->distinct()
                    ->pluck('cliente')
                    ->toArray();

                if (!empty($clientesDelEjecutivo)) {
                    $clientesDB = \App\Models\Logistica\Cliente::select('cliente', 'correos')
                        ->whereIn('cliente', $clientesDelEjecutivo)
                        ->orderBy('cliente')
                        ->get();

                    foreach ($clientesDB as $cliente) {
                        if ($cliente->cliente && is_string($cliente->cliente)) {
                            $correosString = '';
                            if ($cliente->correos) {
                                if (is_array($cliente->correos)) {
                                    $correosLimpio = array_filter($cliente->correos, function($email) {
                                        return is_string($email) && !empty(trim($email));
                                    });
                                    $correosString = implode(', ', $correosLimpio);
                                } elseif (is_string($cliente->correos)) {
                                    $correosString = trim($cliente->correos);
                                }
                            }

                            $clientesEmail[] = [
                                'cliente' => trim($cliente->cliente),
                                'correos' => $correosString
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error obteniendo clientes del ejecutivo: ' . $e->getMessage());
            $clientesEmail = [];
        }

        return view('Logistica.reportes', compact(
            'operaciones', 'stats', 'clientes', 'clientesEmail', 'comportamientoTemporal',
            'statsTemporales', 'esAdmin', 'empleadoActual'
        ));
    }

    /**
     * Obtener operaciones de un cliente especfico para reporte por correo
     */
    public function getoperacionesPorCliente(Request $request)
    {
        try {
            $clienteNombre = $request->cliente;

            if (!$clienteNombre) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no especificado'
                ], 400);
            }

            // Obtener operaciones del cliente
            $operaciones = OperacionLogistica::where('cliente', $clienteNombre)
                ->orderByDesc('created_at')
                ->get();

            // Buscar cliente en catlogo para obtener correos
            $clienteData = Cliente::where('cliente', $clienteNombre)->first();
            $correos = $clienteData && $clienteData->correos ? $clienteData->correos : [];

            // Preparar datos para vista previa con todos los campos del CSV
            $operacionesData = $operaciones->map(function($op, $index) {
                $statusFinal = ($op->status_manual === 'Done') ? 'Done' : $op->status_calculado;
                $statusDisplay = match($statusFinal) {
                    'In Process' => 'En Proceso',
                    'Out of Metric' => 'Fuera de METRICA',
                    'Done' => 'Completado',
                    default => $statusFinal ?? 'En Proceso'
                };

                // Obtener post-operaciones
                $postOps = $op->postoperaciones ?? collect();
                $postOpsCompletas = $postOps->where('estado', 'completa')->pluck('nombre')->join(', ');
                $postOpsPendientes = $postOps->where('estado', 'pendiente')->pluck('nombre')->join(', ');

                // Obtener comentarios del campo texto (no es una relacin)
                $comentariosTexto = $op->comentarios ?? '-';

                return [
                    'no' => $index + 1,
                    'ejecutivo' => $op->ejecutivo ?? 'Sin asignar',
                    'operacion' => $op->operacion ?? '-',
                    'cliente' => $op->cliente ?? '-',
                    'proveedor_cliente_final' => $op->proveedor_o_cliente ?? '-',
                    'fecha_embarque' => optional($op->fecha_embarque)->format('d/m/Y') ?? '-',
                    'no_factura' => $op->no_factura ?? '-',
                    'tipo_operacion' => $op->tipo_operacion_enum ?? '-',
                    'clave' => $op->clave ?? '-',
                    'referencia_interna' => $op->referencia_interna ?? '-',
                    'aduana' => $op->aduana ?? '-',
                    'aa' => $op->agente_aduanal ?? '-',
                    'referencia_aa' => $op->referencia_aa ?? '-',
                    'no_pedimento' => $op->no_pedimento ?? '-',
                    'transporte' => $op->transporte ?? '-',
                    'fecha_arribo_aduana' => optional($op->fecha_arribo_aduana)->format('d/m/Y') ?? '-',
                    'guia_bl' => $op->guia_bl ?? '-',
                    'status' => $statusDisplay,
                    'fecha_modulacion' => optional($op->fecha_modulacion)->format('d/m/Y') ?? '-',
                    'fecha_arribo_planta' => optional($op->fecha_arribo_planta)->format('d/m/Y') ?? '-',
                    'resultado' => $op->resultado ?? '-',
                    'target' => $op->target ?? '-',
                    'dias_transito' => $op->dias_transito ?? '-',
                    'post_operaciones_completas' => $postOpsCompletas ?: '-',
                    'post_operaciones_pendientes' => $postOpsPendientes ?: '-',
                    'comentarios' => $comentariosTexto ?: '-',
                ];
            });

            return response()->json([
                'success' => true,
                'operaciones' => $operacionesData,
                'correos' => $correos,
                'total' => $operaciones->count(),
                'cliente' => $clienteNombre
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener operaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    // Exportar CSV de operaciones - MISMO FORMATO que envio por correo
    public function exportCSV(Request $request)
    {
        try {
            // Obtener usuario actual y verificar permisos
            $usuarioActual = auth()->user();
            $empleadoActual = null;
            $esAdmin = false;

            if ($usuarioActual) {
                $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                    ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                    ->first();
                $esAdmin = $usuarioActual->hasRole('admin');
            }

            // Determinar el tipo de reporte y formato
            $reportType = $request->get('report_type', 'seguimiento');
            $format = $request->get('format', 'excel');
            
            // Log para debug
            \Log::info('Exportando reporte:', [
                'tipo' => $reportType,
                'formato' => $format,
                'include_custom_fields' => $request->get('include_custom_fields'),
                'include_comments' => $request->get('include_comments')
            ]);

            // Construir query con los mismos filtros que usa enviarReporte
            $relationships = ['ejecutivo', 'asignacionesPostOperaciones.postOperacion'];
            
            // Incluir campos personalizados si se especifica (para seguimiento)
            if ($reportType === 'seguimiento' && $request->get('include_custom_fields')) {
                $relationships[] = 'valoresCamposPersonalizados.campo';
            }
            
            // Incluir comentarios si se especifica
            if ($request->get('include_comments')) {
                $relationships[] = 'comentarios';
            }

            $query = OperacionLogistica::with($relationships);

            // Aplicar filtros de permisos
            if (!$esAdmin && $empleadoActual) {
                $query->where('ejecutivo', $empleadoActual->nombre);
            }

            // Aplicar todos los filtros de la request (MISMOS que usa enviarReporte)
            $this->aplicarFiltrosReporte($query, $request);

            // Obtener operaciones
            $operaciones = $query->get();

            // Crear archivo temporal usando el MISMO metodo que enviarReporte
            // Determinar formato final (el método generarArchivoReporte maneja 'csv' como Excel)
            $formatoArchivo = ($format === 'csv') ? 'csv' : 'excel';
            $archivoInfo = $this->generarArchivoReporte($operaciones, $formatoArchivo);

            // Retornar descarga directa
            return response()->download($archivoInfo['path'], $archivoInfo['nombre'], [
                'Content-Type' => $archivoInfo['mime']
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('Error en exportCSV: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar: ' . $e->getMessage()
            ], 500);
        }
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
            'tiposoperacion' => ['Aerea', 'Terrestre', 'Maritima', 'Ferrocarril'],
            'operaciones' => ['Exportacion', 'Importacion'],
            'statusOptions' => ['In Process', 'Done', 'Out of Metric']
        ]);
    }

    public function store(Request $request)
    {
        try {
            // validacion SEGN FLUJO CORPORATIVO - Solo campos obligatorios al crear
            $request->validate([
            // === CAMPOS OBLIGATORIOS AL INICIO (12 mximo) ===

            // A. Informacin Bsica
            'operacion' => 'required|in:EXPORTACION,IMPORTACION',
            'tipo_operacion_enum' => 'required|in:Terrestre,Aerea,Maritima,Ferrocarril',

            // B. Cliente y Ejecutivo
            'cliente' => 'required|string|max:255',
            'ejecutivo' => 'required|string|max:255',

            // C. Fecha Inicial (la nica obligatoria)
            'fecha_embarque' => 'required|date',

            // D. Informacin Inicial Adicional
            'proveedor_o_cliente' => 'required|string|max:255',
            'no_factura' => 'required|string|max:255',
            'clave' => 'required|string|max:100',
            'referencia_interna' => 'required|string|max:255',
            'aduana' => 'required|string|max:255',
            'agente_aduanal' => 'required|string|max:255',

            // === CAMPOS OPCIONALES (se llenan despus) ===
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
            
            // === COLUMNAS OPCIONALES ADICIONALES ===
            'tipo_carga' => 'nullable|string|max:50',
            'tipo_incoterm' => 'nullable|string|max:50',
            'puerto_salida' => 'nullable|string|max:255',
            'in_charge' => 'nullable|string|max:255',
            'proveedor' => 'nullable|string|max:255',
            'tipo_previo' => 'nullable|string|max:100',
            'fecha_etd' => 'nullable|date',
            'fecha_zarpe' => 'nullable|date',
            'pedimento_en_carpeta' => 'nullable|boolean',
            'referencia_cliente' => 'nullable|string|max:255',
            'mail_subject' => 'nullable|string|max:500',
        ]);

        // Crear la operacion - el status se calcula automticamente en el modelo
        $data = [
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
            // Target se calcula automticamente basado en tipo_operacion_enum
            'target' => null, // Se calcular automticamente
            // NO incluir status_manual como null - dejamos que use el default de la base de datos
            // status_calculado y color_status se calculan automticamente en el modelo
            
            // Columnas opcionales adicionales
            'tipo_carga' => $request->tipo_carga,
            'tipo_incoterm' => $request->tipo_incoterm,
            'puerto_salida' => $request->puerto_salida,
            'in_charge' => $request->in_charge,
            'proveedor' => $request->proveedor,
            'tipo_previo' => $request->tipo_previo,
            'fecha_etd' => $request->fecha_etd,
            'fecha_zarpe' => $request->fecha_zarpe,
            'pedimento_en_carpeta' => $request->boolean('pedimento_en_carpeta'),
            'referencia_cliente' => $request->referencia_cliente,
            'mail_subject' => $request->mail_subject,
        ];

        $operacion = OperacionLogistica::create($data);

        // Calcular target automticamente basado en el tipo de operacion
        $targetCalculado = $operacion->calcularTargetAutomatico();
        if ($targetCalculado !== null) {
            $operacion->target = $targetCalculado;
        }

        // Calcular resultado y das en trnsito automticamente
        $operacion->calcularDiasTransito();

        // *** GUARDAR PRIMERO, LUEGO CALCULAR STATUS ***
        $operacion->save();

        // Crear comentario inicial (incluye el status automticamente)
        $operacion->crearComentarioInicialoperacion($request->comentarios);

        // Calcular status final y guardar
        $resultado = $operacion->calcularStatusPorDias();
        $operacion->saveQuietly();

            return response()->json([
                'success' => true,
                'message' => 'operacion creada exitosamente',
                'operacion' => $operacion->fresh(),
                'operacion_id' => $operacion->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error al crear operacion en operacionLogisticaController@store', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la operacion: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $operacion = OperacionLogistica::findOrFail($id);

            // validacion
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
                'status_manual' => 'nullable|in:In Process,Done,Out of Metric',
                
                // Columnas opcionales adicionales
                'tipo_carga' => 'nullable|string|max:50',
                'tipo_incoterm' => 'nullable|string|max:50',
                'puerto_salida' => 'nullable|string|max:255',
                'in_charge' => 'nullable|string|max:255',
                'proveedor' => 'nullable|string|max:255',
                'tipo_previo' => 'nullable|string|max:100',
                'fecha_etd' => 'nullable|date',
                'fecha_zarpe' => 'nullable|date',
                'pedimento_en_carpeta' => 'nullable|boolean',
                'referencia_cliente' => 'nullable|string|max:255',
                'mail_subject' => 'nullable|string|max:500',
            ]);

            // Guardar el status anterior y fechas importantes para el historial
            $statusAnterior = [
                'status_calculado' => $operacion->status_calculado,
                'color_status' => $operacion->color_status,
                'status_manual' => $operacion->status_manual,
                'fecha_arribo_aduana' => $operacion->fecha_arribo_aduana,
                'fecha_modulacion' => $operacion->fecha_modulacion,
                'fecha_arribo_planta' => $operacion->fecha_arribo_planta
            ];

            // Preparar datos para actualizar
            $updateData = [
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
                
                // Columnas opcionales adicionales
                'tipo_carga' => $request->tipo_carga,
                'tipo_incoterm' => $request->tipo_incoterm,
                'puerto_salida' => $request->puerto_salida,
                'in_charge' => $request->in_charge,
                'proveedor' => $request->proveedor,
                'tipo_previo' => $request->tipo_previo,
                'fecha_etd' => $request->fecha_etd,
                'fecha_zarpe' => $request->fecha_zarpe,
                'pedimento_en_carpeta' => $request->boolean('pedimento_en_carpeta'),
                'referencia_cliente' => $request->referencia_cliente,
                'mail_subject' => $request->mail_subject,
            ];

            // Solo incluir status_manual si se enva y no es null
            if ($request->has('status_manual') && !is_null($request->status_manual)) {
                $updateData['status_manual'] = $request->status_manual;
            }

            // Verificar si cambi el comentario para crear nueva entrada
            $comentarioAnterior = $operacion->comentarios;
            $comentarioNuevo = $request->comentarios;

            // Detectar cambios en fechas importantes
            $fechasImportantesCambiaron = [];
            
            // Verificar cambio en fecha arribo aduana
            if ($request->fecha_arribo_aduana != $statusAnterior['fecha_arribo_aduana']) {
                if ($request->fecha_arribo_aduana && !$statusAnterior['fecha_arribo_aduana']) {
                    $fechasImportantesCambiaron[] = [
                        'tipo' => 'fecha_arribo_aduana',
                        'mensaje' => 'La Mercanca lleg a la Aduana',
                        'fecha' => $request->fecha_arribo_aduana
                    ];
                } elseif ($request->fecha_arribo_aduana && $statusAnterior['fecha_arribo_aduana']) {
                    $fechasImportantesCambiaron[] = [
                        'tipo' => 'fecha_arribo_aduana',
                        'mensaje' => 'Fecha de arribo a aduana actualizada',
                        'fecha' => $request->fecha_arribo_aduana
                    ];
                }
            }
            
            // Verificar cambio en fecha modulacin
            if ($request->fecha_modulacion != $statusAnterior['fecha_modulacion']) {
                if ($request->fecha_modulacion && !$statusAnterior['fecha_modulacion']) {
                    $fechasImportantesCambiaron[] = [
                        'tipo' => 'fecha_modulacion',
                        'mensaje' => 'Mercanca fue modulada en aduana',
                        'fecha' => $request->fecha_modulacion
                    ];
                } elseif ($request->fecha_modulacion && $statusAnterior['fecha_modulacion']) {
                    $fechasImportantesCambiaron[] = [
                        'tipo' => 'fecha_modulacion', 
                        'mensaje' => 'Fecha de modulacin actualizada',
                        'fecha' => $request->fecha_modulacion
                    ];
                }
            }
            
            // Verificar cambio en fecha arribo planta
            if ($request->fecha_arribo_planta != $statusAnterior['fecha_arribo_planta']) {
                if ($request->fecha_arribo_planta && !$statusAnterior['fecha_arribo_planta']) {
                    $fechasImportantesCambiaron[] = [
                        'tipo' => 'fecha_arribo_planta',
                        'mensaje' => 'Mercanca arrib a planta/destino final',
                        'fecha' => $request->fecha_arribo_planta
                    ];
                } elseif ($request->fecha_arribo_planta && $statusAnterior['fecha_arribo_planta']) {
                    $fechasImportantesCambiaron[] = [
                        'tipo' => 'fecha_arribo_planta',
                        'mensaje' => 'Fecha de arribo a planta actualizada', 
                        'fecha' => $request->fecha_arribo_planta
                    ];
                }
            }

            // Actualizar todos los campos
            $operacion->update($updateData);
            
            // Si hay cambios de fechas importantes, crear UN SOLO registro consolidado
            if (!empty($fechasImportantesCambiaron)) {
                $this->crearRegistroHistorialFechasConsolidado($operacion, $fechasImportantesCambiaron);
            }

            // Si se cambi el comentario, crear NUEVO registro en el historial
            if ($comentarioNuevo && $comentarioNuevo !== $comentarioAnterior) {
                // Obtener el registro ms reciente para copiar sus datos de status
                $historialReciente = $operacion->historicoMatrizSgm()
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($historialReciente) {
                    // Crear un NUEVO registro en el historial manteniendo el status actual
                    // pero con las nuevas observaciones
                    $operacion->historicoMatrizSgm()->create([
                        'fecha_registro' => now(),
                        'fecha_arribo_aduana' => $historialReciente->fecha_arribo_aduana,
                        'dias_transcurridos' => (int)round($historialReciente->dias_transcurridos ?? 0),
                        'target_dias' => $historialReciente->target_dias,
                        'color_status' => $historialReciente->color_status,
                        'operacion_status' => $historialReciente->operacion_status,
                        'observaciones' => $comentarioNuevo
                    ]);

                    \Log::info('Nuevo registro de historial creado desde update:', [
                        'operacion_id' => $operacion->id,
                        'comentario_anterior' => $comentarioAnterior,
                        'comentario_nuevo' => $comentarioNuevo
                    ]);
                }

                // Tambin crear entrada en el sistema de comentarios
                $operacion->crearComentario(
                    $comentarioNuevo,
                    'edicion_comentario'
                );
            }

            // Recalcular target si cambi el tipo de operacion
            $targetCalculado = $operacion->calcularTargetAutomatico();
            if ($targetCalculado !== null) {
                $operacion->target = $targetCalculado;
            }

            // Recalcular resultado y das en trnsito automticamente
            $operacion->calcularDiasTransito();

            $operacion->save();

            // Calcular el nuevo status
            $resultado = $operacion->calcularStatusPorDias();

            // SIEMPRE generar historial al editar
            if ($request->has('status_manual') && $request->status_manual !== $statusAnterior['status_manual']) {
                // Si se cambi el status manual (especialmente a Done)
                if ($request->status_manual === 'Done') {
                    $operacion->generarHistorialCambioStatus(
                        $resultado,
                        true,
                        'Marcado como DONE manualmente - operacion completada'
                    );
                } else {
                    $operacion->generarHistorialCambioStatus(
                        $resultado,
                        true,
                        'Cambio manual de status a: ' . $request->status_manual
                    );
                }
            } else {
                // Edicin de campos (fechas, datos, etc) - siempre registrar
                $cambios = [];
                if ($request->fecha_arribo_aduana && $request->fecha_arribo_aduana !== $statusAnterior['status_calculado']) {
                    $cambios[] = 'fecha de aduana';
                }
                if ($request->fecha_arribo_planta) {
                    $cambios[] = 'fecha de entrega';
                }

                $descripcionCambio = count($cambios) > 0
                    ? 'Actualizacin de operacion - Cambios en: ' . implode(', ', $cambios)
                    : 'Actualizacin de operacion';

                $operacion->generarHistorialCambioStatus(
                    $resultado,
                    false,
                    $descripcionCambio
                );
            }

            $operacion->saveQuietly();

            return response()->json([
                'success' => true,
                'message' => 'operacion actualizada exitosamente',
                'operacion' => $operacion->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar operacion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la operacion: ' . $e->getMessage()
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

            // Convertir el nombre del cliente a maysculas
            $nombreCliente = strtoupper($request->cliente);

            $request->validate([
                'cliente' => 'required|string|max:255',
                'ejecutivo_asignado_id' => 'nullable|exists:empleados,id',
                'correos' => 'nullable|string', // Recibido como JSON string
                'periodicidad_reporte' => 'nullable|string|max:50'
            ]);

            // Verificar si el cliente ya existe (en maysculas)
            if (Cliente::whereRaw('UPPER(cliente) = ?', [$nombreCliente])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cliente ya existe en el sistema'
                ], 422);
            }

            // Procesar correos si se envan
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
                'cliente' => $nombreCliente, // Guardar en maysculas
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
            \Log::error('Error de validacion en storeCliente:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validacion: ' . implode(', ', $e->validator->errors()->all())
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
            \Log::error('Error de validacion en storeAgente:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validacion: ' . implode(', ', $e->validator->errors()->all())
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

    // Mtodos de actualizacin
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

            // Convertir nombre a maysculas
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

            // Solo actualizar campos opcionales si se envan en la request
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

    // Mtodos de eliminacin
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

            // Eliminar el agente (las operaciones mantienen el nombre como texto)
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
            \Log::error('Error de validacion en storeTransporte:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validacion: ' . implode(', ', $e->validator->errors()->all())
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
     * Obtener catálogo de Incoterms activos
     */
    public function getIncoterms()
    {
        try {
            $incoterms = \App\Models\Logistica\Incoterm::activos()
                ->ordenados()
                ->get(['id', 'codigo', 'nombre', 'descripcion', 'grupo']);

            return response()->json([
                'success' => true,
                'incoterms' => $incoterms
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener incoterms:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los incoterms: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vista oculta para importar Excel de Matriz de Operación
     * Solo accesible por admins
     */
    public function vistaImportarExcel()
    {
        // Verificar que sea admin
        $usuarioActual = auth()->user();
        if (!$usuarioActual || !$usuarioActual->hasRole('admin')) {
            abort(403, 'Acceso no autorizado');
        }

        // Obtener lista de ejecutivos para el select
        $ejecutivos = Empleado::orderBy('nombre')
            ->get(['id', 'nombre', 'correo']);

        return view('logistica.importar-excel', compact('ejecutivos'));
    }

    /**
     * Procesar importación de Excel de Matriz de Operación
     */
    public function importarExcel(Request $request)
    {
        // Verificar que sea admin
        $usuarioActual = auth()->user();
        if (!$usuarioActual || !$usuarioActual->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción'
            ], 403);
        }

        $request->validate([
            'archivo_excel' => 'required|mimes:xlsx,xls,csv|max:10240', // max 10MB
            'ejecutivo_id' => 'required|exists:empleados,id',
        ], [
            'ejecutivo_id.required' => 'Debe seleccionar un ejecutivo para asignar las columnas personalizadas',
            'ejecutivo_id.exists' => 'El ejecutivo seleccionado no existe',
            'archivo_excel.required' => 'Debe seleccionar un archivo Excel',
            'archivo_excel.mimes' => 'El archivo debe ser de tipo Excel (.xlsx, .xls) o CSV',
            'archivo_excel.max' => 'El archivo no debe pesar más de 10MB'
        ]);

        try {
            $empleadoId = $request->ejecutivo_id;
            
            $import = new \App\Imports\MatrizLogisticaImport($empleadoId);
            $import->import($request->file('archivo_excel')->getPathname());

            // Obtener información del resultado
            $columnasActivadas = $import->getColumnasActivadas();
            $camposCreados = $import->getCamposPersonalizadosCreados();

            $mensaje = 'Importación completada exitosamente.';
            
            if (!empty($columnasActivadas)) {
                $mensaje .= ' Se activaron ' . count($columnasActivadas) . ' columnas opcionales para el ejecutivo.';
            }
            
            if (!empty($camposCreados)) {
                $mensaje .= ' Se crearon ' . count($camposCreados) . ' campos personalizados nuevos.';
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'columnas_activadas' => $columnasActivadas,
                'campos_creados' => $camposCreados
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al importar Excel:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el mapeo de columnas para previsualización
     */
    public function obtenerMapeoColumnas()
    {
        // Columnas de la BD
        $columnasBD = [
            'folio' => 'No. Folio',
            'operacion' => 'Operación/Process',
            'cliente' => 'Cliente/Customer',
            'ejecutivo' => 'Ejecutivo',
            'agente_aduanal' => 'Agente Aduanal/Customer Broker',
            'no_factura' => 'No. Factura/Invoice Number',
            'proveedor' => 'Proveedor/Supplier Name',
            'aduana' => 'Aduana/Customs MX',
            'in_charge' => 'Responsable/In Charge',
            'tipo_operacion_enum' => 'Tipo Operación/Freight',
            'guia_bl' => 'Guía/BL/Tracking',
            'fecha_etd' => 'Fecha ETD/Shipp Date ETD',
            'fecha_zarpe' => 'Fecha Zarpe/Shipp Date Zarpe',
            'fecha_arribo_aduana' => 'Fecha Arribo/Arriving Date',
            'fecha_modulacion' => 'Fecha Salida Aduana',
            'fecha_arribo_planta' => 'Fecha Arribo Planta/ETA Planta',
            'status_manual' => 'Status/Estatus',
            'no_pedimento' => 'Pedimento',
            'referencia_cliente' => 'Referencia/REF',
            'mail_subject' => 'Asunto Correo/Mail Subject',
            'tipo_previo' => 'Modalidad/Previo',
            'pedimento_en_carpeta' => 'Pedimento en Carpeta',
            'tipo_carga' => 'Tipo de Carga',
            'tipo_incoterm' => 'Incoterm',
            'puerto_salida' => 'Puerto de Salida',
            'transporte' => 'Transporte',
            'referencia_aa' => 'Referencia AA',
            'referencia_interna' => 'Referencia Interna',
            'clave' => 'Clave',
            'proveedor_o_cliente' => 'Proveedor o Cliente',
            'comentarios' => 'Comentarios',
            'target' => 'Target',
            'dias_transito' => 'Días Tránsito',
            'resultado' => 'Resultado',
            'fecha_embarque' => 'Fecha Embarque',
        ];

        // Columnas opcionales que se pueden activar
        $columnasOpcionales = [
            'tipo_carga',
            'tipo_incoterm',
            'puerto_salida',
            'in_charge',
            'proveedor',
            'tipo_previo',
            'fecha_etd',
            'fecha_zarpe',
            'pedimento_en_carpeta',
            'referencia_cliente',
            'mail_subject',
        ];

        return response()->json([
            'success' => true,
            'columnas_bd' => $columnasBD,
            'columnas_opcionales' => $columnasOpcionales
        ]);
    }

    /**
     * Asignar mltiples clientes a un ejecutivo
     */
    public function asignarClientesEjecutivo(Request $request)
    {
        try {
            // Verificar que el usuario tenga permisos de administrador
            $usuarioActual = auth()->user();
            if (!$usuarioActual || !$usuarioActual->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta accin'
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
                // Si no es admin, solo los clientes asignados a l
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
     * Generar historial inicial cuando se crea una operacion
     */
    private function generarHistorialInicial($operacion)
    {
        try {
            // Crear registro inicial del historial
            HistoricoMatrizSgm::create([
                'operacion_logistica_id' => $operacion->id,
                'fecha_arribo_aduana' => $operacion->fecha_arribo_aduana,
                'fecha_registro' => now()->format('Y-m-d'),
                'dias_transcurridos' => (int)round($operacion->dias_transcurridos_calculados ?? 0),
                'target_dias' => $operacion->target ?? 0,
                'color_status' => $operacion->color_status ?? 'sin_fecha',
                'operacion_status' => $operacion->status_calculado ?? 'In Process',
                'observaciones' => 'operacion creada - Estado inicial'
            ]);

            \Log::info("Historial inicial generado para operacion ID: {$operacion->id}");

        } catch (\Exception $e) {
            \Log::error("Error generando historial inicial: " . $e->getMessage());
        }
    }

    /**
     * Obtener el historial de una operacion
     * Ahora incluye todas las operaciones del mismo cliente y No Ped si estn disponibles
     */
    public function obtenerHistorial($id)
    {
        try {
            $operacion = OperacionLogistica::with([
                'historicoMatrizSgm'
            ])->findOrFail($id);

            // Obtener historial de la operacion especfica
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
                        'dias_transcurridos' => (int)round($registro->dias_transcurridos ?? 0),
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
                    ->limit(5) // Limitar a las 5 ms recientes
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
                    'status_actual' => $operacion->status_actual,
                    'color_status' => $operacion->color_status,
                    'target' => $operacion->target,
                    'resultado' => $operacion->resultado,
                    'dias_transito' => $operacion->dias_transito,
                    
                    // Columnas opcionales adicionales
                    'tipo_carga' => $operacion->tipo_carga,
                    'tipo_incoterm' => $operacion->tipo_incoterm,
                    'puerto_salida' => $operacion->puerto_salida,
                    'in_charge' => $operacion->in_charge,
                    'proveedor' => $operacion->proveedor,
                    'tipo_previo' => $operacion->tipo_previo,
                    'fecha_etd' => $operacion->fecha_etd ? $operacion->fecha_etd->format('Y-m-d') : null,
                    'fecha_zarpe' => $operacion->fecha_zarpe ? $operacion->fecha_zarpe->format('Y-m-d') : null,
                    'pedimento_en_carpeta' => $operacion->pedimento_en_carpeta,
                    'referencia_cliente' => $operacion->referencia_cliente,
                    'mail_subject' => $operacion->mail_subject,
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
                'message' => $historial->count() > 0 ? 'Historial cargado correctamente' : 'Historial generado automticamente'
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
     * Actualizar solo el status manual de una operacion (solo se puede cambiar a 'Done')
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $operacion = OperacionLogistica::findOrFail($id);

            $request->validate([
                'status' => 'required|in:Done'
            ]);

            // NUEVA LGICA: Solo actualizar el status MANUAL, no el automtico
            $operacion->status_manual = 'Done';
            $operacion->fecha_status_manual = now();

            // Recalcular el status automtico (que tomar en cuenta el status manual)
            $resultado = $operacion->calcularStatusPorDias();

            // Generar historial especfico para accin manual
            $operacion->generarHistorialCambioStatus(
                $resultado,
                true, // Es accin manual
                'operacion marcada como completada manualmente por el usuario'
            );

            // Guardar cambios
            $operacion->save();

            return response()->json([
                'success' => true,
                'message' => 'operacion marcada como completada exitosamente',
                'operacion' => $operacion->load(['ejecutivo', 'postoperacion'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una operacion
     */
    public function destroy($id)
    {
        try {
            $operacion = OperacionLogistica::findOrFail($id);

            // Eliminar registros del historial primero (por integridad referencial)
            $operacion->historicoMatrizSgm()->delete();

            // Eliminar la operacion
            $operacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'operacion eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la operacion: ' . $e->getMessage()
            ], 500);
        }
    }

    // =================================
    // MTODOS PARA POST-operacionES
    // =================================

    /**
     * Listar todas las post-operaciones
     */
    public function indexPostoperaciones()
    {
        try {
            $postoperaciones = PostOperacion::with('operacionLogistica')
                ->orderBy('created_at', 'desc')
                ->get();

            $postoperacionesData = $postoperaciones->map(function($postOp) {
                return [
                    'id' => $postOp->id,
                    'nombre' => $postOp->nombre,
                    'descripcion' => $postOp->descripcion,
                    'status' => $postOp->status ?? 'Pendiente',
                    'operacion_relacionada' => $postOp->operacionLogistica
                        ? ($postOp->operacionLogistica->operacion ?? 'operacion #' . $postOp->operacionLogistica->id)
                        : 'Sin operacion especfica',
                    'fecha_creacion' => $postOp->created_at ? $postOp->created_at->format('d/m/Y') : '-',
                    'fecha_completado' => $postOp->fecha_completado ? $postOp->fecha_completado->format('d/m/Y H:i') : null
                ];
            });

            return response()->json([
                'success' => true,
                'postoperaciones' => $postoperacionesData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar post-operaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva post-operacion
     */
    public function storePostoperacion(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'operacion_logistica_id' => 'nullable|exists:operaciones_logisticas,id'
            ]);

            $postoperacion = PostOperacion::create([
                'nombre' => $validatedData['nombre'],
                'descripcion' => $validatedData['descripcion'] ?? null,
                'operacion_logistica_id' => $validatedData['operacion_logistica_id'] ?? null,
                'status' => 'Pendiente',
                'fecha_creacion' => now()
            ]);

            return response()->json([
                'success' => true,
                'postoperacion' => $postoperacion,
                'message' => 'Post-operacion creada exitosamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validacion incorrectos: ' . implode(', ', $e->validator->errors()->all())
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear post-operacion: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar post-operacion como completada
     */
    public function markPostoperacionDone($id)
    {
        try {
            $postoperacion = PostOperacion::findOrFail($id);

            $postoperacion->update([
                'status' => 'Completado',
                'fecha_completado' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post-operacion marcada como completada'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar post-operacion como completada: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar post-operacion
     */
    public function destroyPostoperacion($id)
    {
        try {
            $postoperacion = PostOperacion::findOrFail($id);
            $postoperacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post-operacion eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar post-operacion: ' . $e->getMessage()
            ], 500);
        }
    }

    // =================================
    // MTODOS PARA POST-operacionES POR operacion
    // =================================

    /**
     * Obtener post-operaciones de una operacion especfica
     */
    public function getPostoperacionesByoperacion($operacionId)
    {
        try {
            // Validar que el ID sea válido
            if (!$operacionId || !is_numeric($operacionId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de operacion invalido',
                    'postOperaciones' => []
                ], 400);
            }

            // Obtener informacin de la operacion
            $operacion = OperacionLogistica::find($operacionId);

            if (!$operacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'operacion no encontrada',
                    'postOperaciones' => []
                ], 404);
            }

            // Obtener SOLO las asignaciones especficas de esta operacion (no todas las plantillas)
            $asignaciones = PostoperacionOperacion::where('operacion_logistica_id', $operacionId)
                ->with('postoperacion')
                ->orderBy('created_at', 'desc')
                ->get();

            // Mapear solo las post-operaciones que están asignadas a esta operación
            $postoperacionesData = $asignaciones->map(function($asignacion) {
                $postOpGlobal = $asignacion->postoperacion;
                
                if (!$postOpGlobal) {
                    return null;
                }

                return [
                    'id_global' => $postOpGlobal->id,
                    'id_asignacion' => $asignacion->id,
                    'nombre' => $postOpGlobal->nombre,
                    'descripcion' => $postOpGlobal->descripcion,
                    'status' => $asignacion->status ?? 'Pendiente',
                    'fecha_creacion' => $postOpGlobal->created_at ? $postOpGlobal->created_at->format('d/m/Y H:i') : '-',
                    'fecha_asignacion' => $asignacion->fecha_asignacion ? $asignacion->fecha_asignacion->format('d/m/Y H:i') : null,
                    'fecha_completado' => $asignacion->fecha_completado ? $asignacion->fecha_completado->format('d/m/Y H:i') : null,
                    'notas_especificas' => $asignacion->notas_especificas,
                    'es_plantilla' => false, // Siempre false porque solo mostramos las asignadas
                ];
            })->filter(); // Eliminar nulls

            return response()->json([
                'success' => true,
                'postOperaciones' => $postoperacionesData->values(),
                'operacion_info' => [
                    'id' => $operacion->id,
                    'no_pedimento' => $operacion->no_pedimento
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al cargar post-operaciones para operacion ' . $operacionId . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar post-operaciones: ' . $e->getMessage(),
                'postOperaciones' => []
            ], 500);
        }
    }

    /**
     * Actualizar estado de post-operacion (Completado/No Aplica)
     */
    public function updatePostoperacionEstado(Request $request, $id)
    {
        try {
            $postoperacion = PostOperacion::findOrFail($id);

            $validatedData = $request->validate([
                'estado' => 'required|in:Completado,No Aplica,Pendiente'
            ]);

            $postoperacion->update([
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
    // MTODOS PARA POST-operacionES GLOBALES
    // =================================

    /**
     * Listar post-operaciones globales (plantillas)
     */
    public function indexPostoperacionesGlobales()
    {
        try {
            $postoperaciones = PostOperacion::whereNull('operacion_logistica_id')
                ->orderBy('nombre')
                ->get();

            $postoperacionesData = $postoperaciones->map(function($postOp) {
                return [
                    'id' => $postOp->id,
                    'nombre' => $postOp->nombre,
                    'descripcion' => $postOp->descripcion,
                    'fecha_creacion' => $postOp->created_at ? $postOp->created_at->format('d/m/Y') : '-'
                ];
            });

            return response()->json([
                'success' => true,
                'postoperaciones' => $postoperacionesData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar post-operaciones globales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear post-operacion global (plantilla)
     */
    public function storePostoperacionGlobal(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string'
            ]);

            $postoperacion = PostOperacion::create([
                'nombre' => $validatedData['nombre'],
                'descripcion' => $validatedData['descripcion'] ?? null,
                'operacion_logistica_id' => null, // Global
                'status' => 'Plantilla',
                'fecha_creacion' => now()
            ]);

            return response()->json([
                'success' => true,
                'postoperacion' => $postoperacion,
                'message' => 'Post-operacion global creada exitosamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validacion incorrectos: ' . implode(', ', $e->validator->errors()->all())
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear post-operacion global: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar post-operacion global
     */
    public function destroyPostoperacionGlobal($id)
    {
        try {
            $postoperacion = PostOperacion::whereNull('operacion_logistica_id')->findOrFail($id);
            $postoperacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post-operacion global eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar post-operacion global: ' . $e->getMessage()
            ], 500);
        }
    }

    // =================================
    // MTODOS PARA COMENTARIOS
    // =================================

    /**
     * Obtener comentarios de una operacion
     */
    public function getComentariosByoperacion($operacionId)
    {
        try {
            // Primero obtenemos la operacion
            $operacion = OperacionLogistica::findOrFail($operacionId);

            // Por ahora usamos el campo comentarios de la operacion
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

            // Por ahora guardamos en el campo comentarios de la operacion
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
            // Debug: Log los datos que llegan
            \Log::info('updateComentario recibido:', [
                'comentario_id' => $id,
                'request_data' => $request->all(),
                'comentario_value' => $request->input('comentario'),
                'comentario_length' => strlen($request->input('comentario', ''))
            ]);

            // validacion ms flexible temporalmente
            $comentarioTexto = trim($request->input('comentario', ''));

            if (empty($comentarioTexto)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El comentario no puede estar vaco'
                ], 400);
            }

            $validatedData = ['comentario' => $comentarioTexto];

            // Buscar el comentario especfico a actualizar
            $comentario = OperacionComentario::findOrFail($id);

            // Verificar que el comentario a editar no sea del sistema
            if (in_array($comentario->usuario_nombre, ['Sistema', 'Sistema Automtico', 'Sistema de Prueba'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden editar comentarios del sistema'
                ], 403);
            }

            // Actualizar solo el texto del comentario (sin crear nueva entrada)
            $comentario->update([
                'comentario' => $validatedData['comentario']
            ]);

            // Actualizar tambin el campo legacy en la operacion principal
            $operacion = $comentario->operacionLogistica;
            if ($operacion) {
                $operacion->update([
                    'comentarios' => $validatedData['comentario'] . " (ltima actualizacin: " . now()->format('d/m/Y H:i') . ")"
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Comentario actualizado exitosamente',
                'comentario' => $comentario->fresh()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en updateComentario:', [
                'comentario_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar comentario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el historial de observaciones de una operacion
     */
    public function obtenerHistorialObservaciones($operacionId)
    {
        try {
            $operacion = OperacionLogistica::with(['ejecutivo'])
                ->findOrFail($operacionId);

            // Obtener el historial de observaciones ordenado cronolgicamente
            $historialObservaciones = $operacion->historicoMatrizSgm()
                ->whereNotNull('observaciones')
                ->where('observaciones', '!=', '')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($registro) {
                    return [
                        'id' => $registro->id,
                        'observaciones' => $registro->observaciones,
                        'status' => $registro->operacion_status ?? $registro->status ?? 'N/A',
                        'created_at' => $registro->created_at,
                        'updated_at' => $registro->updated_at,
                        'usuario' => 'Sistema', // Por ahora ser "Sistema" hasta que agreguemos el campo empleado_id
                        'fecha_formateada' => $registro->created_at->format('d/m/Y H:i'),
                        'tiempo_relativo' => $registro->created_at->diffForHumans()
                    ];
                });

            // Obtener observaciones actuales de la operacion
            $observacionActual = $operacion->comentarios ?? '';

            return response()->json([
                'success' => true,
                'observaciones' => $historialObservaciones,
                'operacion' => [
                    'id' => $operacion->id,
                    'operacion' => $operacion->operacion,
                    'cliente' => $operacion->cliente,
                    'no_pedimento' => $operacion->no_pedimento,
                    'status_actual' => $operacion->status_calculado,
                    'observacion_actual' => $observacionActual
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en obtenerHistorialObservaciones:', [
                'operacion_id' => $operacionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial de observaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar directamente las observaciones del historial
     */
    public function updateObservacionesHistorial(Request $request, $operacionId)
    {
        try {
            $comentarioTexto = trim($request->input('observaciones', ''));

            if (empty($comentarioTexto)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Las observaciones no pueden estar vacas'
                ], 400);
            }

            $operacion = OperacionLogistica::findOrFail($operacionId);

            // Obtener el registro ms reciente del historial para copiar sus datos
            $historialReciente = $operacion->historicoMatrizSgm()
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$historialReciente) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontr historial base para crear el nuevo registro'
                ], 404);
            }

            // Crear un NUEVO registro en el historial manteniendo los datos del status actual
            // pero con las nuevas observaciones
            $nuevoHistorial = $operacion->historicoMatrizSgm()->create([
                'fecha_registro' => now(),
                'fecha_arribo_aduana' => $historialReciente->fecha_arribo_aduana,
                'dias_transcurridos' => (int)round($historialReciente->dias_transcurridos ?? 0),
                'target_dias' => $historialReciente->target_dias,
                'color_status' => $historialReciente->color_status,
                'operacion_status' => $historialReciente->operacion_status,
                'observaciones' => $comentarioTexto
            ]);

            // Tambin actualizar el campo comentarios de la operacion principal
            $operacion->update(['comentarios' => $comentarioTexto]);

            \Log::info('Nuevo registro de historial creado:', [
                'operacion_id' => $operacionId,
                'historial_id' => $nuevoHistorial->id,
                'observaciones' => $comentarioTexto
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Observaciones guardadas exitosamente en el historial'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en updateObservacionesHistorial:', [
                'operacion_id' => $operacionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar observaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar estados de mltiples post-operaciones asociadas a una operacion
     * Usa tabla pivot para mantener limpia la separacin entre plantillas y asignaciones
     */
    public function actualizarEstadosPostoperaciones(Request $request, $operacionId)
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
                $postoperacionId = $esPlantilla ? $idGlobal : $postOpId;

                if (!$postoperacionId) {
                    continue;
                }

                // Verificar que la post-operacion global existe
                $postoperacionGlobal = PostOperacion::find($postoperacionId);
                if (!$postoperacionGlobal) {
                    continue;
                }

                // Buscar si ya existe una asignacin para esta operacion
                $asignacionExistente = PostoperacionOperacion::where('post_operacion_id', $postoperacionId)
                    ->where('operacion_logistica_id', $operacionId)
                    ->first();

                if ($estado === 'Pendiente') {
                    // Si se marca como pendiente y existe asignacin, eliminarla
                    if ($asignacionExistente) {
                        $asignacionExistente->delete();
                        $actualizados++;
                    }
                } else {
                    // Crear o actualizar asignacin para estados Completado/No Aplica
                    if ($asignacionExistente) {
                        // Actualizar asignacin existente
                        $asignacionExistente->status = $estado;
                        $asignacionExistente->fecha_completado = $estado === 'Completado' ? now() : null;
                        $asignacionExistente->save();
                        $actualizados++;
                    } else {
                        // Crear nueva asignacin
                        PostoperacionOperacion::create([
                            'post_operacion_id' => $postoperacionId,
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
                'message' => "operacion completada: {$creados} asignaciones creadas, {$actualizados} actualizadas",
                'creados' => $creados,
                'actualizados' => $actualizados
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos invlidos: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar post-operaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalcular automticamente todos los status de las operaciones usando nueva lgica
     */
    public function recalcularStatus()
    {
        try {
            $operaciones = OperacionLogistica::all();
            $actualizadas = 0;
            $historialesGenerados = 0;

            foreach ($operaciones as $operacion) {
                // Usar la nueva lgica de clculo por das
                $resultado = $operacion->actualizarStatusAutomaticamente(false); // No guardar an

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
     * Calcular el status de una operacion basado en sus fechas
     */
    private function calcularStatusoperacion($operacion)
    {
        if ($operacion->fecha_arribo_planta) {
            return 'Entregado';
        } elseif ($operacion->fecha_modulacion) {
            return 'Modulado';
        } elseif ($operacion->fecha_arribo_aduana) {
            return 'En Aduana';
        } elseif ($operacion->fecha_embarque) {
            return 'En Trnsito';
        } else {
            return 'Pendiente';
        }
    }

    /**
     * Calcular das transcurridos: fecha embarque vs fecha arribo a planta (o fecha actual si no hay arribo)
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
     * Determinar color del status basado en target y das transcurridos
     */
    private function determinarColorStatus($operacion, $status, $diasTranscurridos)
    {
        if ($status === 'Entregado') {
            // Si ya se entreg, verificar si fue dentro del target
            $target = $operacion->target ?? $operacion->dias_transito ?? 30;
            return $diasTranscurridos <= $target ? 'green' : 'red';
        }

        // Para operaciones en curso
        $target = $operacion->target ?? $operacion->dias_transito ?? 30;

        if ($diasTranscurridos > $target) {
            return 'red'; // Fuera de METRICA
        } elseif ($diasTranscurridos >= ($target * 0.8)) {
            return 'yellow'; // Cerca del lmite
        } else {
            return 'green'; // Dentro de METRICA
        }
    }

    /**
     * Verificar y actualizar automticamente el status de operaciones al consultar
     * Solo actualiza operaciones que han cambiado desde la ltima verificacin
     */
    private function verificarYActualizarStatusoperaciones()
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
                \Log::info("VERIFICACION AUTOMATICA: {$actualizadas} operaciones actualizadas");
            }

        } catch (\Exception $e) {
            \Log::error("Error en VERIFICACION AUTOMATICA de status: " . $e->getMessage());
        }
    }

    /**
     * Generar reporte Word de una operacion especfica
     */
    public function generarReporteWord($id)
    {
        try {
            $operacion = OperacionLogistica::with([
                'ejecutivo',
                'cliente',
                'agenteAduanal',
                'transporte',
                'postoperaciones',
                'historial' => function($query) {
                    $query->orderBy('created_at', 'desc')->limit(20);
                }
            ])->findOrFail($id);

            $wordService = new WordDocumentService();
            $wordService->crearReporteoperacion($operacion);

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
     * Generar reporte Word de mltiples operaciones (con filtros)
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

            // Limitar a mximo 100 operaciones para evitar documentos muy grandes
            $operaciones = $query->orderBy('created_at', 'desc')->limit(100)->get();

            if ($operaciones->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron operaciones con los filtros aplicados'
                ], 404);
            }

            $wordService = new WordDocumentService();

            $titulo = 'REPORTE DE operacionES LOGISTICAS';
            if ($request->filled('cliente')) {
                $titulo .= ' - ' . $request->cliente;
            }

            $wordService->crearReporteMultiple($operaciones, $titulo);

            $nombreArchivo = 'reporte_operaciones_' . date('Y-m-d_H-i-s') . '.docx';

            // Descargar directamente
            $wordService->descargar($nombreArchivo);

        } catch (\Exception $e) {
            \Log::error("Error generando reporte mltiple Word: " . $e->getMessage());
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
                'postoperaciones',
                'historial' => function($query) {
                    $query->orderBy('created_at', 'desc')->limit(20);
                }
            ])->findOrFail($id);

            $wordService = new WordDocumentService();
            $wordService->crearReporteoperacion($operacion);

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

            // Buscar empleados que no sean ya ejecutivos de LOGISTICA
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
     * Agregar empleado como ejecutivo de LOGISTICA
     */
    public function addEjecutivo(Request $request)
    {
        try {
            $request->validate([
                'empleado_id' => 'required|exists:empleados,id'
            ]);

            $empleado = Empleado::findOrFail($request->empleado_id);

            // Verificar que no sea ya ejecutivo de LOGISTICA
            if ($empleado->area === 'Logistica') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este empleado ya es ejecutivo de LOGISTICA'
                ], 422);
            }

            // Actualizar el rea del empleado a LOGISTICA
            $empleado->update([
                'area' => 'Logistica'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Empleado agregado como ejecutivo de LOGISTICA exitosamente',
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

    // === MTODOS DE IMPORTACIN DE CLIENTES ===

    public function importClientes(Request $request)
    {
        try {
            $request->validate([
                'clientes_file' => 'required|file|mimes:xlsx,xls|max:10240' // 10MB mximo
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

            Log::info("Iniciando importacin de clientes desde archivo: {$filename}");
            Log::info("Intentando guardar archivo en: {$fullPath}");
            Log::info("Directorio de destino existe: " . (file_exists($uploadsDir) ? 'S' : 'No'));
            Log::info("Archivo original vlido: " . ($file->isValid() ? 'S' : 'No'));

            try {
                // Usar mtodo directo para guardar el archivo
                $saved = $file->move($uploadsDir, $filename);

                if ($saved) {
                    Log::info("Archivo guardado exitosamente usando move()");
                    Log::info("??Archivo existe despus de move()? " . (file_exists($fullPath) ? 'S' : 'No'));
                    Log::info("Tamao del archivo guardado: " . (file_exists($fullPath) ? filesize($fullPath) . ' bytes' : 'N/A'));
                } else {
                    throw new \Exception("El mtodo move() retorn false");
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
                'message' => 'Importacin completada exitosamente',
                'resultados' => $resultados
            ]);

        } catch (\Exception $e) {
            Log::error("Error en importacin de clientes: " . $e->getMessage());
            $response = response()->json([
                'success' => false,
                'message' => 'Error al importar clientes: ' . $e->getMessage()
            ], 500);
        } finally {
            // Limpiar archivo temporal siempre, sin importar si hubo xito o error
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
                    'message' => 'No tienes permisos para realizar esta accin'
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

    // === MTODOS DE IMPORTACIN DE PEDIMENTOS ===

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
                throw new \Exception('No se ha proporcionado ningn archivo de pedimentos.');
            }

            // Crear directorio si no existe
            $uploadsDir = storage_path('app/uploads/pedimentos');
            if (!file_exists($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
                Log::info("Directorio creado: {$uploadsDir}");
            }

            $filename = 'pedimentos_' . time() . '.' . $file->getClientOriginalExtension();
            $fullPath = $uploadsDir . DIRECTORY_SEPARATOR . $filename;

            Log::info("Iniciando importacin de pedimentos desde archivo: {$filename}");
            Log::info("Intentando guardar archivo en: {$fullPath}");
            Log::info("Directorio de destino existe: " . (file_exists($uploadsDir) ? 'S' : 'No'));
            Log::info("Archivo original vlido: " . ($file->isValid() ? 'S' : 'No'));

            try {
                // Usar mtodo directo para guardar el archivo
                $saved = $file->move($uploadsDir, $filename);

                if ($saved) {
                    Log::info("Archivo guardado exitosamente usando move()");
                    Log::info("??Archivo existe despus de move()? " . (file_exists($fullPath) ? 'S' : 'No'));
                    Log::info("Tamao del archivo guardado: " . (file_exists($fullPath) ? filesize($fullPath) . ' bytes' : 'N/A'));
                } else {
                    throw new \Exception("El mtodo move() retorn false");
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
                $message = "Importacin completada: {$totalImported} pedimentos importados";
                if ($totalSkipped > 0) {
                    $message .= ", {$totalSkipped} omitidos";
                }
                $response = back()->with('success', $message);
            } else {
                $message = $resultados['message'] ?? 'Error en la importacin';
                $response = back()->with('error', $message);
            }

        } catch (\Exception $e) {
            Log::error("Error en importacin de pedimentos: " . $e->getMessage());
            $response = back()->with('error', 'Error al importar pedimentos: ' . $e->getMessage());
        } finally {
            // Limpiar archivo temporal siempre, sin importar si hubo xito o error
            if (isset($fullPath) && file_exists($fullPath)) {
                unlink($fullPath);
                Log::info("Archivo temporal eliminado: {$fullPath}");
            } elseif (isset($fullPath)) {
                Log::warning("Archivo temporal no encontrado para eliminar: {$fullPath}");
            }
        }

        return $response ?? back()->with('error', 'Error desconocido en la importacin');
    }

    /**
     * Vista pblica para consulta de operaciones (sin autenticacin)
     */
    public function consultaPublica()
    {
        return view('Logistica.consulta-publica');
    }

    /**
     * Bsqueda pblica de operacion por pedimento o factura
     */
    public function buscaroperacionPublica(Request $request)
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
                    'message' => 'No se encontro ninguna operacion con ese ' . ($tipoBusqueda === 'pedimento' ? 'numero de pedimento' : 'numero de factura')
                ]);
            }

            // Cargar relaciones con verificacin
            $historial = \App\Models\Logistica\HistoricoMatrizSgm::where('operacion_logistica_id', $operacion->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Obtener post-operaciones a travs de la tabla pivot
            $postoperaciones = \App\Models\Logistica\PostoperacionOperacion::where('operacion_logistica_id', $operacion->id)
                ->with('postoperacion')
                ->orderBy('created_at', 'desc')
                ->get();

            // Obtener todos los comentarios de la matriz de seguimiento (tabla comentarios)
            $comentariosMatriz = \App\Models\Logistica\OperacionComentario::where('operacion_logistica_id', $operacion->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($comentario) use ($operacion) {
                    // Extraer solo la parte después de "Comentarios:" si existe
                    $comentarioTexto = $comentario->comentario;
                    if (strpos($comentarioTexto, 'Comentarios: ') !== false) {
                        $comentarioExtraido = trim(substr($comentarioTexto, strpos($comentarioTexto, 'Comentarios: ') + 13));
                        if (!empty($comentarioExtraido)) {
                            $comentarioTexto = $comentarioExtraido;
                        }
                    }

                    return [
                        'id' => $comentario->id,
                        'comentario' => $comentarioTexto,
                        'usuario' => $comentario->usuario_nombre ?? 'Sistema',
                        'fecha' => $comentario->created_at->format('d/m/Y H:i:s'),
                        'tipo_accion' => $comentario->tipo_accion,
                        'status_en_momento' => $comentario->status_en_momento,
                    ];
                });

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
                    'dias_transcurridos' => (int)round($operacion->dias_transcurridos_calculados ?? 0),
                    'comentarios' => $operacion->comentarios,
                ],
                'comentarios_matriz' => $comentariosMatriz,
                'historial' => $historial->map(function($item) {
                    return [
                        'id' => $item->id,
                        'fecha' => $item->created_at->format('d/m/Y H:i:s'),
                        'status' => $item->operacion_status ?? 'N/A',
                        'color' => $item->color_status ?? 'sin_fecha',
                        'descripcion' => $item->observaciones ?? 'Sin observaciones',
                        'dias_transcurridos' => (int)round($item->dias_transcurridos ?? 0),
                        'target_dias' => $item->target_dias,
                        'fecha_arribo_aduana' => $item->fecha_arribo_aduana?->format('d/m/Y'),
                        'fecha_registro' => $item->fecha_registro?->format('d/m/Y'),
                    ];
                }),
                'post_operaciones' => $postoperaciones->map(function($item) {
                    return [
                        'id' => $item->id,
                        'nombre' => $item->postoperacion->nombre ?? 'N/A',
                        'descripcion' => $item->postoperacion->descripcion ?? '',
                        'status' => $item->status,
                        'fecha_asignacion' => $item->fecha_asignacion?->format('d/m/Y'),
                        'fecha_completado' => $item->fecha_completado?->format('d/m/Y'),
                        'notas_especificas' => $item->notas_especificas,
                    ];
                })
            ];

            return response()->json($data);

        } catch (\Exception $e) {
            \Log::error('Error en bsqueda pblica: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar la bsqueda. Por favor, intente nuevamente.'
            ], 500);
        }
    }

    /**
     * Enviar reporte por correo con CC automtico al administrador de LOGISTICA
     */
    public function enviarReporte(Request $request)
    {
        try {
            // Log para debug
            \Log::info('Datos recibidos en enviarReporte:', $request->all());

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'destinatarios' => 'required|string|min:1',
                'asunto' => 'required|string|max:255|min:1',
                'mensaje' => 'required|string|min:1',
                'incluir_datos' => 'nullable', // Puede ser string "true"/"false" o boolean
                'formato_datos' => 'nullable|string',
                'operaciones_ids' => 'nullable|string', // Viene como JSON string
                'correos_cc' => 'nullable|string', // Viene como JSON string
                'cliente' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                \Log::error('validacion fallida en enviarReporte:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Procesar incluir_datos
            $incluirDatos = in_array($request->incluir_datos, ['1', 'true', true], true);

            // Obtener correos CC desde el frontend (ya filtrados por el usuario)
            $correosCC = [];
            if ($request->correos_cc) {
                $correosCCDecoded = json_decode($request->correos_cc, true);
                if (is_array($correosCCDecoded)) {
                    $correosCC = $correosCCDecoded;
                }
            }

            // Preparar los destinatarios principales
            $destinatariosPrincipales = array_filter(array_map('trim', explode(',', $request->destinatarios)));

            // Preparar datos del correo
            $datosCorreo = [
                'destinatarios' => $destinatariosPrincipales,
                'correosCC' => $correosCC,
                'asunto' => $request->asunto,
                'mensaje' => $request->mensaje,
                'remitente' => auth()->user()->email ?? 'sistemas@estrategiaeinnovacion.com.mx',
                'nombreRemitente' => auth()->user()->name ?? 'Sistema de LOGISTICA'
            ];

            // Si se solicita incluir datos, preparar el archivo adjunto
            if ($incluirDatos) {
                // USAR EXACTAMENTE EL MISMO PROCESO QUE EXPORTCSV
                $usuarioActual = auth()->user();
                $empleadoActual = null;
                $esAdmin = false;

                if ($usuarioActual) {
                    $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                        ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                        ->first();
                    $esAdmin = $usuarioActual->hasRole('admin');
                }

                // Construir query con los mismos filtros que usa exportCSV
                $query = OperacionLogistica::with(['ejecutivo', 'asignacionesPostOperaciones.postOperacion', 'valoresCamposPersonalizados.campo']);

                // Aplicar filtros de permisos
                if (!$esAdmin && $empleadoActual) {
                    $query->where('ejecutivo', $empleadoActual->nombre);
                }

                // Aplicar todos los filtros de la request (MISMOS que usa exportCSV)
                \Log::info('Filtros aplicados en enviarReporte:', $request->all());
                $this->aplicarFiltrosReporte($query, $request);

                // Obtener operaciones con los MISMOS filtros que exportCSV
                $operaciones = $query->get();
                \Log::info('Operaciones obtenidas después de aplicar filtros en enviarReporte:', ['total' => $operaciones->count()]);

                $formato = $request->formato_datos ?? 'csv';
                $archivoInfo = $this->generarArchivoReporte($operaciones, $formato);
                $datosCorreo['adjunto'] = $archivoInfo;

                // Generar asunto personalizado basado en el cliente
                $asuntoPersonalizado = $request->asunto;
                if ($archivoInfo['cliente_especifico']) {
                    $asuntoPersonalizado = "Reporte {$archivoInfo['nombre_cliente']} - {$archivoInfo['fecha_envio']}";
                }
            } else {
                $asuntoPersonalizado = $request->asunto;
            }

            // Preparar respuesta para abrir Outlook y descargar archivo
            $respuesta = [
                'success' => true,
                'message' => 'Datos preparados para Outlook',
                'outlook_data' => [
                    'to' => implode(';', $destinatariosPrincipales),
                    'cc' => implode(';', $correosCC),
                    'subject' => $asuntoPersonalizado,
                    'body' => $request->mensaje
                ],
                'detalles' => [
                    'destinatarios_principales' => count($destinatariosPrincipales),
                    'correos_cc' => count($correosCC),
                    'cc_incluidos' => $correosCC
                ]
            ];

            // Si se solicita incluir datos, preparar descarga del archivo
            if ($incluirDatos) {
                // USAR EXACTAMENTE EL MISMO PROCESO QUE EXPORTCSV (duplicado para la descarga)
                $usuarioActual = auth()->user();
                $empleadoActual = null;
                $esAdmin = false;

                if ($usuarioActual) {
                    $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                        ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                        ->first();
                    $esAdmin = $usuarioActual->hasRole('admin');
                }

                // Construir query con los mismos filtros que usa exportCSV
                $query = OperacionLogistica::with(['ejecutivo', 'asignacionesPostOperaciones.postOperacion', 'valoresCamposPersonalizados.campo']);

                // Aplicar filtros de permisos
                if (!$esAdmin && $empleadoActual) {
                    $query->where('ejecutivo', $empleadoActual->nombre);
                }

                // Aplicar todos los filtros de la request (MISMOS que usa exportCSV)
                $this->aplicarFiltrosReporte($query, $request);

                // Obtener operaciones con los MISMOS filtros que exportCSV
                $operaciones = $query->get();

                $formato = $request->formato_datos ?? 'csv';
                $archivoInfo = $this->generarArchivoReporte($operaciones, $formato);

                // Crear URL de descarga temporal con timestamp nico
                $nombreArchivoDescarga = time() . '_' . basename($archivoInfo['path']);
                $rutaPublica = 'temp/' . $nombreArchivoDescarga;

                // Copiar archivo a directorio pblico temporal con manejo de errores robusto
                $rutaPublicaCompleta = public_path($rutaPublica);
                $directorioTemp = dirname($rutaPublicaCompleta);
                
                try {
                    if (!file_exists($directorioTemp)) {
                        // Crear directorio con permisos ms amplios para producción
                        if (!mkdir($directorioTemp, 0775, true)) {
                            throw new \Exception("No se pudo crear el directorio temporal");
                        }
                        // Intentar cambiar propietario si es posible
                        if (function_exists('chown') && is_executable('/usr/bin/chown')) {
                            @chown($directorioTemp, 'www-data');
                            @chgrp($directorioTemp, 'www-data');
                        }
                    }
                    
                    // Verificar que el directorio sea escribible
                    if (!is_writable($directorioTemp)) {
                        throw new \Exception("El directorio temporal no tiene permisos de escritura: " . $directorioTemp);
                    }
                    
                    // Intentar copiar archivo
                    if (!copy($archivoInfo['path'], $rutaPublicaCompleta)) {
                        throw new \Exception("Error al copiar archivo a directorio temporal");
                    }
                    
                } catch (\Exception $e) {
                    // Fallback: usar storage/app/public como alternativa
                    \Log::warning("Error con directorio public/temp, usando storage como alternativa: " . $e->getMessage());
                    
                    $rutaStorage = 'temp/' . $nombreArchivoDescarga;
                    $rutaStorageCompleta = storage_path('app/public/' . $rutaStorage);
                    $directorioStorageTemp = dirname($rutaStorageCompleta);
                    
                    if (!file_exists($directorioStorageTemp)) {
                        mkdir($directorioStorageTemp, 0775, true);
                    }
                    
                    copy($archivoInfo['path'], $rutaStorageCompleta);
                    $rutaPublica = 'storage/' . $rutaStorage;
                }

                $respuesta['download_url'] = url($rutaPublica);
                $respuesta['download_filename'] = $archivoInfo['nombre'];

                // Programar limpieza del archivo temporal (opcional - se puede hacer manualmente)
                // El archivo se eliminar despus de 1 hora
                $this->programarLimpiezaArchivo($rutaPublicaCompleta);
            }

            return response()->json($respuesta);

        } catch (\Exception $e) {
            \Log::error('Error al enviar reporte: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al enviar el correo: ' . $e->getMessage(),
                'error_details' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * FUNCIN DESHABILITADA: Los correos ahora se envan solo por webhook
     *
     * Esta funcin se mantuvo comentada para referencia histrica,
     * pero todos los envos de correo se procesan a travs del webhook de N8N
     */
    /*
    private function procesarEnvioCorreo($datos)
    {
        // FUNCIN DESHABILITADA - SE USA WEBHOOK
        return [
            'success' => false,
            'message' => 'Funcin deshabilitada - usar webhook'
        ];
    }
    */    /**
     * Generar archivo de reporte para adjuntar
     */
    private function generarArchivoReporte($operaciones, $formato)
    {
        $timestamp = date('Y-m-d_H-i-s');
        $fechaEnvio = date('d-m-Y');

        // Crear directorio temporal si no existe
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Obtener campos personalizados y configuración de columnas para el usuario actual
        $camposPersonalizados = collect();
        $configColumnas = [];
        $usuarioActual = auth()->user();
        $empleadoActual = null;
        
        if ($usuarioActual) {
            $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                ->first();
            $esAdmin = $usuarioActual->hasRole('admin');

            if ($esAdmin) {
                // Admin ve todos los campos activos
                $camposPersonalizados = CampoPersonalizadoMatriz::where('activo', true)
                    ->orderBy('orden')
                    ->get();
            } elseif ($empleadoActual) {
                // Usuario normal ve solo campos asignados a él
                $camposPersonalizados = CampoPersonalizadoMatriz::where('activo', true)
                    ->whereHas('ejecutivos', function($q) use ($empleadoActual) {
                        $q->where('empleados.id', $empleadoActual->id);
                    })
                    ->orderBy('orden')
                    ->get();
            }
            
            // Obtener configuración de columnas visibles del ejecutivo
            if ($empleadoActual) {
                $columnasVisibles = ColumnaVisibleEjecutivo::getColumnasVisiblesParaEjecutivo($empleadoActual->id);
                $columnasOcultas = ColumnaVisibleEjecutivo::getColumnasPredeterminadasOcultas($empleadoActual->id);
                $idioma = ColumnaVisibleEjecutivo::getIdiomaEjecutivo($empleadoActual->id);
                
                $configColumnas = [
                    'columnas_visibles' => $columnasVisibles,
                    'columnas_ocultas' => $columnasOcultas,
                    'idioma' => $idioma
                ];
            }
        }

        // Obtener cliente(s) para el nombre del archivo
        $clientes = $operaciones->pluck('cliente')->unique()->filter();
        $clienteNombre = '';
        $esClienteEspecifico = false;
        
        if ($clientes->count() == 1) {
            // Un solo cliente
            $clienteNombre = $clientes->first();
            $esClienteEspecifico = true;
        } elseif ($clientes->count() > 1) {
            // Mltiples clientes
            $clienteNombre = 'Multiples_Clientes';
        } else {
            // Sin cliente especfico
            $clienteNombre = 'Todos_Clientes';
        }

        // Generar nombre de archivo según si es cliente específico o no
        if ($esClienteEspecifico) {
            // Para cliente específico: "[Nombre del Cliente] - [Fecha].xlsx"
            $nombreArchivo = "{$clienteNombre} - {$fechaEnvio}.xlsx";
        } else {
            // Para múltiples clientes o todos: formato original
            $clienteLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', $clienteNombre);
            $clienteLimpio = preg_replace('/_+/', '_', $clienteLimpio);
            $clienteLimpio = trim($clienteLimpio, '_');
            $nombreArchivo = "Reporte de operaciones logistica {$clienteLimpio} {$fechaEnvio}.xlsx";
        }

        if ($formato === 'csv') {
            $rutaCompleta = $tempDir . '/' . $nombreArchivo;
            $this->generarExcelTSV($operaciones, $rutaCompleta, $camposPersonalizados, $configColumnas);
            return [
                'path' => $rutaCompleta,
                'nombre' => $nombreArchivo,
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'cliente_especifico' => $esClienteEspecifico,
                'nombre_cliente' => $esClienteEspecifico ? $clienteNombre : null,
                'fecha_envio' => $fechaEnvio
            ];
        } elseif ($formato === 'excel') {
            // Usar el mismo nombreArchivo ya generado arriba
            $rutaCompleta = $tempDir . '/' . $nombreArchivo;

            // Preparar estadísticas para el Excel
            $estadisticas = $this->calcularEstadisticasReporte($operaciones);

            // Obtener columnas ordenadas para el ejecutivo actual
            $usuarioActual = auth()->user();
            $empleadoActual = null;
            $columnasOrdenadas = [];
            
            if ($usuarioActual) {
                $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                    ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                    ->first();
                
                if ($empleadoActual) {
                    $idioma = ColumnaVisibleEjecutivo::getIdiomaEjecutivo($empleadoActual->id);
                    $columnasOrdenadas = ColumnaVisibleEjecutivo::getColumnasOrdenadasParaEjecutivo($empleadoActual->id, $idioma);
                }
            }

            // Generar Excel profesional con gráficos y columnas ordenadas
            $excelService = new ExcelReportService();
            
            // Pasar las columnas ordenadas al servicio
            if (!empty($columnasOrdenadas)) {
                $excelService->setColumnasOrdenadas($columnasOrdenadas);
            }
            
            $excelService->generateLogisticsReport($operaciones, [], $estadisticas);
            $excelService->save($rutaCompleta);

            return [
                'path' => $rutaCompleta,
                'nombre' => $nombreArchivo,
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'cliente_especifico' => $esClienteEspecifico,
                'nombre_cliente' => $esClienteEspecifico ? $clienteNombre : null,
                'fecha_envio' => $fechaEnvio
            ];
        }

        return null;
    }

    /**
     * Calcular estadsticas para el reporte Excel
     */
    private function calcularEstadisticasReporte($operaciones)
    {
        $total = $operaciones->count();
        $completadas = $operaciones->where('status_manual', 'Done')->count();
        $enProceso = $operaciones->where(function($op) {
            return $op->status_manual !== 'Done' && $op->status_manual !== 'Out of Metric';
        })->count();
        $fueraMetrica = $operaciones->where('status_manual', 'Out of Metric')->count();

        return [
            'total' => $total,
            'completadas' => $completadas,
            'en_proceso' => $enProceso,
            'fuera_metrica' => $fueraMetrica,
            'eficiencia' => $total > 0 ? round(($completadas / $total) * 100, 1) : 0,
            'promedio_dias' => $total > 0 ? round($operaciones->avg(function($op) {
                return $op->calcularDiasTranscurridos();
            }), 1) : 0
        ];
    }

    /**
     * Generar archivo Excel con diseño profesional y columnas configuradas por ejecutivo
     * @param Collection $operaciones - Operaciones a exportar
     * @param string $rutaArchivo - Ruta donde guardar el archivo
     * @param Collection|null $camposPersonalizados - Campos personalizados del ejecutivo
     * @param array $configColumnas - Configuración de columnas visibles ['columnas_visibles' => [], 'columnas_ocultas' => [], 'idioma' => 'es']
     */
    private function generarExcelTSV($operaciones, $rutaArchivo, $camposPersonalizados = null, $configColumnas = [])
    {
        // Usar PhpSpreadsheet para generar Excel nativo con diseño
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Operaciones Logísticas');
        
        // Obtener configuración de columnas
        $columnasVisibles = $configColumnas['columnas_visibles'] ?? [];
        $columnasOcultas = $configColumnas['columnas_ocultas'] ?? [];
        $idioma = $configColumnas['idioma'] ?? 'es';
        
        // Nombres de columnas según idioma
        $nombresPredeterminados = ColumnaVisibleEjecutivo::$columnasPredeterminadas;
        $nombresOpcionales = ColumnaVisibleEjecutivo::$columnasOpcionales;
        
        // Mapeo de columnas base (predeterminadas) con sus claves para posicionamiento
        $columnasBaseFull = [
            'id' => $nombresPredeterminados['id'][$idioma] ?? 'No.',
            'ejecutivo' => $nombresPredeterminados['ejecutivo'][$idioma] ?? 'Ejecutivo',
            'operacion' => $nombresPredeterminados['operacion'][$idioma] ?? 'Operación',
            'cliente' => $nombresPredeterminados['cliente'][$idioma] ?? 'Cliente',
            'proveedor_o_cliente' => $nombresPredeterminados['proveedor_o_cliente'][$idioma] ?? 'Proveedor/Cliente',
            'fecha_embarque' => $nombresPredeterminados['fecha_embarque'][$idioma] ?? 'Fecha de Embarque',
            'no_factura' => $nombresPredeterminados['no_factura'][$idioma] ?? 'No. Factura',
            'tipo_operacion_enum' => $nombresPredeterminados['tipo_operacion_enum'][$idioma] ?? 'T. Operación',
            'clave' => $nombresPredeterminados['clave'][$idioma] ?? 'Clave',
            'referencia_interna' => $nombresPredeterminados['referencia_interna'][$idioma] ?? 'Referencia Interna',
            'aduana' => $nombresPredeterminados['aduana'][$idioma] ?? 'Aduana',
            'agente_aduanal' => $nombresPredeterminados['agente_aduanal'][$idioma] ?? 'A.A',
            'referencia_aa' => $nombresPredeterminados['referencia_aa'][$idioma] ?? 'Referencia A.A',
            'no_pedimento' => $nombresPredeterminados['no_pedimento'][$idioma] ?? 'No Ped',
            'transporte' => $nombresPredeterminados['transporte'][$idioma] ?? 'Transporte',
            'fecha_arribo_aduana' => $nombresPredeterminados['fecha_arribo_aduana'][$idioma] ?? 'Fecha Arribo Aduana',
            'guia_bl' => $nombresPredeterminados['guia_bl'][$idioma] ?? 'Guía/BL',
            'status' => $nombresPredeterminados['status'][$idioma] ?? 'Status',
            'fecha_modulacion' => $nombresPredeterminados['fecha_modulacion'][$idioma] ?? 'Fecha Modulación',
            'fecha_arribo_planta' => $nombresPredeterminados['fecha_arribo_planta'][$idioma] ?? 'Fecha Arribo Planta',
            'resultado' => $nombresPredeterminados['resultado'][$idioma] ?? 'Resultado',
            'target' => $nombresPredeterminados['target'][$idioma] ?? 'Target',
            'dias_transito' => $nombresPredeterminados['dias_transito'][$idioma] ?? 'Días en Tránsito',
            'post_operaciones' => $nombresPredeterminados['post_operaciones'][$idioma] ?? 'Post-Operaciones',
            'comentarios' => $nombresPredeterminados['comentarios'][$idioma] ?? 'Comentarios'
        ];
        
        // Columnas opcionales con sus nombres
        $columnasOpcionalesFull = [
            'tipo_carga' => $nombresOpcionales['tipo_carga'][$idioma] ?? 'Tipo de Carga',
            'tipo_incoterm' => $nombresOpcionales['tipo_incoterm'][$idioma] ?? 'Incoterm',
            'puerto_salida' => $nombresOpcionales['puerto_salida'][$idioma] ?? 'Puerto de Salida',
            'in_charge' => $nombresOpcionales['in_charge'][$idioma] ?? 'Responsable',
            'proveedor' => $nombresOpcionales['proveedor'][$idioma] ?? 'Proveedor',
            'tipo_previo' => $nombresOpcionales['tipo_previo'][$idioma] ?? 'Modalidad/Previo',
            'fecha_etd' => $nombresOpcionales['fecha_etd'][$idioma] ?? 'Fecha ETD',
            'fecha_zarpe' => $nombresOpcionales['fecha_zarpe'][$idioma] ?? 'Fecha Zarpe',
            'pedimento_en_carpeta' => $nombresOpcionales['pedimento_en_carpeta'][$idioma] ?? 'Pedimento en Carpeta',
            'referencia_cliente' => $nombresOpcionales['referencia_cliente'][$idioma] ?? 'Referencia Cliente',
            'mail_subject' => $nombresOpcionales['mail_subject'][$idioma] ?? 'Asunto de Correo'
        ];
        
        // Filtrar columnas base (quitar las ocultas)
        $columnasBase = [];
        foreach ($columnasBaseFull as $clave => $nombre) {
            if (!in_array($clave, $columnasOcultas)) {
                $columnasBase[$clave] = $nombre;
            }
        }
        
        // Agregar columnas opcionales visibles
        foreach ($columnasVisibles as $colVisible) {
            if (isset($columnasOpcionalesFull[$colVisible])) {
                $columnasBase[$colVisible] = $columnasOpcionalesFull[$colVisible];
            }
        }

        // Construir cabeceras con campos personalizados insertados en posición correcta
        $cabeceras = [];
        $camposEnPosicion = []; // Para rastrear qué campos personalizados van después de qué columna
        
        // Agrupar campos personalizados por su posición (mostrar_despues_de)
        if ($camposPersonalizados && $camposPersonalizados->isNotEmpty()) {
            foreach ($camposPersonalizados as $campo) {
                $posicion = $campo->mostrar_despues_de ?? 'comentarios'; // Por defecto al final
                if (!isset($camposEnPosicion[$posicion])) {
                    $camposEnPosicion[$posicion] = [];
                }
                $camposEnPosicion[$posicion][] = $campo;
            }
        }

        // Construir array de cabeceras insertando campos personalizados en su posición
        foreach ($columnasBase as $clave => $nombreColumna) {
            $cabeceras[] = ['tipo' => 'base', 'clave' => $clave, 'nombre' => $nombreColumna];
            
            // Si hay campos personalizados que van después de esta columna, insertarlos
            if (isset($camposEnPosicion[$clave])) {
                foreach ($camposEnPosicion[$clave] as $campoPersonalizado) {
                    $cabeceras[] = ['tipo' => 'personalizado', 'campo' => $campoPersonalizado, 'nombre' => $campoPersonalizado->nombre];
                }
            }
        }
        
        // Agregar campos personalizados sin posición definida o con posición inválida al final
        $clavesValidas = array_keys($columnasBase);
        if ($camposPersonalizados && $camposPersonalizados->isNotEmpty()) {
            foreach ($camposPersonalizados as $campo) {
                $posicion = $campo->mostrar_despues_de;
                if (!$posicion || !in_array($posicion, $clavesValidas)) {
                    // Solo agregar si no fue agregado antes (posición inválida o vacía)
                    if (!$posicion) {
                        $cabeceras[] = ['tipo' => 'personalizado', 'campo' => $campo, 'nombre' => $campo->nombre];
                    }
                }
            }
        }

        // Configurar estilo profesional para cabeceras
        $estilosCabecera = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 
                      'startColor' => ['rgb' => '2E86AB']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];

        // Estilo especial para campos personalizados (fondo diferente)
        $estilosCabeceraPersonalizado = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 
                      'startColor' => ['rgb' => '6366F1']], // Color indigo para campos personalizados
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];

        // Escribir cabeceras con estilo Excel
        $columna = 1;
        foreach ($cabeceras as $cabecera) {
            $coordenada = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna) . '1';
            $sheet->setCellValue($coordenada, $cabecera['nombre']);
            // Aplicar estilo diferente para campos personalizados
            if ($cabecera['tipo'] === 'personalizado') {
                $sheet->getStyle($coordenada)->applyFromArray($estilosCabeceraPersonalizado);
            } else {
                $sheet->getStyle($coordenada)->applyFromArray($estilosCabecera);
            }
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna))->setAutoSize(true);
            $columna++;
        }

        // Preparar mapeo de valores de campos personalizados por operación
        $valoresPorOperacion = [];
        if ($camposPersonalizados && $camposPersonalizados->isNotEmpty()) {
            foreach ($operaciones as $op) {
                $valoresPorOperacion[$op->id] = $op->valoresCamposPersonalizados->keyBy('campo_personalizado_id');
            }
        }

        // DATOS COMPLETOS DE LA MATRIZ DE SEGUIMIENTO
        $filaExcel = 2; // Comenzar despus de las cabeceras
        foreach ($operaciones as $operacion) {
            // Calcular status actual (prioriza Done manual, sino usa calculado)
            $statusFinal = ($operacion->status_manual === 'Done') ? 'Done' : $operacion->status_calculado;
            $statusDisplay = match($statusFinal) {
                'In Process' => 'En Proceso',
                'Out of Metric' => 'Fuera de METRICA',
                'Done' => 'Completado',
                default => $statusFinal ?? 'En Proceso'
            };

            // Mapeo de valores base (columnas predeterminadas y opcionales)
            $valoresBase = [
                // Columnas predeterminadas
                'id' => $operacion->id,
                'ejecutivo' => $operacion->ejecutivo ?? 'Sin asignar',
                'operacion' => $operacion->operacion ?? '-',
                'cliente' => $operacion->cliente ?? 'Sin cliente',
                'proveedor_o_cliente' => $operacion->proveedor_o_cliente ?? '-',
                'fecha_embarque' => optional($operacion->fecha_embarque)->format('d/m/Y') ?? '-',
                'no_factura' => $operacion->no_factura ?? '-',
                'tipo_operacion_enum' => $operacion->tipo_operacion_enum ?? '-',
                'clave' => $operacion->clave ?? '-',
                'referencia_interna' => $operacion->referencia_interna ?? '-',
                'aduana' => $operacion->aduana ?? '-',
                'agente_aduanal' => $operacion->agente_aduanal ?? '-',
                'referencia_aa' => $operacion->referencia_aa ?? '-',
                'no_pedimento' => $operacion->no_pedimento ?? '-',
                'transporte' => $operacion->transporte ?? '-',
                'fecha_arribo_aduana' => optional($operacion->fecha_arribo_aduana)->format('d/m/Y') ?? '-',
                'guia_bl' => $operacion->guia_bl ?? '-',
                'status' => $statusDisplay,
                'fecha_modulacion' => optional($operacion->fecha_modulacion)->format('d/m/Y') ?? '-',
                'fecha_arribo_planta' => optional($operacion->fecha_arribo_planta)->format('d/m/Y') ?? '-',
                'resultado' => $operacion->resultado ?? '-',
                'target' => $operacion->target ?? '-',
                'dias_transito' => $operacion->dias_transito ?? '-',
                'post_operaciones' => $this->formatearPostOperaciones($operacion),
                'comentarios' => $this->limpiarTexto($operacion->comentarios ?? '-'),
                
                // Columnas opcionales
                'tipo_carga' => $operacion->tipo_carga ?? '-',
                'tipo_incoterm' => $operacion->tipo_incoterm ?? '-',
                'puerto_salida' => $operacion->puerto_salida ?? '-',
                'in_charge' => $operacion->in_charge ?? '-',
                'proveedor' => $operacion->proveedor ?? '-',
                'tipo_previo' => $operacion->tipo_previo ?? '-',
                'fecha_etd' => optional($operacion->fecha_etd)->format('d/m/Y') ?? '-',
                'fecha_zarpe' => optional($operacion->fecha_zarpe)->format('d/m/Y') ?? '-',
                'pedimento_en_carpeta' => $operacion->pedimento_en_carpeta ? 'Sí' : 'No',
                'referencia_cliente' => $operacion->referencia_cliente ?? '-',
                'mail_subject' => $operacion->mail_subject ?? '-'
            ];

            // Construir fila siguiendo el orden de cabeceras
            $fila = [];
            $valoresOperacion = $valoresPorOperacion[$operacion->id] ?? collect();
            
            foreach ($cabeceras as $cabecera) {
                if ($cabecera['tipo'] === 'base') {
                    $fila[] = $valoresBase[$cabecera['clave']] ?? '-';
                } else {
                    // Campo personalizado
                    $campo = $cabecera['campo'];
                    $valorCampo = $valoresOperacion->get($campo->id);
                    $valorMostrar = '-';
                    if ($valorCampo && $valorCampo->valor) {
                        $valorMostrar = $valorCampo->valor;
                        // Formatear fecha si es campo de tipo fecha
                        if ($campo->tipo === 'fecha') {
                            try {
                                $valorMostrar = \Carbon\Carbon::parse($valorCampo->valor)->format('d/m/Y');
                            } catch (\Exception $e) {
                                $valorMostrar = $valorCampo->valor;
                            }
                        }
                    }
                    $fila[] = $valorMostrar;
                }
            }

            // Escribir fila de datos en Excel con estilo
            $columna = 1;
            foreach ($fila as $valor) {
                $coordenada = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna) . $filaExcel;
                $sheet->setCellValue($coordenada, $valor);
                
                // Aplicar estilo a datos
                $sheet->getStyle($coordenada)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]
                ]);
                
                $columna++;
            }
            
            $filaExcel++; // Incrementar fila para la siguiente iteracin
        }

        // Guardar archivo Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($rutaArchivo);
    }

    /**
     * Formatear post-operaciones para mostrar en Excel
     */
    private function formatearPostOperaciones($operacion)
    {
        if (!$operacion->asignacionesPostOperaciones || $operacion->asignacionesPostOperaciones->isEmpty()) {
            return '-';
        }

        $postOpsTexto = [];
        foreach ($operacion->asignacionesPostOperaciones as $asignacion) {
            $nombre = $asignacion->postOperacion->nombre ?? 'Sin nombre';
            $status = $asignacion->status ?? 'Pendiente';
            $postOpsTexto[] = "{$nombre} ({$status})";
        }

        return implode('; ', $postOpsTexto);
    }

    /**
     * Limpiar texto para CSV eliminando TODOS los caracteres problemticos
     */
    private function limpiarTexto($texto)
    {
        if (!$texto) return 'Sin datos';

        // Convertir a string si no lo es
        $texto = (string)$texto;

        // Reemplazos especficos para casos conocidos
        $reemplazos = [
            'M????????xico' => 'MEXICO',
            'M????XICO' => 'MEXICO',
            'M????%????XI' => 'MEXICO',
            'M????%????' => 'MEX',
            '????????' => 'a', '????????' => 'e', '????????' => 'i', '????????' => 'o', '????????' => 'u',
            '????????' => 'n', '????????' => 'u'
        ];

        foreach ($reemplazos as $buscar => $reemplazar) {
            $texto = str_replace($buscar, $reemplazar, $texto);
        }

        // Eliminar caracteres no ASCII problemticos
        $texto = preg_replace('/[\x80-\xFF]/', '', $texto);

        // Solo permitir caracteres bsicos
        $texto = preg_replace('/[^A-Za-z0-9\s\-\_\.\,\/\(\)]/', ' ', $texto);

        // Limpiar espacios mltiples
        $texto = preg_replace('/\s+/', ' ', trim($texto));

        // Truncar si es muy largo
        return strlen($texto) > 100 ? substr($texto, 0, 97) . '...' : $texto;
    }    /**
     * Obtener texto de performance legible
     */
    private function obtenerPerformanceTexto($resultado)
    {
        switch($resultado) {
            case 'excelente':
                return 'EXCELENTE';
            case 'bueno':
                return 'BUENO';
            case 'regular':
                return 'REGULAR';
            case 'malo':
                return 'DEFICIENTE';
            default:
                return 'SIN EVALUAR';
        }
    }

    // metodos auxiliares para el reporte
    private function formatearStatusConIconos($status)
    {
        switch ($status) {
            case 'Done':
                return '???????? COMPLETADO';
            case 'In Process':
                return '???????? EN PROCESO';
            case 'Out of Metric':
                return '???????? FUERA METRICA';
            case 'In Time':
                return '???????? EN TIEMPO';
            default:
                return '???????? ' . mb_strtoupper($status ?? 'INDEFINIDO');
        }
    }

    /**
     * Obtener performance con iconos visuales
     */
    private function obtenerPerformanceConIconos($resultado)
    {
        switch($resultado) {
            case 'excelente':
                return '???????? EXCELENTE';
            case 'bueno':
                return '???????? BUENO';
            case 'regular':
                return '???????? REGULAR';
            case 'malo':
                return '???????? DEFICIENTE';
            default:
                return '???????? SIN EVALUAR';
        }
    }

    /**
     * Formatear eficiencia con indicadores
     */
    private function formatearEficienciaConIconos($eficiencia)
    {
        $eficienciaNum = round($eficiencia, 1);

        if ($eficienciaNum >= 90) {
            return "???????? {$eficienciaNum}%";
        } elseif ($eficienciaNum >= 70) {
            return "???????? {$eficienciaNum}%";
        } elseif ($eficienciaNum >= 50) {
            return "???????? {$eficienciaNum}%";
        } else {
            return "???????? {$eficienciaNum}%";
        }
    }

    /**
     * Limpiar comentarios para Excel
     */
    private function limpiarComentariosParaExcel($comentarios)
    {
        if (!$comentarios || trim($comentarios) === '') {
            return '???????? Sin observaciones';
        }

        // Limpiar y truncar comentarios largos
        $comentarios = preg_replace('/\s+/', ' ', trim($comentarios));

        if (strlen($comentarios) > 50) {
            return substr($comentarios, 0, 47) . '...';
        }

        return $comentarios;
    }

    /**
     * Formatear status con indicador visual
     */
    private function formatearStatusConIndicador($status)
    {
        switch ($status) {
            case 'Done':
                return '???????? COMPLETADO';
            case 'In Process':
                return '???????? EN PROCESO';
            case 'Out of Metric':
                return '???????? FUERA DE METRICA';
            default:
                return '???????? ' . strtoupper($status ?? 'DESCONOCIDO');
        }
    }

    /**
     * Formatear resultado con indicador visual
     */
    private function formatearResultadoConIndicador($resultado)
    {
        switch ($resultado) {
            case 'Completado a Tiempo':
                return '???????? COMPLETADO A TIEMPO';
            case 'Completado con Retraso':
                return '???????? COMPLETADO CON RETRASO';
            case 'En Tiempo':
                return '???????? EN TIEMPO';
            case 'En Riesgo':
                return '???????? EN RIESGO';
            case 'Con Retraso':
                return '???????? CON RETRASO';
            case 'Fuera de METRICA':
                return '???????? FUERA DE METRICA';
            default:
                return '???????? ' . strtoupper($resultado ?? 'DESCONOCIDO');
        }
    }

    /**
     * Limpiar comentarios para CSV
     */
    private function limpiarComentarios($comentarios)
    {
        // Remover saltos de lnea y caracteres especiales
        $comentarios = str_replace(["\n", "\r", "\t"], ' ', $comentarios);
        // Limitar longitud
        return strlen($comentarios) > 100 ? substr($comentarios, 0, 97) . '...' : $comentarios;
    }

    /**
     * Calcular resultado de performance de operacion
     */
    private function calcularResultadoPerformance($operacion)
    {
        $dias = $operacion->calcularDiasTranscurridos();
        $target = $operacion->dias_objetivo ?? 5;

        if ($operacion->status_manual === 'Done') {
            return $dias <= $target ? 'Completado a Tiempo' : 'Completado con Retraso';
        } elseif ($operacion->status_manual === 'Out of Metric') {
            return 'Fuera de METRICA';
        } else {
            if ($dias <= $target) return 'En Tiempo';
            elseif ($dias <= $target + 2) return 'En Riesgo';
            else return 'Con Retraso';
        }
    }

    /**
     * Calcular eficiencia de operacion
     */
    private function calcularEficienciaoperacion($operacion)
    {
        $dias = $operacion->calcularDiasTranscurridos();
        $target = $operacion->dias_objetivo ?? 5;

        if ($dias == 0) return 100;
        if ($operacion->status_manual === 'Done') {
            return max(0, round((1 - (($dias - $target) / $target)) * 100, 1));
        }

        return round((1 - ($dias / ($target + 5))) * 100, 1);
    }

    /**
     * Exportar Excel profesional con grficos y diseo moderno
     */
    public function exportExcelProfesional(Request $request)
    {
        try {
            // Obtener usuario actual y verificar permisos
            $usuarioActual = auth()->user();
            $empleadoActual = null;
            $esAdmin = false;

            if ($usuarioActual) {
                $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                    ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                    ->first();
                $esAdmin = $usuarioActual->hasRole('admin');
            }

            // Construir query con los mismos filtros que el reporte normal
            $query = OperacionLogistica::with('ejecutivo');

            // Aplicar filtros de permisos
            if (!$esAdmin && $empleadoActual) {
                $query->where('ejecutivo', $empleadoActual->nombre);
            }

            // Aplicar todos los filtros de la request
            $this->aplicarFiltrosReporte($query, $request);

            // Obtener operaciones
            $operaciones = $query->get();

            // Calcular estadsticas
            $estadisticas = $this->calcularEstadisticasReporte($operaciones);

            // Preparar filtros aplicados para mostrar en el reporte
            $filtrosAplicados = [
                'periodo' => $request->periodo,
                'mes' => $request->mes,
                'anio' => $request->anio,
                'cliente' => $request->cliente,
                'status' => $request->status,
                'fecha_desde' => $request->fecha_desde,
                'fecha_hasta' => $request->fecha_hasta,
                'usuario' => $usuarioActual->name ?? 'Sistema'
            ];

            // Generar Excel profesional
            $excelService = new ExcelReportService();
            $excelService->generateLogisticsReport($operaciones, $filtrosAplicados, $estadisticas);

            // Configurar respuesta para descarga
            $filename = 'Reporte_Logistica_Profesional_' . date('Y-m-d_H-i-s') . '.xlsx';

            return response()->streamDownload(function() use ($excelService) {
                echo $excelService->output();
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar Excel profesional: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el reporte Excel: ' . $e->getMessage());
        }
    }

    /**
     * Aplicar filtros comunes para reportes
     */
    private function aplicarFiltrosReporte($query, Request $request)
    {
        // Filtro por perodo
        if ($request->filled('periodo')) {
            $periodo = $request->periodo;
            if ($periodo === 'semanal') {
                $query->where('created_at', '>=', now()->subWeek());
            } elseif ($periodo === 'mensual') {
                $query->where('created_at', '>=', now()->subMonth());
            } elseif ($periodo === 'anual') {
                $query->where('created_at', '>=', now()->subYear());
            }
        }

        // Filtro por mes y ao especficos
        if ($request->filled('mes') && $request->filled('anio')) {
            $query->whereMonth('created_at', $request->mes)
                  ->whereYear('created_at', $request->anio);
        }

        // Filtro por cliente
        if ($request->filled('cliente')) {
            $query->where('cliente', 'like', '%' . $request->cliente . '%');
        }

        // Filtro por status
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'Done') {
                $query->where('status_manual', 'Done');
            } elseif ($status === 'In Process') {
                $query->where(function($q){
                    $q->where(function($qq){
                        $qq->where('status_manual', '!=', 'Done')->orWhereNull('status_manual');
                    })->where('status_calculado', 'In Process');
                });
            } elseif ($status === 'Out of Metric') {
                $query->where('status_manual', 'Out of Metric');
            }
        }

        // Filtros por fechas especficas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Filtro por ejecutivo
        if ($request->filled('ejecutivo')) {
            $query->where('ejecutivo', 'like', '%' . $request->ejecutivo . '%');
        }

        // Filtro por tipo de operación
        if ($request->filled('tipo_operacion')) {
            $query->where('tipo_operacion_enum', $request->tipo_operacion);
        }

        // Filtro por aduana
        if ($request->filled('aduana')) {
            $query->where('aduana', 'like', '%' . $request->aduana . '%');
        }

        // Filtro por agente aduanal
        if ($request->filled('agente_aduanal')) {
            $query->where('agente_aduanal', 'like', '%' . $request->agente_aduanal . '%');
        }

        // Filtro por búsqueda general
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('operacion', 'like', '%' . $searchTerm . '%')
                  ->orWhere('cliente', 'like', '%' . $searchTerm . '%')
                  ->orWhere('no_pedimento', 'like', '%' . $searchTerm . '%')
                  ->orWhere('no_factura', 'like', '%' . $searchTerm . '%')
                  ->orWhere('referencia_interna', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filtro explícito por IDs de operaciones (usado al enviar por correo)
        if ($request->filled('operaciones_ids')) {
            $ids = $request->operaciones_ids;

            // Puede venir como JSON string o como arreglo directo
            if (is_string($ids)) {
                $decoded = json_decode($ids, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $ids = $decoded;
                }
            }

            if (is_array($ids) && !empty($ids)) {
                $ids = array_filter($ids, fn($id) => is_numeric($id));
                if (!empty($ids)) {
                    $query->whereIn('id', $ids);
                }
            }
        }
    }

    /**
     * Obtener historial completo de comentarios de una operacion
     */
    public function obtenerHistorialComentarios($id)
    {
        try {
            $operacion = OperacionLogistica::with('comentariosCronologicos')->findOrFail($id);

            $comentarios = $operacion->comentariosCronologicos
                ->filter(function ($comentario) {
                    // Filtrar comentarios del sistema, solo mostrar los del ejecutivo
                    return !in_array($comentario->usuario_nombre, ['Sistema', 'Sistema Automtico', 'Sistema de Prueba']);
                })
                ->map(function ($comentario) use ($operacion) {
                    // Cambiar el ttulo de actualizacion_automatica por el nmero de pedimento
                    $tipoAccion = $comentario->tipo_accion;
                    if ($tipoAccion === 'actualizacion_automatica' && $operacion->no_pedimento) {
                        $tipoAccion = $operacion->no_pedimento;
                    }

                    // Para mostrar: extraer solo la parte despus de "Comentarios:" si existe
                    $comentarioTextoMostrar = $comentario->comentario;
                    $comentarioTextoEdicion = $comentario->comentario; // Siempre usar texto completo para edicin

                    if (strpos($comentarioTextoMostrar, 'Comentarios: ') !== false) {
                        $comentarioTextoExtraido = trim(substr($comentarioTextoMostrar, strpos($comentarioTextoMostrar, 'Comentarios: ') + 13));
                        if (!empty($comentarioTextoExtraido)) {
                            $comentarioTextoMostrar = $comentarioTextoExtraido;
                            $comentarioTextoEdicion = $comentarioTextoExtraido; // Para edicin usar el texto extrado
                        }
                    }

                    return [
                        'id' => $comentario->id,
                        'comentario' => $comentarioTextoMostrar,
                        'comentario_edicion' => $comentarioTextoEdicion, // Texto especfico para edicin
                        'status_en_momento' => $comentario->status_en_momento,
                        'tipo_accion' => $tipoAccion,
                        'icono_accion' => $comentario->icono_accion,
                        'usuario_nombre' => $comentario->usuario_nombre,
                        'fecha_formateada' => $comentario->fecha_formateada,
                        'created_at' => $comentario->created_at->toISOString(),
                    ];
                })
                ->values(); // Reindexar despus del filtro

            return response()->json([
                'success' => true,
                'comentarios' => $comentarios,
                'operacion' => [
                    'id' => $operacion->id,
                    'operacion' => $operacion->operacion,
                    'cliente' => $operacion->cliente,
                    'no_pedimento' => $operacion->no_pedimento,
                    'status_actual' => $operacion->status_actual,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial de comentarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar informacin del email y archivo al webhook de N8N
     */
    private function enviarWebhookLogistica($datosCorreo, $request)
    {
        try {
            $webhookUrl = config('services.n8n.logistica_webhook_url');

            if (empty($webhookUrl)) {
                \Log::info('Webhook de LOGISTICA no configurado. Se omite notificacin.');
                return ['success' => false, 'message' => 'Webhook no configurado'];
            }

            // Preparar payload con toda la informacin del email
            $payload = [
                'tipo' => 'reporte_logistica',
                'timestamp' => now()->toISOString(),
                'email' => [
                    'destinatarios' => $datosCorreo['destinatarios'],
                    'correos_cc' => $datosCorreo['correosCC'] ?? [],
                    'asunto' => $datosCorreo['asunto'],
                    'mensaje' => $datosCorreo['mensaje'],
                    'remitente' => $datosCorreo['remitente'],
                    'nombre_remitente' => $datosCorreo['nombreRemitente']
                ],
                'datos_adicionales' => [
                    'incluir_datos' => $datosCorreo['incluir_datos'] ?? false,
                    'formato_datos' => $datosCorreo['formato_datos'] ?? null,
                    'operaciones_ids' => $request->operaciones_ids ?? [],
                    'usuario_envio' => [
                        'id' => auth()->id(),
                        'name' => auth()->user()->name ?? 'Usuario desconocido',
                        'email' => auth()->user()->email ?? 'correo@desconocido.com'
                    ]
                ]
            ];

            // Si hay archivo adjunto, incluir informacin del archivo
            if (isset($datosCorreo['adjunto'])) {
                $archivoPath = $datosCorreo['adjunto']['path'];

                // Convertir archivo a base64 para enviar en el webhook
                if (file_exists($archivoPath)) {
                    $archivoContenido = base64_encode(file_get_contents($archivoPath));
                    $payload['archivo'] = [
                        'nombre' => $datosCorreo['adjunto']['nombre'],
                        'mime_type' => $datosCorreo['adjunto']['mime'],
                        'size' => filesize($archivoPath),
                        'contenido_base64' => $archivoContenido
                    ];
                }
            }

            // Enviar al webhook (sin verificacin SSL para desarrollo)
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->timeout(30)
                ->post($webhookUrl, $payload);

            if ($response->successful()) {
                \Log::info('Webhook de LOGISTICA enviado correctamente.', [
                    'url' => $webhookUrl,
                    'destinatarios' => count($datosCorreo['destinatarios']),
                    'tiene_archivo' => isset($datosCorreo['adjunto'])
                ]);

                return [
                    'success' => true,
                    'message' => 'Webhook enviado correctamente'
                ];
            } else {
                \Log::error('No se pudo enviar el webhook de LOGISTICA.', [
                    'url' => $webhookUrl,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Error al enviar webhook: ' . $response->status()
                ];
            }

        } catch (\Exception $e) {
            \Log::error('Error al enviar el webhook de LOGISTICA.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno del webhook: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Programar limpieza de archivo temporal
     */
    private function programarLimpiezaArchivo($rutaArchivo)
    {
        // Crear un job o comando simple para limpiar el archivo despus de 1 hora
        // Por simplicidad, lo hacemos con un archivo de control
        try {
            $timeToDelete = time() + 3600; // 1 hora
            $controlFile = dirname($rutaArchivo) . '/.cleanup_' . basename($rutaArchivo) . '.txt';
            file_put_contents($controlFile, $timeToDelete);
        } catch (\Exception $e) {
            // Si no se puede programar la limpieza, no es crtico
            Log::debug('No se pudo programar limpieza de archivo temporal: ' . $e->getMessage());
        }
    }

    /**
     * Limpiar archivos temporales vencidos (se puede llamar peridicamente)
     */
    public function limpiarArchivosTemporales()
    {
        try {
            $dirTemporal = public_path('temp');
            if (!is_dir($dirTemporal)) {
                return response()->json(['success' => true, 'message' => 'Directorio temporal no existe']);
            }

            $archivosEliminados = 0;
            $controlesLimpieza = glob($dirTemporal . '/.cleanup_*.txt');

            foreach ($controlesLimpieza as $controlFile) {
                $timeToDelete = (int)file_get_contents($controlFile);
                if (time() >= $timeToDelete) {
                    // Es hora de eliminar el archivo
                    $nombreArchivo = str_replace(['.cleanup_', '.txt'], '', basename($controlFile));
                    $archivoAEliminar = $dirTemporal . '/' . $nombreArchivo;

                    if (file_exists($archivoAEliminar)) {
                        unlink($archivoAEliminar);
                        $archivosEliminados++;
                    }

                    // Eliminar tambin el archivo de control
                    unlink($controlFile);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Limpieza completada. {$archivosEliminados} archivos eliminados."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en limpieza: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear UN SOLO registro de historial consolidado para mltiples cambios de fechas
     */
    private function crearRegistroHistorialFechasConsolidado($operacion, $cambios)
    {
        try {
            // Calcular das transcurridos actuales
            $resultado = $operacion->calcularStatusPorDias();
            
            // Construir mensaje consolidado
            $mensajes = [];
            foreach ($cambios as $cambio) {
                $fechaFormateada = \Carbon\Carbon::parse($cambio['fecha'])->format('d/m/Y');
                $mensajes[] = $cambio['mensaje'] . ' (' . $fechaFormateada . ')';
            }
            
            // Unir todos los mensajes en una sola observacin
            $observacionConsolidada = implode('; ', $mensajes);
            
            // Crear el registro nico en el historial
            $historial = $operacion->historicoMatrizSgm()->create([
                'fecha_registro' => now(),
                'fecha_arribo_aduana' => $operacion->fecha_arribo_aduana,
                'dias_transcurridos' => (int)round($resultado['dias_transcurridos'] ?? 0),
                'target_dias' => $resultado['target'],
                'color_status' => $resultado['color'],
                'operacion_status' => $resultado['status'],
                'observaciones' => $observacionConsolidada
            ]);

            \Log::info('Registro consolidado de historial creado:', [
                'operacion_id' => $operacion->id,
                'cantidad_cambios' => count($cambios),
                'tipos_cambio' => array_column($cambios, 'tipo'),
                'observacion_final' => $observacionConsolidada
            ]);

            return $historial;
        } catch (\Exception $e) {
            \Log::error('Error al crear registro consolidado de historial:', [
                'operacion_id' => $operacion->id,
                'cambios' => $cambios,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Exportar reporte de pedimentos
     */
    public function exportPedimentos(Request $request)
    {
        try {
            // TODO: Implementar lógica específica para pedimentos
            // Por ahora retornamos un CSV con estructura básica de pedimentos
            
            $filename = 'reporte_pedimentos_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = storage_path('app/temp/' . $filename);
            
            // Asegurar que el directorio existe
            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }
            
            // Crear archivo CSV
            $file = fopen($filepath, 'w');
            
            // Headers del CSV para pedimentos
            $headers = [
                'Número Pedimento',
                'Cliente', 
                'Clave',
                'Tipo Operación',
                'Moneda',
                'Monto',
                'Estado Pago',
                'Fecha Embarque',
                'Fecha Creación'
            ];
            
            fputcsv($file, $headers);
            
            // Por ahora agregamos datos de ejemplo
            // TODO: Implementar consulta real de pedimentos según filtros
            $datosPedimentos = [
                ['PED-2024-001', 'Cliente Ejemplo', 'A1', 'Importación', 'MXN', '$50,000.00', 'Pagado', '2024-12-15', '2024-12-01'],
                ['PED-2024-002', 'Cliente ABC', 'B1', 'Exportación', 'USD', '$25,000.00', 'Pendiente', '2024-12-16', '2024-12-02'],
            ];
            
            foreach ($datosPedimentos as $fila) {
                fputcsv($file, $fila);
            }
            
            fclose($file);
            
            return response()->download($filepath, $filename, [
                'Content-Type' => 'text/csv'
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('Error en exportPedimentos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar pedimentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar reporte de resumen ejecutivo
     */
    public function exportResumenEjecutivo(Request $request)
    {
        try {
            // Obtener datos de estadísticas actuales usando la misma lógica del método reportes
            $stats = $this->calcularEstadisticasTemporales($request);
            
            $filename = 'resumen_ejecutivo_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = storage_path('app/temp/' . $filename);
            
            // Asegurar que el directorio existe
            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }
            
            // Crear archivo CSV
            $file = fopen($filepath, 'w');
            
            // Headers del CSV para resumen ejecutivo
            fputcsv($file, ['RESUMEN EJECUTIVO DE OPERACIONES LOGÍSTICAS']);
            fputcsv($file, ['Generado el: ' . date('d/m/Y H:i:s')]);
            fputcsv($file, ['']);
            
            // Métricas principales
            fputcsv($file, ['MÉTRICAS PRINCIPALES']);
            fputcsv($file, ['Métrica', 'Valor']);
            fputcsv($file, ['Total de Operaciones', $stats['total_operaciones']]);
            fputcsv($file, ['Operaciones en Tiempo', $stats['en_tiempo'] ?? 0]);
            fputcsv($file, ['Operaciones Completadas a Tiempo', $stats['completado_tiempo'] ?? 0]);
            fputcsv($file, ['Operaciones en Riesgo', $stats['en_riesgo'] ?? 0]);
            fputcsv($file, ['Operaciones con Retraso', $stats['con_retraso'] ?? 0]);
            fputcsv($file, ['Operaciones Completadas con Retraso', $stats['completado_retraso'] ?? 0]);
            
            // Calcular eficiencia
            $operacionesExitosas = ($stats['en_tiempo'] ?? 0) + ($stats['completado_tiempo'] ?? 0);
            $eficienciaGeneral = $stats['total_operaciones'] > 0 ? 
                round(($operacionesExitosas / $stats['total_operaciones']) * 100, 1) : 0;
                
            fputcsv($file, ['Eficiencia General (%)', $eficienciaGeneral . '%']);
            fputcsv($file, ['Promedio de Días', round($stats['promedio_dias'] ?? 0, 1)]);
            fputcsv($file, ['Target Promedio', round($stats['promedio_target'] ?? 3, 1)]);
            
            fputcsv($file, ['']);
            
            // Distribución porcentual
            fputcsv($file, ['DISTRIBUCIÓN PORCENTUAL']);
            fputcsv($file, ['Estado', 'Cantidad', 'Porcentaje']);
            if ($stats['total_operaciones'] > 0) {
                $total = $stats['total_operaciones'];
                fputcsv($file, ['En Tiempo', $stats['en_tiempo'] ?? 0, round((($stats['en_tiempo'] ?? 0) / $total) * 100, 1) . '%']);
                fputcsv($file, ['Completado a Tiempo', $stats['completado_tiempo'] ?? 0, round((($stats['completado_tiempo'] ?? 0) / $total) * 100, 1) . '%']);
                fputcsv($file, ['En Riesgo', $stats['en_riesgo'] ?? 0, round((($stats['en_riesgo'] ?? 0) / $total) * 100, 1) . '%']);
                fputcsv($file, ['Con Retraso', $stats['con_retraso'] ?? 0, round((($stats['con_retraso'] ?? 0) / $total) * 100, 1) . '%']);
                fputcsv($file, ['Completado con Retraso', $stats['completado_retraso'] ?? 0, round((($stats['completado_retraso'] ?? 0) / $total) * 100, 1) . '%']);
            }
            
            fclose($file);
            
            return response()->download($filepath, $filename, [
                'Content-Type' => 'text/csv'
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('Error en exportResumenEjecutivo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar resumen ejecutivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular estadísticas temporales para reportes
     */
    private function calcularEstadisticasTemporales(Request $request)
    {
        // Obtener usuario actual y verificar permisos
        $usuarioActual = auth()->user();
        $empleadoActual = null;
        $esAdmin = false;

        if ($usuarioActual) {
            $empleadoActual = Empleado::where('correo', $usuarioActual->email)
                ->orWhere('nombre', 'like', '%' . $usuarioActual->name . '%')
                ->first();
            $esAdmin = $usuarioActual->hasRole('admin');
        }

        // Construir query base
        $query = OperacionLogistica::query();

        // Aplicar filtros de permisos
        if (!$esAdmin && $empleadoActual) {
            $query->where('ejecutivo', $empleadoActual->nombre);
        }

        // Aplicar filtros adicionales si están presentes
        $this->aplicarFiltrosReporte($query, $request);

        $operaciones = $query->get();
        $totalOperaciones = $operaciones->count();

        if ($totalOperaciones === 0) {
            return [
                'total_operaciones' => 0,
                'en_tiempo' => 0,
                'completado_tiempo' => 0,
                'en_riesgo' => 0,
                'con_retraso' => 0,
                'completado_retraso' => 0,
                'promedio_dias' => 0,
                'promedio_target' => 3
            ];
        }

        // Calcular estadísticas basadas en el estado actual
        $stats = [
            'total_operaciones' => $totalOperaciones,
            'en_tiempo' => 0,
            'completado_tiempo' => 0,
            'en_riesgo' => 0,
            'con_retraso' => 0,
            'completado_retraso' => 0,
            'promedio_dias' => 0,
            'promedio_target' => 3
        ];

        $totalDias = 0;
        $totalTarget = 0;

        foreach ($operaciones as $operacion) {
            // Calcular días transcurridos y target para cada operación
            $resultado = $this->calcularDiasYTarget($operacion);
            $totalDias += $resultado['dias_transcurridos'] ?? 0;
            $totalTarget += $resultado['target'] ?? 3;

            // Clasificar según el status actual
            $status = $resultado['status'] ?? 'PENDIENTE';
            switch ($status) {
                case 'EN_TIEMPO':
                    $stats['en_tiempo']++;
                    break;
                case 'COMPLETADO_TIEMPO':
                    $stats['completado_tiempo']++;
                    break;
                case 'EN_RIESGO':
                    $stats['en_riesgo']++;
                    break;
                case 'CON_RETRASO':
                    $stats['con_retraso']++;
                    break;
                case 'COMPLETADO_RETRASO':
                    $stats['completado_retraso']++;
                    break;
            }
        }

        // Calcular promedios
        $stats['promedio_dias'] = $totalOperaciones > 0 ? $totalDias / $totalOperaciones : 0;
        $stats['promedio_target'] = $totalOperaciones > 0 ? $totalTarget / $totalOperaciones : 3;

        return $stats;
    }
}








