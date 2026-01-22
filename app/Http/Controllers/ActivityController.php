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
use Illuminate\Support\Facades\DB; 

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user(); 
        $miEmpleado = $user->empleado; 

        // 1. CONFIGURACIÓN DE PERMISOS
        $esDireccion = false;
        $esSupervisor = false;
        $puedePlanificar = false; // Por defecto nadie planifica semanalmente
        $idsVisibles = [$user->id]; 

        if ($miEmpleado) {
            $posicionLower = mb_strtolower($miEmpleado->posicion, 'UTF-8');

            // --- REGLA DE NEGOCIO: SOLO ESTAS POSICIONES USAN EL PLANIFICADOR SEMANAL ---
            // El resto solo crea actividades sueltas ("bomberazos")
            if (Str::contains($posicionLower, ['anexo 24', 'anexo24', 'post-operacion', 'post operacion', 'post operación'])) {
                $puedePlanificar = true;
            }

            if (str_contains($posicionLower, 'direccion') || str_contains($posicionLower, 'dirección')) {
                $esDireccion = true;
            }

            // Obtener subordinados
            $subordinadosIds = Empleado::where('supervisor_id', $miEmpleado->id)
                                        ->pluck('user_id')->filter()->toArray();
            
            if (count($subordinadosIds) > 0) {
                $esSupervisor = true;
                $idsVisibles = array_merge($idsVisibles, $subordinadosIds);
            }
        }

        // 2. CONTEXTO
        $targetUserId = $user->id;
        if (($esSupervisor || $esDireccion) && $request->filled('user_id')) {
            if ($esDireccion || in_array($request->user_id, $idsVisibles)) {
                $targetUserId = $request->user_id;
            }
        }
        $targetUser = User::findOrFail($targetUserId);

        // 3. FECHAS
        $viewDate = $request->has('week_view') ? Carbon::parse($request->week_view) : now();
        $startOfWeek = $viewDate->copy()->startOfWeek();
        $endOfWeek = $viewDate->copy()->endOfWeek();
        $isHistoryView = $endOfWeek->lt(now()->startOfWeek());
        $verTodo = $request->has('ver_historial') && $request->ver_historial == '1';

        // 4. CONSULTA
        $query = Activity::with(['user.empleado.supervisor', 'historial.user'])
            ->where('user_id', $targetUserId) 
            ->whereBetween('fecha_compromiso', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')]);

        if (!$verTodo && !$isHistoryView) {
            $query->whereNotIn('estatus', ['Completado', 'Rechazado']);
        }
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nombre_actividad', 'like', "%{$request->search}%")
                  ->orWhere('cliente', 'like', "%{$request->search}%")
                  ->orWhere('area', 'like', "%{$request->search}%");
            });
        }

        // Ordenamiento
        $weeklyActivities = $query
            ->orderByRaw("CASE estatus WHEN 'Completado' THEN 2 ELSE 1 END")
            ->orderByRaw("CASE prioridad 
                            WHEN 'Alta' THEN 1 
                            WHEN 'Media' THEN 2 
                            WHEN 'Baja' THEN 3 
                            ELSE 4 END")
            ->orderBy('fecha_compromiso')
            ->orderBy('hora_inicio_programada')
            ->get();

        // 5. PENDIENTES
        $pendingActivities = collect();
        if (!$isHistoryView) { 
            $pendingActivities = Activity::with(['user.empleado', 'historial.user'])
                ->where('user_id', $targetUserId)
                ->where('estatus', 'Por Aprobar')
                ->get();
        }

        $mainActivities = $weeklyActivities->merge($pendingActivities)->unique('id');

        // 6. EXTRAS
        $teamUsers = collect();
        if ($esDireccion) {
            $teamUsers = User::orderBy('name')->get();
        } elseif ($esSupervisor) {
            $teamUsers = User::whereIn('id', $idsVisibles)->orderBy('name')->get();
        }

        $globalPendingCount = 0;
        if ($esSupervisor || $esDireccion) {
            $q = Activity::where('estatus', 'Por Aprobar')->where('user_id', '!=', $targetUserId);
            if (!$esDireccion) $q->whereIn('user_id', $idsVisibles);
            $globalPendingCount = $q->count();
        }

        $misRechazos = Activity::where('user_id', $user->id)->where('estatus', 'Rechazado')->get();

        // NOTA: Ya no pasamos $necesitaCliente porque ahora Cliente/Área son para todos
        $kpiBase = $weeklyActivities; 
        $kpis = [
            'total'       => $kpiBase->count(),
            'completadas' => $kpiBase->where('estatus', 'Completado')->count(),
            'proceso'     => $kpiBase->where('estatus', 'En proceso')->count(),
            'pendientes'  => $kpiBase->where('estatus', 'En blanco')->count(),
            'retardos'    => $kpiBase->where('estatus', 'Retardo')->count(),
        ];

        return view('activities.index', compact(
            'mainActivities', 'teamUsers', 'targetUser', 'kpis', 
            'esDireccion', 'esSupervisor', 'puedePlanificar', // Nueva variable de control
            'globalPendingCount', 'misRechazos', 
            'startOfWeek', 'endOfWeek', 'isHistoryView', 'verTodo'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_actividad' => 'required|max:255',
            'fecha_compromiso' => 'required|date',
            'area'             => 'required|string'
        ]);

        $data = $request->all();

        // Si soy jefe y asigno a otro
        if ($request->filled('assigned_to')) {
            // Aquí podrías validar permisos extra si quisieras
            $data['user_id'] = $request->assigned_to;
        } else {
            $data['user_id'] = Auth::id();
        }

        $data['fecha_inicio'] = now(); // Fecha de Asignación (Creación)
        $data['estatus'] = 'En blanco';
        $data['metrico'] = 1;

        Activity::create($data);

        return redirect()->back()->with('success', 'Actividad creada correctamente.');
    }

    public function storeBatch(Request $request)
    {
        $request->validate(['semana_inicio' => 'required|date', 'plan' => 'array']);
        
        return DB::transaction(function () use ($request) {
            $fechaBase = Carbon::parse($request->semana_inicio);
            $count = 0;
            
            if (empty($request->plan)) return redirect()->back()->with('warning', 'Sin datos.');

            foreach ($request->plan as $diaIndex => $tareasDelDia) {
                $fechaReal = $fechaBase->copy()->addDays($diaIndex);
                if (!is_array($tareasDelDia)) continue;

                foreach ($tareasDelDia as $tarea) {
                    $nombre = trim($tarea['actividad'] ?? '');
                    if (empty($nombre)) continue;

                    Activity::create([
                        'user_id'          => Auth::id(), // Batch es personal
                        'area'             => $tarea['area'] ?? 'General',
                        'cliente'          => $tarea['cliente'] ?? null,
                        'tipo_actividad'   => $tarea['tipo'] ?? 'Operativo',
                        'nombre_actividad' => $nombre,
                        'hora_inicio_programada' => $tarea['start_time'] ?? null,
                        'hora_fin_programada'    => $tarea['end_time'] ?? null,
                        'fecha_inicio'     => now(), // Fecha Asignación
                        'fecha_compromiso' => $fechaReal,
                        'prioridad'        => 'Media',
                        'estatus'          => 'Por Aprobar',
                        'metrico'          => 1,
                    ]);
                    $count++;
                }
            }
            return redirect()->route('activities.index')->with('success', "Plan enviado: {$count} actividades.");
        });
    }

    // --- UPDATE INTELIGENTE (LOGS Y PERMISOS) ---
    public function update(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $user = Auth::user();
        
        // 1. Validar Permisos
        $esDireccion = $user->empleado && str_contains(strtolower($user->empleado->posicion), 'direcc');
        $esSupervisor = false;
        
        // ¿Soy su supervisor directo?
        if ($user->empleado && $activity->user->empleado && $user->empleado->id === $activity->user->empleado->supervisor_id) {
            $esSupervisor = true;
        }
        // ¿Soy el dueño y es un borrador?
        $esDuenoBorrador = ($activity->user_id === $user->id && $activity->estatus === 'En blanco');

        $puedeEditarTodo = $esDireccion || $esSupervisor || $esDuenoBorrador;

        $original = $activity->toArray(); // Guardar estado anterior

        // 2. Asignar datos según permisos
        if ($puedeEditarTodo) {
            // Jefe: Puede cambiar todo
            $activity->fill($request->except(['evidencia']));
        } else {
            // Empleado: Solo puede cambiar estatus y comentarios.
            // Ignoramos cambios en fechas/prioridad que vengan del request
            $activity->estatus = $request->estatus;
            $activity->comentarios = $request->comentarios;
            // No hacemos fill de lo demás para proteger integridad
        }

        // 3. DETECTOR DE CAMBIOS (LOG DETALLADO)
        $mapaCampos = [
            'nombre_actividad' => 'Actividad', 
            'estatus' => 'Estatus', 
            'prioridad' => 'Prioridad',
            'fecha_compromiso' => 'Fecha Compromiso', 
            'hora_inicio_programada' => 'Hora Inicio', 
            'hora_fin_programada' => 'Hora Fin', 
            'comentarios' => 'Comentarios',
            'cliente' => 'Cliente',
            'area' => 'Área'
        ];

        foreach ($activity->getDirty() as $campo => $nuevoValor) {
            if (!array_key_exists($campo, $mapaCampos)) continue;
            
            $nombreLegible = $mapaCampos[$campo];
            $valorAnterior = $original[$campo] ?? '-';

            // Formato limpio para fechas
            if (str_contains($campo, 'fecha') && $valorAnterior !== '-') {
                $valorAnterior = \Carbon\Carbon::parse($valorAnterior)->format('Y-m-d');
                $nuevoValor = \Carbon\Carbon::parse($nuevoValor)->format('Y-m-d');
            }
            // Formato limpio para horas
            if (str_contains($campo, 'hora') && $valorAnterior !== '-') {
                $valorAnterior = substr($valorAnterior, 0, 5);
                $nuevoValor = substr($nuevoValor, 0, 5);
            }

            if ($valorAnterior == $nuevoValor) continue;

            $mensaje = ($campo === 'comentarios') 
                ? "Actualizó comentarios / bitácora" 
                : "Cambió $nombreLegible: '$valorAnterior' ➝ '$nuevoValor'";

            ActivityHistory::create([
                'activity_id' => $activity->id, 
                'user_id' => Auth::id(),
                'action' => 'updated', 
                'details' => $mensaje
            ]);
        }

        // 4. Lógica de Estados y Fechas Finales
        if ($activity->estatus == 'Completado' && $original['estatus'] != 'Completado') {
            $activity->fecha_final = now();
        }
        if ($original['estatus'] == 'Completado' && $activity->estatus != 'Completado') {
            $activity->fecha_final = null;
            $activity->resultado_dias = null;
            $activity->porcentaje = null;
        }
        // Auto-corrección de rechazo
        if ($activity->estatus === 'Rechazado' && $original['estatus'] === 'Rechazado') {
            // Sigue rechazado
        } elseif ($activity->estatus === 'Rechazado') {
            $activity->estatus = 'Por Aprobar';
        }

        // Archivos
        if ($request->hasFile('evidencia')) {
            if ($activity->evidencia_path) Storage::disk('public')->delete($activity->evidencia_path);
            $activity->evidencia_path = $request->file('evidencia')->store('evidencias', 'public');
            ActivityHistory::create([
                'activity_id' => $activity->id, 'user_id' => Auth::id(), 
                'action' => 'file', 'details' => 'Adjuntó evidencia'
            ]);
        }

        $activity->save();
        return redirect()->back()->with('success', 'Actualizado.');
    }

    public function destroy($id)
    {
        $activity = Activity::findOrFail($id);
        $user = Auth::user();
        
        $esDireccion = $user->empleado && str_contains(strtolower($user->empleado->posicion), 'direcc');
        $esSupervisor = $user->empleado && $activity->user->empleado && $user->empleado->id === $activity->user->empleado->supervisor_id;
        $esDuenoBorrador = ($activity->user_id === $user->id && $activity->estatus === 'En blanco');

        if ($esDireccion || $esSupervisor || $esDuenoBorrador) {
            $activity->delete();
            return redirect()->back()->with('success', 'Eliminado.');
        }
        abort(403, 'No tienes permiso para eliminar esta actividad.');
    }

    public function approve(Request $request, $id)
    {
        $act = Activity::findOrFail($id);
        $act->estatus = 'Planeado'; 
        $act->motivo_rechazo = null;
        $act->save();
        ActivityHistory::create(['activity_id'=>$id, 'user_id'=>Auth::id(), 'action'=>'approved', 'details'=>'Aprobó la actividad']);
        return back()->with('success', 'Aprobada.');
    }

    public function reject(Request $request, $id)
    {
        $act = Activity::findOrFail($id);
        $act->estatus = 'Rechazado';
        $act->motivo_rechazo = $request->input('motivo', 'Revisión');
        $act->save();
        ActivityHistory::create(['activity_id'=>$id, 'user_id'=>Auth::id(), 'action'=>'rejected', 'details'=>'Rechazó: '.$request->motivo]);
        return back()->with('warning', 'Rechazada.');
    }

    public function start($id)
    {
        $act = Activity::findOrFail($id);
        $act->estatus = 'En proceso';
        $act->fecha_inicio = now(); // Actualiza fecha real de inicio de ejecución (opcional, o mantienes la de creación)
        $act->save();
        ActivityHistory::create(['activity_id'=>$id, 'user_id'=>Auth::id(), 'action'=>'updated', 'details'=>'Inició ejecución']);
        return back()->with('success', 'Iniciada.');
    }
}