<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Services\ProcesarAsistenciaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; // Necesario para DB::raw

class RelojChecadorImportController extends Controller
{
    public function index(Request $request)
    {
        $query = Asistencia::with('empleado'); 

        // Filtros (igual que antes)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('empleado_no', 'like', "%{$search}%");
            });
        }

        // Rango de Fechas (Default: Mes actual)
        $inicio = $request->filled('fecha_inicio') ? $request->fecha_inicio : now()->startOfMonth()->toDateString();
        $fin = $request->filled('fecha_fin') ? $request->fecha_fin : now()->endOfMonth()->toDateString();

        // Aplicar filtro de fechas a la consulta principal
        $query->whereBetween('fecha', [$inicio, $fin]);

        // --- CÁLCULO DE DASHBOARD (KPIs) ---
        // Usamos una consulta separada pero optimizada para los stats sobre el mismo rango
        $statsQuery = Asistencia::whereBetween('fecha', [$inicio, $fin]);
        
        $totalRegistros = $statsQuery->count();
        
        // 1. Asistencia Correcta (Entrada y Salida OK, sin retardos injustificados)
        $asistenciasOk = $statsQuery->clone()
            ->whereNotNull('entrada')
            ->whereNotNull('salida')
            ->where(function($q) {
                $q->where('es_retardo', false)
                  ->orWhere('es_justificado', true);
            })->count();

        // 2. Retardos (Tarde y NO justificado)
        $retardos = $statsQuery->clone()
            ->where('es_retardo', true)
            ->where('es_justificado', false)
            ->count();

        // 3. Faltas (Tipo 'falta' O sin registros)
        $faltas = $statsQuery->clone()
            ->where(function($q) {
                $q->where('tipo_registro', 'falta')
                  ->orWhere(function($sub) {
                      $sub->whereNull('entrada')->whereNull('salida');
                  });
            })->count();

        // 4. Top 3 Empleados con más Retardos
        $topRetardos = Asistencia::whereBetween('fecha', [$inicio, $fin])
            ->where('es_retardo', true)
            ->where('es_justificado', false)
            ->select('nombre', DB::raw('count(*) as total'))
            ->groupBy('nombre')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        // Calcular porcentaje de asistencia (Evitar división por cero)
        $porcentajeAsistencia = $totalRegistros > 0 
            ? round(($asistenciasOk / $totalRegistros) * 100, 1) 
            : 0;

        // Paginación para la tabla
        $asistencias = $query->orderBy('fecha', 'desc')
                             ->orderBy('nombre', 'asc')
                             ->paginate(20)
                             ->withQueryString();

        return view('Recursos_Humanos.reloj_checador', compact(
            'asistencias', 
            'totalRegistros', 
            'asistenciasOk', 
            'retardos', 
            'faltas', 
            'porcentajeAsistencia',
            'topRetardos'
        ));
    }

    public function start(Request $request)
    {
        // ... (código existente del método start) ...
        set_time_limit(300); 

        $request->validate([
            'archivo' => ['required', 'file', 'max:10240'],
            'progress_key' => ['required', 'string'], 
        ]);

        $file = $request->file('archivo');
        $filename = $file->getClientOriginalName();
        $path = $file->storeAs('imports/reloj', Str::uuid() . '_' . $filename);
        $fullPath = Storage::path($path);
        
        $progressKey = $request->progress_key;

        Cache::put($progressKey, [
            'status' => 'procesando',
            'percent' => 5,
            'mensaje' => 'Iniciando lectura...',
            'finalizado' => false
        ], now()->addMinutes(10));

        session()->save(); 

        try {
            if (!class_exists(ProcesarAsistenciaService::class)) {
                throw new \Exception("Clase de servicio no encontrada.");
            }

            $service = new ProcesarAsistenciaService();
            $resultado = $service->process($fullPath, true, function ($estado) use ($progressKey) {
                $p = 0;
                if (($estado['total'] ?? 0) > 0) {
                    $p = round(($estado['indice'] / $estado['total']) * 100);
                }
                Cache::put($progressKey, [
                    'status' => 'procesando',
                    'percent' => $p > 5 ? $p : 5, 
                    'mensaje' => "Procesando hoja {$estado['indice']} de {$estado['total']}",
                    'finalizado' => false
                ], now()->addMinutes(2));
            });

            Cache::put($progressKey, [
                'status' => 'completado',
                'percent' => 100,
                'mensaje' => "Completado. " . ($resultado['total_registros'] ?? 0) . " registros procesados.",
                'finalizado' => true
            ], now()->addMinutes(5));

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            Log::error("Error Importación: " . $e->getMessage());
            Cache::put($progressKey, [
                'status' => 'error',
                'percent' => 0,
                'mensaje' => "Error: " . $e->getMessage(),
                'finalizado' => true
            ], now()->addMinutes(5));
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ... (Mantener resto de métodos: progress, update, store, clear) ...
    public function progress(string $key)
    {
        $data = Cache::get($key);
        return response()->json($data ?? ['percent' => 0, 'finalizado' => false, 'mensaje' => 'Esperando inicio...']);
    }

    public function update(Request $request, $id)
    {
        $asistencia = Asistencia::findOrFail($id);
        $asistencia->update([
            'tipo_registro' => $request->tipo_registro,
            'comentarios' => $request->comentarios,
            'es_justificado' => $request->has('es_justificado'),
        ]);
        return back()->with('success', 'Actualizado.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio', 
            'tipo_registro' => 'required'
        ]);

        $empleado = \App\Models\Empleado::find($request->empleado_id);
        $fechaInicio = \Carbon\Carbon::parse($request->fecha_inicio);
        $fechaFin = $request->fecha_fin ? \Carbon\Carbon::parse($request->fecha_fin) : $fechaInicio->copy();

        $contador = 0;
        while ($fechaInicio->lte($fechaFin)) {
            Asistencia::updateOrInsert(
                ['empleado_id' => $empleado->id, 'fecha' => $fechaInicio->toDateString()],
                [
                    'empleado_no' => $empleado->id_empleado ?? 'S/N',
                    'nombre' => $empleado->nombre,
                    'tipo_registro' => $request->tipo_registro,
                    'comentarios' => $request->comentarios,
                    'entrada' => null,
                    'salida' => null,
                    'checadas' => [],
                    'es_justificado' => true,
                    'updated_at' => now(),
                ]
            );
            $fechaInicio->addDay();
            $contador++;
        }
        return back()->with('success', "Se registraron $contador días de {$request->tipo_registro}.");
    }

    public function clear()
    {
        try {
            Asistencia::truncate();
            return redirect()->route('reloj.index')->with('success', 'Base vaciada.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}