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
    private function isEvaluationWindowOpen()
    {
        $now = Carbon::now();
        // Ajusta las fechas a tu necesidad
        return ($now->month == 6 && $now->day >= 21 && $now->day <= 30) || 
               ($now->month == 12 && $now->day >= 22 && $now->day <= 31);
    }

    private function isHR($user)
    {
        $empleado = Empleado::where('correo', $user->email)->first();
        if ($empleado) {
            return str_contains($empleado->area, 'Recursos Humanos') || str_contains($empleado->area, 'RH');
        }
        return false;
    }

    private function isManager($empleado)
    {
        if (!$empleado) return false;
        return Empleado::where('supervisor_id', $empleado->id)->exists();
    }

    private function canEvaluate($targetId)
    {
        $user = Auth::user();
        if (!$user) return false;
        $me = Empleado::where('correo', $user->email)->first();
        if (!$me) return false;

        $isUserHR = $this->isHR($user);
        $isManager = $this->isManager($me);

        // 1. Empleada RH (No Jefa) -> Ve a TODOS (Solo Soft Skills)
        if ($isUserHR && !$isManager) return true;

        // 2. Jefes y Empleados normales -> Solo su círculo
        $target = Empleado::find($targetId);
        if (!$target) return false;

        $isSubordinate = ($target->supervisor_id == $me->id);
        $isBoss = ($me->supervisor_id == $target->id);
        $isMe = ($me->id == $target->id);

        return $isSubordinate || $isBoss || $isMe;
    }

    public function index(Request $request)
    {
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
        $user = Auth::user();
        $me = Empleado::where('correo', $user->email)->first();
        
        $isUserHR = $this->isHR($user);
        $isManager = $this->isManager($me);

        $query = Empleado::query();

        if ($isUserHR && !$isManager) {
            // RH ve a todos
        } elseif ($me) {
            $query->where(function($q) use ($me) {
                $q->where('supervisor_id', $me->id)
                  ->orWhere('id', $me->supervisor_id)
                  ->orWhere('id', $me->id);
            });
        } else {
            $query->where('id', 0);
        }

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

        // Cargar evaluación
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

        // --- Permisos y Roles ---
        $user = Auth::user();
        $me = Empleado::where('correo', $user->email)->first();
        $isUserHR = $this->isHR($user);
        $isManager = $this->isManager($me);
        $isMe = ($me && $me->id == $empleado->id);

        // --- Criterios ---
        $puesto = $empleado->posicion;
        $areaEvaluacion = 'General';
        if (str_contains($puesto, 'Logistica')) $areaEvaluacion = 'Logistica';
        elseif (str_contains($puesto, 'Pedimentos') || str_contains($puesto, 'Comercio Exterior')) $areaEvaluacion = 'Pedimentos';
        elseif (str_contains($puesto, 'TI') || str_contains($puesto, 'Sistemas')) $areaEvaluacion = 'TI';
        elseif (str_contains($puesto, 'Legal')) $areaEvaluacion = 'Legal';
        elseif (str_contains($puesto, 'Auditoria')) $areaEvaluacion = 'Auditoria';

        $queryCriterios = CriterioEvaluacion::query();

        // Si es RH Auxiliar -> Solo Soft Skills
        if ($isUserHR && !$isManager && !$isMe) {
            $queryCriterios->where(function($q) {
                $q->where('area', 'Recursos Humanos')->orWhere('area', 'General');
            });
        } else {
            // Jefes -> Todo
            $queryCriterios->where(function($q) use ($areaEvaluacion) {
                $q->where('area', $areaEvaluacion)
                  ->orWhere('area', 'Recursos Humanos')
                  ->orWhere('area', 'General');
            });
        }
        $criterios = $queryCriterios->get();

        // --- Sidebar con Estado (Mejora visual) ---
        if ($isUserHR && !$isManager) {
            $empleadosSidebar = Empleado::where('area', $empleado->area)->get();
        } else {
            $empleadosSidebar = Empleado::where(function($q) use ($me) {
                 $q->where('supervisor_id', $me->id)
                   ->orWhere('id', $me->supervisor_id)
                   ->orWhere('id', $me->id);
            })->get();
        }

        // Cargamos el estado para los iconos del sidebar
        $empleadosSidebar->map(function($emp) use ($periodo) {
            $emp->eval_status = Evaluacion::where('empleado_id', $emp->id)
                ->where('periodo', $periodo)
                ->select('edit_count')
                ->first();
            return $emp;
        });

        $isWindowOpen = $this->isEvaluationWindowOpen();
        $isFinalized = ($evaluacionExistente && $evaluacionExistente->edit_count >= 1);
        
        // Bloqueo de edición
        $canEditContent = $isWindowOpen && !$isFinalized && !$isMe;

        return view('Recursos_Humanos.evaluacion.show', [
            'empleado' => $empleado,
            'area' => $areaEvaluacion,
            'empleados' => $empleadosSidebar,
            'criterios' => $criterios,
            'periodo' => $periodo,
            'evaluacion' => $evaluacionExistente,
            'respuestas' => $respuestas,
            'observaciones' => $observaciones,
            'is_locked' => !$canEditContent,
            'isWindowOpen' => $isWindowOpen,
            'isMe' => $isMe // Pasamos esta variable para ocultar el formulario en la vista
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->isEvaluationWindowOpen()) return back()->with('error', 'Periodo cerrado.');
        if (!$this->canEvaluate($request->empleado_id)) return abort(403);
        
        // Seguridad Extra: Empleado no se edita a sí mismo
        $user = Auth::user();
        $me = Empleado::where('correo', $user->email)->first();
        if ($me && $me->id == $request->empleado_id) return back()->with('error', 'Acción no permitida.');

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
        if (!$this->isEvaluationWindowOpen()) return back()->with('error', 'Periodo cerrado.');
        $evaluacion = Evaluacion::findOrFail($id);
        
        $user = Auth::user();
        $me = Empleado::where('correo', $user->email)->first();
        if ($me && $me->id == $evaluacion->empleado_id) return back()->with('error', 'Acción no permitida.');

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
            return redirect()->route('rh.evaluacion.index', ['periodo' => $evaluacion->periodo])->with('success', 'Evaluación actualizada.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}