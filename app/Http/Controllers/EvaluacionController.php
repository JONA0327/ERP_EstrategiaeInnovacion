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
        // Ventana de Junio (21 al 30) y Diciembre (22 al 31)
        return ($now->month == 6 && $now->day >= 21 && $now->day <= 30) || 
               ($now->month == 12 && $now->day >= 22 && $now->day <= 31);
        
        // return true; // Descomentar para pruebas
    }

    /**
     * Verifica si el usuario logueado tiene permiso para evaluar al objetivo.
     * Regla: Solo puedo evaluar a mis subordinados o a mi supervisor directo.
     */
    private function canEvaluate($targetId)
    {
        $user = Auth::user();
        if (!$user) return false;

        // Buscamos el perfil de empleado del usuario logueado
        // Asumiendo que el User y Empleado se vinculan por correo
        $me = Empleado::where('correo', $user->email)->first(); 

        // Si soy admin o de RH, podría tener permiso total (Opcional)
        // if ($user->hasRole('admin') || str_contains($me->area, 'Recursos Humanos')) return true;

        if (!$me) return false; // Si no tiene perfil de empleado, no evalúa

        $target = Empleado::find($targetId);
        if (!$target) return false;

        // 1. Es mi subordinado (Yo soy su supervisor)
        $isSubordinate = ($target->supervisor_id == $me->id);

        // 2. Es mi supervisor (Él es mi supervisor)
        $isBoss = ($me->supervisor_id == $target->id);

        // 3. Soy yo mismo (Autoevaluación - Opcional, si quisieras permitirlo)
        // $isMe = ($me->id == $target->id);

        return $isSubordinate || $isBoss;
    }

    public function index(Request $request)
    {
        // 1. Lógica de Periodos
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

        // 2. Filtrado por Jerarquía
        $user = Auth::user();
        $me = Empleado::where('correo', $user->email)->first();

        $query = Empleado::query();

        if ($me) {
            // Solo mostrar: Mis subordinados O Mi jefe
            $query->where(function($q) use ($me) {
                $q->where('supervisor_id', $me->id)   // Mis subordinados
                  ->orWhere('id', $me->supervisor_id); // Mi jefe
            });
        } else {
            // Si el usuario no es empleado (ej. Super Admin genérico), quizás ver todos o ninguno.
            // Aquí dejamos vacío para que no vea nada por seguridad, o todos si es admin.
             if (!$user->hasRole('admin')) { // Asumiendo Spatie o similar
                 $query->where('id', 0); // No mostrar nada
             }
        }

        // 3. Filtros de Búsqueda (Visuales)
        if ($request->has('area') && $request->area !== 'Todos') {
            $query->where('posicion', 'LIKE', '%' . $request->area . '%');
        }

        // Ejecutar consulta y cargar estado de evaluación
        $empleados = $query->get()->map(function($empleado) use ($selectedPeriod) {
            $empleado->evaluacion_actual = Evaluacion::where('empleado_id', $empleado->id)
                ->where('periodo', $selectedPeriod)
                ->first();
            return $empleado;
        });

        // Obtener áreas solo de los empleados visibles
        $areas = $empleados->pluck('posicion')->unique();

        return view('Recursos_Humanos.evaluacion.index', compact('areas', 'empleados', 'periodos', 'selectedPeriod', 'isWindowOpen'));
    }

    public function show(Request $request, $id)
    {
        // 1. Validar Permisos de Jerarquía
        if (!$this->canEvaluate($id)) {
            return redirect()->route('rh.evaluacion.index')
                ->with('error', 'No tienes permiso para evaluar a este empleado.');
        }

        $empleado = Empleado::findOrFail($id);
        $periodo = $request->query('periodo');

        // ... (Carga de datos de evaluación igual que antes) ...
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

        // Lógica de Áreas para Criterios
        $puesto = $empleado->posicion;
        $areaEvaluacion = 'General';
        if (str_contains($puesto, 'Logistica')) $areaEvaluacion = 'Logistica';
        elseif (str_contains($puesto, 'Pedimentos') || str_contains($puesto, 'Comercio Exterior')) $areaEvaluacion = 'Pedimentos';
        elseif (str_contains($puesto, 'TI') || str_contains($puesto, 'Sistemas')) $areaEvaluacion = 'TI';
        elseif (str_contains($puesto, 'Legal')) $areaEvaluacion = 'Legal';
        elseif (str_contains($puesto, 'Recursos Humanos') || str_contains($puesto, 'RH')) $areaEvaluacion = 'RH';
        elseif (str_contains($puesto, 'Auditoria')) $areaEvaluacion = 'Auditoria';

        $criterios = CriterioEvaluacion::where('area', $areaEvaluacion)->get();
        if ($criterios->isEmpty()) {
            $criterios = CriterioEvaluacion::where('area', 'General')->get();
        }

        // Sidebar: Filtrar usando la misma lógica de "Quién puedo ver"
        // Para no complicar, en el sidebar mostramos SOLO al que estamos evaluando
        // O repetimos la lógica de mis subordinados/jefe.
        $me = Empleado::where('correo', Auth::user()->email)->first();
        $empleadosSidebar = Empleado::where(function($q) use ($me) {
             $q->where('supervisor_id', $me->id)
               ->orWhere('id', $me->supervisor_id);
        })->get();


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
        if (!$this->isEvaluationWindowOpen()) {
            return redirect()->route('rh.evaluacion.index')->with('error', 'Periodo cerrado.');
        }

        // Validar Jerarquía
        if (!$this->canEvaluate($request->empleado_id)) {
            return redirect()->route('rh.evaluacion.index')->with('error', 'No autorizado.');
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
        if (!$this->isEvaluationWindowOpen()) {
            return redirect()->route('rh.evaluacion.index')->with('error', 'Periodo cerrado.');
        }

        $evaluacion = Evaluacion::findOrFail($id);

        // Validar Jerarquía usando el empleado de la evaluación
        if (!$this->canEvaluate($evaluacion->empleado_id)) {
            return redirect()->route('rh.evaluacion.index')->with('error', 'No autorizado.');
        }

        if ($evaluacion->edit_count >= 1) {
            return back()->with('error', 'Edición bloqueada.');
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
            return redirect()->route('rh.evaluacion.index', ['periodo' => $evaluacion->periodo])->with('success', 'Evaluación actualizada.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}