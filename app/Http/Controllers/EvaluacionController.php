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
    public function index(Request $request)
    {
        // 1. Periodos (Igual que antes)
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

        // 2. Filtros
        $areas = Empleado::select('posicion')->distinct()->pluck('posicion');
        $query = Empleado::query();

        if ($request->has('area') && $request->area !== 'Todos') {
            $query->where('posicion', 'LIKE', '%' . $request->area . '%');
        }

        // 3. Eager Loading inteligente: Traemos los empleados CON su evaluación del periodo actual
        $empleados = $query->get()->map(function($empleado) use ($selectedPeriod) {
            $empleado->evaluacion_actual = Evaluacion::where('empleado_id', $empleado->id)
                ->where('periodo', $selectedPeriod)
                ->first();
            return $empleado;
        });

        return view('Recursos_Humanos.evaluacion.index', compact('areas', 'empleados', 'periodos', 'selectedPeriod'));
    }

    public function show(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);
        $periodo = $request->query('periodo');

        // Buscar si ya existe evaluación (Tu lógica existente)
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

        // --- LÓGICA DE ÁREA (Ya la tenías, la usamos de base) ---
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

        // Obtener Criterios
        $criterios = CriterioEvaluacion::where('area', $areaEvaluacion)->get();
        if ($criterios->isEmpty()) {
            $criterios = CriterioEvaluacion::where('area', 'General')->get();
        }

        // ---------------------------------------------------------
        // CORRECCIÓN: FILTRAR EMPLEADOS POR LA MISMA LÓGICA DEL ÁREA
        // ---------------------------------------------------------
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
                // Si es 'General', usamos la columna area de la BD como fallback
                $querySidebar->where('area', $empleado->area);
                break;
        }

        $empleados = $querySidebar->get();
        // ---------------------------------------------------------

        return view('Recursos_Humanos.evaluacion.show', [
            'empleado' => $empleado,
            'area' => $areaEvaluacion,
            'empleados' => $empleados, // Ahora contiene la lista filtrada correctamente
            'criterios' => $criterios,
            'periodo' => $periodo,
            'evaluacion' => $evaluacionExistente, 
            'respuestas' => $respuestas,
            'observaciones' => $observaciones,
            'is_locked' => ($evaluacionExistente && $evaluacionExistente->edit_count >= 1)
        ]);
    }

    public function store(Request $request)
    {
        // ... (Tu código store actual se mantiene igual) ...
        // Asegúrate de importar DB y Auth arriba
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
                'edit_count' => 0 // Comienza en 0
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
            return redirect()->route('rh.evaluacion.index', ['periodo' => $request->periodo])->with('success', 'Evaluación creada.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // NUEVO MÉTODO UPDATE
    public function update(Request $request, $id)
    {
        $evaluacion = Evaluacion::findOrFail($id);

        // 1. Validar bloqueo
        if ($evaluacion->edit_count >= 1) {
            return back()->with('error', 'Esta evaluación ya fue editada y se encuentra cerrada.');
        }

        try {
            DB::beginTransaction();

            $calificaciones = collect($request->calificaciones);
            $promedio = $calificaciones->avg(); 

            // 2. Actualizar cabecera incrementando edit_count
            $evaluacion->update([
                'promedio_final' => $promedio,
                'comentarios_generales' => $request->comentarios_generales,
                'edit_count' => $evaluacion->edit_count + 1 // <--- Esto ahora sí funcionará
            ]);

            // 3. Borrar detalles viejos y guardar los nuevos
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
            
            return redirect()
                ->route('rh.evaluacion.index', ['periodo' => $evaluacion->periodo])
                ->with('success', 'Evaluación actualizada correctamente. Se ha bloqueado para futuras ediciones.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}