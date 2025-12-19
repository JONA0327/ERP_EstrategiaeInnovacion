<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\CriterioEvaluacion;
use App\Models\Evaluacion;
use App\Models\EvaluacionDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EvaluacionController extends Controller
{
    /**
     * Verifica si estamos en los últimos 10 días de Junio o Diciembre.
     */
    private function isEvaluationWindowOpen()
    {
        $now = Carbon::now();
        
        // Ventana de Junio (30 días): del 21 al 30
        $isJuneWindow = ($now->month == 6 && $now->day >= 21 && $now->day <= 30);
        
        // Ventana de Diciembre (31 días): del 22 al 31
        $isDecWindow = ($now->month == 12 && $now->day >= 22 && $now->day <= 31);

        // Retorna TRUE si estamos en fecha, FALSE si está cerrado
        return $isJuneWindow || $isDecWindow; 
        
        // --- PARA TUS PRUEBAS (Descomenta la siguiente línea para simular que está abierto) ---
        // return true; 
    }

    public function index(Request $request)
    {
        // 1. Periodos
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $defaultPeriod = ($currentMonth <= 6) ? "$currentYear | Enero - Junio" : "$currentYear | Julio - Diciembre";
        $selectedPeriod = $request->input('periodo', $defaultPeriod);
        
        $periodos = [
            ($currentYear + 1) . " | Enero - Junio",
            "$currentYear | Julio - Diciembre",
            "$currentYear | Enero - Junio",
            ($currentYear - 1) . " | Julio - Diciembre",
            ($currentYear - 1) . " | Enero - Junio",
        ];

        // 2. Verificar Ventana de Tiempo
        $isWindowOpen = $this->isEvaluationWindowOpen();

        // 3. Filtros y Empleados
        $areas = Empleado::select('posicion')->distinct()->pluck('posicion');
        $query = Empleado::query();

        if ($request->has('area') && $request->area !== 'Todos') {
            $query->where('posicion', 'LIKE', '%' . $request->area . '%');
        }

        // Cargamos empleados junto con su evaluación del periodo seleccionado
        $empleados = $query->get()->map(function($empleado) use ($selectedPeriod) {
            $empleado->evaluacion_actual = Evaluacion::where('empleado_id', $empleado->id)
                ->where('periodo', $selectedPeriod)
                ->first();
            return $empleado;
        });

        return view('Recursos_Humanos.evaluacion.index', compact('areas', 'empleados', 'periodos', 'selectedPeriod', 'isWindowOpen'));
    }

    public function show(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);
        $periodo = $request->query('periodo');

        // Buscar Evaluación Existente
        $evaluacionExistente = Evaluacion::with('detalles')
            ->where('empleado_id', $id)
            ->where('periodo', $periodo)
            ->first();

        $respuestas = [];
        $observaciones = [];
        if ($evaluacionExistente) {
            foreach ($evaluacionExistente->detalles as $detalle) {
                $respuestas[$detalle->criterio_id] = $detalle->calificacion;
                $observaciones[$detalle->criterio_id] = $detalle->observaciones;
            }
        }

        // Determinar Área de Evaluación
        $puesto = $empleado->posicion;
        $areaEvaluacion = 'General';

        if (str_contains($puesto, 'Logistica')) {
            $areaEvaluacion = 'Logistica';
        } elseif (str_contains($puesto, 'Pedimentos') || str_contains($puesto, 'Comercio Exterior')) {
            $areaEvaluacion = 'Pedimentos';
        } elseif (str_contains($puesto, 'TI') || str_contains($puesto, 'Sistemas') || str_contains($puesto, 'Tecnica')) {
            $areaEvaluacion = 'TI';
        } elseif (str_contains($puesto, 'Legal')) {
            $areaEvaluacion = 'Legal';
        } elseif (str_contains($puesto, 'Recursos Humanos') || str_contains($puesto, 'RH')) {
            $areaEvaluacion = 'RH';
        } elseif (str_contains($puesto, 'Auditoria')) {
            $areaEvaluacion = 'Auditoria';
        }

        $criterios = CriterioEvaluacion::where('area', $areaEvaluacion)->get();
        if ($criterios->isEmpty()) {
            $criterios = CriterioEvaluacion::where('area', 'General')->get();
        }

        // Filtrar Sidebar (Compañeros del mismo equipo)
        $querySidebar = Empleado::query();
        switch ($areaEvaluacion) {
            case 'Logistica':
                $querySidebar->where('posicion', 'LIKE', '%Logistica%');
                break;
            case 'Pedimentos':
                $querySidebar->where(function($q) {
                    $q->where('posicion', 'LIKE', '%Pedimentos%')
                      ->orWhere('posicion', 'LIKE', '%Comercio Exterior%');
                });
                break;
            case 'TI':
                $querySidebar->where(function($q) {
                    $q->where('posicion', 'LIKE', '%TI%')
                      ->orWhere('posicion', 'LIKE', '%Sistemas%')
                      ->orWhere('posicion', 'LIKE', '%Tecnica%');
                });
                break;
            case 'Legal':
                $querySidebar->where('posicion', 'LIKE', '%Legal%');
                break;
            case 'RH':
                $querySidebar->where(function($q) {
                    $q->where('posicion', 'LIKE', '%Recursos Humanos%')
                      ->orWhere('posicion', 'LIKE', '%RH%');
                });
                break;
            case 'Auditoria':
                $querySidebar->where('posicion', 'LIKE', '%Auditoria%');
                break;
            default:
                $querySidebar->where('area', $empleado->area);
                break;
        }
        $empleadosSidebar = $querySidebar->get();

        // Verificar Bloqueo (Fecha cerrada O Ediciones agotadas)
        $isWindowOpen = $this->isEvaluationWindowOpen();
        $is_locked = ($evaluacionExistente && $evaluacionExistente->edit_count >= 1) || !$isWindowOpen;

        return view('Recursos_Humanos.evaluacion.show', [
            'empleado' => $empleado,
            'area' => $areaEvaluacion,
            'empleados' => $empleadosSidebar,
            'criterios' => $criterios,
            'periodo' => $periodo,
            'evaluacion' => $evaluacionExistente, 
            'respuestas' => $respuestas,
            'observaciones' => $observaciones,
            'is_locked' => $is_locked,
            'isWindowOpen' => $isWindowOpen
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validar Fecha
        if (!$this->isEvaluationWindowOpen()) {
            return redirect()->route('rh.evaluacion.index')
                ->with('error', 'El periodo de evaluaciones está cerrado. Solo disponible los últimos 10 días del semestre.');
        }

        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'periodo' => 'required|string',
            'calificaciones' => 'required|array',
        ]);

        try {
            DB::beginTransaction();
            $calificaciones = collect($request->calificaciones);
            $promedio = $calificaciones->avg(); 

            $evaluacion = Evaluacion::create([
                'empleado_id' => $request->empleado_id,
                'evaluador_id' => Auth::id() ?? 1,
                'periodo' => $request->periodo,
                'promedio_final' => $promedio,
                'comentarios_generales' => $request->comentarios_generales,
                'edit_count' => 0 
            ]);

            foreach ($request->calificaciones as $criterioId => $valor) {
                EvaluacionDetalle::create([
                    'evaluacion_id' => $evaluacion->id,
                    'criterio_id' => $criterioId,
                    'calificacion' => $valor,
                    'observaciones' => $request->observaciones[$criterioId] ?? null
                ]);
            }
            DB::commit();
            return redirect()->route('rh.evaluacion.index', ['periodo' => $request->periodo])->with('success', 'Evaluación creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        // 1. Validar Fecha
        if (!$this->isEvaluationWindowOpen()) {
            return redirect()->route('rh.evaluacion.index')
                ->with('error', 'El periodo de evaluaciones está cerrado. No se permiten ediciones fuera de fecha.');
        }

        $evaluacion = Evaluacion::findOrFail($id);

        // 2. Validar Ediciones Agotadas
        if ($evaluacion->edit_count >= 1) {
            return back()->with('error', 'Esta evaluación ya ha sido editada una vez y no se puede modificar más.');
        }

        try {
            DB::beginTransaction();

            $calificaciones = collect($request->calificaciones);
            $promedio = $calificaciones->avg(); 

            $evaluacion->update([
                'promedio_final' => $promedio,
                'comentarios_generales' => $request->comentarios_generales,
                'edit_count' => $evaluacion->edit_count + 1 
            ]);

            $evaluacion->detalles()->delete();

            foreach ($request->calificaciones as $criterioId => $valor) {
                EvaluacionDetalle::create([
                    'evaluacion_id' => $evaluacion->id,
                    'criterio_id' => $criterioId,
                    'calificacion' => $valor,
                    'observaciones' => $request->observaciones[$criterioId] ?? null
                ]);
            }

            DB::commit();
            return redirect()->route('rh.evaluacion.index', ['periodo' => $evaluacion->periodo])
                             ->with('success', 'Evaluación actualizada. Se ha bloqueado para futuras ediciones.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }
}