@extends('layouts.erp')

@section('title', 'Tablero de Actividades')

@section('content')
<div class="min-h-screen bg-slate-50/50 py-8" x-data="{ showFilters: false }">
    <div class="max-w-[95%] mx-auto space-y-6">
        
        {{-- ENCABEZADO --}}
        <div class="flex flex-col md:flex-row justify-between items-center bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Tablero de Actividades</h1>
                <p class="text-xs text-slate-500 mt-1">Gestión operativa y seguimiento de compromisos</p>
            </div>
            <div class="flex gap-3 mt-4 md:mt-0">
                <div class="text-right hidden lg:block mr-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Fecha Actual</p>
                    <p class="text-sm font-bold text-indigo-600">{{ now()->isoFormat('D [de] MMMM, YYYY') }}</p>
                </div>
            </div>
        </div>

        {{-- 1. KPIS (Tarjetas Superiores) --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            {{-- Total --}}
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-slate-800">{{ $kpis['total'] }}</span>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase mt-3">Total Actividades</p>
            </div>

            {{-- Completadas --}}
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-emerald-600">{{ $kpis['completadas'] }}</span>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase mt-3">Completadas</p>
            </div>

            {{-- En Proceso --}}
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-blue-600">{{ $kpis['proceso'] }}</span>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase mt-3">En Proceso</p>
            </div>

            {{-- Pendientes --}}
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-slate-50 rounded-lg text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-slate-600">{{ $kpis['pendientes'] }}</span>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase mt-3">Pendientes (Blanco)</p>
            </div>

            {{-- Retardos --}}
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

        {{-- 2. FILTROS Y ACCIONES --}}
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex flex-col lg:flex-row justify-between gap-4 items-center">
                <button @click="showFilters = !showFilters" class="flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-50 px-4 py-2 rounded-lg hover:bg-slate-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filtros Avanzados
                </button>

                <form method="GET" class="flex flex-wrap gap-2 items-center w-full lg:w-auto">
                    {{-- Buscador Global --}}
                    <div class="relative w-full lg:w-64">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar actividad, responsable..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border-transparent focus:bg-white focus:border-indigo-300 focus:ring-0 rounded-xl text-xs font-medium transition-all">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    
                    {{-- Select Responsable (Si es admin/direccion) --}}
                    @if(isset($users) && count($users) > 0)
                        <select name="user_id" onchange="this.form.submit()" class="bg-slate-50 border-transparent text-xs rounded-xl focus:ring-0 cursor-pointer">
                            <option value="">Todos los Responsables</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    @endif

                    <button type="submit" class="hidden">Buscar</button>
                </form>
            </div>

            {{-- Filtros Expandibles --}}
            <div x-show="showFilters" x-transition class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-1 md:grid-cols-4 gap-4">
                <form method="GET" id="advancedFilters">
                    {{-- Mantener search si existe --}}
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 w-full">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Estatus</label>
                            <select name="estatus" onchange="this.form.form.submit()" class="w-full text-xs rounded-lg border-slate-200">
                                <option value="">Todos</option>
                                <option value="En blanco">En blanco</option>
                                <option value="En proceso">En proceso</option>
                                <option value="Completado">Completado</option>
                                <option value="Retardo">Retardo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Prioridad</label>
                            <select name="prioridad" onchange="this.form.form.submit()" class="w-full text-xs rounded-lg border-slate-200">
                                <option value="">Todas</option>
                                <option value="Alta">Alta</option>
                                <option value="Media">Media</option>
                                <option value="Baja">Baja</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Desde</label>
                            <input type="date" name="fecha_inicio" class="w-full text-xs rounded-lg border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Hasta</label>
                            <input type="date" name="fecha_fin" class="w-full text-xs rounded-lg border-slate-200">
                        </div>
                    </div>
                    <div class="mt-2 text-right">
                        <button type="submit" class="text-xs text-indigo-600 font-bold hover:underline">Aplicar Filtros</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 3. FORMULARIO NUEVA ACTIVIDAD --}}
        <div class="bg-white p-5 shadow-lg rounded-2xl border-l-4 border-indigo-500 relative overflow-hidden">
            <div class="absolute right-0 top-0 p-4 opacity-5 pointer-events-none">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
            </div>

            <form action="{{ route('activities.store') }}" method="POST" class="relative z-10 grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                @csrf
                
                {{-- DEFINICIÓN DE ÁREAS (Nuevo Select) --}}
                @php
                    $areas = [
                        'Logistica',
                        'Legal',
                        'Anexo 24',
                        'Auditoria',
                        'TI',
                        'Administración',
                        'Dirección',
                        'Recursos Humanos',
                        'Ventas',
                        'Operaciones',
                        'Contabilidad',
                        'Mantenimiento'
                    ];
                    sort($areas); 
                @endphp

                <div class="col-span-12 md:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Área</label>
                    <select name="area" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer bg-white" required>
                        <option value="">Seleccionar...</option>
                        @foreach($areas as $areaOption)
                            <option value="{{ $areaOption }}">{{ $areaOption }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-12 md:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tipo</label>
                    <input type="text" name="tipo_actividad" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej. Proyecto" required>
                </div>
                <div class="col-span-12 md:col-span-4">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Actividad</label>
                    <input type="text" name="nombre_actividad" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Descripción detallada..." required>
                </div>
                <div class="col-span-12 md:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Compromiso</label>
                    <input type="date" name="fecha_compromiso" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                <div class="col-span-12 md:col-span-1">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Prio</label>
                    <select name="prioridad" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer bg-white">
                        <option value="Baja">Baja</option>
                        <option value="Media" selected>Media</option>
                        <option value="Alta">Alta</option>
                    </select>
                </div>
                <div class="col-span-12 md:col-span-1">
                    <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 px-2 rounded-lg text-xs shadow-md hover:bg-indigo-700 transition hover:shadow-lg transform active:scale-95 flex justify-center items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Agregar
                    </button>
                </div>
            </form>
        </div>

        {{-- 4. TABLA DE ACTIVIDADES --}}
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs text-left">
                    <thead class="bg-slate-800 text-slate-100 font-semibold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-3 py-4 w-12 text-center">Resp.</th>
                            <th class="px-3 py-4 w-48">Supervisor</th>
                            {{-- NUEVA COLUMNA ÁREA --}}
                            <th class="px-3 py-4 w-24 text-center">Área</th>
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

                            {{-- CAMPO ÁREA EN TABLA --}}
                            <td class="px-3 py-3 text-center">
                                <span class="px-2 py-1 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600 text-[9px] font-bold uppercase tracking-wide">
                                    {{ Str::limit($act->area ?? 'N/A', 10) }}
                                </span>
                            </td>

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
                                    
                                    $prioColor = match($act->prioridad) { 
                                        'Alta'=>'bg-red-50 text-red-700 border-red-200', 
                                        'Media'=>'bg-yellow-50 text-yellow-700 border-yellow-200', 
                                        default=>'bg-blue-50 text-blue-700 border-blue-200' 
                                    };
                                @endphp
                                <form action="{{ route('activities.update', $act->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <select name="prioridad" onchange="this.form.submit()" 
                                            class="text-[10px] py-1 pl-2 pr-6 rounded-md border {{ $prioColor }} font-bold shadow-sm focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed appearance-none w-full text-center"
                                            {{ !$puedeEditar ? 'disabled' : '' }}>
                                        <option value="Baja" {{ $act->prioridad == 'Baja' ? 'selected' : '' }}>Baja</option>
                                        <option value="Media" {{ $act->prioridad == 'Media' ? 'selected' : '' }}>Media</option>
                                        <option value="Alta" {{ $act->prioridad == 'Alta' ? 'selected' : '' }}>Alta 🔥</option>
                                    </select>
                                </form>
                            </td>

                            <td class="px-3 py-3 text-slate-800 font-medium leading-snug break-words">
                                {{ $act->nombre_actividad }}
                            </td>
                            
                            <td class="px-3 py-3 text-center text-slate-500">{{ $act->fecha_inicio ? $act->fecha_inicio->format('d/m') : '-' }}</td>
                            <td class="px-3 py-3 text-center text-indigo-700 font-bold">{{ $act->fecha_compromiso ? $act->fecha_compromiso->format('d/m') : '-' }}</td>
                            <td class="px-3 py-3 text-center text-slate-500">{{ $act->fecha_final ? $act->fecha_final->format('d/m') : '-' }}</td>
                            
                            <td class="px-2 py-3 text-center bg-slate-50 font-mono text-slate-600 border-l border-slate-100">{{ $act->metrico }}</td>
                            <td class="px-2 py-3 text-center bg-slate-50 font-mono font-bold border-l border-slate-100 {{ ($act->resultado_dias > $act->metrico) ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ $act->resultado_dias ?? '-' }}
                            </td>
                            <td class="px-2 py-3 text-center bg-slate-50 font-bold text-slate-800 border-l border-slate-100">
                                {{ isset($act->porcentaje) ? number_format($act->porcentaje, 0).'%' : '-' }}
                            </td>

                            <td class="px-3 py-3">
                                @php
                                    $statusStyle = match($act->estatus) {
                                        'Completado' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'Retardo' => 'bg-red-100 text-red-800 border-red-200 animate-pulse',
                                        'Completado con retardo' => 'bg-orange-100 text-orange-800 border-orange-200',
                                        'En proceso' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'En blanco' => 'bg-slate-100 text-slate-500 border-slate-200',
                                        default => 'bg-white text-slate-700 border-slate-300'
                                    };
                                    $statusLabel = match($act->estatus) {
                                        'En blanco' => '⚪ Pendiente',
                                        'En proceso' => '🔵 En proceso',
                                        'Completado' => '🟢 Listo',
                                        'Retardo' => '🔴 Retardo',
                                        'Completado con retardo' => '🟠 Tardío',
                                        default => $act->estatus
                                    };
                                @endphp
                                <div class="text-[10px] py-1 px-2 text-center rounded-md border {{ $statusStyle }} font-semibold shadow-sm">
                                    {{ $statusLabel }}
                                </div>
                            </td>

                            <td class="px-2 py-3 text-center">
                                <button @click="openNotes({{ $act->id }}, '{{ addslashes($act->nombre_actividad) }}', '{{ $act->estatus }}', '{{ $act->evidencia_path ? \Storage::url($act->evidencia_path) : '' }}')" 
                                        class="text-indigo-600 hover:text-white hover:bg-indigo-600 border border-indigo-200 bg-indigo-50 p-1.5 rounded-lg transition-all shadow-sm group-hover:border-indigo-300 flex items-center gap-1 mx-auto" title="Editar y Subir Evidencia">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    @if($act->evidencia_path)
                                        <span class="text-[9px] font-bold">📎</span>
                                    @endif
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
                        <tr><td colspan="15" class="py-12 text-center text-slate-400 italic bg-slate-50/50 rounded-b-xl border-t border-slate-100">No se encontraron actividades.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($activities->hasPages())
                <div class="px-4 py-3 bg-white border-t border-slate-100">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL DE EDICIÓN / EVIDENCIA --}}
<div id="notesModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeNotes()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form id="notesForm" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-bold text-gray-900 mb-2" id="modal-title">Actualizar Actividad</h3>
                    <p class="text-sm text-gray-500 mb-4" id="modal-activity-name">Nombre de la actividad...</p>
                    
                    <div class="space-y-4">
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Comentarios / Notas</label>
                            <textarea name="comentarios" id="modal-comentarios" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Escribe aquí..."></textarea>
                        </div>

                        <div class="bg-indigo-50 p-3 rounded-lg border border-indigo-100">
                            <label class="block text-sm font-medium text-indigo-900 mb-2">Subir Evidencia (PDF/Imagen)</label>
                            <input type="file" name="evidencia" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200">
                            
                            <div id="modal-evidencia-link" class="mt-2 hidden">
                                <a href="#" target="_blank" class="text-xs text-indigo-600 hover:underline flex items-center gap-1 font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Ver evidencia actual
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Guardar Cambios</button>
                    <button type="button" onclick="closeNotes()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL DE HISTORIAL (TIMELINE NUEVO) --}}
<div id="historyModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeHistory()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <div class="bg-indigo-600 px-4 py-3 flex justify-between items-center shadow-md z-10 relative">
                <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Historial de Cambios
                </h3>
                <button type="button" onclick="closeHistory()" class="text-indigo-200 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider" id="history-activity-title">Activity Name</p>
            </div>

            <div class="px-4 py-6 sm:p-6 max-h-[60vh] overflow-y-auto bg-white">
                <ol class="relative border-l-2 border-indigo-100 ml-3 space-y-8" id="history-timeline-container">
                    {{-- JS Inyectado Aquí --}}
                </ol>
                
                <div id="history-empty-state" class="hidden text-center py-8">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">No hay registros de cambios.</p>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                <button type="button" onclick="closeHistory()" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPTS --}}
<script>
    function openNotes(id, name, estatus, evidenciaUrl) {
        document.getElementById('notesForm').action = "/activities/" + id;
        document.getElementById('modal-activity-name').innerText = name;
        document.getElementById('modal-estatus').value = estatus;
        
        // Cargar comentario actual en el textarea
        var currentNote = document.getElementById('notes-data-' + id);
        if(currentNote) {
            document.getElementById('modal-comentarios').value = currentNote.value;
        }
        
        const linkDiv = document.getElementById('modal-evidencia-link');
        const linkTag = linkDiv.querySelector('a');
        if(evidenciaUrl) {
            linkTag.href = evidenciaUrl;
            linkDiv.classList.remove('hidden');
        } else {
            linkDiv.classList.add('hidden');
        }

        document.getElementById('notesModal').classList.remove('hidden');
    }

    function closeNotes() {
        document.getElementById('notesModal').classList.add('hidden');
    }

    // --- LÓGICA DEL HISTORIAL (CORREGIDA) ---
    function openHistory(id, title) {
        const textarea = document.getElementById('history-data-' + id);
        if (!textarea) return;

        let history = [];
        try {
            history = JSON.parse(textarea.value);
        } catch (e) {
            console.error("Error parsing history", e);
            return;
        }

        document.getElementById('history-activity-title').textContent = title;
        const container = document.getElementById('history-timeline-container');
        const emptyState = document.getElementById('history-empty-state');
        container.innerHTML = '';

        if (!history || history.length === 0) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
            
            // Ordenar por fecha (más reciente arriba)
            history.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

            history.forEach(log => {
                // 1. CORRECCIÓN DE FECHA (Invalid Date Fix)
                // Si la fecha viene como "2025-01-01 12:00:00", le ponemos la "T" para que sea ISO valida
                let dateStrRaw = log.created_at || new Date().toISOString();
                if (dateStrRaw.indexOf('T') === -1) {
                    dateStrRaw = dateStrRaw.replace(' ', 'T'); 
                }
                
                const date = new Date(dateStrRaw);
                // Validamos si la fecha es válida, si no, usamos la actual o placeholder
                const isValidDate = !isNaN(date.getTime());
                
                const dateDisplay = isValidDate 
                    ? date.toLocaleDateString('es-MX', { day: '2-digit', month: 'short' }) 
                    : '--/--';
                const timeDisplay = isValidDate 
                    ? date.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }) 
                    : '--:--';
                
                const userName = log.user ? log.user.name : 'Sistema';
                const userInitials = userName.substring(0, 2).toUpperCase();
                
                // 2. LÓGICA DE CONTENIDO (Mostrar qué cambió)
                let contentHtml = '';
                
                if (log.action === 'created') {
                    contentHtml = `<span class="text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded text-xs">✨ Nueva Actividad Creada</span>`;
                } 
                else if (log.action === 'comment' || log.comentario) {
                    // CASO COMENTARIO (Nuevo)
                    contentHtml = `
                        <div class="text-sm text-gray-600">Agregó un comentario:</div>
                        <div class="mt-2 text-xs bg-yellow-50 p-2 rounded-lg border border-yellow-100 text-gray-700 italic">
                            "${log.comentario || 'Sin texto'}"
                        </div>
                    `;
                }
                else if (log.field) {
                    // CASO CAMBIO DE CAMPO
                    const fieldMap = {
                        'estatus': 'Estatus',
                        'prioridad': 'Prioridad',
                        'fecha_compromiso': 'Fecha Compromiso',
                        'nombre_actividad': 'Descripción',
                        'evidencia_path': 'Evidencia',
                        'porcentaje': 'Porcentaje'
                    };
                    const fieldName = fieldMap[log.field] || log.field;
                    
                    let oldVal = log.old_value || 'Vacío';
                    let newVal = log.new_value || 'Vacío';

                    // Si es evidencia, poner texto amigable
                    if(log.field === 'evidencia_path') {
                        oldVal = 'Sin archivo';
                        newVal = 'Archivo adjunto 📎';
                    }

                    contentHtml = `
                        <div class="text-sm text-gray-600">
                            Modificó <span class="font-bold text-gray-800">${fieldName}</span>
                        </div>
                        <div class="mt-2 flex items-center gap-2 text-xs bg-gray-50 p-2 rounded-lg border border-gray-100 flex-wrap">
                            <div class="text-red-500 line-through opacity-75">${oldVal}</div>
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            <div class="text-emerald-600 font-bold">${newVal}</div>
                        </div>
                    `;
                } else {
                    // CASO GENÉRICO (Fallback)
                    contentHtml = `<span class="text-gray-500 text-xs">Actualización registrada.</span>`;
                }

                const item = `
                    <li class="mb-6 ml-6">
                        <span class="absolute flex items-center justify-center w-8 h-8 bg-white rounded-full -left-4 ring-4 ring-gray-50 shadow-sm border border-gray-100">
                            <span class="text-[10px] font-bold text-indigo-600" title="${userName}">${userInitials}</span>
                        </span>
                        <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200">
                            <div class="flex justify-between items-start mb-2 pb-2 border-b border-gray-50">
                                <span class="text-xs font-bold text-gray-900">${userName}</span>
                                <time class="text-[10px] font-medium text-gray-400">${dateDisplay} · ${timeDisplay}</time>
                            </div>
                            <div class="font-normal">
                                ${contentHtml}
                            </div>
                        </div>
                    </li>
                `;
                container.insertAdjacentHTML('beforeend', item);
            });
        }
        document.getElementById('historyModal').classList.remove('hidden');
    }

    function closeHistory() {
        document.getElementById('historyModal').classList.add('hidden');
    }
</script>
@endsection