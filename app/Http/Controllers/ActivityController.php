<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityHistory;
use App\Models\User;
use App\Models\Empleado; // IMPORTANTE: No olvides importar este modelo
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
{
    /**
     * Muestra el tablero de actividades con filtros y KPIs.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $miEmpleado = $user->empleado; 

        // 1. Iniciar Query base con relaciones
        $query = Activity::with(['user.empleado', 'historial.user']);

        // --- LÓGICA DE VISIBILIDAD (SEGURIDAD) ---
        $esDireccion = false;
        $esSupervisor = false;
        $idsVisibles = [$user->id]; // Por defecto, solo veo lo mío

        if ($miEmpleado) {
            // A. Checar si es Dirección (ver todo)
            $pos = mb_strtolower($miEmpleado->posicion, 'UTF-8');
            if (str_contains($pos, 'direccion') || str_contains($pos, 'dirección')) {
                $esDireccion = true;
            }

            // B. Checar si es Supervisor (ver a su equipo)
            // Buscamos empleados que reporten a él
            $subordinadosIds = Empleado::where('supervisor_id', $miEmpleado->id)
                                        ->pluck('user_id')
                                        ->filter() // Quita nulos
                                        ->toArray();
            
            if (count($subordinadosIds) > 0) {
                $esSupervisor = true;
                $idsVisibles = array_merge($idsVisibles, $subordinadosIds);
            }
        }

        // C. Aplicar filtro maestro de seguridad
        if (!$esDireccion) {
            // Si NO es dirección, filtramos: solo actividades mías o de mi equipo
            $query->whereIn('user_id', $idsVisibles);
        }
        // (Si es dirección, no aplicamos restricción, ve todo)

        // ------------------------------------------

        // 2. Aplicar Filtros del Buscador (Search bar)
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nombre_actividad', 'like', "%{$request->search}%")
                  ->orWhere('area', 'like', "%{$request->search}%")
                  ->orWhere('tipo_actividad', 'like', "%{$request->search}%");
            });
        }
        
        // Filtro específico de usuario (Dropdown)
        if ($request->user_id) {
            // Validación extra de seguridad: ¿Tengo permiso de ver a ese usuario?
            if ($esDireccion || in_array($request->user_id, $idsVisibles)) {
                $query->where('user_id', $request->user_id);
            }
        }

        // Otros filtros
        if ($request->estatus) {
            $query->where('estatus', $request->estatus);
        }

        if ($request->prioridad) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->fecha_inicio) {
            $query->whereDate('fecha_compromiso', '>=', $request->fecha_inicio);
        }
        
        if ($request->fecha_fin) {
            $query->whereDate('fecha_compromiso', '<=', $request->fecha_fin);
        }

        // 3. Obtener Actividades Paginadas
        $activities = $query->orderBy('created_at', 'desc')
                            ->paginate(20)
                            ->withQueryString();

        // 4. Calcular KPIs (Respetando la visibilidad)
        // Creamos una query limpia para los contadores
        $kpiQuery = Activity::query();
        
        if (!$esDireccion) {
            $kpiQuery->whereIn('user_id', $idsVisibles);
        }

        $kpis = [
            'total'       => (clone $kpiQuery)->count(),
            'completadas' => (clone $kpiQuery)->where('estatus', 'Completado')->count(),
            'proceso'     => (clone $kpiQuery)->where('estatus', 'En proceso')->count(),
            'pendientes'  => (clone $kpiQuery)->where('estatus', 'En blanco')->count(),
            'retardos'    => (clone $kpiQuery)->where('estatus', 'Retardo')->count(),
        ];

        // 5. Lista de usuarios para el filtro (Dropdown)
        // Solo enviamos los usuarios que la persona logueada tiene permiso de ver
        if ($esDireccion) {
            $users = User::orderBy('name')->get();
        } else {
            $users = User::whereIn('id', $idsVisibles)->orderBy('name')->get();
        }

        return view('activities.index', compact('activities', 'kpis', 'users', 'esDireccion', 'esSupervisor'));
    }

    /**
     * Guarda una nueva actividad.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_actividad' => 'required|string|max:255',
            'tipo_actividad'   => 'required|string|max:100',
            'area'             => 'required|string|max:100',
            'fecha_compromiso' => 'required|date',
            'prioridad'        => 'nullable|in:Alta,Media,Baja',
        ]);

        $activity = Activity::create([
            'user_id'          => Auth::id(),
            'nombre_actividad' => $request->nombre_actividad,
            'tipo_actividad'   => $request->tipo_actividad,
            'area'             => $request->area,
            'fecha_inicio'     => now(),
            'fecha_compromiso' => $request->fecha_compromiso,
            'prioridad'        => $request->prioridad ?? 'Media',
            'estatus'          => 'En blanco',
            'metrico'          => 1,
        ]);

        ActivityHistory::create([
            'activity_id' => $activity->id,
            'user_id'     => Auth::id(),
            'action'      => 'created',
            'details'     => 'Creó la actividad'
        ]);

        return redirect()->route('activities.index')
                         ->with('success', 'Actividad creada correctamente');
    }

    /**
     * Actualiza una actividad.
     */
    public function update(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        
        // Autorización simple: Solo el dueño o un admin/supervisor debería editar
        // (Puedes refinar esto si quieres bloquear edición a subordinados)
        
        $originalData = $activity->only(['estatus', 'prioridad', 'comentarios']);
        $activity->fill($request->except(['evidencia']));

        // REGLA DE NEGOCIO: Si regresa a proceso, limpiamos fecha final
        if (in_array($activity->estatus, ['En proceso', 'En blanco', 'Retardo'])) {
            $activity->fecha_final = null;
            $activity->resultado_dias = null;
            $activity->porcentaje = 0; 
        }

        // Manejo de Evidencia
        if ($request->hasFile('evidencia')) {
            if ($activity->evidencia_path) {
                Storage::disk('public')->delete($activity->evidencia_path);
            }

            $path = $request->file('evidencia')->store('evidencias_actividades', 'public');
            $activity->evidencia_path = $path;
            
            ActivityHistory::create([
                'activity_id' => $activity->id,
                'user_id' => Auth::id(),
                'action' => 'updated',
                'field' => 'evidencia_path',
                'old_value' => null,
                'new_value' => 'Archivo adjuntado'
            ]);
        }

        // Historial de cambios importantes
        if ($activity->isDirty('estatus')) {
            ActivityHistory::create([
                'activity_id' => $activity->id,
                'user_id'     => Auth::id(),
                'action'      => 'updated',
                'field'       => 'estatus',
                'old_value'   => $originalData['estatus'],
                'new_value'   => $activity->estatus
            ]);
            
            if ($activity->estatus == 'Completado') {
                $activity->fecha_final = now();
            }
        }

        if ($activity->isDirty('prioridad')) {
            ActivityHistory::create([
                'activity_id' => $activity->id,
                'user_id' => Auth::id(),
                'action' => 'updated',
                'field' => 'prioridad',
                'old_value' => $originalData['prioridad'],
                'new_value' => $activity->prioridad
            ]);
        }
        
        if ($request->comentarios && $request->comentarios !== $originalData['comentarios']) {
             ActivityHistory::create([
                'activity_id' => $activity->id,
                'user_id' => Auth::id(),
                'action' => 'comment',
                'comentario' => $request->comentarios
            ]);
        }

        $activity->save();

        return redirect()->route('activities.index')
                         ->with('success', 'Actividad actualizada');
    }

    public function destroy($id)
    {
        $activity = Activity::findOrFail($id);
        $activity->delete();

        return redirect()->route('activities.index')
                         ->with('success', 'Actividad eliminada');
    }
}