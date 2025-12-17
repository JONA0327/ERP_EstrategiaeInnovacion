<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Empleado;
use App\Services\ProcesarAsistenciaService; // Asegúrate de tener este servicio o usa la lógica anterior
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RelojChecadorImportController extends Controller
{
    /**
     * Muestra el panel principal y calcula KPIs de forma eficiente.
     */
    public function index(Request $request)
    {
        // 1. Definir Periodo (Default: Mes Actual)
        $inicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fin = $request->input('fecha_fin', now()->endOfMonth()->toDateString());

        // 2. Query Base (Reutilizable)
        // Usamos el Scope 'enPeriodo' que creamos en el modelo
        $baseQuery = Asistencia::enPeriodo($inicio, $fin);

        // 3. Cálculo de KPIs usando Scopes (Mucho más limpio)
        $kpis = [
            'total' => $baseQuery->count(),
            'ok' => (clone $baseQuery)->puntuales()->count(),
            'retardos' => (clone $baseQuery)->retardosInjustificados()->count(),
            'faltas' => (clone $baseQuery)->faltas()->count(),
        ];

        // Porcentaje de eficiencia
        $porcentajeAsistencia = $kpis['total'] > 0 
            ? round(($kpis['ok'] / $kpis['total']) * 100, 1) 
            : 0;

        // Top Retardos (Optimizado con DB::raw)
        $topRetardos = (clone $baseQuery)
            ->retardosInjustificados()
            ->select('nombre', DB::raw('count(*) as total'))
            ->groupBy('nombre')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        // 4. Obtener datos para la Tabla (Paginados y con búsqueda)
        $asistencias = Asistencia::with('empleado')
            ->enPeriodo($inicio, $fin)
            ->buscar($request->search) // Scope de búsqueda
            ->orderBy('fecha', 'desc')
            ->orderBy('nombre', 'asc')
            ->paginate(20)
            ->withQueryString();

        return view('Recursos_Humanos.reloj_checador', compact(
            'asistencias', 
            'porcentajeAsistencia',
            'topRetardos'
        ) + [
            'totalRegistros' => $kpis['total'],
            'asistenciasOk' => $kpis['ok'],
            'retardos' => $kpis['retardos'],
            'faltas' => $kpis['faltas']
        ]);
    }

    /**
     * Actualiza un registro individual (Edición rápida).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo_registro' => 'required|string',
            'comentarios' => 'nullable|string|max:255',
        ]);

        $asistencia = Asistencia::findOrFail($id);
        
        $asistencia->update([
            'tipo_registro' => $request->tipo_registro,
            'comentarios' => $request->comentarios,
            'es_justificado' => $request->has('es_justificado'),
        ]);

        return back()->with('success', 'Registro actualizado correctamente.');
    }

    /**
     * Registra incidencias masivas (Vacaciones, Incapacidades).
     * OPTIMIZADO: Usa upsert para evitar bucles de consultas.
     */
    public function store(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'tipo_registro' => 'required'
        ]);

        $empleado = Empleado::findOrFail($request->empleado_id);
        $inicio = Carbon::parse($request->fecha_inicio);
        $fin = $request->fecha_fin ? Carbon::parse($request->fecha_fin) : $inicio->copy();

        // Preparar array masivo
        $datosParaInsertar = [];
        $now = now();

        for ($date = $inicio->copy(); $date->lte($fin); $date->addDay()) {
            $datosParaInsertar[] = [
                'empleado_id' => $empleado->id,
                'fecha' => $date->toDateString(),
                'empleado_no' => $empleado->id_empleado ?? 'S/N',
                'nombre' => $empleado->nombre,
                'tipo_registro' => $request->tipo_registro,
                'comentarios' => $request->comentarios,
                'entrada' => null,
                'salida' => null,
                'checadas' => json_encode([]), // Campo obligatorio si es NOT NULL
                'es_justificado' => true,
                'es_retardo' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // UN SOLO query para insertar/actualizar todo (Mucho más rápido)
        Asistencia::upsert(
            $datosParaInsertar, 
            ['empleado_id', 'fecha'], // Columnas únicas para detectar duplicados
            ['tipo_registro', 'comentarios', 'es_justificado', 'entrada', 'salida', 'updated_at'] // Qué actualizar si ya existe
        );

        $dias = count($datosParaInsertar);
        return back()->with('success', "Se registraron $dias días de {$request->tipo_registro} para {$empleado->nombre}.");
    }

    /**
     * Proceso de Importación (Async con Cache)
     * Este código ya estaba bien diseñado, solo lo mantenemos limpio.
     */
    public function start(Request $request)
    {
        set_time_limit(300); 

        $request->validate([
            'archivo' => ['required', 'file', 'max:10240', 'mimes:xls,xlsx'],
            'progress_key' => ['required', 'string'], 
        ]);

        $file = $request->file('archivo');
        $path = $file->storeAs('imports/reloj', Str::uuid() . '_' . $file->getClientOriginalName());
        $fullPath = Storage::path($path);
        
        $key = $request->progress_key;
        $this->updateProgress($key, 'procesando', 5, 'Iniciando lectura...');

        try {
            if (!class_exists(ProcesarAsistenciaService::class)) {
                throw new \Exception("Servicio de procesamiento no encontrado.");
            }

            $service = new ProcesarAsistenciaService();
            
            $resultado = $service->process($fullPath, true, function ($estado) use ($key) {
                $percent = ($estado['total'] > 0) ? round(($estado['indice'] / $estado['total']) * 100) : 0;
                $this->updateProgress($key, 'procesando', max(5, $percent), "Procesando registros...");
            });

            $this->updateProgress($key, 'completado', 100, "Completado. " . ($resultado['total_registros'] ?? 0) . " registros.", true);

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            Log::error("Error Importación Reloj: " . $e->getMessage());
            $this->updateProgress($key, 'error', 0, "Error: " . $e->getMessage(), true);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Helper privado para limpiar código repetitivo en start()
    private function updateProgress($key, $status, $percent, $msg, $finalizado = false) {
        Cache::put($key, [
            'status' => $status,
            'percent' => $percent,
            'mensaje' => $msg,
            'finalizado' => $finalizado
        ], now()->addMinutes(10));
    }

    public function progress(string $key)
    {
        return response()->json(Cache::get($key) ?? ['percent' => 0, 'finalizado' => false]);
    }

    public function clear()
    {
        // Se podría agregar validación de permisos de admin aquí
        Asistencia::truncate();
        return redirect()->route('rh.reloj.index')->with('success', 'Base de datos de asistencia vaciada correctamente.');
    }
}