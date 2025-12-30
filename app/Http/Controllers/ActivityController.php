<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // <--- IMPORTANTE: Para guardar archivos
use App\Models\Activity;
use App\Models\Empleado;
use App\Models\User;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $miEmpleado = $user->empleado; 

        $esDireccion = $this->esPersonalDeDireccion($miEmpleado);

        $teamUserIds = [];

        if ($esDireccion) {
            if ($request->filled('ver_equipo_de')) {
                $liderId = $request->ver_equipo_de;
                $teamUserIds = $this->obtenerIdsSubordinadosRecursivo($liderId);
                $liderUser = Empleado::find($liderId)?->user_id;
                if ($liderUser) $teamUserIds[] = $liderUser;
            } else {
                $teamUserIds = User::pluck('id')->toArray();
            }
        } else {
            $teamUserIds = [$user->id];
            if ($miEmpleado) {
                $misSubordinados = $this->obtenerIdsSubordinadosRecursivo($miEmpleado->id);
                $teamUserIds = array_merge($teamUserIds, $misSubordinados);
            }
        }

        $teamUserIds = array_values(array_unique($teamUserIds));

        // CONSULTA (Automáticamente excluye los borrados por SoftDeletes)
        $query = Activity::whereIn('user_id', $teamUserIds)
            ->with(['user.empleado.supervisor', 'historial']);

        if ($request->filled('user_filter')) $query->where('user_id', $request->user_filter);
        if ($request->filled('prioridad')) $query->where('prioridad', $request->prioridad);
        if ($request->filled('estatus')) $query->where('estatus', $request->estatus);
        if ($request->filled('fecha_inicio')) $query->whereDate('fecha_inicio', '>=', $request->fecha_inicio);
        if ($request->filled('fecha_fin')) $query->whereDate('fecha_inicio', '<=', $request->fecha_fin);

        $activities = $query
            ->orderByRaw("CASE WHEN estatus LIKE '%Completado%' THEN 2 ELSE 1 END")
            ->orderByRaw("CASE prioridad WHEN 'Alta' THEN 1 WHEN 'Media' THEN 2 WHEN 'Baja' THEN 3 ELSE 4 END")
            ->orderBy('fecha_compromiso', 'asc')
            ->get();

        $filterUsers = User::whereIn('id', $teamUserIds)->orderBy('name')->get();

        $listaSupervisores = [];
        if ($esDireccion && $miEmpleado) {
            $listaSupervisores = Empleado::where('supervisor_id', $miEmpleado->id)
                ->with('user')
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

        // 1. MANEJO DE EVIDENCIA (OPCIONAL)
        // Solo procesamos si el usuario subió algo
        if ($request->hasFile('evidencia')) {
            $request->validate([
                'evidencia' => 'file|mimes:pdf,jpg,jpeg,png,zip,doc,docx,xls,xlsx|max:10240' // 10MB
            ]);

            // Borrar archivo anterior si existe (limpieza)
            if ($activity->evidencia_path && Storage::disk('public')->exists($activity->evidencia_path)) {
                Storage::disk('public')->delete($activity->evidencia_path);
            }

            // Guardar el nuevo
            $path = $request->file('evidencia')->store('actividades_evidencia', 'public');
            $activity->evidencia_path = $path;
        }

        // 2. CAMBIO DE ESTATUS Y FECHAS
        if ($request->has('estatus')) {
            $s = $request->estatus;
            
            // Si completa, ponemos fecha final hoy
            if (str_contains($s, 'Completado') && !$activity->fecha_final) {
                $activity->fecha_final = now();
            } 
            // Si regresa a proceso, quitamos la fecha final
            elseif (!str_contains($s, 'Completado')) {
                $activity->fecha_final = null;
            }
            
            $activity->estatus = $s;
        }

        // 3. CAMBIO DE PRIORIDAD (Solo Jefes)
        if ($request->has('prioridad')) {
            $puede = false;
            if ($miEmpleado && $activity->user->empleado && $miEmpleado->id === $activity->user->empleado->supervisor_id) $puede = true;
            if ($this->esPersonalDeDireccion($miEmpleado)) $puede = true;

            if ($puede) $activity->prioridad = $request->prioridad;
        }

        if ($request->has('comentarios')) $activity->comentarios = $request->comentarios;

        $activity->save();
        return redirect()->route('activities.index')->with('success', 'Actividad actualizada.');
    }

    public function destroy($id)
    {
        // Ahora esto realiza un "Soft Delete" gracias al Modelo
        // El registro sigue en BD pero con fecha en 'deleted_at'
        Activity::where('user_id', Auth::id())->findOrFail($id)->delete();
        return redirect()->route('activities.index')->with('success', 'Actividad eliminada (enviada a papelera).');
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