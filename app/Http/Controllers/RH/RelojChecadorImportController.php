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

class RelojChecadorImportController extends Controller
{
    // ... (index, start, progress y update se mantienen igual) ...
    public function index(Request $request)
    {
        $query = Asistencia::with('empleado'); 

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('empleado_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        } else {
            $query->whereMonth('fecha', now()->month)
                  ->whereYear('fecha', now()->year);
        }

        $asistencias = $query->orderBy('fecha', 'desc')
                             ->orderBy('nombre', 'asc')
                             ->paginate(20)
                             ->withQueryString();

        return view('Recursos_Humanos.reloj_checador', compact('asistencias'));
    }

    public function start(Request $request)
    {
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

    // --- AQUÍ ESTÁ LA LÓGICA DE VACACIONES MASIVAS (CORREGIDA) ---
    public function store(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'fecha_inicio' => 'required|date',
            // Validar que fin sea igual o mayor a inicio
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio', 
            'tipo_registro' => 'required'
        ]);

        $empleado = \App\Models\Empleado::find($request->empleado_id);
        
        $fechaInicio = \Carbon\Carbon::parse($request->fecha_inicio);
        // Si no hay fecha fin, usar inicio (es solo un día)
        $fechaFin = $request->fecha_fin ? \Carbon\Carbon::parse($request->fecha_fin) : $fechaInicio->copy();

        $contador = 0;

        // Bucle día por día
        while ($fechaInicio->lte($fechaFin)) {
            
            // Opcional: Saltar fines de semana si lo deseas (descomentar si la empresa no trabaja S/D)
            // if ($fechaInicio->isWeekend()) { $fechaInicio->addDay(); continue; }

            Asistencia::updateOrInsert(
                [
                    'empleado_id' => $empleado->id,
                    'fecha' => $fechaInicio->toDateString(),
                ],
                [
                    'empleado_no' => $empleado->id_empleado ?? 'S/N',
                    'nombre' => $empleado->nombre,
                    'tipo_registro' => $request->tipo_registro,
                    'comentarios' => $request->comentarios,
                    'entrada' => null, // Limpiamos entrada/salida porque es incidencia
                    'salida' => null,
                    'checadas' => [], // <--- CORRECCIÓN: Array vacío para incidencias (evita error SQL)
                    'es_justificado' => true, // Incidencias creadas manualmente siempre son "justificadas"
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