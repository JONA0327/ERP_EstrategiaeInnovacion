@extends('layouts.erp')

@section('title', 'Tablero de Actividades')

@section('content')
<div class="min-h-screen bg-slate-50/50 py-8" x-data="{ showFilters: false }">
    <div class="max-w-[98%] mx-auto space-y-6">
        
        {{-- HEADER PRINCIPAL --}}
        <div class="flex flex-col md:flex-row justify-between items-center bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Tablero de Actividades</h1>
                <p class="text-xs text-slate-500 mt-1">Gestión operativa y seguimiento de compromisos</p>
            </div>
            
            {{-- BOTÓN PLANIFICADOR (Con Restricción de Horario) --}}
            <div class="flex gap-3 mt-4 md:mt-0 items-center">
                @if($necesitaCliente)
                    @php
                        $now = now();
                        // REGLA: Lunes antes de las 11:00 AM (00:00 - 10:59)
                        $esTiempoDePlanear = $now->isMonday() && $now->hour < 11;
                        if(isset($esDireccion) && $esDireccion) $esTiempoDePlanear = true; // Dirección siempre puede
                    @endphp

                    @if($esTiempoDePlanear)
                        {{-- Botón Activo --}}
                        <button onclick="openPlanModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-lg hover:bg-indigo-700 flex items-center gap-2 transition transform hover:scale-105 animate-pulse">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Planificar Semana
                        </button>
                    @else
                        {{-- Botón Bloqueado --}}
                        <div class="group relative">
                            <button disabled class="bg-slate-200 text-slate-400 px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 cursor-not-allowed border border-slate-300">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                Planificación Cerrada
                            </button>
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 bg-slate-800 text-white text-[10px] text-center p-2 rounded opacity-0 group-hover:opacity-100 transition pointer-events-none z-20 shadow-xl">
                                Solo disponible los Lunes antes de las 11:00 AM.
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-slate-800"></div>
                            </div>
                        </div>
                    @endif
                @endif

                <div class="text-right hidden lg:block mr-4 border-l pl-4 border-slate-200">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Hoy</p>
                    <p class="text-sm font-bold text-indigo-600">{{ now()->isoFormat('D MMM, YYYY · h:mm A') }}</p>
                </div>
            </div>
        </div>

        {{-- 1. ZONA DE APROBACIÓN (SOLO SUPERVISORES) --}}
        @if(($esSupervisor || $esDireccion) && !empty($pendingApprovals) && count($pendingApprovals) > 0)
        <div class="bg-orange-50 border-l-4 border-orange-500 p-6 rounded-r-xl shadow-sm animate-fade-in-down mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-orange-100 text-orange-600 rounded-full">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-orange-900">Revisiones Pendientes</h3>
                    <p class="text-xs text-orange-700">Tu equipo ha enviado planes semanales. Revisa, ajusta tiempos si es necesario y aprueba.</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($pendingApprovals as $userId => $userActivities)
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-orange-100">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-3 mb-3">
                            <span class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                                {{ substr($userActivities->first()->user->name, 0, 2) }}
                            </span>
                            <h4 class="font-bold text-slate-700 text-sm">{{ $userActivities->first()->user->name }}</h4>
                            <span class="text-[10px] text-slate-400 bg-slate-50 px-2 py-1 rounded-lg ml-auto">{{ count($userActivities) }} tareas</span>
                        </div>

                        <div class="space-y-2 max-h-60 overflow-y-auto pr-1 custom-scrollbar">
                            @foreach($userActivities as $act)
                                <div class="relative bg-slate-50 p-3 rounded-lg border border-slate-200 hover:border-orange-300 transition group">
                                    <form action="{{ route('activities.approve', $act->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        
                                        {{-- Campo de Edición Rápida (Nombre) --}}
                                        <div class="mb-2">
                                            <input type="text" name="ajuste_nombre" value="{{ $act->nombre_actividad }}" class="w-full bg-transparent border-0 border-b border-slate-300 focus:border-orange-500 focus:ring-0 text-xs px-0 font-bold text-slate-700 leading-tight" title="Editar nombre">
                                            <div class="flex flex-wrap gap-2 text-[10px] text-slate-400 mt-1">
                                                <span class="flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg> {{ $act->fecha_compromiso->format('D d') }}</span>
                                                @if($act->cliente)
                                                    <span class="bg-yellow-100 text-yellow-700 px-1.5 rounded flex items-center gap-1 font-semibold">{{ $act->cliente }}</span>
                                                @endif
                                                @if($act->tipo_actividad)
                                                    <span class="bg-indigo-50 text-indigo-600 px-1.5 rounded">{{ $act->tipo_actividad }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center justify-between gap-2">
                                            {{-- Selector de Prioridad --}}
                                            <select name="ajuste_prio" class="border-none bg-white rounded shadow-sm text-[10px] font-bold text-slate-600 focus:ring-orange-500 py-1 pl-2 pr-6 cursor-pointer">
                                                <option value="Alta" {{ $act->prioridad == 'Alta' ? 'selected' : '' }}>Alta</option>
                                                <option value="Media" {{ $act->prioridad == 'Media' ? 'selected' : '' }}>Media</option>
                                                <option value="Baja" {{ $act->prioridad == 'Baja' ? 'selected' : '' }}>Baja</option>
                                            </select>

                                            <div class="flex gap-1">
                                                <button type="submit" class="bg-emerald-100 text-emerald-700 hover:bg-emerald-200 border border-emerald-200 px-2 py-1 rounded text-[10px] font-bold transition flex items-center gap-1" title="Aprobar">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> OK
                                                </button>
                                                <button type="button" onclick="submitReject({{ $act->id }})" class="bg-white text-red-500 hover:bg-red-50 border border-red-200 px-2 py-1 rounded text-[10px] font-bold transition" title="Rechazar">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <form id="reject-form-{{ $act->id }}" action="{{ route('activities.reject', $act->id) }}" method="POST" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- 2. ZONA: MIS OBJETIVOS SEMANALES (PLAN APROBADO) --}}
        @if(isset($plannedActivities) && $plannedActivities->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-indigo-100 p-5 mb-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        Mis Objetivos de la Semana
                    </h3>
                    <p class="text-xs text-slate-500">Estas son tus intenciones aprobadas. Dale "Iniciar" cuando realmente comiences a trabajar en ellas para pasarlas a tu reporte.</p>
                </div>

                {{-- BARRA DE ADHERENCIA --}}
                <div class="bg-indigo-50 px-4 py-2 rounded-lg border border-indigo-100 flex items-center gap-3">
                    <span class="text-xs font-bold text-indigo-700">Pendientes por iniciar:</span>
                    <span class="bg-white text-indigo-800 text-xs font-black px-2 py-0.5 rounded shadow-sm">{{ $plannedActivities->count() }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($plannedActivities as $plan)
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 hover:shadow-md transition group">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 uppercase">{{ $plan->fecha_compromiso->format('l d') }}</span>
                            @if($plan->prioridad == 'Alta')
                                <span class="text-[10px] text-red-600 font-bold flex items-center gap-1">🔥 Alta</span>
                            @endif
                        </div>
                        
                        <h4 class="text-xs font-bold text-slate-700 mb-1 line-clamp-2" title="{{ $plan->nombre_actividad }}">{{ $plan->nombre_actividad }}</h4>
                        
                        <div class="flex flex-col gap-1 mb-3">
                            @if($plan->cliente)
                                <p class="text-[10px] text-slate-500 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    {{ Str::limit($plan->cliente, 20) }}
                                </p>
                            @endif
                            <span class="text-[9px] text-slate-400 italic">{{ $plan->tipo_actividad }}</span>
                        </div>

                        <form action="{{ route('activities.start', $plan->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button type="submit" class="w-full bg-white border border-slate-300 hover:border-indigo-500 hover:text-indigo-600 text-slate-600 text-xs font-bold py-1.5 rounded-md transition shadow-sm flex items-center justify-center gap-2 group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Iniciar Tarea
                            </button>
                        </form>
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
                    <button type="submit" class="hidden">Buscar</button>
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
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tipo</label>
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
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Prio</label>
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

        {{-- 6. TABLA --}}
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
                            <th class="px-3 py-4 w-28 text-center">Prio</th>
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
                                    {{-- Distintivo Visual: Planeado vs Bomberazo --}}
                                    @php
                                        $esPlaneada = $act->historial->contains('action', 'approved');
                                    @endphp

                                    @if($esPlaneada)
                                        <span class="mt-0.5 text-indigo-500" title="Actividad Planeada y Aprobada">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg>
                                        </span>
                                    @else
                                        <span class="mt-0.5 text-orange-400" title="Actividad Extra / No Planeada (Bomberazo)">
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
                                <button @click="openNotes({{ $act->id }}, '{{ addslashes($act->nombre_actividad) }}', '{{ $act->estatus }}', '{{ $act->evidencia_path ? \Storage::url($act->evidencia_path) : '' }}', '{{ addslashes($act->cliente ?? '') }}')" 
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
                        </tr>
                        @empty
                        <tr><td colspan="16" class="py-12 text-center text-slate-400 italic bg-slate-50/50 rounded-b-xl border-t border-slate-100">No hay actividades.</td></tr>
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

{{-- MODAL DE PLANIFICADOR SEMANAL (ESTILO CALENDARIO KANBAN) --}}
@if($necesitaCliente)
<div id="planModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-90 transition-opacity backdrop-blur-sm" onclick="closePlanModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-slate-100 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-[95%] w-full border border-slate-200">
            <form action="{{ route('activities.storeBatch') }}" method="POST">
                @csrf
                
                {{-- Header del Modal --}}
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
                
                {{-- GRID SEMANAL --}}
                <div class="p-4 overflow-x-auto bg-slate-100">
                    <div class="min-w-[1000px] grid grid-cols-5 gap-4">
                        
                        @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $index => $dia)
                        <div class="flex flex-col h-full">
                            {{-- Cabecera del Día --}}
                            <div class="bg-white border-b-4 border-indigo-500 rounded-t-lg p-3 text-center shadow-sm mb-2">
                                <h4 class="text-sm font-black text-slate-700 uppercase">{{ $dia }}</h4>
                                <span class="text-xs text-indigo-600 font-bold" id="label-date-{{ $index }}">--/--</span>
                            </div>

                            {{-- Contenedor de Tarjetas --}}
                            <div class="flex-1 bg-slate-200/60 rounded-lg p-2 space-y-2 min-h-[300px] border border-slate-200 shadow-inner" id="container-day-{{ $index }}"></div>

                            {{-- Botón Agregar --}}
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
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeNotes()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form id="notesForm" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-bold text-gray-900 mb-2">Actualizar Actividad</h3>
                    <p class="text-sm text-gray-500 mb-4" id="modal-activity-name">...</p>
                    
                    <div class="space-y-4">
                        @if($necesitaCliente)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                                <input type="text" name="cliente" id="modal-cliente" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estatus</label>
                            <select name="estatus" id="modal-estatus" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="En blanco">Pendiente (En blanco)</option>
                                <option value="En proceso">En proceso</option>
                                <option value="Completado">Completado</option>
                                <option value="Retardo">Retardo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Comentarios</label>
                            <textarea name="comentarios" id="modal-comentarios" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>
                        <div class="bg-indigo-50 p-3 rounded-lg border border-indigo-100">
                            <label class="block text-sm font-medium text-indigo-900 mb-2">Evidencia</label>
                            <input type="file" name="evidencia" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200">
                            <div id="modal-evidencia-link" class="mt-2 hidden">
                                <a href="#" target="_blank" class="text-xs text-indigo-600 hover:underline font-bold">Ver evidencia actual</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Guardar</button>
                    <button type="button" onclick="closeNotes()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL HISTORIAL --}}
<div id="historyModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeHistory()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
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
    // --- PLANIFICADOR SEMANAL ---
    
    function openPlanModal() {
        document.getElementById('planModal').classList.remove('hidden');
        updateWeekLabels();
        
        // Agregar tarjetas vacías si está limpio para animar la UI
        for(let i=0; i<5; i++) {
            const container = document.getElementById(`container-day-${i}`);
            if(container && container.children.length === 0) addTaskCard(i);
        }
    }

    function closePlanModal() { 
        document.getElementById('planModal').classList.add('hidden'); 
    }

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

    function addTaskCard(dayIndex) {
        const container = document.getElementById(`container-day-${dayIndex}`);
        const cardIndex = container.children.length + Math.floor(Math.random() * 10000);

        const card = document.createElement('div');
        card.className = "bg-white p-2.5 rounded-lg shadow-sm border border-slate-200 relative group animate-fade-in-up hover:shadow-md transition";
        
        card.innerHTML = `
            <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition cursor-pointer z-10 bg-white rounded-full p-0.5 shadow-sm" onclick="this.parentElement.remove()">
                <svg class="w-3 h-3 text-red-400 hover:text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            
            <div class="space-y-1.5">
                <input type="hidden" name="plan[${dayIndex}][${cardIndex}][area]" value="Anexo 24">

                <div class="flex gap-2">
                    <div class="flex-1">
                        <input type="text" name="plan[${dayIndex}][${cardIndex}][cliente]" 
                            class="w-full text-[9px] font-bold border-0 border-b border-slate-200 focus:border-indigo-500 focus:ring-0 px-0 bg-transparent placeholder-slate-400 text-indigo-700" 
                            placeholder="Cliente (Opcional)">
                    </div>
                    <div class="w-1/3">
                        <input type="text" name="plan[${dayIndex}][${cardIndex}][tipo]" 
                            class="w-full text-[9px] font-bold border-0 border-b border-slate-200 focus:border-indigo-500 focus:ring-0 px-0 bg-transparent placeholder-slate-400 text-slate-600 text-right" 
                            placeholder="Tipo (Ej. Proyecto)">
                    </div>
                </div>
                
                <textarea name="plan[${dayIndex}][${cardIndex}][actividad]" rows="2"
                          class="w-full text-[10px] border-0 bg-slate-50 rounded p-1.5 focus:ring-1 focus:ring-indigo-500 placeholder-slate-400 resize-none leading-snug text-slate-700" 
                          placeholder="Descripción de la actividad..."></textarea>
            </div>
        `;
        
        container.appendChild(card);
        const txt = card.querySelector('textarea');
        if(txt) txt.focus();
    }

    // --- RECHAZAR ACTIVIDAD ---
    function submitReject(id) {
        if(confirm('¿Estás seguro de RECHAZAR esta actividad? Se eliminará permanentemente.')) {
            document.getElementById('reject-form-' + id).submit();
        }
    }

    // --- MODALES GENERALES ---
    function openNotes(id, name, estatus, evidenciaUrl, cliente) {
        document.getElementById('notesForm').action = "/activities/" + id;
        document.getElementById('modal-activity-name').innerText = name;
        document.getElementById('modal-estatus').value = estatus;
        
        const clientInput = document.getElementById('modal-cliente');
        if(clientInput) clientInput.value = cliente || '';
        
        var currentNote = document.getElementById('notes-data-' + id);
        if(currentNote) document.getElementById('modal-comentarios').value = currentNote.value;
        
        const linkDiv = document.getElementById('modal-evidencia-link');
        const linkTag = linkDiv.querySelector('a');
        if(evidenciaUrl) { linkTag.href = evidenciaUrl; linkDiv.classList.remove('hidden'); } 
        else { linkDiv.classList.add('hidden'); }
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