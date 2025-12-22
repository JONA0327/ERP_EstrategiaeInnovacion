<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Reporte de Actividades') }}
                </h2>
                
                @if(request('ver_equipo_de'))
                    @php 
                        $liderViendo = \App\Models\Empleado::find(request('ver_equipo_de'));
                    @endphp
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 border border-indigo-200 shadow-sm">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Equipo de: {{ $liderViendo ? $liderViendo->nombre_completo : 'Desconocido' }}
                    </span>
                    <a href="{{ route('activities.index') }}" class="text-xs text-gray-400 hover:text-red-500 font-bold px-1" title="Quitar filtro">✕</a>
                @endif
            </div>
            
            @if(isset($esDireccion) && $esDireccion)
                <button @click="directorOpen = true" class="bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold py-2 px-4 rounded shadow-lg flex items-center gap-2 transition-transform transform hover:scale-105">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Estructura
                </button>
            @endif
        </div>
    </x-slot>

    <style>
        .animate-pulse-fast { animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
    </style>

    <div class="py-6" x-data="{ 
        historyOpen: false, 
        notesOpen: false,
        directorOpen: false,
        currentActivity: null, 
        currentActivityId: null,
        currentNotes: '',
        historyLogs: [],

        async openHistory(activityId, activityName) {
            this.currentActivity = activityName;
            this.historyOpen = true;
            this.historyLogs = [];
            try {
                const rawData = document.getElementById('history-data-' + activityId).value;
                this.historyLogs = JSON.parse(rawData);
            } catch (e) { console.error('Error logs', e); }
        },

        openNotes(activityId, activityName) {
            this.currentActivityId = activityId;
            this.currentActivity = activityName;
            this.currentNotes = document.getElementById('notes-data-' + activityId).value;
            this.notesOpen = true;
        },

        appendSignature() {
            const now = new Date();
            const fecha = now.toLocaleString('es-MX', { day: '2-digit', month: '2-digit', hour: '2-digit', minute:'2-digit' });
            const userName = '{{ Auth::user()->name }}';
            const prefix = this.currentNotes ? '\n\n' : ''; 
            this.currentNotes += `${prefix}[${fecha} - ${userName}]: `;
            this.$nextTick(() => { document.getElementById('big-note-area').focus(); });
        }
    }">
        <div class="max-w-[98%] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm">
                <form method="GET" action="{{ route('activities.index') }}" class="flex flex-wrap gap-4 items-end">
                    
                    @if(request('ver_equipo_de'))
                        <input type="hidden" name="ver_equipo_de" value="{{ request('ver_equipo_de') }}">
                    @endif

                    @if(isset($filterUsers) && $filterUsers->count() > 1)
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Responsable</label>
                        <select name="user_filter" class="w-full text-xs border-gray-300 rounded-md focus:ring-indigo-500 cursor-pointer shadow-sm">
                            <option value="">-- Todos --</option>
                            @foreach($filterUsers as $u)
                                <option value="{{ $u->id }}" {{ request('user_filter') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="w-28">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Prioridad</label>
                        <select name="prioridad" class="w-full text-xs border-gray-300 rounded-md focus:ring-indigo-500 cursor-pointer shadow-sm">
                            <option value="">Todas</option>
                            <option value="Alta" {{ request('prioridad') == 'Alta' ? 'selected' : '' }}>Alta 🔥</option>
                            <option value="Media" {{ request('prioridad') == 'Media' ? 'selected' : '' }}>Media</option>
                            <option value="Baja" {{ request('prioridad') == 'Baja' ? 'selected' : '' }}>Baja</option>
                        </select>
                    </div>

                    <div class="w-36">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Estatus</label>
                        <select name="estatus" class="w-full text-xs border-gray-300 rounded-md focus:ring-indigo-500 cursor-pointer shadow-sm">
                            <option value="">Todos</option>
                            <option value="En blanco" {{ request('estatus') == 'En blanco' ? 'selected' : '' }}>⚪ En blanco</option>
                            <option value="En proceso" {{ request('estatus') == 'En proceso' ? 'selected' : '' }}>🔵 En proceso</option>
                            <option value="Completado" {{ request('estatus') == 'Completado' ? 'selected' : '' }}>🟢 Completado</option>
                            <option value="Retardo" {{ request('estatus') == 'Retardo' ? 'selected' : '' }}>🔴 Retardo</option>
                            <option value="Completado con retardo" {{ request('estatus') == 'Completado con retardo' ? 'selected' : '' }}>🟠 Con Retardo</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Desde</label>
                            <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="text-xs border-gray-300 rounded-md w-32 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Hasta</label>
                            <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="text-xs border-gray-300 rounded-md w-32 shadow-sm">
                        </div>
                    </div>

                    <div class="flex gap-2 pb-0.5">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-xs font-bold transition flex items-center gap-1 shadow-md">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Filtrar
                        </button>
                        
                        @if(request()->hasAny(['prioridad', 'estatus', 'fecha_inicio', 'fecha_fin', 'user_filter', 'ver_equipo_de']))
                            <a href="{{ route('activities.index') }}" class="bg-white text-gray-600 border border-gray-300 px-3 py-2 rounded-md text-xs font-bold hover:bg-gray-50 transition shadow-sm">
                                Limpiar
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg border-l-4 border-indigo-500">
                <form action="{{ route('activities.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    @csrf
                    <div class="col-span-12 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Área</label>
                        <input type="text" name="area" class="w-full text-xs rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej. Sistemas" required>
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipo</label>
                        <input type="text" name="tipo_actividad" class="w-full text-xs rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej. Proyecto" required>
                    </div>
                    <div class="col-span-12 md:col-span-4">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Actividad</label>
                        <input type="text" name="nombre_actividad" class="w-full text-xs rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Descripción..." required>
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Compromiso</label>
                        <input type="date" name="fecha_compromiso" class="w-full text-xs rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <div class="col-span-12 md:col-span-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Prio</label>
                        <select name="prioridad" class="w-full text-xs rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="Baja">Baja</option>
                            <option value="Media" selected>Media</option>
                            <option value="Alta">Alta</option>
                        </select>
                    </div>
                    <div class="col-span-12 md:col-span-1">
                        <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 px-2 rounded-md text-xs shadow hover:bg-indigo-700 transition">
                            + Add
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-[10px] md:text-xs text-center divide-y divide-gray-200">
                    <thead class="bg-gray-800 text-white font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-1 py-3">Resp.</th>
                            <th class="px-1 py-3">Área</th>
                            <th class="px-1 py-3">Tipo</th>
                            <th class="px-1 py-3 w-20">Prio</th>
                            <th class="px-2 py-3 text-left w-64">Actividad</th>
                            <th class="px-1 py-3">Inicio</th>
                            <th class="px-1 py-3">Compromiso</th>
                            <th class="px-1 py-3">Fin</th>
                            <th class="px-1 py-3 bg-gray-700 text-gray-100">Métrico</th>
                            <th class="px-1 py-3 bg-gray-700 text-gray-100">Días</th>
                            <th class="px-1 py-3 bg-gray-700 text-gray-100">%</th>
                            <th class="px-2 py-3 w-28">Estatus</th>
                            <th class="px-2 py-3">Bitácora</th>
                            <th class="px-1 py-3">Logs</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($activities as $act)
                        <tr class="hover:bg-indigo-50 transition-colors {{ str_contains($act->estatus, 'Completado') ? 'bg-gray-50 opacity-90' : '' }}">
                            
                            <td class="px-1 py-2" title="{{ $act->user->name ?? 'N/A' }}">
                                @if($act->user_id === Auth::id())
                                    <span class="inline-flex items-center justify-center px-2 py-1 rounded bg-indigo-100 text-indigo-800 font-bold text-[9px] border border-indigo-200">YO</span>
                                @else
                                    <span class="inline-flex items-center justify-center px-2 py-1 rounded bg-orange-100 text-orange-800 font-bold text-[9px] border border-orange-200" title="{{ $act->user->name }}">
                                        {{ strtoupper(substr($act->user->name ?? 'U', 0, 2)) }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-1 py-2 font-semibold text-gray-600">{{ $act->area }}</td>
                            <td class="px-1 py-2 text-gray-500">
                                <span class="px-1 py-0.5 border rounded bg-gray-50 text-[9px]">{{ $act->tipo_actividad }}</span>
                            </td>
                            
                            <td class="px-1 py-2">
                                @php
                                    $puedeEditar = false;
                                    $miEmpleado = Auth::user()->empleado;
                                    $suEmpleado = $act->user->empleado ?? null;
                                    
                                    // 1. Dirección edita todo
                                    if (isset($esDireccion) && $esDireccion) { $puedeEditar = true; }
                                    // 2. Supervisor directo
                                    elseif ($miEmpleado && $suEmpleado && $miEmpleado->id === $suEmpleado->supervisor_id) { $puedeEditar = true; }
                                    // 3. Dueño
                                    elseif ($act->user_id === Auth::id()) { $puedeEditar = true; }

                                    $pc = match($act->prioridad) { 
                                        'Alta'=>'text-red-700 bg-red-50 border-red-200 font-bold', 
                                        'Media'=>'text-yellow-700 bg-yellow-50 border-yellow-200', 
                                        default=>'text-blue-700 bg-blue-50 border-blue-200'
                                    };
                                @endphp
                                <form action="{{ route('activities.update', $act->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <select name="prioridad" onchange="this.form.submit()" 
                                            class="text-[9px] py-0.5 pl-1 pr-4 rounded border {{ $pc }} w-full focus:ring-0 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                                            {{ !$puedeEditar ? 'disabled' : '' }}>
                                        <option value="Baja" {{ $act->prioridad == 'Baja' ? 'selected' : '' }}>Baja</option>
                                        <option value="Media" {{ $act->prioridad == 'Media' ? 'selected' : '' }}>Media</option>
                                        <option value="Alta" {{ $act->prioridad == 'Alta' ? 'selected' : '' }}>Alta 🔥</option>
                                    </select>
                                </form>
                            </td>

                            <td class="px-2 py-2 text-left font-medium text-gray-900 break-words leading-snug">
                                {{ $act->nombre_actividad }}
                            </td>
                            
                            <td class="px-1 py-2 text-gray-500">{{ $act->fecha_inicio ? $act->fecha_inicio->format('d/m') : '-' }}</td>
                            <td class="px-1 py-2 font-bold text-indigo-700">{{ $act->fecha_compromiso ? $act->fecha_compromiso->format('d/m') : '-' }}</td>
                            <td class="px-1 py-2 text-gray-500">{{ $act->fecha_final ? $act->fecha_final->format('d/m') : '-' }}</td>
                            
                            <td class="px-1 py-2 bg-gray-50 font-mono">{{ $act->metrico }}</td>
                            <td class="px-1 py-2 bg-gray-50 font-mono font-bold {{ ($act->resultado_dias > $act->metrico) ? 'text-red-600' : 'text-green-600' }}">
                                {{ $act->resultado_dias ?? '-' }}
                            </td>
                            <td class="px-1 py-2 bg-gray-50 font-bold text-gray-700">
                                {{ isset($act->porcentaje) ? number_format($act->porcentaje, 0).'%' : '-' }}
                            </td>

                            <td class="px-1 py-2">
                                <form action="{{ route('activities.update', $act->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    @php
                                        $statusClass = match($act->estatus) {
                                            'Completado' => 'text-green-700 bg-green-50 font-bold border-green-200',
                                            'Retardo' => 'text-red-700 bg-red-100 font-bold border-red-200 animate-pulse-fast',
                                            'Completado con retardo' => 'text-orange-700 bg-orange-50 font-bold border-orange-200',
                                            'En blanco' => 'text-gray-400 bg-gray-50 italic border-gray-200',
                                            default => 'text-indigo-600 bg-white border-gray-200'
                                        };
                                    @endphp
                                    <select name="estatus" onchange="this.form.submit()" class="text-[9px] py-0.5 pl-1 pr-5 rounded border {{ $statusClass }} w-full cursor-pointer shadow-sm">
                                        <option value="En blanco" {{ $act->estatus == 'En blanco' ? 'selected' : '' }}>⚪ En blanco</option>
                                        <option value="En proceso" {{ $act->estatus == 'En proceso' ? 'selected' : '' }}>🔵 En proceso</option>
                                        <option value="Completado" {{ str_contains($act->estatus, 'Completado') ? 'selected' : '' }}>🟢 Completado</option>
                                        @if(in_array($act->estatus, ['Retardo', 'Completado con retardo']))
                                            <option value="{{ $act->estatus }}" selected disabled>{{ $act->estatus }}</option>
                                        @endif
                                    </select>
                                </form>
                            </td>

                            <td class="px-1 py-2">
                                <button @click="openNotes({{ $act->id }}, '{{ addslashes($act->nombre_actividad) }}')" 
                                        class="text-indigo-600 hover:text-indigo-900 border border-indigo-200 bg-indigo-50 hover:bg-white px-2 py-1 rounded shadow-sm flex items-center gap-1 mx-auto transition-all">
                                    <span>📝</span>
                                    <span class="font-bold text-[9px]">{{ !empty($act->comentarios) ? 'Ver' : '+' }}</span>
                                </button>
                                <textarea id="notes-data-{{ $act->id }}" class="hidden">{{ $act->comentarios }}</textarea>
                            </td>

                            <td class="px-1 py-2">
                                <button @click="openHistory({{ $act->id }}, '{{ addslashes($act->nombre_actividad) }}')" 
                                        class="text-gray-400 hover:text-indigo-600 p-1 rounded-full hover:bg-gray-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </button>
                                <textarea id="history-data-{{ $act->id }}" class="hidden">{{ json_encode($act->historial) }}</textarea>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="15" class="py-8 text-center text-gray-500 italic">No se encontraron actividades con estos filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(isset($esDireccion) && $esDireccion)
        <div x-show="directorOpen" style="display: none;" class="fixed inset-0 overflow-hidden z-50">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="directorOpen = false"></div>
                <div class="fixed inset-y-0 left-0 flex max-w-full pr-10 pointer-events-none">
                    <div class="w-screen max-w-xs pointer-events-auto bg-gray-900 shadow-xl flex flex-col h-full transform transition ease-in-out duration-500 sm:duration-700" 
                         x-transition:enter="translate-x-full" x-transition:leave="-translate-x-full">
                        
                        <div class="bg-gray-800 px-4 py-6 flex justify-between items-center border-b border-gray-700">
                            <div><h2 class="text-lg font-bold text-white">Estructura</h2><p class="text-xs text-gray-400">Selecciona un líder para ver su equipo.</p></div>
                            <button @click="directorOpen = false" class="text-gray-400 hover:text-white">✕</button>
                        </div>

                        <div class="flex-1 overflow-y-auto py-4">
                            <nav class="space-y-1 px-2">
                                <a href="{{ route('activities.index') }}" 
                                   class="text-gray-300 hover:bg-gray-800 hover:text-white group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ !request('ver_equipo_de') ? 'bg-indigo-900 text-white' : '' }}">
                                    <span class="truncate">🏢 Ver Empresa Completa</span>
                                </a>

                                <div class="mt-4 mb-2 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Mis Gerentes / Supervisores
                                </div>

                                @if(isset($listaSupervisores) && count($listaSupervisores) > 0)
                                    @foreach($listaSupervisores as $sup)
                                        <a href="{{ route('activities.index', ['ver_equipo_de' => $sup->id]) }}" 
                                           class="text-gray-300 hover:bg-gray-800 hover:text-white group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request('ver_equipo_de') == $sup->id ? 'bg-gray-800 text-white border-l-4 border-indigo-500' : '' }}">
                                            <div class="mr-3 flex-shrink-0 h-6 w-6 rounded-full bg-gray-700 flex items-center justify-center text-xs font-bold text-white">
                                                {{ substr($sup->nombre_completo ?? 'S', 0, 1) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="truncate">{{ $sup->nombre_completo }}</p>
                                                <p class="text-[10px] text-gray-500 truncate">{{ $sup->posicion }}</p>
                                            </div>
                                        </a>
                                    @endforeach
                                @else
                                    <p class="text-xs text-gray-500 px-2 italic">No tienes supervisores directos asignados.</p>
                                @endif
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div x-show="notesOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="notesOpen = false"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form :action="'/activities/' + currentActivityId" method="POST">
                        @csrf @method('PUT')
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Bitácora: <span x-text="currentActivity" class="text-indigo-600 font-bold"></span></h3>
                            <div class="mt-4">
                                <div class="flex gap-2 mb-2">
                                    <button type="button" @click="appendSignature()" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-full text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none transition">
                                        ✍️ Agregar mi firma
                                    </button>
                                </div>
                                <textarea id="big-note-area" name="comentarios" x-model="currentNotes" rows="10" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md font-mono bg-gray-50" placeholder="Escribe aquí el historial..."></textarea>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm transition">Guardar Cambios</button>
                            <button type="button" @click="notesOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="historyOpen" style="display: none;" class="fixed inset-0 overflow-hidden z-50">
             <div class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="historyOpen = false"></div>
             <div class="fixed inset-y-0 right-0 flex max-w-full pl-10 pointer-events-none">
                <div class="w-screen max-w-md pointer-events-auto bg-white shadow-xl flex flex-col transform transition ease-in-out duration-500 sm:duration-700">
                    <div class="bg-gray-800 px-4 py-4 flex justify-between items-center">
                        <h2 class="text-white font-bold">Historial</h2>
                        <button @click="historyOpen = false" class="text-gray-400 hover:text-white">✕</button>
                    </div>
                    <div class="p-4 flex-1 overflow-y-auto bg-gray-50">
                        <ul class="space-y-4">
                            <template x-for="log in historyLogs" :key="log.id">
                                <li class="text-xs border-b border-gray-200 pb-2 bg-white p-2 rounded shadow-sm">
                                    <div class="font-bold text-gray-700 uppercase" x-text="log.campo_modificado"></div>
                                    <div class="flex gap-2 mt-1 items-center">
                                        <span class="text-red-400 line-through bg-red-50 px-1 rounded" x-text="log.valor_anterior || 'Vacío'"></span>
                                        <span class="text-gray-400">➔</span>
                                        <span class="text-green-600 font-bold bg-green-50 px-1 rounded" x-text="log.valor_nuevo"></span>
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-1 flex justify-end">
                                        <span x-text="new Date(log.fecha_cambio).toLocaleString()"></span>
                                    </div>
                                </li>
                            </template>
                            <li x-show="historyLogs.length === 0" class="text-center text-gray-400 mt-10 italic">Sin registros.</li>
                        </ul>
                    </div>
                </div>
             </div>
        </div>

    </div>
</x-app-layout>