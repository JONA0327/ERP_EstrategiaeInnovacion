<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use App\Models\Logistica\Pedimento;
use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\PedimentoOperacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PedimentoController extends Controller
{
    /**
     * Mostrar la lista de pedimentos con su estado de pago
     */
    public function index(Request $request)
    {
        // Obtener todas las operaciones que tienen número de pedimento y agrupar por clave
        $operacionesQuery = OperacionLogistica::whereNotNull('no_pedimento')
            ->where('no_pedimento', '!=', '')
            ->whereNotNull('clave')
            ->where('clave', '!=', '');

        // Aplicar filtros si existen
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $operacionesQuery->where(function($query) use ($buscar) {
                $query->where('clave', 'like', "%{$buscar}%")
                      ->orWhere('cliente', 'like', "%{$buscar}%")
                      ->orWhere('no_pedimento', 'like', "%{$buscar}%");
            });
        }

        // Agrupar por clave y contar pedimentos
        $clavesPedimentos = $operacionesQuery
            ->select('clave', 
                     \DB::raw('COUNT(*) as total_pedimentos'),
                     \DB::raw('GROUP_CONCAT(DISTINCT cliente ORDER BY cliente SEPARATOR ", ") as clientes'),
                     \DB::raw('GROUP_CONCAT(DISTINCT ejecutivo ORDER BY ejecutivo SEPARATOR ", ") as ejecutivos'),
                     \DB::raw('MIN(fecha_embarque) as primera_fecha'),
                     \DB::raw('MAX(fecha_embarque) as ultima_fecha'))
            ->groupBy('clave')
            ->orderBy('clave')
            ->paginate(15);

        // Para cada clave, obtener las operaciones individuales y crear registros en tabla separada
        $pedimentosConEstado = collect();
        
        foreach ($clavesPedimentos as $claveData) {
            // Obtener todas las operaciones individuales de esta clave
            $operacionesIndividuales = OperacionLogistica::where('clave', $claveData->clave)
                ->whereNotNull('no_pedimento')
                ->where('no_pedimento', '!=', '')
                ->select('id', 'no_pedimento', 'cliente', 'ejecutivo', 'fecha_embarque')
                ->get();
            
            // Para cada operación, obtener o crear su registro de pago individual en tabla separada
            $pedimentosPorPagar = 0;
            $pedimentosPagados = 0;
            
            foreach ($operacionesIndividuales as $operacion) {
                // Buscar en la tabla de pedimentos_operaciones (tabla separada)
                $registroPago = PedimentoOperacion::where('no_pedimento', $operacion->no_pedimento)
                    ->where('operacion_logistica_id', $operacion->id)
                    ->first();
                
                if (!$registroPago) {
                    // Crear registro individual en tabla separada
                    try {
                        PedimentoOperacion::create([
                            'no_pedimento' => $operacion->no_pedimento,
                            'clave' => $claveData->clave,
                            'operacion_logistica_id' => $operacion->id,
                            'estado_pago' => 'pendiente'
                        ]);
                        $pedimentosPorPagar++;
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Si ya existe, obtener su estado
                        $registroExistente = PedimentoOperacion::where('no_pedimento', $operacion->no_pedimento)
                            ->where('operacion_logistica_id', $operacion->id)
                            ->first();
                        if ($registroExistente && $registroExistente->estado_pago === 'pendiente') {
                            $pedimentosPorPagar++;
                        } elseif ($registroExistente && $registroExistente->estado_pago === 'pagado') {
                            $pedimentosPagados++;
                        }
                    }
                } else {
                    if ($registroPago->estado_pago === 'pendiente') {
                        $pedimentosPorPagar++;
                    } elseif ($registroPago->estado_pago === 'pagado') {
                        $pedimentosPagados++;
                    }
                }
            }
            
            // Crear objeto resumen para la clave (solo para visualización)
            // Buscar el ID del catálogo para esta clave
            $catalogoPedimento = Pedimento::where('clave', $claveData->clave)->first();
            
            $resumenClave = (object) [
                'id' => $catalogoPedimento ? $catalogoPedimento->id : null,
                'clave' => $claveData->clave,
                'descripcion' => "Tipo de operación {$claveData->clave}",
                'total_pedimentos' => $claveData->total_pedimentos,
                'clientes' => $claveData->clientes,
                'ejecutivos' => $claveData->ejecutivos,
                'primera_fecha' => $claveData->primera_fecha,
                'ultima_fecha' => $claveData->ultima_fecha,
                'pedimentos_por_pagar' => $pedimentosPorPagar,
                'pedimentos_pagados' => $pedimentosPagados,
                // Estado general basado en pedimentos individuales
                'estado_pago' => $pedimentosPorPagar > 0 ? 'pendiente' : ($pedimentosPagados > 0 ? 'pagado' : 'pendiente'),
                'fecha_pago' => null, // Se calculará desde los pedimentos individuales si es necesario
                'monto' => null // Se calculará desde los pedimentos individuales si es necesario
            ];
            
            $pedimentosConEstado->push($resumenClave);
        }

        // Aplicar filtro de estado si existe
        if ($request->filled('estado_pago')) {
            $pedimentosConEstado = $pedimentosConEstado->filter(function ($item) use ($request) {
                return $item->estado_pago === $request->estado_pago;
            });
        }

        // Crear paginador manual para mantener la estructura
        $paginatedPedimentos = new \Illuminate\Pagination\LengthAwarePaginator(
            $pedimentosConEstado,
            $clavesPedimentos->total(),
            $clavesPedimentos->perPage(),
            $clavesPedimentos->currentPage(),
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        $paginatedPedimentos->appends($request->query());

        // Calcular estadísticas basadas en pedimentos individuales
        $totalPedimentosIndividuales = $pedimentosConEstado->sum('total_pedimentos');
        $porPagar = $pedimentosConEstado->sum('pedimentos_por_pagar');
        $pagados = $pedimentosConEstado->sum('pedimentos_pagados');
        
        $totalClaves = $pedimentosConEstado->count();
            
        // Obtener solo las claves reales de operaciones
        $clavesReales = OperacionLogistica::whereNotNull('no_pedimento')
            ->where('no_pedimento', '!=', '')
            ->whereNotNull('clave')
            ->where('clave', '!=', '')
            ->pluck('clave')
            ->unique();
            
        $stats = [
            'total_claves' => $totalClaves,
            'total_pedimentos' => $totalPedimentosIndividuales,
            'pagados' => $pagados,
            'pendientes' => $porPagar
        ];
        
        return view('Logistica.pedimentos.index', [
            'paginatedPedimentos' => $paginatedPedimentos,
            'stats' => $stats
        ]);
    }

    /**
     * Mostrar un pedimento específico con sus operaciones asociadas
     */
    public function show($id)
    {
        try {
            $pedimento = Pedimento::findOrFail($id);
            
            // Obtener operaciones asociadas a este pedimento
            $operaciones = OperacionLogistica::where('no_pedimento', $pedimento->clave)
                ->select('id', 'cliente', 'ejecutivo', 'aduana', 'fecha_embarque', 'tipo_operacion_enum', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'pedimento' => $pedimento,
                'operaciones' => $operaciones
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener detalles del pedimento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los detalles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar el estado de pago de un pedimento
     */
    public function updateEstadoPago(Request $request, $id)
    {
        try {
            $request->validate([
                'estado_pago' => 'required|in:pendiente,pagado,vencido',
                'fecha_pago' => 'nullable|date',
                'monto' => 'nullable|numeric|min:0',
                'observaciones_pago' => 'nullable|string|max:500',
                'fecha_vencimiento' => 'nullable|date'
            ]);

            $pedimento = Pedimento::findOrFail($id);

            $datosActualizacion = [
                'estado_pago' => $request->estado_pago,
                'monto' => $request->monto,
                'observaciones_pago' => $request->observaciones_pago,
                'fecha_vencimiento' => $request->fecha_vencimiento
            ];

            // Solo establecer fecha_pago si el estado es "pagado"
            if ($request->estado_pago === 'pagado') {
                $datosActualizacion['fecha_pago'] = $request->fecha_pago ?: now();
            } else {
                $datosActualizacion['fecha_pago'] = null;
            }

            $pedimento->update($datosActualizacion);

            return response()->json([
                'success' => true,
                'message' => 'Estado de pago actualizado correctamente',
                'pedimento' => $pedimento->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de pago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar múltiples pedimentos como pagados
     */
    public function marcarPagados(Request $request)
    {
        try {
            $request->validate([
                'pedimentos' => 'required|array',
                'pedimentos.*' => 'exists:pedimentos,id',
                'fecha_pago' => 'required|date',
                'monto' => 'nullable|numeric|min:0'
            ]);

            $actualizados = 0;
            foreach ($request->pedimentos as $pedimentoId) {
                $pedimento = Pedimento::find($pedimentoId);
                if ($pedimento && $pedimento->estado_pago !== 'pagado') {
                    $pedimento->update([
                        'estado_pago' => 'pagado',
                        'fecha_pago' => $request->fecha_pago,
                        'monto' => $request->monto
                    ]);
                    $actualizados++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Se marcaron {$actualizados} pedimentos como pagados",
                'actualizados' => $actualizados
            ]);

        } catch (\Exception $e) {
            Log::error('Error al marcar pedimentos como pagados: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar los pagos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo pedimento
     */
    public function store(Request $request)
    {
        Pedimento::create([
            'categoria' => $request->categoria,
            'subcategoria' => $request->subcategoria,
            'clave' => $request->clave,
            'descripcion' => $request->descripcion,
            'estado_pago' => 'pendiente',
            'fecha_vencimiento' => $request->fecha_vencimiento
        ]);
        
        return redirect()->back()->with('success', 'Pedimento creado exitosamente');
    }

    /**
     * Eliminar un pedimento
     */
    public function destroy($id)
    {
        $pedimento = Pedimento::findOrFail($id);
        $pedimento->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Obtener pedimentos de una clave específica
     */
    public function getPedimentosPorClave($clave)
    {
        $operaciones = OperacionLogistica::where('clave', $clave)
            ->whereNotNull('no_pedimento')
            ->where('no_pedimento', '!=', '')
            ->get();

        $pedimentos = [];
        foreach ($operaciones as $operacion) {
            $registroPago = PedimentoOperacion::where('no_pedimento', $operacion->no_pedimento)
                ->where('operacion_logistica_id', $operacion->id)
                ->first();
            
            $pedimentos[] = [
                'id' => $registroPago ? $registroPago->id : null,
                'operacion_id' => $operacion->id,
                'no_pedimento' => $operacion->no_pedimento,
                'clave' => $clave, // Agregar la clave que faltaba
                'cliente' => $operacion->cliente,
                'ejecutivo' => $operacion->ejecutivo,
                'fecha_embarque' => $operacion->fecha_embarque,
                'estado_pago' => $registroPago ? $registroPago->estado_pago : 'pendiente',
                'fecha_pago' => $registroPago ? $registroPago->fecha_pago : null,
                'monto' => $registroPago ? $registroPago->monto : null,
                'moneda' => $registroPago ? $registroPago->moneda : 'MXN',
                'observaciones' => $registroPago ? $registroPago->observaciones : null // Corregir nombre
            ];
        }

        return response()->json($pedimentos);
    }

    /**
     * Actualizar estado de un pedimento específico
     */
    public function actualizarPedimento(Request $request, $id = null)
    {
        $request->validate([
            'no_pedimento' => 'required|string',
            'operacion_logistica_id' => 'required|integer|exists:operaciones_logisticas,id',
            'estado_pago' => 'required|in:pendiente,pagado',
            'fecha_pago' => 'nullable|date',
            'monto' => 'nullable|numeric|min:0',
            'moneda' => 'nullable|string|max:10',
            'observaciones' => 'nullable|string|max:500'
        ]);

        // Buscar o crear el registro del pedimento individual
        $pedimentoOperacion = PedimentoOperacion::where('no_pedimento', $request->no_pedimento)
            ->where('operacion_logistica_id', $request->operacion_logistica_id)
            ->first();
        
        if (!$pedimentoOperacion) {
            // Obtener la clave de la operación
            $operacion = OperacionLogistica::findOrFail($request->operacion_logistica_id);
            
            $pedimentoOperacion = new PedimentoOperacion();
            $pedimentoOperacion->no_pedimento = $request->no_pedimento;
            $pedimentoOperacion->clave = $operacion->clave;
            $pedimentoOperacion->operacion_logistica_id = $request->operacion_logistica_id;
        }

        $pedimentoOperacion->estado_pago = $request->estado_pago;
        $pedimentoOperacion->monto = $request->monto;
        $pedimentoOperacion->moneda = $request->moneda ?: 'MXN';
        $pedimentoOperacion->observaciones = $request->observaciones;

        // Manejo de fecha de pago
        if ($request->estado_pago === 'pagado') {
            // Si se proporciona fecha manual, usarla; si no, usar fecha actual
            $pedimentoOperacion->fecha_pago = $request->fecha_pago ? \Carbon\Carbon::parse($request->fecha_pago) : now();
        } else {
            $pedimentoOperacion->fecha_pago = null;
        }

        $pedimentoOperacion->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado de pago del pedimento individual actualizado correctamente',
            'pedimento_operacion' => $pedimentoOperacion
        ]);
    }

    /**
     * Obtener monedas disponibles desde la API
     */
    public function getMonedas()
    {
        try {
            $response = file_get_contents('https://api.appnexus.com/currency');
            $data = json_decode($response, true);
            
            if ($data && isset($data['response']['currencies'])) {
                return response()->json($data['response']['currencies']);
            }
            
            // Fallback a monedas básicas si la API falla
            return response()->json([
                ['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar (USD)'],
                ['code' => 'MXN', 'symbol' => '$', 'name' => 'Mexican Peso (MXN)'],
                ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro (EUR)'],
                ['code' => 'GBP', 'symbol' => '£', 'name' => 'British Pound (GBP)']
            ]);
        } catch (Exception $e) {
            // Fallback en caso de error
            return response()->json([
                ['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar (USD)'],
                ['code' => 'MXN', 'symbol' => '$', 'name' => 'Mexican Peso (MXN)']
            ]);
        }
    }
}