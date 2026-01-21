@extends('layouts.erp')

@section('title', 'Tablero de Actividades')

@section('content')
<div class="min-h-screen bg-slate-50/50 py-8" x-data="{ showFilters: false }">
    <div class="max-w-[98%] mx-auto space-y-6">
        
        {{-- HEADER PRINCIPAL CON RELOJ JS Y BLOQUEO AUTOMÁTICO --}}
        <div class="flex flex-col md:flex-row justify-between items-center bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Tablero de Actividades</h1>
                <p class="text-xs text-slate-500 mt-1">Gestión operativa y seguimiento de compromisos</p>
            </div>
            
            <div class="flex gap-3 mt-4 md:mt-0 items-center">
                @if($necesitaCliente)
                    @php
                        // Variables PHP iniciales para renderizado server-side
                        $now = now();
                        $esTiempoDePlanear = $now->isMonday() && $now->hour < 11;
                        //$esTiempoDePlanear = true;
                        if(isset($esDireccion) && $esDireccion) $esTiempoDePlanear = true; 
                    @endphp

                    {{-- Contenedor de Botones (Controlado por JS) --}}
                    <div id="planning-controls" data-is-admin="{{ (isset($esDireccion) && $esDireccion) ? 'true' : 'false' }}">
                        
                        {{-- Botón Activo --}}
                        <button id="btn-planificar" onclick="openPlanModal()" class="{{ $esTiempoDePlanear ? '' : 'hidden' }} bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-lg hover:bg-indigo-700 flex items-center gap-2 transition transform hover:scale-105 animate-pulse">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Planificar Semana
                        </button>

                        {{-- Botón Bloqueado --}}
                        <div id="btn-bloqueado" class="group relative {{ $esTiempoDePlanear ? 'hidden' : '' }}">
                            <button disabled class="bg-slate-200 text-slate-400 px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 cursor-not-allowed border border-slate-300">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                Planificación Cerrada
                            </button>
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 bg-slate-800 text-white text-[10px] text-center p-2 rounded opacity-0 group-hover:opacity-100 transition pointer-events-none z-20 shadow-xl">
                                Solo disponible los Lunes antes de las 11:00 AM.
                            </div>
                        </div>
                    </div>
                @endif

                <div class="text-right hidden lg:block mr-4 border-l pl-4 border-slate-200">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Hoy</p>
                    <p id="live-clock" class="text-sm font-bold text-indigo-600 capitalize">
                        {{ now()->isoFormat('D MMM, YYYY · h:mm A') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ALERTA DE RECHAZOS (PARA EL EMPLEADO) --}}
        @if(isset($misRechazos) && $misRechazos->count() > 0)
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm animate-fade-in-down mb-2">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                </div>
                <div class="ml-3 w-full">
                    <h3 class="text-sm font-bold text-red-800">Atención: Tienes actividades rechazadas</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($misRechazos as $rej)
                                <li class="flex flex-col md:flex-row md:items-center gap-1 md:gap-2">
                                    <span><span class="font-bold">{{ $rej->nombre_actividad }}:</span> {{ $rej->motivo_rechazo ?? 'Sin motivo' }}</span>
                                    <button onclick="openNotes({{ $rej->id }}, '{{ addslashes($rej->nombre_actividad) }}', 'Rechazado', '{{ $rej->evidencia_path ? \Storage::url($rej->evidencia_path) : '' }}', '{{ addslashes($rej->cliente ?? '') }}', '{{ $rej->fecha_compromiso->format('Y-m-d') }}', '{{ $rej->hora_inicio_programada }}', '{{ $rej->hora_fin_programada }}', '{{ addslashes($rej->motivo_rechazo) }}')" 
                                            class="text-xs bg-white border border-red-200 px-2 py-0.5 rounded text-red-600 font-bold hover:bg-red-100 transition shadow-sm">
                                        Corregir ahora
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- 1. ZONA DE APROBACIÓN (SOLO SUPERVISORES) - CON CARDS Y MODAL --}}
        @if(($esSupervisor || $esDireccion) && !empty($pendingApprovals) && count($pendingApprovals) > 0)
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-orange-100 text-orange-600 rounded-full">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Revisiones Pendientes</h3>
                    <p class="text-xs text-slate-500">Haz clic en la tarjeta del empleado para revisar su plan detallado.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($pendingApprovals as $userId => $userActivities)
                    @php $user = $userActivities->first()->user; @endphp
                    
                    <div x-data="{ openModal: false }" class="relative">
                        {{-- CARD EMPLEADO --}}
                        <button @click="openModal = true" class="w-full bg-white p-5 rounded-xl shadow-sm border border-slate-200 hover:shadow-md hover:border-orange-300 transition text-left group">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-sm font-bold text-slate-600 border border-slate-200 group-hover:bg-orange-50 group-hover:text-orange-600 transition">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-700 text-sm group-hover:text-orange-700 transition">{{ Str::limit($user->name, 18) }}</h4>
                                        <span class="text-[10px] text-slate-400">{{ $user->empleado->posicion ?? 'Colaborador' }}</span>
                                    </div>
                                </div>
                                <span class="bg-orange-100 text-orange-700 text-xs font-bold px-2.5 py-1 rounded-full border border-orange-200">
                                    {{ count($userActivities) }}
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-orange-400 h-1.5 rounded-full" style="width: 100%"></div>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-2 text-right">Click para revisar plan</p>
                        </button>

                        {{-- MODAL REVISIÓN --}}
                        <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 transition-opacity bg-slate-900 bg-opacity-75" @click="openModal = false"></div>

                                <div class="inline-block align-bottom bg-slate-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full">
                                    
                                    <div class="bg-white px-4 py-3 border-b border-slate-200 flex justify-between items-center">
                                        <h3 class="text-lg font-bold text-slate-800">Plan Semanal: <span class="text-indigo-600">{{ $user->name }}</span></h3>
                                        <button @click="openModal = false" class="text-slate-400 hover:text-red-500"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                                    </div>

                                    <div class="p-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                                        <div class="grid grid-cols-1 gap-3">
                                            @foreach($userActivities as $act)
                                                <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4 items-start md:items-center justify-between" x-data="{ rejecting: false }">
                                                    
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <span class="text-[10px] font-bold bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded uppercase">{{ $act->fecha_compromiso->format('l d') }}</span>
                                                            {{-- Mostramos hora si existe --}}
                                                            @if($act->hora_inicio_programada)
                                                                <span class="text-[10px] font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded border border-slate-200">
                                                                    ⏰ {{ \Carbon\Carbon::parse($act->hora_inicio_programada)->format('H:i') }} - {{ \Carbon\Carbon::parse($act->hora_fin_programada)->format('H:i') }}
                                                                </span>
                                                            @endif
                                                            @if($act->prioridad == 'Alta') <span class="text-[10px] font-bold text-red-600">🔥 Alta</span> @endif
                                                            <span class="text-[10px] text-slate-400 border border-slate-100 px-1.5 rounded">{{ $act->area }}</span>
                                                        </div>
                                                        <p class="text-sm font-bold text-slate-800">{{ $act->nombre_actividad }}</p>
                                                        <p class="text-xs text-slate-500 mt-0.5">
                                                            @if($act->cliente) <span class="font-semibold text-slate-600">{{ $act->cliente }}</span> · @endif
                                                            {{ $act->tipo_actividad }}
                                                        </p>
                                                    </div>

                                                    <div class="flex items-center gap-2 min-w-[200px] justify-end">
                                                        {{-- Aprobar --}}
                                                        <form action="{{ route('activities.approve', $act->id) }}" method="POST" x-show="!rejecting">
                                                            @csrf @method('PUT')
                                                            <input type="hidden" name="ajuste_prio" value="{{ $act->prioridad }}">
                                                            <button type="submit" class="bg-emerald-100 text-emerald-700 hover:bg-emerald-200 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1 transition">
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                                Aprobar
                                                            </button>
                                                        </form>

                                                        {{-- Botón Rechazar --}}
                                                        <button type="button" @click="rejecting = true" x-show="!rejecting" class="bg-white border border-red-200 text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                                            Rechazar...
                                                        </button>

                                                        {{-- Form Rechazar --}}
                                                        <form action="{{ route('activities.reject', $act->id) }}" method="POST" class="flex items-center gap-2 w-full animate-fade-in-down" x-show="rejecting" style="display: none;">
                                                            @csrf @method('PUT') 
                                                            <input type="text" name="motivo" required placeholder="Motivo..." class="w-full text-xs border-red-300 focus:border-red-500 focus:ring-red-500 rounded-lg shadow-sm" style="min-width: 150px;">
                                                            <button type="submit" class="bg-red-600 text-white hover:bg-red-700 p-1.5 rounded-lg shadow-sm" title="Confirmar">
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                            </button>
                                                            <button type="button" @click="rejecting = false" class="text-slate-400 hover:text-slate-600 p-1" title="Cancelar">
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <div class="bg-slate-100 px-4 py-3 border-t border-slate-200 text-right">
                                        <button @click="openModal = false" class="text-sm text-slate-500 hover:text-slate-700 font-bold">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- 2. ZONA: MIS OBJETIVOS SEMANALES (AGRUPADO POR DÍA) --}}
        @if(isset($plannedActivities) && $plannedActivities->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-indigo-100 p-4 mb-6 relative overflow-hidden">
            <div class="flex justify-between items-center mb-4 border-b border-indigo-50 pb-2">
                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Mis Objetivos de la Semana
                </h3>
                <span class="bg-indigo-50 text-indigo-700 text-[10px] font-black px-2 py-0.5 rounded-full border border-indigo-100">{{ $plannedActivities->count() }} Actividades</span>
            </div>

            @php
                // Agrupamos lógicamente por fecha para pintar columnas
                $semana = [];
                $inicioSemana = now()->startOfWeek();
                for($i=0; $i<5; $i++) {
                    $dia = $inicioSemana->copy()->addDays($i);
                    $key = $dia->format('Y-m-d');
                    // Filtramos las actividades de este día
                    $actividadesDelDia = $plannedActivities->filter(function($act) use ($dia) {
                        return $act->fecha_compromiso->isSameDay($dia);
                    });
                    $semana[] = [
                        'fecha' => $dia,
                        'nombre' => $dia->isoFormat('dddd'), // lunes, martes...
                        'actividades' => $actividadesDelDia
                    ];
                }
            @endphp

            {{-- GRID DE 5 COLUMNAS (LUN-VIE) --}}
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                @foreach($semana as $diaInfo)
                    <div class="flex flex-col gap-2 pt-2 md:pt-0 md:px-2 first:pl-0 last:pr-0">
                        
                        {{-- CABECERA DEL DÍA --}}
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-black uppercase {{ $diaInfo['fecha']->isToday() ? 'text-indigo-600' : 'text-slate-400' }}">
                                {{ $diaInfo['nombre'] }}
                            </span>
                            <span class="text-[9px] font-bold text-slate-300">{{ $diaInfo['fecha']->format('d') }}</span>
                        </div>

                        {{-- LISTA DE TARJETAS DEL DÍA --}}
                        @if($diaInfo['actividades']->count() > 0)
                            <div class="space-y-2">
                                @foreach($diaInfo['actividades'] as $plan)
                                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-2 hover:shadow-md transition group relative">
                                        
                                        {{-- Puntito de Prioridad --}}
                                        @if($plan->prioridad == 'Alta')
                                            <div class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-red-500 rounded-full" title="Alta Prioridad"></div>
                                        @endif

                                        {{-- Hora --}}
                                        @if($plan->hora_inicio_programada)
                                            <div class="text-[9px] font-mono font-bold text-indigo-400 mb-0.5">
                                                {{ \Carbon\Carbon::parse($plan->hora_inicio_programada)->format('H:i') }}
                                            </div>
                                        @endif

                                        {{-- Título --}}
                                        <h4 class="text-[10px] leading-snug font-bold text-slate-700 mb-1 line-clamp-2" title="{{ $plan->nombre_actividad }}">
                                            {{ $plan->nombre_actividad }}
                                        </h4>

                                        {{-- Cliente --}}
                                        @if($plan->cliente)
                                            <p class="text-[9px] text-slate-400 truncate mb-1.5" title="{{ $plan->cliente }}">
                                                {{ Str::limit($plan->cliente, 15) }}
                                            </p>
                                        @endif

                                        {{-- Botón Iniciar --}}
                                        <form action="{{ route('activities.start', $plan->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <button type="submit" class="w-full bg-white border border-indigo-100 hover:border-indigo-400 text-indigo-600 text-[9px] font-bold py-0.5 rounded shadow-sm flex items-center justify-center gap-1 transition-colors">
                                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /></svg>
                                                Iniciar
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{-- ESPACIO VACÍO --}}
                            <div class="h-16 rounded-lg border border-dashed border-slate-100 flex items-center justify-center">
                                <span class="text-[9px] text-slate-300 italic">Libre</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- 3. KPIS (Solo Real) --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-slate-800">{{ $kpis['total'] }}</span>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase mt-3">Total Actividades</p>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-emerald-600">{{ $kpis['completadas'] }}</span>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase mt-3">Completadas</p>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-blue-600">{{ $kpis['proceso'] }}</span>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase mt-3">En Proceso</p>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-slate-50 rounded-lg text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-slate-600">{{ $kpis['pendientes'] }}</span>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase mt-3">Pendientes</p>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-red-100 flex flex-col justify-between hover:shadow-md transition relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-16 h-16 bg-red-500 blur-2xl opacity-10 group-hover:opacity-20 transition"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div class="p-2 bg-red-50 rounded-lg text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-red-600">{{ $kpis['retardos'] }}</span>
                </div>
                <p class="text-[10px] font-bold text-red-400 uppercase mt-3 relative z-10">Retardos Críticos</p>
            </div>
        </div>

        {{-- 4. FILTROS --}}
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex flex-col lg:flex-row justify-between gap-4 items-center">
                <button @click="showFilters = !showFilters" class="flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-50 px-4 py-2 rounded-lg hover:bg-slate-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filtros Avanzados
                </button>

                <form method="GET" class="flex flex-wrap gap-2 items-center w-full lg:w-auto">
                    <div class="relative w-full lg:w-64">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar actividad, cliente..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border-transparent focus:bg-white focus:border-indigo-300 focus:ring-0 rounded-xl text-xs font-medium transition-all">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    @if(isset($users) && count($users) > 1)
                        <select name="user_id" onchange="this.form.submit()" class="bg-slate-50 border-transparent text-xs rounded-xl focus:ring-0 cursor-pointer w-full lg:w-auto">
                            <option value="">{{ ($esDireccion ?? false) ? 'Todos los Responsables' : 'Mi Equipo' }}</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </form>
            </div>

            <div x-show="showFilters" x-transition class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-1 md:grid-cols-4 gap-4">
                <form method="GET" id="advancedFilters">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    @if(request('user_id')) <input type="hidden" name="user_id" value="{{ request('user_id') }}"> @endif
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 w-full">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Estatus</label>
                            <select name="estatus" onchange="this.form.form.submit()" class="w-full text-xs rounded-lg border-slate-200">
                                <option value="">Todos</option>
                                <option value="En blanco" {{ request('estatus') == 'En blanco' ? 'selected' : '' }}>En blanco</option>
                                <option value="En proceso" {{ request('estatus') == 'En proceso' ? 'selected' : '' }}>En proceso</option>
                                <option value="Completado" {{ request('estatus') == 'Completado' ? 'selected' : '' }}>Completado</option>
                                <option value="Retardo" {{ request('estatus') == 'Retardo' ? 'selected' : '' }}>Retardo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Prioridad</label>
                            <select name="prioridad" onchange="this.form.form.submit()" class="w-full text-xs rounded-lg border-slate-200">
                                <option value="">Todas</option>
                                <option value="Alta" {{ request('prioridad') == 'Alta' ? 'selected' : '' }}>Alta</option>
                                <option value="Media" {{ request('prioridad') == 'Media' ? 'selected' : '' }}>Media</option>
                                <option value="Baja" {{ request('prioridad') == 'Baja' ? 'selected' : '' }}>Baja</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Desde</label>
                            <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="w-full text-xs rounded-lg border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Hasta</label>
                            <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="w-full text-xs rounded-lg border-slate-200">
                        </div>
                    </div>
                    <div class="mt-2 text-right">
                        <button type="submit" class="text-xs text-indigo-600 font-bold hover:underline">Aplicar Filtros</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 5. FORMULARIO RÁPIDO (SINGLE) --}}
        <div class="bg-white p-5 shadow-lg rounded-2xl border-l-4 border-indigo-500 relative overflow-hidden">
            <div class="absolute right-0 top-0 p-4 opacity-5 pointer-events-none">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
            </div>

            <form action="{{ route('activities.store') }}" method="POST" class="relative z-10 grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                @csrf
                @php 
                    $areas = ['Logistica', 'Legal', 'Anexo 24', 'Auditoria', 'TI', 'Dirección', 'Recursos Humanos', 'Operaciones']; 
                    sort($areas); 
                @endphp

                <div class="col-span-12 md:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Área</label>
                    <select name="area" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500" required>
                        <option value="">Seleccionar...</option>
                        @foreach($areas as $areaOption)
                            <option value="{{ $areaOption }}">{{ $areaOption }}</option>
                        @endforeach
                    </select>
                </div>

                @if($necesitaCliente)
                    <div class="col-span-12 md:col-span-2">
                        <label class="block text-[10px] font-bold text-indigo-600 uppercase mb-1">Cliente</label>
                        <input type="text" name="cliente" class="w-full text-xs rounded-lg border-indigo-200 focus:ring-indigo-500 bg-indigo-50" placeholder="Nombre">
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tipo</label>
                        <input type="text" name="tipo_actividad" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500" placeholder="Ej. Proyecto" required>
                    </div>
                    <div class="col-span-12 md:col-span-3">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Actividad</label>
                        <input type="text" name="nombre_actividad" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500" placeholder="Descripción..." required>
                    </div>
                @else
                    <div class="col-span-12 md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Cliente</label>
                        <input type="text" name="tipo_actividad" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500" placeholder="Ej. Proyecto" required>
                    </div>
                    <div class="col-span-12 md:col-span-4">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Actividad</label>
                        <input type="text" name="nombre_actividad" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500" placeholder="Descripción..." required>
                    </div>
                @endif

                <div class="col-span-12 md:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Compromiso</label>
                    <input type="date" name="fecha_compromiso" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500" required>
                </div>
                <div class="col-span-12 md:col-span-1">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Prioridad</label>
                    <select name="prioridad" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500">
                        <option value="Baja">Baja</option>
                        <option value="Media" selected>Media</option>
                        <option value="Alta">Alta</option>
                    </select>
                </div>
                <div class="col-span-12 md:col-span-1">
                    <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 px-2 rounded-lg text-xs shadow-md hover:bg-indigo-700 transition flex justify-center items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Agregar
                    </button>
                </div>
            </form>
        </div>

        {{-- 6. TABLA PRINCIPAL --}}
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs text-left">
                    <thead class="bg-slate-800 text-slate-100 font-semibold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-3 py-4 w-12 text-center">Resp.</th>
                            <th class="px-3 py-4 w-48">Supervisor</th>
                            <th class="px-3 py-4 w-24 text-center">Área</th>
                            
                            @if($necesitaCliente)
                                <th class="px-3 py-4 w-32">Cliente</th>
                            @endif

                            <th class="px-3 py-4 w-24">Tipo</th>
                            <th class="px-3 py-4 w-28 text-center">Prioridad</th>
                            <th class="px-3 py-4 min-w-[250px]">Actividad</th>
                            <th class="px-3 py-4 w-20 text-center">Inicio</th>
                            <th class="px-3 py-4 w-20 text-center">Promesa</th>
                            <th class="px-3 py-4 w-20 text-center">Fin</th>
                            <th class="px-2 py-4 w-16 text-center bg-slate-700">Meta</th>
                            <th class="px-2 py-4 w-16 text-center bg-slate-700">Real</th>
                            <th class="px-2 py-4 w-16 text-center bg-slate-700">Efic.</th>
                            <th class="px-3 py-4 w-40 text-center">Estatus</th>
                            <th class="px-2 py-4 w-16 text-center">Editar</th>
                            <th class="px-2 py-4 w-12 text-center">Log</th>
                            {{-- NUEVA COLUMNA DE ELIMINAR --}}
                            <th class="px-2 py-4 w-10 text-center text-red-300">
                                <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($activities as $act)
                        <tr class="hover:bg-indigo-50/40 transition-colors group {{ str_contains($act->estatus, 'Completado') ? 'bg-slate-50/80' : '' }}">
                            <td class="px-3 py-3 text-center">
                                @if($act->user_id === Auth::id())
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold text-[9px] border border-indigo-200 shadow-sm">YO</span>
                                @else
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-700 font-bold text-[9px] border border-orange-200 shadow-sm" title="{{ $act->user->name }}">
                                        {{ strtoupper(substr($act->user->name ?? 'U', 0, 2)) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-slate-600 font-medium leading-tight text-[11px]">
                                {{ Str::limit($act->user->empleado->supervisor->nombre ?? '-', 25) }}
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="px-2 py-1 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600 text-[9px] font-bold uppercase tracking-wide">
                                    {{ Str::limit($act->area ?? 'N/A', 10) }}
                                </span>
                            </td>
                            @if($necesitaCliente)
                                <td class="px-3 py-3 text-slate-700 font-bold text-[10px]">
                                    @if($act->cliente)
                                        <span class="bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded border border-yellow-200 block truncate max-w-[120px]" title="{{ $act->cliente }}">
                                            {{ $act->cliente }}
                                        </span>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            @endif
                            <td class="px-3 py-3 text-slate-500">
                                <span class="px-2 py-1 rounded bg-slate-100 border border-slate-200 text-[10px]">{{ Str::limit($act->tipo_actividad, 12) }}</span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                @php
                                    $puedeEditar = false;
                                    $miEmpleado = Auth::user()->empleado;
                                    $suEmpleado = $act->user->empleado ?? null;
                                    if (isset($esDireccion) && $esDireccion) { $puedeEditar = true; }
                                    elseif ($miEmpleado && $suEmpleado && $miEmpleado->id === $suEmpleado->supervisor_id) { $puedeEditar = true; }
                                    elseif ($act->user_id === Auth::id()) { $puedeEditar = true; } 
                                    $prioColor = match($act->prioridad) { 
                                        'Alta'=>'bg-red-50 text-red-700 border-red-200', 
                                        'Media'=>'bg-yellow-50 text-yellow-700 border-yellow-200', 
                                        default=>'bg-blue-50 text-blue-700 border-blue-200' 
                                    };
                                @endphp
                                <form action="{{ route('activities.update', $act->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <select name="prioridad" onchange="this.form.submit()" 
                                            class="text-[10px] py-1 pl-2 pr-6 rounded-md border {{ $prioColor }} font-bold shadow-sm focus:ring-2 focus:ring-indigo-500 cursor-pointer w-full text-center"
                                            {{ !$puedeEditar ? 'disabled' : '' }}>
                                        <option value="Baja" {{ $act->prioridad == 'Baja' ? 'selected' : '' }}>Baja</option>
                                        <option value="Media" {{ $act->prioridad == 'Media' ? 'selected' : '' }}>Media</option>
                                        <option value="Alta" {{ $act->prioridad == 'Alta' ? 'selected' : '' }}>Alta 🔥</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-3 py-3 text-slate-800 font-medium leading-snug break-words">
                                <div class="flex items-start gap-2">
                                    @php $esPlaneada = $act->historial->contains('action', 'approved'); @endphp
                                    @if($esPlaneada)
                                        <span class="mt-0.5 text-indigo-500" title="Actividad Planeada y Aprobada">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg>
                                        </span>
                                    @else
                                        <span class="mt-0.5 text-orange-400" title="Actividad Extra / Bomberazo">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" /></svg>
                                        </span>
                                    @endif
                                    <span>{{ $act->nombre_actividad }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center text-slate-500">{{ $act->fecha_inicio ? $act->fecha_inicio->format('d/m') : '-' }}</td>
                            <td class="px-3 py-3 text-center text-indigo-700 font-bold">{{ $act->fecha_compromiso ? $act->fecha_compromiso->format('d/m') : '-' }}</td>
                            <td class="px-3 py-3 text-center text-slate-500">{{ $act->fecha_final ? $act->fecha_final->format('d/m') : '-' }}</td>
                            <td class="px-2 py-3 text-center bg-slate-50 font-mono text-slate-600 border-l border-slate-100">{{ $act->metrico }}</td>
                            <td class="px-2 py-3 text-center bg-slate-50 font-mono font-bold border-l border-slate-100 {{ ($act->resultado_dias > $act->metrico) ? 'text-red-600' : 'text-emerald-600' }}">{{ $act->resultado_dias ?? '-' }}</td>
                            <td class="px-2 py-3 text-center bg-slate-50 font-bold text-slate-800 border-l border-slate-100">{{ isset($act->porcentaje) ? number_format($act->porcentaje, 0).'%' : '-' }}</td>
                            <td class="px-3 py-3">
                                @php
                                    $statusStyle = match($act->estatus) {
                                        'Completado' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'Retardo' => 'bg-red-100 text-red-800 border-red-200 animate-pulse',
                                        'Completado con retardo' => 'bg-orange-100 text-orange-800 border-orange-200',
                                        'En proceso' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        default => 'bg-slate-100 text-slate-500 border-slate-200'
                                    };
                                @endphp
                                <div class="text-[10px] py-1 px-2 text-center rounded-md border {{ $statusStyle }} font-semibold shadow-sm">
                                    {{ $act->estatus }}
                                </div>
                            </td>
                            <td class="px-2 py-3 text-center">
                                <button @click="openNotes({{ $act->id }}, '{{ addslashes($act->nombre_actividad) }}', '{{ $act->estatus }}', '{{ $act->evidencia_path ? \Storage::url($act->evidencia_path) : '' }}', '{{ addslashes($act->cliente ?? '') }}', '{{ $act->fecha_compromiso->format('Y-m-d') }}', '{{ $act->hora_inicio_programada }}', '{{ $act->hora_fin_programada }}', '{{ addslashes($act->motivo_rechazo ?? '') }}')" 
                                        class="text-indigo-600 hover:text-white hover:bg-indigo-600 border border-indigo-200 bg-indigo-50 p-1.5 rounded-lg transition-all shadow-sm flex items-center gap-1 mx-auto" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    @if($act->evidencia_path) <span class="text-[9px] font-bold">📎</span> @endif
                                </button>
                                <textarea id="notes-data-{{ $act->id }}" class="hidden">{{ $act->comentarios }}</textarea>
                            </td>
                            <td class="px-2 py-3 text-center">
                                <button @click="openHistory({{ $act->id }}, '{{ addslashes($act->nombre_actividad) }}')" 
                                        class="text-slate-400 hover:text-slate-700 p-1 rounded-full hover:bg-slate-100 transition-colors" title="Ver Historial">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </button>
                                <textarea id="history-data-{{ $act->id }}" class="hidden">{{ json_encode($act->historial) }}</textarea>
                            </td>
                            
                            {{-- LÓGICA BOTÓN ELIMINAR --}}
                            <td class="px-2 py-3 text-center">
                                @php
                                    $puedeEliminar = false;
                                    $currentUser = Auth::user();
                                    
                                    // 1. Si es Dirección (Admin)
                                    if (isset($esDireccion) && $esDireccion) {
                                        $puedeEliminar = true;
                                    }
                                    // 2. Si es Supervisor directo del dueño de la actividad
                                    elseif ($currentUser->empleado && $act->user->empleado && $currentUser->empleado->id === $act->user->empleado->supervisor_id) {
                                        $puedeEliminar = true;
                                    }
                                    // 3. (Opcional) Si es el dueño y la actividad está "En blanco" (aún no iniciada)
                                    elseif ($act->user_id === $currentUser->id && $act->estatus === 'En blanco') {
                                        $puedeEliminar = true;
                                    }
                                @endphp

                                @if($puedeEliminar)
                                    <form action="{{ route('activities.destroy', $act->id) }}" method="POST" onsubmit="return confirm('⚠️ ¿Estás seguro de eliminar esta actividad?\n\nEsta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-300 hover:text-red-600 transition-colors p-1 rounded-full hover:bg-red-50 group" title="Eliminar Actividad">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="17" class="py-12 text-center text-slate-400 italic bg-slate-50/50 rounded-b-xl border-t border-slate-100">No hay actividades.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($activities->hasPages())
                <div class="px-4 py-3 bg-white border-t border-slate-100">{{ $activities->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL DE PLANIFICADOR --}}
@if($necesitaCliente)
<div id="planModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-90 transition-opacity backdrop-blur-sm" onclick="closePlanModal()"></div>
        <div class="relative inline-block align-bottom bg-slate-100 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-[95%] w-full border border-slate-200">
            <form action="{{ route('activities.storeBatch') }}" method="POST">
                @csrf
                <div class="bg-indigo-700 px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Planificación Semanal
                        </h3>
                        <p class="text-indigo-200 text-xs mt-1">Define tus objetivos por día. Estas actividades requieren aprobación.</p>
                    </div>
                    <div class="flex items-center gap-2 bg-indigo-800 p-1.5 rounded-lg border border-indigo-600">
                        <label class="text-indigo-200 text-xs font-bold pl-2">Semana del:</label>
                        <input type="date" name="semana_inicio" id="weekPicker" class="bg-indigo-600 border-none text-white text-sm rounded font-bold focus:ring-0" 
                               value="{{ now()->startOfWeek()->format('Y-m-d') }}" onchange="updateWeekLabels()">
                    </div>
                </div>
                <div class="p-4 overflow-x-auto bg-slate-100">
                    <div class="min-w-[1000px] grid grid-cols-5 gap-4">
                        @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $index => $dia)
                        <div class="flex flex-col h-full">
                            <div class="bg-white border-b-4 border-indigo-500 rounded-t-lg p-3 text-center shadow-sm mb-2">
                                <h4 class="text-sm font-black text-slate-700 uppercase">{{ $dia }}</h4>
                                <span class="text-xs text-indigo-600 font-bold" id="label-date-{{ $index }}">--/--</span>
                            </div>
                            <div class="flex-1 bg-slate-200/60 rounded-lg p-2 space-y-2 min-h-[300px] border border-slate-200 shadow-inner" id="container-day-{{ $index }}"></div>
                            <button type="button" onclick="addTaskCard({{ $index }})" class="mt-2 w-full py-2 border-2 border-dashed border-slate-300 rounded-lg text-slate-400 text-xs font-bold hover:border-indigo-400 hover:text-indigo-600 hover:bg-white transition flex justify-center items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Agregar Tarea
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="bg-white px-6 py-4 flex justify-end items-center gap-3 border-t border-slate-200">
                    <button type="button" onclick="closePlanModal()" class="px-5 py-2.5 border border-slate-300 rounded-xl text-slate-600 text-sm font-bold hover:bg-slate-50 transition">Cancelar</button>
                    <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition transform hover:-translate-y-0.5">
                        Guardar y Enviar a Revisión
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL DE EDICIÓN Y NOTAS --}}
<div id="notesModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" onclick="closeNotes()"></div>
        
        <div class="relative inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-200">
            <form id="notesForm" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                
                {{-- HEADER DEL MODAL --}}
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg leading-6 font-bold text-slate-800">Detalles de Actividad</h3>
                        <button type="button" onclick="closeNotes()" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- ALERTA DE RECHAZO (Solo visible si es rechazada) --}}
                    <div id="modal-rejection-alert" class="hidden mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-r text-sm">
                        <p class="font-bold text-red-800 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Actividad Rechazada
                        </p>
                        <p class="text-red-700 mt-1">Motivo: <span id="modal-rejection-reason" class="font-semibold italic">...</span></p>
                        <p class="text-xs text-red-500 mt-2">Corrige los datos abajo y guarda para enviar a revisión nuevamente.</p>
                    </div>

                    <div class="space-y-4">
                        {{-- NOMBRE DE ACTIVIDAD (Input) --}}
                        <div>
                            <label id="label-activity-name" class="block text-xs font-bold text-slate-500 uppercase mb-1">Actividad</label>
                            <textarea name="nombre_actividad" id="modal-activity-name" rows="2" 
                                class="w-full text-sm rounded-lg border-transparent bg-slate-100 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition shadow-sm resize-none" 
                                placeholder="Describe la actividad..."></textarea>
                        </div>

                        {{-- CAMPOS DE PLANIFICACIÓN (Fecha y Horas) - Ocultos por defecto --}}
                        <div id="modal-planning-fields" class="hidden grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-lg border border-slate-100 animate-fade-in-down">
                            <div class="col-span-2">
                                <p class="text-[10px] font-bold text-indigo-600 uppercase mb-2 border-b border-indigo-100 pb-1">Corregir Planificación</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Fecha Compromiso</label>
                                <input type="date" name="fecha_compromiso" id="modal-fecha" class="w-full text-xs rounded-md border-slate-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Inicio</label>
                                    <input type="time" name="hora_inicio_programada" id="modal-hora-inicio" class="w-full text-xs rounded-md border-slate-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Fin</label>
                                    <input type="time" name="hora_fin_programada" id="modal-hora-fin" class="w-full text-xs rounded-md border-slate-300 shadow-sm">
                                </div>
                            </div>
                        </div>

                        @if($necesitaCliente)
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Cliente</label>
                                <input type="text" name="cliente" id="modal-cliente" class="w-full text-sm rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        @endif

                        {{-- ESTATUS (Oculto si es rechazo) --}}
                        <div id="div-estatus-selector">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Estatus Actual</label>
                            <select name="estatus" id="modal-estatus" class="w-full text-sm rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="En blanco">Pendiente (En blanco)</option>
                                <option value="En proceso">En proceso</option>
                                <option value="Completado">Completado</option>
                                <option value="Retardo">Retardo</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Comentarios / Bitácora</label>
                            <textarea name="comentarios" id="modal-comentarios" rows="3" class="w-full text-sm rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Agrega notas sobre el avance..."></textarea>
                        </div>

                        <div class="bg-indigo-50 p-3 rounded-lg border border-indigo-100 flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold text-indigo-800 mb-1">Evidencia (Opcional)</label>
                                <input type="file" name="evidencia" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-white file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                            <div id="modal-evidencia-link" class="hidden">
                                <a href="#" target="_blank" class="flex items-center gap-1 text-xs font-bold text-indigo-600 hover:underline bg-white px-2 py-1 rounded shadow-sm">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    Ver Actual
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-4 py-3 sm:px-6 flex flex-row-reverse gap-2 border-t border-slate-200">
                    <button type="submit" class="inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                        Guardar Cambios
                    </button>
                    <button type="button" onclick="closeNotes()" class="inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-sm font-bold text-slate-700 hover:bg-slate-50 focus:outline-none transition">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL HISTORIAL --}}
<div id="historyModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeHistory()"></div>
        <div class="relative inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <div class="bg-indigo-600 px-4 py-3 flex justify-between items-center shadow-md z-10 relative">
                <h3 class="text-lg leading-6 font-bold text-white">Historial</h3>
                <button type="button" onclick="closeHistory()" class="text-indigo-200 hover:text-white"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider" id="history-activity-title">Activity</p>
            </div>
            <div class="px-4 py-6 sm:p-6 max-h-[60vh] overflow-y-auto bg-white">
                <ol class="relative border-l-2 border-indigo-100 ml-3 space-y-8" id="history-timeline-container"></ol>
                <div id="history-empty-state" class="hidden text-center py-8"><p class="text-gray-500 text-sm">No hay registros.</p></div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPTS --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ejecutar inmediatamente y luego cada segundo
        updateLiveClock();
        setInterval(updateLiveClock, 1000);
    });

    function updateLiveClock() {
        const now = new Date();
        const options = { day: 'numeric', month: 'short', year: 'numeric', hour: 'numeric', minute: '2-digit' };
        
        const dia = now.getDate();
        const mes = now.toLocaleString('es-MX', { month: 'short' });
        const anio = now.getFullYear();
        const hora = now.toLocaleString('es-MX', { hour: 'numeric', minute: '2-digit', hour12: true });
        
        const clockElement = document.getElementById('live-clock');
        if(clockElement) {
            clockElement.innerText = `${dia} ${mes}, ${anio} · ${hora}`;
        }

        // LÓGICA DE BLOQUEO EN TIEMPO REAL
        const controls = document.getElementById('planning-controls');
        if (!controls) return;

        const isAdmin = controls.getAttribute('data-is-admin') === 'true';
        if (isAdmin) return;

        const day = now.getDay(); // 1 = Lunes
        const hour = now.getHours(); 

        const esLunes = (day === 1);
        const antesDeLas11 = (hour < 11);
        const esTiempoDePlanear = esLunes && antesDeLas11;

        const btnPlanificar = document.getElementById('btn-planificar');
        const btnBloqueado = document.getElementById('btn-bloqueado');

        if (esTiempoDePlanear) {
            if(btnPlanificar) btnPlanificar.classList.remove('hidden');
            if(btnBloqueado) btnBloqueado.classList.add('hidden');
        } else {
            if(btnPlanificar) btnPlanificar.classList.add('hidden');
            if(btnBloqueado) btnBloqueado.classList.remove('hidden');
            
            const modal = document.getElementById('planModal');
            if(modal && !modal.classList.contains('hidden')) {
                closePlanModal();
                alert('El tiempo de planificación ha terminado.');
            }
        }
    }

    // --- PLANIFICADOR SEMANAL ---
    function openPlanModal() {
        document.getElementById('planModal').classList.remove('hidden');
        updateWeekLabels();
        for(let i=0; i<5; i++) {
            const container = document.getElementById(`container-day-${i}`);
            if(container && container.children.length === 0) addTaskCard(i);
        }
    }
    function closePlanModal() { document.getElementById('planModal').classList.add('hidden'); }

    function updateWeekLabels() {
        const input = document.getElementById('weekPicker').value;
        if(!input) return;
        const lunes = new Date(input + 'T00:00:00'); 
        for(let i=0; i<5; i++) {
            const dia = new Date(lunes);
            dia.setDate(lunes.getDate() + i);
            const label = dia.toLocaleDateString('es-MX', { day: 'numeric', month: 'short' });
            const labelEl = document.getElementById(`label-date-${i}`);
            if(labelEl) labelEl.innerText = label;
        }
    }

    // FUNCIÓN ACTUALIZADA CON TIEMPO DE INICIO Y FIN
    function addTaskCard(dayIndex) {
        const container = document.getElementById(`container-day-${dayIndex}`);
        const cardIndex = container.children.length + Math.floor(Math.random() * 10000);
        const card = document.createElement('div');
        
        card.className = "bg-white p-2.5 rounded-lg shadow-sm border border-slate-200 relative group animate-fade-in-up hover:shadow-md transition";
        
        card.innerHTML = `
            <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition cursor-pointer z-10 bg-white rounded-full p-0.5 shadow-sm" onclick="this.parentElement.remove()">
                <svg class="w-3 h-3 text-red-400 hover:text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div class="space-y-2">
                <input type="hidden" name="plan[${dayIndex}][${cardIndex}][area]" value="Anexo 24">
                
                <div class="flex items-center gap-2 bg-slate-50 p-1.5 rounded border border-slate-100">
                    <div class="flex-1">
                        <label class="block text-[8px] font-bold text-slate-400 uppercase">Inicio</label>
                        <input type="time" name="plan[${dayIndex}][${cardIndex}][start_time]" 
                               class="w-full text-[10px] font-bold border-0 bg-transparent p-0 focus:ring-0 text-slate-700 h-4" required>
                    </div>
                    <div class="text-slate-300">-</div>
                    <div class="flex-1 text-right">
                        <label class="block text-[8px] font-bold text-slate-400 uppercase">Fin</label>
                        <input type="time" name="plan[${dayIndex}][${cardIndex}][end_time]" 
                               class="w-full text-[10px] font-bold border-0 bg-transparent p-0 focus:ring-0 text-slate-700 h-4 text-right" required>
                    </div>
                </div>

                <div class="flex gap-2">
                    <div class="flex-1">
                        <input type="text" name="plan[${dayIndex}][${cardIndex}][cliente]" class="w-full text-[9px] font-bold border-0 border-b border-slate-200 focus:border-indigo-500 focus:ring-0 px-0 bg-transparent placeholder-slate-400 text-indigo-700" placeholder="Cliente (Opcional)">
                    </div>
                    <div class="w-1/3">
                        <input type="text" name="plan[${dayIndex}][${cardIndex}][tipo]" class="w-full text-[9px] font-bold border-0 border-b border-slate-200 focus:border-indigo-500 focus:ring-0 px-0 bg-transparent placeholder-slate-400 text-slate-600 text-right" placeholder="Tipo">
                    </div>
                </div>

                <textarea name="plan[${dayIndex}][${cardIndex}][actividad]" rows="2" class="w-full text-[10px] border-0 bg-slate-50 rounded p-1.5 focus:ring-1 focus:ring-indigo-500 placeholder-slate-400 resize-none leading-snug text-slate-700" placeholder="Descripción de la actividad..." required></textarea>
            </div>
        `;
        container.appendChild(card);
        const timeInput = card.querySelector('input[type="time"]');
        if(timeInput) timeInput.focus();
    }

    // --- MODALES GENERALES ---
    // Actualizamos la firma para recibir todos los datos de planificación
    function openNotes(id, name, estatus, evidenciaUrl, cliente, fecha, horaInicio, horaFin, motivoRechazo) {
        const form = document.getElementById('notesForm');
        form.action = "/activities/" + id;
        
        // 1. Llenar campos básicos
        document.getElementById('modal-activity-name').value = name;
        document.getElementById('modal-estatus').value = estatus;
        
        const clientInput = document.getElementById('modal-cliente');
        if(clientInput) clientInput.value = cliente || '';

        // 2. Llenar campos de planificación (NUEVO)
        document.getElementById('modal-fecha').value = fecha || '';
        document.getElementById('modal-hora-inicio').value = horaInicio || '';
        document.getElementById('modal-hora-fin').value = horaFin || '';

        // 3. Manejo de Comentarios Previos
        var currentNote = document.getElementById('notes-data-' + id);
        if(currentNote) document.getElementById('modal-comentarios').value = currentNote.value;
        
        // 4. Manejo de Evidencia
        const linkDiv = document.getElementById('modal-evidencia-link');
        const linkTag = linkDiv.querySelector('a');
        if(evidenciaUrl) { 
            linkTag.href = evidenciaUrl; 
            linkDiv.classList.remove('hidden'); 
        } else { 
            linkDiv.classList.add('hidden'); 
        }

        // 5. LÓGICA DE MODO "CORRECCIÓN" vs "AVANCE"
        const divRechazo = document.getElementById('modal-rejection-alert');
        const txtMotivo = document.getElementById('modal-rejection-reason');
        const containerPlanning = document.getElementById('modal-planning-fields');
        const labelName = document.getElementById('label-activity-name');

        if (estatus === 'Rechazado') {
            // MODO CORRECCIÓN: Mostramos alerta roja y habilitamos campos de planificación
            divRechazo.classList.remove('hidden');
            txtMotivo.innerText = motivoRechazo || 'Sin motivo especificado.';
            
            containerPlanning.classList.remove('hidden'); // Mostrar inputs de hora/fecha
            
            // Hacemos el nombre editable y notorio
            document.getElementById('modal-activity-name').classList.remove('bg-gray-100', 'border-transparent');
            document.getElementById('modal-activity-name').classList.add('bg-white', 'border-gray-300');
            document.getElementById('modal-activity-name').readOnly = false;
            
            labelName.innerText = "Corregir Nombre de la Actividad:";
            labelName.classList.add('text-red-700', 'font-bold');

            // Ocultamos el selector de estatus (se auto-pondrá en Por Aprobar al guardar)
            document.getElementById('div-estatus-selector').classList.add('hidden');

        } else {
            // MODO NORMAL: Ocultamos alerta y protegemos planificación
            divRechazo.classList.add('hidden');
            containerPlanning.classList.add('hidden'); // Ocultar inputs extra para no saturar
            
            // Nombre solo lectura
            document.getElementById('modal-activity-name').classList.add('bg-gray-100', 'border-transparent');
            document.getElementById('modal-activity-name').classList.remove('bg-white', 'border-gray-300');
            document.getElementById('modal-activity-name').readOnly = true;
            
            labelName.innerText = "Actividad:";
            labelName.classList.remove('text-red-700', 'font-bold');

            // Mostramos selector de estatus normal
            document.getElementById('div-estatus-selector').classList.remove('hidden');
        }

        document.getElementById('notesModal').classList.remove('hidden');
    }

    function closeNotes() { document.getElementById('notesModal').classList.add('hidden'); }

    function openHistory(id, title) {
        const textarea = document.getElementById('history-data-' + id);
        if (!textarea) return;
        let history = [];
        try { history = JSON.parse(textarea.value); } catch (e) { return; }
        
        document.getElementById('history-activity-title').textContent = title;
        const container = document.getElementById('history-timeline-container');
        const emptyState = document.getElementById('history-empty-state');
        container.innerHTML = '';

        if (!history || history.length === 0) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
            history.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            history.forEach(log => {
                let dateStrRaw = log.created_at || new Date().toISOString();
                if (dateStrRaw.indexOf('T') === -1) dateStrRaw = dateStrRaw.replace(' ', 'T'); 
                const date = new Date(dateStrRaw);
                const dateDisplay = !isNaN(date) ? date.toLocaleDateString('es-MX', { day: '2-digit', month: 'short' }) : '--';
                const timeDisplay = !isNaN(date) ? date.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }) : '--';
                
                const userName = log.user ? log.user.name : 'Sistema';
                let contentHtml = log.action === 'created' ? '<span class="text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded text-xs">✨ Creada</span>' : log.details || `Modificó <b>${log.field}</b>`;
                if(log.action === 'approved') contentHtml = '<span class="text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded text-xs">✅ Aprobada por Supervisor</span>';
                if(log.action === 'rejected') contentHtml = '<span class="text-red-600 font-bold bg-red-50 px-2 py-0.5 rounded text-xs">🛑 Rechazada: ' + (log.details.replace('Rechazado: ', '') || '') + '</span>';
                if(log.action === 'updated' && log.details && log.details.includes('Inició')) contentHtml = '<span class="text-blue-600 font-bold bg-blue-50 px-2 py-0.5 rounded text-xs">▶️ Iniciada (Agregada a Reporte)</span>';

                const item = `<li class="mb-6 ml-6"><span class="absolute flex items-center justify-center w-8 h-8 bg-white rounded-full -left-4 ring-4 ring-gray-50 shadow-sm border border-gray-100 text-[10px] font-bold text-indigo-600">${userName.substring(0,2).toUpperCase()}</span><div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm"><p class="text-xs font-bold text-gray-900">${userName} <span class="text-gray-400 font-normal">· ${dateDisplay} ${timeDisplay}</span></p><div class="text-sm mt-1">${contentHtml}</div></div></li>`;
                container.insertAdjacentHTML('beforeend', item);
            });
        }
        document.getElementById('historyModal').classList.remove('hidden');
    }
    function closeHistory() { document.getElementById('historyModal').classList.add('hidden'); }
</script>

<style>
    .animate-fade-in-down { animation: fadeInDown 0.5s ease-out; }
    .animate-fade-in-up { animation: fadeInUp 0.3s ease-out; }
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endsection