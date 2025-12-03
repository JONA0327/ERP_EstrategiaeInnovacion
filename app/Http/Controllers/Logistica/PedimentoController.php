<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use App\Models\Logistica\Pedimento;
use App\Models\Logistica\OperacionLogistica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PedimentoController extends Controller
{
    /**
     * Mostrar la lista de pedimentos con su estado de pago
     */
    public function index(Request $request)
    {
        try {
            // Obtener pedimentos únicos de las operaciones logísticas
            $query = OperacionLogistica::whereNotNull('no_pedimento')
                ->where('no_pedimento', '!=', '')
                ->select('no_pedimento')
                ->distinct();

            // Aplicar filtros
            if ($request->filled('estado_pago')) {
                // Este filtro lo aplicaremos después de obtener los pedimentos
            }

            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $query->where('no_pedimento', 'like', "%{$buscar}%");
            }

            $numerosPedimento = $query->pluck('no_pedimento');

            // Buscar o crear registros de pedimentos
            $pedimentos = collect();
            foreach ($numerosPedimento as $numeroPedimento) {
                $pedimento = Pedimento::where('clave', $numeroPedimento)->first();
                
                if (!$pedimento) {
                    // Crear pedimento si no existe
                    $pedimento = Pedimento::create([
                        'categoria' => 'Operación',
                        'subcategoria' => 'Logística',
                        'clave' => $numeroPedimento,
                        'descripcion' => "Pedimento asociado a operaciones logísticas",
                        'estado_pago' => 'pendiente'
                    ]);
                }

                // Obtener operaciones asociadas
                $operacionesAsociadas = OperacionLogistica::where('no_pedimento', $numeroPedimento)->get();
                $pedimento->operaciones_count = $operacionesAsociadas->count();
                $pedimento->operaciones_asociadas = $operacionesAsociadas;

                $pedimentos->push($pedimento);
            }

            // Aplicar filtro por estado si se especifica
            if ($request->filled('estado_pago')) {
                $pedimentos = $pedimentos->filter(function ($pedimento) use ($request) {
                    return $pedimento->estado_pago === $request->estado_pago;
                });
            }

            // Paginación manual
            $page = $request->get('page', 1);
            $perPage = 15;
            $total = $pedimentos->count();
            $pedimentos = $pedimentos->forPage($page, $perPage);

            // Simular objeto de paginación
            $paginatedPedimentos = new \Illuminate\Pagination\LengthAwarePaginator(
                $pedimentos,
                $total,
                $perPage,
                $page,
                [
                    'path' => $request->url(),
                    'pageName' => 'page',
                ]
            );

            $paginatedPedimentos->appends($request->query());

            // Estadísticas
            $stats = [
                'total' => $numerosPedimento->count(),
                'pendientes' => Pedimento::whereIn('clave', $numerosPedimento)->pendientes()->count(),
                'pagados' => Pedimento::whereIn('clave', $numerosPedimento)->pagados()->count(),
                'vencidos' => Pedimento::whereIn('clave', $numerosPedimento)->vencidos()->count(),
            ];

            return view('Logistica.pedimentos.index', compact('paginatedPedimentos', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error en PedimentoController@index: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los pedimentos: ' . $e->getMessage());
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

            $pedimento->update([
                'estado_pago' => $request->estado_pago,
                'fecha_pago' => $request->fecha_pago,
                'monto' => $request->monto,
                'observaciones_pago' => $request->observaciones_pago,
                'fecha_vencimiento' => $request->fecha_vencimiento
            ]);

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
     * Obtener detalles de un pedimento
     */
    public function show($id)
    {
        try {
            $pedimento = Pedimento::findOrFail($id);
            
            // Obtener operaciones asociadas
            $operaciones = OperacionLogistica::where('no_pedimento', $pedimento->clave)
                ->with(['ejecutivo'])
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
     * Eliminar un pedimento
     */
    public function destroy($id)
    {
        try {
            $pedimento = Pedimento::findOrFail($id);
            
            // Verificar si hay operaciones asociadas
            $operacionesCount = OperacionLogistica::where('no_pedimento', $pedimento->clave)->count();
            
            if ($operacionesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar el pedimento. Tiene {$operacionesCount} operaciones asociadas."
                ], 400);
            }

            $pedimento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pedimento eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar pedimento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el pedimento: ' . $e->getMessage()
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
                'message' => "Se marcaron {$actualizados} pedimentos como pagados"
            ]);

        } catch (\Exception $e) {
            Log::error('Error al marcar pedimentos como pagados: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar los pagos: ' . $e->getMessage()
            ], 500);
        }
    }
}