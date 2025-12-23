<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Activity;
use App\Models\Empleado;
use App\Models\User;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $miEmpleado = $user->empleado; 

        // 1. DETECTAR SI ES DIRECCIÓN
        $esDireccion = $this->esPersonalDeDireccion($miEmpleado);

        // 2. OBTENER SCOPE
        $teamUserIds = [];

        if ($esDireccion) {
            // MODO DIRECTOR: Ver todo o filtrar por líder
            if ($request->filled('ver_equipo_de')) {
                $liderId = $request->ver_equipo_de;
                $teamUserIds = $this->obtenerIdsSubordinadosRecursivo($liderId);
                $liderUser = Empleado::find($liderId)?->user_id;
                if ($liderUser) $teamUserIds[] = $liderUser;
            } else {
                $teamUserIds = User::pluck('id')->toArray();
            }
        } else {
            // MODO MORTAL: Ver lo mío y mis subordinados
            $teamUserIds = [$user->id];
            if ($miEmpleado) {
                $misSubordinados = $this->obtenerIdsSubordinadosRecursivo($miEmpleado->id);
                $teamUserIds = array_merge($teamUserIds, $misSubordinados);
            }
        }

        $teamUserIds = array_values(array_unique($teamUserIds));

        // 3. CONSULTA (Cargamos 'supervisor' para la tabla)
        $query = Activity::whereIn('user_id', $teamUserIds)
            ->with(['user.empleado.supervisor', 'historial']);

        // Filtros
        if ($request->filled('user_filter')) $query->where('user_id', $request->user_filter);
        if ($request->filled('prioridad')) $query->where('prioridad', $request->prioridad);
        if ($request->filled('estatus')) $query->where('estatus', $request->estatus);
        if ($request->filled('fecha_inicio')) $query->whereDate('fecha_inicio', '>=', $request->fecha_inicio);
        if ($request->filled('fecha_fin')) $query->whereDate('fecha_inicio', '<=', $request->fecha_fin);

        // Ordenar: Completados al final, Prio Alta arriba
        $activities = $query
            ->orderByRaw("CASE WHEN estatus LIKE '%Completado%' THEN 2 ELSE 1 END")
            ->orderByRaw("CASE prioridad WHEN 'Alta' THEN 1 WHEN 'Media' THEN 2 WHEN 'Baja' THEN 3 ELSE 4 END")
            ->orderBy('fecha_compromiso', 'asc')
            ->get();

        // 4. DATOS VISTA
        $filterUsers = User::whereIn('id', $teamUserIds)->orderBy('name')->get();

        // Sidebar para Director: Sus reportes directos
        $listaSupervisores = [];
        if ($esDireccion && $miEmpleado) {
            $listaSupervisores = Empleado::where('supervisor_id', $miEmpleado->id)
                ->with('user') // Solo necesitamos user y datos propios, no el supervisor del supervisor
                ->get();
        }

        return view('activities.index', compact('activities', 'filterUsers', 'esDireccion', 'listaSupervisores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_actividad' => 'required|string|max:255',
            'fecha_compromiso' => 'required|date',
            'prioridad'        => 'required|in:Baja,Media,Alta',
            'area'             => 'required|string',
            'tipo_actividad'   => 'required|string',
        ]);

        Activity::create([
            'user_id'          => Auth::id(),
            'area'             => $request->area,
            'tipo_actividad'   => $request->tipo_actividad,
            'nombre_actividad' => $request->nombre_actividad,
            'fecha_compromiso' => $request->fecha_compromiso,
            'prioridad'        => $request->prioridad,
            'estatus'          => 'En blanco',
        ]);

        return redirect()->route('activities.index')->with('success', 'Creado');
    }

    public function update(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $user = Auth::user();
        $miEmpleado = $user->empleado;

        if ($request->has('estatus')) {
            $s = $request->estatus;
            if (str_contains($s, 'Completado') && !$activity->fecha_final) $activity->fecha_final = now();
            elseif (!str_contains($s, 'Completado')) $activity->fecha_final = null;
            $activity->estatus = $s;
        }

        if ($request->has('prioridad')) {
            $puede = false;
            // Solo Jefes o Dirección pueden cambiar prioridad (Dueño NO)
            if ($miEmpleado && $activity->user->empleado && $miEmpleado->id === $activity->user->empleado->supervisor_id) $puede = true;
            if ($this->esPersonalDeDireccion($miEmpleado)) $puede = true;

            if ($puede) $activity->prioridad = $request->prioridad;
        }

        if ($request->has('comentarios')) $activity->comentarios = $request->comentarios;

        $activity->save();
        return redirect()->route('activities.index');
    }

    public function destroy($id)
    {
        Activity::where('user_id', Auth::id())->findOrFail($id)->delete();
        return redirect()->route('activities.index');
    }

    private function obtenerIdsSubordinadosRecursivo($empleadoId)
    {
        $userIds = [];
        $subs = Empleado::where('supervisor_id', $empleadoId)->get();
        foreach ($subs as $sub) {
            if ($sub->user_id) $userIds[] = $sub->user_id;
            $nietos = $this->obtenerIdsSubordinadosRecursivo($sub->id);
            if (!empty($nietos)) $userIds = array_merge($userIds, $nietos);
        }
        return $userIds;
    }

    private function esPersonalDeDireccion($empleado)
    {
        if (!$empleado) return false;
        $pos = strtolower($empleado->posicion ?? '');
        $dep = strtolower($empleado->departamento ?? '');
        return str_contains($pos, 'dirección') || str_contains($pos, 'direccion') || 
               str_contains($pos, 'director') || str_contains($pos, 'general') || 
               str_contains($dep, 'dirección');
    }
}