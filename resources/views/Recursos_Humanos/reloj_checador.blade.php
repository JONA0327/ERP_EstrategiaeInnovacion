@extends('layouts.erp')

@section('content')
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
                    {{ __('Control de Asistencia') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">Gestión de entradas, salidas e incidencias del personal.</p>
            </div>
            <div class="flex gap-3">
                <button onclick="abrirModalIncidencia()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Registrar Incidencia
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50 min-h-screen" x-data="{ openImport: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- SECCIÓN DE ESTADÍSTICAS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                
                {{-- 1. HORAS TOTALES --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500 relative overflow-hidden group hover:shadow-md transition">
                    <div class="relative z-10">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Horas Totales</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $horasTotales ?? 0 }} <span class="text-lg text-gray-400 font-normal">hrs</span></p>
                        <p class="text-xs text-blue-600 font-medium mt-1">Periodo actual</p>
                    </div>
                    <div class="absolute right-0 top-0 h-full w-24 bg-gradient-to-l from-blue-50 to-transparent opacity-50 group-hover:opacity-100 transition"></div>
                    <div class="absolute -right-2 -bottom-4 text-blue-100 opacity-50">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                {{-- 2. EFICIENCIA --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-indigo-500 relative overflow-hidden group hover:shadow-md transition">
                    <div class="relative z-10">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Eficiencia</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $porcentajeAsistencia }}<span class="text-lg text-gray-400 font-normal">%</span></p>
                    </div>
                    <div class="absolute right-0 top-0 h-full w-24 bg-gradient-to-l from-indigo-50 to-transparent opacity-50 group-hover:opacity-100 transition"></div>
                    <div class="absolute -right-2 -bottom-4 text-indigo-100 opacity-50">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                {{-- 3. RETARDOS --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500 relative overflow-hidden group hover:shadow-md transition">
                    <div class="relative z-10">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Retardos</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $retardos }}</p>
                        <p class="text-xs text-amber-600 font-medium mt-1">Sin justificar</p>
                    </div>
                    <div class="absolute -right-4 -bottom-4 text-amber-100 opacity-50">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                {{-- 4. AUSENCIAS --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500 relative overflow-hidden group hover:shadow-md transition">
                    <div class="relative z-10">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Ausencias</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $faltas }}</p>
                        <p class="text-xs text-red-600 font-medium mt-1">Requieren atención</p>
                    </div>
                    <div class="absolute -right-4 -bottom-4 text-red-100 opacity-50">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                </div>

                {{-- 5. TOP RETARDOS --}}
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Top Retardos</p>
                        <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500">Este mes</span>
                    </div>
                    <div class="space-y-3">
                        @forelse($topRetardos as $top)
                            <div class="flex items-center justify-between group">
                                <div class="flex items-center gap-2 overflow-hidden">
                                    <div class="w-6 h-6 rounded-full bg-gray-200 text-[10px] flex items-center justify-center font-bold text-gray-600 flex-shrink-0">
                                        {{ substr($top->nombre, 0, 1) }}
                                    </div>
                                    <span class="text-xs font-medium text-gray-700 truncate group-hover:text-indigo-600 transition">{{ Str::limit($top->nombre, 15) }}</span>
                                </div>
                                <span class="text-xs font-bold text-red-500 bg-red-50 px-1.5 py-0.5 rounded">{{ $top->total }}</span>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <p class="text-xs text-gray-400">¡Sin retardos registrados! 🎉</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- IMPORTADOR Y FILTROS --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-white border-b border-gray-100 flex flex-col lg:flex-row justify-between items-center gap-4">
                    
                    <button @click="openImport = !openImport" :class="{'bg-blue-50 text-blue-700 border-blue-100': openImport, 'bg-gray-50 text-gray-700 border-gray-200': !openImport}" class="flex items-center px-4 py-2 rounded-lg border text-sm font-semibold transition-all duration-200 w-full lg:w-auto justify-center lg:justify-start group">
                        <svg class="w-5 h-5 mr-2 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <span x-text="openImport ? 'Cerrar Importador' : 'Importar Archivo Excel'"></span>
                    </button>

                    <form method="GET" action="{{ route('rh.reloj.index') }}" class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto items-center">
                        <div class="relative w-full sm:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar empleado..." class="pl-9 w-full rounded-lg border-gray-300 bg-gray-50 focus:bg-white text-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                        </div>
                        
                        <div class="flex gap-2 w-full sm:w-auto items-center bg-gray-50 p-1 rounded-lg border border-gray-200">
                            <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio', now()->startOfMonth()->toDateString()) }}" class="border-none bg-transparent text-sm text-gray-600 focus:ring-0 w-32 p-1">
                            <span class="text-gray-400 text-xs">➜</span>
                            <input type="date" name="fecha_fin" value="{{ request('fecha_fin', now()->endOfMonth()->toDateString()) }}" class="border-none bg-transparent text-sm text-gray-600 focus:ring-0 w-32 p-1">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded p-1.5 shadow-sm transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- AREA DE CARGA (Importador) --}}
                <div x-show="openImport" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="bg-blue-50/50 border-b border-blue-100 p-6" style="display: none;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="text-sm font-bold text-blue-900 mb-2">Carga de Datos</h4>
                            <p class="text-xs text-blue-700 mb-4">Suba el archivo .xlsx exportado del reloj checador ZKTeco.</p>
                            
                            <form id="importForm" class="space-y-4">
                                @csrf
                                <div class="flex gap-3">
                                    <input type="file" name="archivo" accept=".xls,.xlsx" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition bg-white border border-blue-200 rounded-lg cursor-pointer">
                                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow-sm transition flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        Procesar
                                    </button>
                                </div>
                                <div id="progressContainer" class="hidden">
                                    <div class="flex justify-between text-xs font-semibold text-blue-800 mb-1">
                                        <span id="progressMessage">Cargando...</span>
                                        <span id="progressPercent">0%</span>
                                    </div>
                                    <div class="w-full bg-blue-200 rounded-full h-2 overflow-hidden">
                                        <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="border-l border-blue-200 pl-8 flex flex-col justify-center">
                            <h4 class="text-sm font-bold text-red-900 mb-2">Zona de Peligro</h4>
                            <p class="text-xs text-red-700 mb-4">Eliminar todos los registros actuales para reiniciar la base de datos.</p>
                            <form action="{{ route('rh.reloj.clear') }}" method="POST" onsubmit="return confirm('ATENCIÓN: Esto borrará TODO el historial de asistencia. ¿Está seguro?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-bold flex items-center gap-1 hover:underline decoration-red-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Vaciar Base de Datos
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABLA RESUMEN POR EMPLEADO (VISTA RECUPERADA & MEJORADA) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-10"></th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Empleado</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Asistencias</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Retardos</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Faltas</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Hrs Totales</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            @foreach($empleados as $empleado)
                                @php
                                    // 1. Indexar asistencias por fecha para acceso rápido y seguro
                                    $asistenciasMap = $empleado->asistencias->keyBy(function($item) {
                                        return $item->fecha instanceof \Carbon\Carbon 
                                            ? $item->fecha->format('Y-m-d') 
                                            : substr($item->fecha, 0, 10);
                                    });

                                    // 2. Cálculos Estadísticos
                                    $totalAsistencias = $empleado->asistencias->where('tipo_registro', 'asistencia')->count();
                                    $totalRetardos = $empleado->asistencias->where('es_retardo', true)->where('es_justificado', false)->count();
                                    $totalFaltas = $empleado->asistencias->where('tipo_registro', 'falta')->where('es_justificado', false)->count();
                                    
                                    // 3. Calcular Horas
                                    $minutosEmpleado = 0;
                                    foreach($empleado->asistencias as $asis) {
                                        if($asis->entrada && $asis->salida) {
                                            $e = \Carbon\Carbon::parse($asis->entrada);
                                            $s = \Carbon\Carbon::parse($asis->salida);
                                            if($s->gt($e)) $minutosEmpleado += $e->diffInMinutes($s);
                                        }
                                    }
                                    $horasEmp = floor($minutosEmpleado / 60);
                                    $minsEmp = $minutosEmpleado % 60;
                                @endphp

                                <tr x-data="{ expanded: false }" class="hover:bg-gray-50/50 transition-colors border-b border-gray-50 group">
                                    {{-- ROW PRINCIPAL --}}
                                    <td colspan="7" class="p-0">
                                        <div class="flex items-center w-full">
                                            {{-- Toggle Button --}}
                                            <div class="px-6 py-4 whitespace-nowrap cursor-pointer text-gray-400 group-hover:text-indigo-600 transition" @click="expanded = !expanded">
                                                <svg class="w-5 h-5 transform transition-transform duration-200" :class="{'rotate-90': expanded}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </div>
                                            {{-- Empleado Datos --}}
                                            <div class="px-6 py-4 whitespace-nowrap flex-1">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-indigo-100 to-white flex items-center justify-center text-indigo-700 text-sm font-bold border border-indigo-100 shadow-sm">
                                                        {{ substr($empleado->nombre, 0, 1) }}
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-bold text-gray-900">{{ $empleado->nombre }} {{ $empleado->apellido_paterno }}</div>
                                                        <div class="text-xs text-gray-500 uppercase tracking-wide">{{ $empleado->posicion ?? 'N/A' }} • ID: {{ $empleado->id_empleado ?? 'S/N' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Stats --}}
                                            <div class="px-6 py-4 whitespace-nowrap text-center w-32">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $totalAsistencias }}
                                                </span>
                                            </div>
                                            <div class="px-6 py-4 whitespace-nowrap text-center w-32">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $totalRetardos > 0 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $totalRetardos }}
                                                </span>
                                            </div>
                                            <div class="px-6 py-4 whitespace-nowrap text-center w-32">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $totalFaltas > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ $totalFaltas }}
                                                </span>
                                            </div>
                                            <div class="px-6 py-4 whitespace-nowrap text-center w-32">
                                                <span class="text-sm font-mono text-gray-600 font-bold">{{ sprintf('%d:%02d', $horasEmp, $minsEmp) }} hrs</span>
                                            </div>
                                            {{-- Toggle Action --}}
                                            <div class="px-6 py-4 whitespace-nowrap text-right w-32">
                                                <button @click="expanded = !expanded" class="text-xs text-indigo-600 hover:text-indigo-900 font-medium hover:underline focus:outline-none">
                                                    <span x-text="expanded ? 'Ocultar Detalle' : 'Ver Detalle'"></span>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- DETALLE EXPANDIBLE (HISTORIAL) --}}
                                        <div x-show="expanded" x-collapse class="bg-gray-50/50 border-t border-gray-100 shadow-inner">
                                            <div class="px-4 py-4 sm:px-6">
                                                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                                                    <table class="min-w-full divide-y divide-gray-200">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Fecha</th>
                                                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-500 uppercase">Entrada</th>
                                                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-500 uppercase">Salida</th>
                                                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-500 uppercase">Horas</th>
                                                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-500 uppercase">Estado</th>
                                                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Acción</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100">
                                                            @foreach($fechas as $fechaObj)
                                                                @php
                                                                    // Recuperamos usando el Y-m-d del objeto fecha
                                                                    $dia = $asistenciasMap->get($fechaObj->format('Y-m-d'));
                                                                @endphp
                                                                <tr class="hover:bg-indigo-50/30 transition-colors">
                                                                    <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-700">
                                                                        <span class="font-bold">{{ $fechaObj->format('d/m') }}</span> 
                                                                        <span class="text-gray-400 ml-1">{{ $fechaObj->isoFormat('ddd') }}</span>
                                                                    </td>
                                                                    @if($dia)
                                                                        {{-- Entrada --}}
                                                                        <td class="px-4 py-2 whitespace-nowrap text-center text-xs font-mono {{ $dia->es_retardo && !$dia->es_justificado ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                                                                            {{ $dia->entrada ? substr($dia->entrada, 0, 5) : '--:--' }}
                                                                            @if($dia->es_retardo && !$dia->es_justificado)<span class="text-[9px] text-red-400 block leading-none mt-0.5">Tarde</span>@endif
                                                                        </td>
                                                                        {{-- Salida --}}
                                                                        <td class="px-4 py-2 whitespace-nowrap text-center text-xs font-mono text-gray-600">
                                                                            {{ $dia->salida ? substr($dia->salida, 0, 5) : '--:--' }}
                                                                        </td>
                                                                        {{-- Horas Diarias --}}
                                                                        <td class="px-4 py-2 whitespace-nowrap text-center text-xs font-mono text-gray-500">
                                                                            @if($dia->entrada && $dia->salida)
                                                                                @php
                                                                                    $e = \Carbon\Carbon::parse($dia->entrada);
                                                                                    $s = \Carbon\Carbon::parse($dia->salida);
                                                                                    $diff = ($s->gt($e)) ? $e->diff($s)->format('%H:%I') : '--';
                                                                                @endphp
                                                                                {{ $diff }}
                                                                            @else
                                                                                --
                                                                            @endif
                                                                        </td>
                                                                        {{-- Estado Badge --}}
                                                                        <td class="px-4 py-2 whitespace-nowrap text-center">
                                                                             @php
                                                                                $statusColor = match($dia->tipo_registro) {
                                                                                    'falta' => ($dia->es_justificado ? 'text-orange-700 bg-orange-50 border-orange-200' : 'text-red-700 bg-red-50 border-red-200'),
                                                                                    'asistencia' => ($dia->es_retardo && !$dia->es_justificado) ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-green-700 bg-green-50 border-green-200',
                                                                                    'vacaciones' => 'text-blue-700 bg-blue-50 border-blue-200',
                                                                                    'incapacidad' => 'text-purple-700 bg-purple-50 border-purple-200',
                                                                                    default => 'text-gray-700 bg-gray-50 border-gray-200'
                                                                                };
                                                                                $statusLabel = match($dia->tipo_registro) {
                                                                                    'asistencia' => ($dia->es_retardo && !$dia->es_justificado) ? 'Retardo' : 'OK',
                                                                                    'falta' => ($dia->es_justificado) ? 'Justificada' : 'Falta',
                                                                                    default => ucfirst($dia->tipo_registro)
                                                                                };
                                                                             @endphp
                                                                             <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusColor }}">
                                                                                {{ $statusLabel }}
                                                                             </span>
                                                                        </td>
                                                                        {{-- Botón Editar --}}
                                                                        <td class="px-4 py-2 whitespace-nowrap text-right text-xs">
                                                                            <button onclick="abrirModalEdicion({{ $dia }})" class="text-indigo-600 hover:text-indigo-900 font-medium hover:underline p-1">
                                                                                Editar
                                                                            </button>
                                                                        </td>
                                                                    @else
                                                                        {{-- Sin Registro --}}
                                                                        <td colspan="4" class="px-4 py-2 text-center text-xs text-gray-300 italic">-- Sin registro --</td>
                                                                        <td class="px-4 py-2 text-right text-xs">
                                                                            <button onclick="abrirModalIncidencia({{ $empleado->id }}, '{{ $fechaObj->toDateString() }}')" class="text-gray-400 hover:text-gray-600 font-medium hover:underline text-[10px] whitespace-nowrap">
                                                                                + Justificar
                                                                            </button>
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    {{ $empleados->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL DE EDICIÓN (Registro Existente) --}}
    <div id="modalEdicion" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="cerrarModalEdicion()"></div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form id="formEdicion" method="POST">
                    @csrf @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Registro Individual</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Incidencia</label>
                                <select name="tipo_registro" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="asistencia">Asistencia Normal</option>
                                    <option value="falta">Falta</option>
                                    <option value="vacaciones">Vacaciones</option>
                                    <option value="incapacidad">Incapacidad</option>
                                    <option value="permiso">Permiso con Goce</option>
                                    <option value="descanso">Día de Descanso</option>
                                </select>
                            </div>
                            <div class="flex items-center bg-gray-50 p-2 rounded border border-gray-200">
                                <input id="es_justificado" name="es_justificado" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <label for="es_justificado" class="ml-2 block text-sm text-gray-900 font-medium">Justificar Retardo / Falta</label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Comentarios</label>
                                <textarea name="comentarios" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Guardar Cambios</button>
                        <button type="button" onclick="cerrarModalEdicion()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL DE CREACIÓN (Nueva Incidencia / Justificación Rápida) --}}
    <div id="modalIncidencia" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="cerrarModalIncidencia()"></div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="{{ route('rh.reloj.store') }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6" x-data="{ tipo: 'vacaciones' }">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Registrar / Justificar Incidencia</h3>
                            <button type="button" onclick="cerrarModalIncidencia()" class="text-gray-400 hover:text-gray-500">
                                <span class="sr-only">Cerrar</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Empleado</label>
                                <select name="empleado_id" id="modal_empleado_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    
                                    {{-- OPCIÓN NUEVA PARA MASIVOS --}}
                                    <option value="all" class="font-bold text-indigo-600 bg-indigo-50">
                                        👥 APLICAR A TODOS LOS EMPLEADOS (Masivo)
                                    </option>
                                    <option disabled>──────────────────────────</option>

                                    @foreach(\App\Models\Empleado::orderBy('nombre')->get() as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->nombre }} ({{ $emp->id_empleado }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Registro</label>
                                <select name="tipo_registro" id="modal_tipo_registro" x-model="tipo" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="vacaciones">🌴 Vacaciones</option>
                                    <option value="incapacidad">🏥 Incapacidad</option>
                                    <option value="permiso">📄 Permiso Especial</option>
                                    <option value="falta">❌ Falta / Justificación</option>
                                    <option value="descanso">🏠 Día de Descanso</option>
                                </select>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Periodo a Aplicar</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Desde</label>
                                        <input type="date" name="fecha_inicio" id="modal_fecha_inicio" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                    <div x-show="['vacaciones', 'incapacidad', 'permiso'].includes(tipo)" x-transition>
                                        <label class="block text-sm font-medium text-gray-700">Hasta (Inclusive)</label>
                                        <input type="date" name="fecha_fin" id="modal_fecha_fin" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                                <p x-show="['vacaciones', 'incapacidad', 'permiso'].includes(tipo)" class="text-xs text-gray-500 mt-2">
                                    * Se crearán registros para todos los días del rango seleccionado.
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Motivo / Comentarios</label>
                                <textarea name="comentarios" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Ej: Autorizado por Gerencia"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar Registros
                        </button>
                        <button type="button" onclick="cerrarModalIncidencia()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Importador JS (Sin cambios)
        document.getElementById('importForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const uniqueKey = 'import_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            formData.append('progress_key', uniqueKey);

            const btn = this.querySelector('button');
            const originalText = btn.innerHTML;
            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');
            const progressPercent = document.getElementById('progressPercent');
            const progressMessage = document.getElementById('progressMessage');

            btn.disabled = true;
            btn.innerHTML = 'Cargando...';
            progressContainer.classList.remove('hidden');
            progressBar.style.width = '0%';
            progressPercent.innerText = '0%';
            progressMessage.innerText = 'Iniciando...';

            let pollInterval = setInterval(() => {
                fetch(`/recursos-humanos/reloj/progress/${uniqueKey}`)
                    .then(r => r.json())
                    .then(status => {
                        let p = status.percent || 0;
                        progressBar.style.width = p + '%';
                        progressPercent.innerText = p + '%';
                        progressMessage.innerText = status.mensaje || 'Procesando...';
                        if (status.finalizado || status.status === 'error') {
                            clearInterval(pollInterval);
                            if(status.status === 'error') {
                                alert('Error: ' + status.mensaje);
                                btn.disabled = false;
                                btn.innerHTML = originalText;
                            }
                        }
                    }).catch(err => console.log(err));
            }, 1000);

            fetch("{{ route('rh.reloj.start') }}", {
                method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }
            })
            .then(r => r.json())
            .then(data => {
                clearInterval(pollInterval);
                if (data.error) throw new Error(data.error);
                progressBar.style.width = '100%';
                progressPercent.innerText = '100%';
                progressMessage.innerText = '¡Completado!';
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(error => {
                clearInterval(pollInterval);
                console.error(error);
                alert('Error al procesar: ' + error.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });

        // Modales
        function abrirModalEdicion(asistencia) {
            const form = document.getElementById('formEdicion');
            form.action = `/recursos-humanos/reloj/update/${asistencia.id}`;
            form.querySelector('[name="tipo_registro"]').value = asistencia.tipo_registro;
            form.querySelector('[name="comentarios"]').value = asistencia.comentarios || '';
            form.querySelector('[name="es_justificado"]').checked = asistencia.es_justificado;
            document.getElementById('modalEdicion').classList.remove('hidden');
        }
        function cerrarModalEdicion() {
            document.getElementById('modalEdicion').classList.add('hidden');
        }

        // Nueva función para abrir modal desde "Sin Registro" pre-llenado
        function abrirModalIncidencia(empleadoId = null, fecha = null) {
            if(empleadoId && fecha) {
                // Pre-llenar datos para justificación rápida
                document.getElementById('modal_empleado_id').value = empleadoId;
                document.getElementById('modal_fecha_inicio').value = fecha;
                document.getElementById('modal_fecha_fin').value = fecha; // Por defecto 1 día
                document.getElementById('modal_tipo_registro').value = 'falta'; // Sugerir Falta/Justificación
                
                // Disparar evento para actualizar x-data si fuera necesario (opcional)
                document.getElementById('modal_tipo_registro').dispatchEvent(new Event('change'));
            }
            document.getElementById('modalIncidencia').classList.remove('hidden');
        }
        function cerrarModalIncidencia() {
            document.getElementById('modalIncidencia').classList.add('hidden');
        }
    </script>
@endsection