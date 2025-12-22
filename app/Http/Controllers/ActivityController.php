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
    /**
     * Muestra la lista de actividades.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $miEmpleado = $user->empleado; // Relación hasOne en User

        // 1. DETECTAR SI ES DIRECCIÓN (Usando 'posicion' y sin acentos)
        $esDireccion = $this->esPersonalDeDireccion($miEmpleado);

        // 2. OBTENER SCOPE DE USUARIOS
        $teamUserIds = [];

        if ($esDireccion) {
            // --- MODO DIRECTOR ---
            
            // Si seleccionó a un líder en el sidebar (Ej. Liliana)
            if ($request->filled('ver_equipo_de')) {
                $liderId = $request->ver_equipo_de;
                
                // Traemos recursivamente a toda la descendencia (Isaac, Jonathan, etc.)
                $teamUserIds = $this->obtenerIdsSubordinadosRecursivo($liderId);
                
                // Agregamos al líder mismo a la lista
                $liderUser = Empleado::find($liderId)?->user_id;
                if ($liderUser) $teamUserIds[] = $liderUser;

            } else {
                // Si no hay filtro, el Director ve TODO el universo
                $teamUserIds = User::pluck('id')->toArray();
            }

        } else {
            // --- MODO MORTAL (Supervisor / Empleado) ---
            
            $teamUserIds = [$user->id]; // Lo mío

            if ($miEmpleado) {
                // Mis subordinados recursivos (si soy Liliana, veo a mis chicos)
                $misSubordinados = $this->obtenerIdsSubordinadosRecursivo($miEmpleado->id);
                $teamUserIds = array_merge($teamUserIds, $misSubordinados);
            }
        }

        // Limpiar duplicados
        $teamUserIds = array_values(array_unique($teamUserIds));

        // 3. CONSULTA
        $query = Activity::whereIn('user_id', $teamUserIds)
            ->with(['user.empleado', 'historial']);

        // Filtros
        if ($request->filled('user_filter')) {
            $query->where('user_id', $request->user_filter);
        }
        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_inicio', '<=', $request->fecha_fin);
        }

        // 4. ORDENAR
        $activities = $query
            ->orderByRaw("CASE WHEN estatus LIKE '%Completado%' THEN 2 ELSE 1 END")
            ->orderByRaw("CASE prioridad WHEN 'Alta' THEN 1 WHEN 'Media' THEN 2 WHEN 'Baja' THEN 3 ELSE 4 END")
            ->orderBy('fecha_compromiso', 'asc')
            ->get();

        // 5. DATOS PARA LA VISTA
        $filterUsers = User::whereIn('id', $teamUserIds)->orderBy('name')->get();

        // Sidebar para Director: Solo sus reportes directos (Nivel 1 hacia abajo)
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

        return redirect()->route('activities.index')->with('success', 'Actividad creada.');
    }

    public function update(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $user = Auth::user();
        $miEmpleado = $user->empleado;

        // --- ESTATUS ---
        if ($request->has('estatus')) {
            $nuevoEstatus = $request->estatus;
            if (str_contains($nuevoEstatus, 'Completado') && !$activity->fecha_final) {
                $activity->fecha_final = now();
            } elseif (!str_contains($nuevoEstatus, 'Completado')) {
                $activity->fecha_final = null;
            }
            $activity->estatus = $nuevoEstatus;
        }

        // --- PRIORIDAD (PERMISOS) ---
        if ($request->has('prioridad')) {
            $puedeEditar = false;
            
            // 1. Es el dueño
            if ($activity->user_id === $user->id) {
                $puedeEditar = true;
            }

            // 2. Es Supervisor Directo
            if ($miEmpleado && $activity->user->empleado) {
                if ($miEmpleado->id === $activity->user->empleado->supervisor_id) {
                    $puedeEditar = true;
                }
            }

            // 3. Es Dirección (Validación corregida)
            if ($this->esPersonalDeDireccion($miEmpleado)) {
                $puedeEditar = true;
            }

            if ($puedeEditar) {
                $activity->prioridad = $request->prioridad;
            }
        }

        // --- BITÁCORA ---
        if ($request->has('comentarios')) {
            $activity->comentarios = $request->comentarios;
        }

        $activity->save();

        return redirect()->route('activities.index');
    }

    public function destroy($id)
    {
        $activity = Activity::where('user_id', Auth::id())->findOrFail($id);
        $activity->delete();
        return redirect()->route('activities.index');
    }

    // --- FUNCIONES AUXILIARES ---

    private function obtenerIdsSubordinadosRecursivo($empleadoId)
    {
        $userIds = [];
        // Busca hijos directos (Nivel 1)
        $subordinadosDirectos = Empleado::where('supervisor_id', $empleadoId)->get();

        foreach ($subordinadosDirectos as $sub) {
            // Agrega usuario si existe
            if ($sub->user_id) $userIds[] = $sub->user_id;
            
            // Busca nietos (Nivel 2, 3...) recursivamente
            $nietos = $this->obtenerIdsSubordinadosRecursivo($sub->id);
            if (!empty($nietos)) {
                $userIds = array_merge($userIds, $nietos);
            }
        }
        return $userIds;
    }

    /**
     * Verifica si el empleado pertenece a Dirección (Insensible a acentos y mayúsculas)
     */
    private function esPersonalDeDireccion($empleado)
    {
        if (!$empleado) return false;
        
        // Usamos 'posicion' como pediste
        $posicion = strtolower($empleado->posicion ?? '');
        $depto = strtolower($empleado->departamento ?? '');
        
        return str_contains($posicion, 'dirección') || 
               str_contains($posicion, 'direccion') ||  // Sin acento
               str_contains($posicion, 'director') || 
               str_contains($depto, 'dirección') || 
               str_contains($depto, 'direccion') || 
               str_contains($posicion, 'general');
    }
}