<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityHistory;
use App\Models\User;
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
        // 1. Iniciar Query (CORREGIDO)
        // Quitamos 'evidencias' del with(), ya que es una columna, no una relación.
        $query = Activity::with(['user.empleado', 'historial.user']);

        // 2. Aplicar Filtros
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nombre_actividad', 'like', "%{$request->search}%")
                  ->orWhere('area', 'like', "%{$request->search}%")
                  ->orWhere('tipo_actividad', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

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

        // 4. Calcular KPIs
        // Nota: Si quieres que los KPIs respeten los filtros actuales, usa (clone $query)->...
        // Si quieres KPIs globales (de todo el sistema), usa Activity::...
        // Aquí dejo los globales como pediste antes:
        $kpis = [
            'total'       => Activity::count(),
            'completadas' => Activity::where('estatus', 'Completado')->count(),
            'proceso'     => Activity::where('estatus', 'En proceso')->count(),
            'pendientes'  => Activity::where('estatus', 'En blanco')->count(),
            'retardos'    => Activity::where('estatus', 'Retardo')->count(),
        ];

        // 5. Lista de usuarios
        $users = User::orderBy('name')->get();

        return view('activities.index', compact('activities', 'kpis', 'users'));
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
        
        // Guardamos valores actuales antes de modificar (para el historial)
        $originalData = $activity->only(['estatus', 'prioridad', 'comentarios']);

        // Actualizamos con los datos que vienen del formulario (excepto archivo)
        $activity->fill($request->except(['evidencia']));

        // --- [FIX] LÓGICA DE REVERSA (IMPORTANTE) ---
        // Si el usuario cambia manualmente a "En proceso", "En blanco" o "Retardo",
        // debemos BORRAR la fecha final. Si no lo hacemos, el modelo verá que existe
        // una fecha final y pensará que la actividad sigue cerrada, regresándola a "Completado".
        if (in_array($activity->estatus, ['En proceso', 'En blanco', 'Retardo'])) {
            $activity->fecha_final = null;
            $activity->resultado_dias = null;
            $activity->porcentaje = 0; 
        }
        // ---------------------------------------------

        // Manejo de Evidencia (Archivo adjunto)
        if ($request->hasFile('evidencia')) {
            // Si ya había archivo, lo borramos para no llenar el servidor
            if ($activity->evidencia_path) {
                Storage::disk('public')->delete($activity->evidencia_path);
            }

            $path = $request->file('evidencia')->store('evidencias_actividades', 'public');
            $activity->evidencia_path = $path;
            
            // Log en historial
            ActivityHistory::create([
                'activity_id' => $activity->id,
                'user_id' => Auth::id(),
                'action' => 'updated',
                'field' => 'evidencia_path',
                'old_value' => null,
                'new_value' => 'Archivo adjuntado'
            ]);
        }

        // --- HISTORIAL DE CAMBIOS ---

        // 1. Cambio de Estatus
        if ($activity->isDirty('estatus')) {
            ActivityHistory::create([
                'activity_id' => $activity->id,
                'user_id'     => Auth::id(),
                'action'      => 'updated',
                'field'       => 'estatus',
                'old_value'   => $originalData['estatus'],
                'new_value'   => $activity->estatus
            ]);
            
            // Si lo marcó como completado, ponemos la fecha de hoy como cierre
            if ($activity->estatus == 'Completado') {
                $activity->fecha_final = now();
            }
        }

        // 2. Cambio de Prioridad
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
        
        // 3. Comentarios (Notas nuevas)
        if ($request->comentarios && $request->comentarios !== $originalData['comentarios']) {
             ActivityHistory::create([
                'activity_id' => $activity->id,
                'user_id' => Auth::id(),
                'action' => 'comment',
                'comentario' => $request->comentarios
            ]);
        }

        // Guardamos todo
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