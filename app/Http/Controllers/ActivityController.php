<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityHistory;
use App\Models\User;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $miEmpleado = $user->empleado; 

        // --- CONFIGURACIÓN DE PERMISOS ---
        $esDireccion = false;
        $esSupervisor = false;
        $necesitaCliente = false;
        $idsVisibles = [$user->id]; 

        if ($miEmpleado) {
            $posicionLower = mb_strtolower($miEmpleado->posicion, 'UTF-8');
            
            // Regla: Solo Anexo 24 y Post-Operacion usan Cliente y Planeador
            $necesitaCliente = Str::contains($posicionLower, ['anexo 24', 'anexo24', 'post-operacion', 'post operacion', 'post-operación']);

            if (str_contains($posicionLower, 'direccion') || str_contains($posicionLower, 'dirección')) {
                $esDireccion = true;
            }

            // Detectar Subordinados
            $subordinadosIds = Empleado::where('supervisor_id', $miEmpleado->id)
                                        ->pluck('user_id')->filter()->toArray();
            
            if (count($subordinadosIds) > 0) {
                $esSupervisor = true;
                $idsVisibles = array_merge($idsVisibles, $subordinadosIds);
            }
        }

        // --- 1. ZONA DE APROBACIÓN (SOLO SUPERVISORES) ---
        // Busca actividades estancadas en "Por Aprobar" del equipo
        $pendingApprovals = [];
        if (($esSupervisor || $esDireccion) && empty($request->search)) {
            $pendingApprovals = Activity::with('user.empleado')
                ->whereIn('user_id', $idsVisibles)
                ->where('estatus', 'Por Aprobar')
                ->orderBy('user_id')
                ->get()
                ->groupBy('user_id');
        }

        // --- 2. ZONA "MIS OBJETIVOS" (BUCKET PLANEADO) ---
        // Actividades aprobadas pero que aún no inician (No salen en tabla principal)
        $plannedActivities = Activity::where('user_id', $user->id)
            ->where('estatus', 'Planeado')
            ->orderBy('fecha_compromiso', 'asc')
            ->get();

        // --- 3. TABLA PRINCIPAL (REPORTE DE ACTIVIDADES REALES) ---
        $query = Activity::with(['user.empleado', 'historial.user']);

        if (!$esDireccion) {
            $query->whereIn('user_id', $idsVisibles);
        }

        // Ocultamos lo que es "futuro" (Planeado), "limbo" (Por Aprobar) o "Rechazado" (para no ensuciar tabla)
        // El rechazado se ve en la alerta superior
        $query->whereNotIn('estatus', ['Por Aprobar', 'Planeado', 'Rechazado']);

        // Filtros
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nombre_actividad', 'like', "%{$request->search}%")
                  ->orWhere('area', 'like', "%{$request->search}%")
                  ->orWhere('cliente', 'like', "%{$request->search}%")
                  ->orWhere('tipo_actividad', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->user_id && ($esDireccion || in_array($request->user_id, $idsVisibles))) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->estatus) $query->where('estatus', $request->estatus);
        if ($request->prioridad) $query->where('prioridad', $request->prioridad);
        if ($request->fecha_inicio) $query->whereDate('fecha_compromiso', '>=', $request->fecha_inicio);
        if ($request->fecha_fin) $query->whereDate('fecha_compromiso', '<=', $request->fecha_fin);

        $activities = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Buscamos las rechazadas APARTE para pasarlas a la vista
        $misRechazos = Activity::where('user_id', $user->id)->where('estatus', 'Rechazado')->get();

        // KPIs (Solo cuentan lo Real)
        $kpiQuery = Activity::query()->whereNotIn('estatus', ['Por Aprobar', 'Planeado', 'Rechazado']);
        if (!$esDireccion) $kpiQuery->whereIn('user_id', $idsVisibles);

        $kpis = [
            'total'       => (clone $kpiQuery)->count(),
            'completadas' => (clone $kpiQuery)->where('estatus', 'Completado')->count(),
            'proceso'     => (clone $kpiQuery)->where('estatus', 'En proceso')->count(),
            'pendientes'  => (clone $kpiQuery)->where('estatus', 'En blanco')->count(),
            'retardos'    => (clone $kpiQuery)->where('estatus', 'Retardo')->count(),
        ];

        $users = $esDireccion ? User::orderBy('name')->get() : User::whereIn('id', $idsVisibles)->orderBy('name')->get();

        return view('activities.index', compact('activities', 'kpis', 'users', 'esDireccion', 'esSupervisor', 'necesitaCliente', 'pendingApprovals', 'plannedActivities', 'misRechazos'));
    }

    // --- GUARDAR PLAN SEMANAL (LOTE) ---
    public function storeBatch(Request $request)
    {
        // 1. REGLA DE NEGOCIO: Solo Lunes antes de las 11:00 AM (Excepto Dirección)
        $now = now();
        $esDireccion = Auth::user()->empleado && str_contains(strtolower(Auth::user()->empleado->posicion), 'direccion');

        if ( (!$now->isMonday() || $now->hour >= 11) && !$esDireccion ) {
            return redirect()->back()->with('error', '🚫 El Plan Semanal solo se puede enviar los Lunes antes de las 11:00 AM. El sistema se ha cerrado.');
        }

        $request->validate([
            'semana_inicio' => 'required|date',
            'plan' => 'array',
        ]);

        $fechaBase = Carbon::parse($request->semana_inicio);
        $count = 0;

        foreach ($request->plan as $diaIndex => $tareasDelDia) {
            // diaIndex 0 = Lunes, 1 = Martes, etc.
            $fechaReal = $fechaBase->copy()->addDays($diaIndex);
            
            if (!is_array($tareasDelDia)) continue;

            foreach ($tareasDelDia as $tarea) {
                // Si la descripción está vacía, saltamos
                if (empty($tarea['actividad'])) continue;

                Activity::create([
                    'user_id'          => Auth::id(),
                    'area'             => $tarea['area'] ?? 'Anexo 24', // O el área por defecto de tu empresa
                    'cliente'          => $tarea['cliente'] ?? null,
                    'tipo_actividad'   => $tarea['tipo'] ?? 'General', 
                    'nombre_actividad' => $tarea['actividad'],
                    
                    // --- NUEVOS CAMPOS DE TIEMPO ---
                    'hora_inicio_programada' => $tarea['start_time'] ?? null, 
                    'hora_fin_programada'    => $tarea['end_time'] ?? null,
                    
                    'fecha_inicio'     => now(), // Fecha de registro
                    'fecha_compromiso' => $fechaReal, // Fecha planeada de ejecución
                    'prioridad'        => 'Media',
                    'estatus'          => 'Por Aprobar', 
                    'metrico'          => 1,
                ]);
                $count++;
            }
        }

        return redirect()->route('activities.index')
            ->with('success', "Se enviaron $count actividades a revisión de tu supervisor.");
    }

    public function approve(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        
        if($request->filled('ajuste_nombre')) $activity->nombre_actividad = $request->ajuste_nombre;
        if($request->filled('ajuste_prio')) $activity->prioridad = $request->ajuste_prio;

        $activity->estatus = 'Planeado'; 
        // Limpiamos rechazo previo si existía
        $activity->motivo_rechazo = null; 
        $activity->save();

        ActivityHistory::create([
            'activity_id' => $activity->id,
            'user_id' => Auth::id(),
            'action' => 'approved',
            'details' => 'Plan aprobado por supervisor'
        ]);

        return back()->with('success', 'Actividad aprobada e integrada a los objetivos del usuario.');
    }

    public function start($id)
    {
        $activity = Activity::findOrFail($id);
        $activity->estatus = 'En proceso';
        $activity->fecha_inicio = now(); 
        $activity->save();

        ActivityHistory::create([
            'activity_id' => $activity->id,
            'user_id' => Auth::id(),
            'action' => 'updated',
            'details' => 'Inició ejecución de actividad planeada'
        ]);

        return back()->with('success', 'Actividad activada en tu bitácora diaria.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'required|string|min:3'
        ]);

        $activity = Activity::findOrFail($id);
        $activity->estatus = 'Rechazado';
        $activity->motivo_rechazo = $request->motivo;
        $activity->save();

        ActivityHistory::create([
            'activity_id' => $activity->id,
            'user_id' => Auth::id(),
            'action' => 'rejected',
            'details' => 'Rechazado: ' . $request->motivo
        ]);

        return back()->with('warning', 'La actividad fue enviada de regreso al usuario para corrección.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_actividad' => 'required|string|max:255',
            'tipo_actividad'   => 'required|string|max:100',
            'area'             => 'required|string|max:100',
            'fecha_compromiso' => 'required|date',
            'prioridad'        => 'nullable|in:Alta,Media,Baja',
            'cliente'          => 'nullable|string|max:150',
        ]);

        $activity = Activity::create([
            'user_id'          => Auth::id(),
            'nombre_actividad' => $request->nombre_actividad,
            'tipo_actividad'   => $request->tipo_actividad,
            'area'             => $request->area,
            'cliente'          => $request->cliente,
            'fecha_inicio'     => now(),
            'fecha_compromiso' => $request->fecha_compromiso,
            'prioridad'        => $request->prioridad ?? 'Media',
            'estatus'          => 'En blanco',
            'metrico'          => 1,
        ]);

        ActivityHistory::create([
            'activity_id' => $activity->id, 'user_id' => Auth::id(), 'action' => 'created', 'details' => 'Creó la actividad'
        ]);

        return redirect()->route('activities.index')->with('success', 'Actividad creada correctamente');
    }

    public function update(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $originalData = $activity->only(['estatus', 'prioridad', 'comentarios', 'cliente']);
        $activity->fill($request->except(['evidencia']));

        // --- LÓGICA DE CORRECCIÓN DE RECHAZO ---
        if ($activity->estatus === 'Rechazado') {
            // Si el usuario edita una rechazada, la devolvemos a "Por Aprobar"
            $activity->estatus = 'Por Aprobar';
            ActivityHistory::create([
                'activity_id' => $activity->id, 'user_id' => Auth::id(), 'action' => 'updated', 
                'details' => 'Corrección realizada tras rechazo'
            ]);
        }

        if (in_array($activity->estatus, ['En proceso', 'En blanco', 'Retardo'])) {
            $activity->fecha_final = null;
            $activity->resultado_dias = null;
            $activity->porcentaje = 0; 
        }

        if ($request->hasFile('evidencia')) {
            if ($activity->evidencia_path) Storage::disk('public')->delete($activity->evidencia_path);
            $path = $request->file('evidencia')->store('evidencias_actividades', 'public');
            $activity->evidencia_path = $path;
            
            ActivityHistory::create([
                'activity_id' => $activity->id, 'user_id' => Auth::id(), 'action' => 'updated',
                'field' => 'evidencia_path', 'old_value' => null, 'new_value' => 'Archivo adjuntado'
            ]);
        }

        if ($activity->isDirty('cliente')) {
            ActivityHistory::create([
                'activity_id' => $activity->id, 'user_id' => Auth::id(), 'action' => 'updated',
                'field' => 'cliente', 'old_value' => $originalData['cliente'], 'new_value' => $activity->cliente
            ]);
        }

        if ($activity->isDirty('estatus') && $activity->estatus !== 'Por Aprobar') {
            ActivityHistory::create([
                'activity_id' => $activity->id, 'user_id' => Auth::id(), 'action' => 'updated',
                'field' => 'estatus', 'old_value' => $originalData['estatus'], 'new_value' => $activity->estatus
            ]);
            if ($activity->estatus == 'Completado') $activity->fecha_final = now();
        }
        
        if ($request->comentarios && $request->comentarios !== $originalData['comentarios']) {
             ActivityHistory::create([
                'activity_id' => $activity->id, 'user_id' => Auth::id(), 'action' => 'comment', 'comentario' => $request->comentarios
            ]);
        }

        $activity->save();
        return redirect()->route('activities.index')->with('success', 'Actividad actualizada');
    }

    /**
     * Eliminar actividad con verificación de permisos.
     * Solo Dirección, Supervisor directo o el Dueño (si no ha iniciado) pueden borrar.
     */
    public function destroy($id)
    {
        $activity = Activity::findOrFail($id);
        $user = Auth::user();
        
        // 1. Verificar si es Dirección
        $esDireccion = false;
        if ($user->empleado && (str_contains(strtolower($user->empleado->posicion), 'direccion') || str_contains(strtolower($user->empleado->posicion), 'dirección'))) {
            $esDireccion = true;
        }

        // 2. Verificar si es Supervisor del dueño de la actividad
        $esSupervisor = false;
        if ($user->empleado && $activity->user->empleado && $user->empleado->id === $activity->user->empleado->supervisor_id) {
            $esSupervisor = true;
        }

        // 3. Verificar si es el Dueño (Solo si la actividad está 'En blanco' o 'Rechazado' para no borrar historial ya iniciado)
        $esDueno = ($activity->user_id === $user->id) && in_array($activity->estatus, ['En blanco', 'Rechazado', 'Por Aprobar']);

        if ($esDireccion || $esSupervisor || $esDueno) {
            $activity->delete(); // SoftDelete activado en el modelo
            return redirect()->route('activities.index')->with('success', 'Actividad eliminada correctamente.');
        }

        abort(403, 'No tienes permiso para eliminar esta actividad.');
    }
}