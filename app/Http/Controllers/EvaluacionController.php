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
    // ... (isEvaluationWindowOpen se mantiene igual) ...
    private function isEvaluationWindowOpen()
    {
        $now = Carbon::now();
        return ($now->month == 6 && $now->day >= 21 && $now->day <= 30) || 
               ($now->month == 12 && $now->day >= 22 && $now->day <= 31);
    }

    /**
     * Verifica si es RH.
     */
    private function isHR($user)
    {
        $empleado = Empleado::where('correo', $user->email)->first();
        if ($empleado) {
            return str_contains($empleado->area, 'Recursos Humanos') || str_contains($empleado->area, 'RH');
        }
        return false;
    }

    /**
     * Verifica si el empleado es JEFE (tiene gente a cargo).
     */
    private function isManager($empleado)
    {
        if (!$empleado) return false;
        // Verifica si existe al menos un empleado que lo tenga como supervisor
        return Empleado::where('supervisor_id', $empleado->id)->exists();
    }

    /**
     * Permisos de visualización.
     */
    private function canEvaluate($targetId)
    {
        $user = Auth::user();
        if (!$user) return false;
        $me = Empleado::where('correo', $user->email)->first();
        if (!$me) return false;

        $isUserHR = $this->isHR($user);
        $isManager = $this->isManager($me);

        // CASO 1: Empleada de RH (No Jefa) -> Puede ver a TODOS para Soft Skills
        if ($isUserHR && !$isManager) {
            return true;
        }

        // CASO 2: Resto del mundo (Incluida Coordinadora RH) -> Solo su círculo
        $target = Empleado::find($targetId);
        if (!$target) return false;

        $isSubordinate = ($target->supervisor_id == $me->id);
        $isBoss = ($me->supervisor_id == $target->id);
        $isMe = ($me->id == $target->id);

        return $isSubordinate || $isBoss || $isMe;
    }

    public function index(Request $request)
    {
        // ... (Variables de fecha y periodo igual) ...
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

        $isWindowOpen = $this->isEvaluationWindowOpen();

        // --- LÓGICA DE VISUALIZACIÓN ---
        $user = Auth::user();
        $me = Empleado::where('correo', $user->email)->first();
        
        $isUserHR = $this->isHR($user);
        $isManager = $this->isManager($me); // ¿Es jefa?

        $query = Empleado::query();

        if ($isUserHR && !$isManager) {
            // CASO EMPLEADA RH: Ve a TODOS (para evaluar soft skills)
            // No aplicamos filtros de ID
        } elseif ($me) {
            // CASO COORDINADORA RH Y DEMÁS JEFES: Ven solo a su gente
            $query->where(function($q) use ($me) {
                $q->where('supervisor_id', $me->id)    // Mis subordinados
                  ->orWhere('id', $me->supervisor_id)  // Mi jefe
                  ->orWhere('id', $me->id);            // Yo mismo
            });
        } else {
            $query->where('id', 0); // Nadie
        }

        // Filtros visuales
        if ($request->has('area') && $request->area !== 'Todos') {
            $query->where('posicion', 'LIKE', '%' . $request->area . '%');
        }

        $empleados = $query->get()->map(function($empleado) use ($selectedPeriod) {
            $empleado->evaluacion_actual = Evaluacion::where('empleado_id', $empleado->id)
                ->where('periodo', $selectedPeriod)
                ->first();
            return $empleado;
        });

        $areas = Empleado::select('posicion')->distinct()->pluck('posicion');

        return view('Recursos_Humanos.evaluacion.index', compact('areas', 'empleados', 'periodos', 'selectedPeriod', 'isWindowOpen'));
    }

    public function show(Request $request, $id)
    {
        if (!$this->canEvaluate($id)) {
            return redirect()->route('rh.evaluacion.index')->with('error', 'No autorizado.');
        }

        $empleado = Empleado::findOrFail($id);
        $periodo = $request->query('periodo');

        // ... (Carga de evaluación existente igual) ...
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

        // --- LÓGICA DE CRITERIOS FILTRADOS ---
        $user = Auth::user();
        $me = Empleado::where('correo', $user->email)->first();
        $isUserHR = $this->isHR($user);
        $isManager = $this->isManager($me);

        // Determinar área técnica del evaluado
        $puesto = $empleado->posicion;
        $areaEvaluacion = 'General';
        if (str_contains($puesto, 'Logistica')) $areaEvaluacion = 'Logistica';
        elseif (str_contains($puesto, 'Pedimentos') || str_contains($puesto, 'Comercio Exterior')) $areaEvaluacion = 'Pedimentos';
        elseif (str_contains($puesto, 'TI') || str_contains($puesto, 'Sistemas')) $areaEvaluacion = 'TI';
        elseif (str_contains($puesto, 'Legal')) $areaEvaluacion = 'Legal';
        elseif (str_contains($puesto, 'Auditoria')) $areaEvaluacion = 'Auditoria';

        $queryCriterios = CriterioEvaluacion::query();

        // REGLA DE ORO:
        // Si es Empleada RH (No Jefa) Y NO se está evaluando a sí misma -> SOLO SOFT SKILLS
        if ($isUserHR && !$isManager && $empleado->id !== $me->id) {
            $queryCriterios->where(function($q) {
                $q->where('area', 'Recursos Humanos')
                  ->orWhere('area', 'General');
            });
        } 
        // Si es Jefe (o Coordinadora con su equipo) o Autoevaluación -> TODO (Técnico + Soft)
        else {
            $queryCriterios->where(function($q) use ($areaEvaluacion) {
                $q->where('area', $areaEvaluacion)
                  ->orWhere('area', 'Recursos Humanos')
                  ->orWhere('area', 'General');
            });
        }
        
        $criterios = $queryCriterios->get();

        // Sidebar (Mantener contexto)
        // Si es Empleada RH -> Ve a los del área del evaluado (para navegar fácil)
        // Si es Jefe -> Ve a su equipo
        if ($isUserHR && !$isManager) {
            $empleadosSidebar = Empleado::where('area', $empleado->area)->get();
        } else {
            $empleadosSidebar = Empleado::where(function($q) use ($me) {
                 $q->where('supervisor_id', $me->id)
                   ->orWhere('id', $me->supervisor_id)
                   ->orWhere('id', $me->id);
            })->get();
        }

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

    // ... (store y update se mantienen igual, usan canEvaluate) ...
    public function store(Request $request)
    {
        if (!$this->isEvaluationWindowOpen()) return redirect()->back()->with('error', 'Periodo cerrado.');
        if (!$this->canEvaluate($request->empleado_id)) return abort(403);

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
                'evaluador_id' => Auth::id(),
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
            return redirect()->route('rh.evaluacion.index', ['periodo' => $request->periodo])->with('success', 'Evaluación guardada.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        if (!$this->isEvaluationWindowOpen()) return redirect()->back()->with('error', 'Periodo cerrado.');
        
        $evaluacion = Evaluacion::findOrFail($id);
        if (!$this->canEvaluate($evaluacion->empleado_id)) return abort(403);
        if ($evaluacion->edit_count >= 1) return back()->with('error', 'Edición bloqueada.');

        try {
            DB::beginTransaction();
            $calificaciones = collect($request->calificaciones);
            $promedio = $calificaciones->avg(); 

            $evaluacion->update([
                'promedio_final' => $promedio,
                'comentarios_generales' => $request->comentarios_generales,
                'edit_count' => $evaluacion->edit_count + 1 
            ]);

            $evaluacion->detalles()->delete(); // Borramos detalles viejos para insertar los nuevos (simple y efectivo)

            foreach ($request->calificaciones as $criterioId => $valor) {
                EvaluacionDetalle::create([
                    'evaluacion_id' => $evaluacion->id,
                    'criterio_id' => $criterioId,
                    'calificacion' => $valor,
                    'observaciones' => $request->observaciones[$criterioId] ?? null
                ]);
            }
            DB::commit();
            return redirect()->route('rh.evaluacion.index', ['periodo' => $evaluacion->periodo])->with('success', 'Evaluación actualizada.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}