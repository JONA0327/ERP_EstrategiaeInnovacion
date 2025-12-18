@extends('layouts.erp')

@section('title', 'Evaluación - ' . $empleado->nombre)

@section('content')
<div class="min-h-screen bg-slate-50 py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
        
        {{-- Alertas de Éxito o Error (Opcional, si tu layout no las maneja globalmente) --}}
        @if(session('error'))
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
                <p class="font-bold">Error</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-8">
            
            {{-- COLUMNA IZQUIERDA: LISTA DE EMPLEADOS (SIDEBAR) --}}
            <div class="w-full lg:w-1/4">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
                    <div class="p-4 bg-slate-50 border-b border-slate-200">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Equipo: {{ $area ?? 'General' }}</span>
                    </div>
                    <div class="max-h-[70vh] overflow-y-auto custom-scrollbar p-2 space-y-1">
                        @if(isset($empleados) && count($empleados) > 0)
                            @foreach($empleados as $emp)
                                {{-- Enlace mantiene el parametro 'periodo' para no perder el contexto --}}
                                <a href="{{ route('rh.evaluacion.show', ['id' => $emp->id, 'periodo' => $periodo]) }}" class="flex items-center p-3 rounded-xl transition-all duration-200 {{ $empleado->id === $emp->id ? 'bg-indigo-50 border border-indigo-100 shadow-sm' : 'hover:bg-slate-50 border border-transparent' }}">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold overflow-hidden {{ $empleado->id === $emp->id ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                                            @if(isset($emp->foto_path) && $emp->foto_path)
                                                <img src="{{ asset('storage/' . $emp->foto_path) }}" class="w-full h-full object-cover">
                                            @else
                                                {{ substr($emp->nombre, 0, 1) }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="ml-3 overflow-hidden">
                                        <p class="text-sm font-semibold truncate {{ $empleado->id === $emp->id ? 'text-indigo-900' : 'text-slate-700' }}">
                                            {{ $emp->nombre }}
                                        </p>
                                        <p class="text-[10px] truncate {{ $empleado->id === $emp->id ? 'text-indigo-600' : 'text-slate-500' }}">
                                            {{ $emp->posicion ?? 'N/A' }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: INFORMACIÓN Y FORMULARIO --}}
            <div class="w-full lg:w-3/4 space-y-6">
                
                {{-- TARJETA DE INFORMACIÓN DEL EMPLEADO --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 md:p-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-indigo-100/50 to-transparent rounded-bl-full -mr-10 -mt-10 pointer-events-none"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row gap-6 items-center md:items-start text-center md:text-left">
                        <div class="w-24 h-24 rounded-full bg-white p-1 shadow-lg ring-4 ring-indigo-50">
                            <div class="w-full h-full rounded-full bg-slate-100 flex items-center justify-center overflow-hidden">
                                @if(isset($empleado->foto_path) && $empleado->foto_path)
                                    <img src="{{ asset('storage/' . $empleado->foto_path) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-3xl font-bold text-slate-400">{{ substr($empleado->nombre, 0, 1) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold text-slate-900">{{ $empleado->nombre }} {{ $empleado->apellido_paterno }}</h2>
                            <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-2">
                                <span class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold border border-indigo-200">
                                    {{ $empleado->posicion ?? 'Puesto no asignado' }}
                                </span>
                                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold border border-slate-200">
                                    ID: {{ $empleado->id_empleado ?? 'N/D' }}
                                </span>
                                
                                {{-- Badge visual del Periodo --}}
                                <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold border border-blue-200 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    {{ $periodo }}
                                </span>
                            </div>
                            
                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-slate-600">
                                <div class="flex items-center justify-center md:justify-start gap-2 bg-slate-50 px-3 py-1.5 rounded-lg">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 00-2-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    {{ $empleado->correo ?? 'Sin correo' }}
                                </div>
                                @if(isset($empleado->fecha_ingreso))
                                <div class="flex items-center justify-center md:justify-start gap-2 bg-slate-50 px-3 py-1.5 rounded-lg">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Ingreso: {{ \Carbon\Carbon::parse($empleado->fecha_ingreso)->format('d/m/Y') }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FORMULARIO DE EVALUACIÓN --}}
                <div class="bg-white rounded-3xl shadow-lg border border-slate-200 overflow-hidden">
                    <div class="bg-slate-900 px-8 py-5 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-white">Formulario de Evaluación</h3>
                            <p class="text-indigo-200 text-xs mt-0.5">Criterios para: {{ $area ?? 'General' }}</p>
                        </div>
                    </div>

                    <div class="p-8">
                        @php
                            // Determinamos la acción: Crear o Actualizar
                            $actionRoute = isset($evaluacion) 
                                ? route('rh.evaluacion.update', $evaluacion->id) 
                                : route('rh.evaluacion.store');
                        @endphp

                        <form method="POST" action="{{ $actionRoute }}">
                            @csrf
                            @if(isset($evaluacion))
                                @method('PUT')
                            @endif

                            {{-- INPUTS OCULTOS VITALES --}}
                            <input type="hidden" name="empleado_id" value="{{ $empleado->id }}">
                            <input type="hidden" name="periodo" value="{{ $periodo }}">

                            {{-- Alerta si la evaluación está bloqueada --}}
                            @if(isset($is_locked) && $is_locked)
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 rounded-r">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700 font-bold">
                                                Modo Lectura
                                            </p>
                                            <p class="text-sm text-yellow-700">
                                                Esta evaluación ya ha sido editada y finalizada. No se permiten más cambios.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($criterios) && $criterios->isNotEmpty())
                                <div class="space-y-12">
                                    @foreach($criterios as $criterio)
                                        @php
                                            // Recuperar valores previos para pre-llenar
                                            $valPrevio = $respuestas[$criterio->id] ?? null;
                                            $obsPrevia = $observaciones[$criterio->id] ?? '';
                                        @endphp

                                        <div class="relative" x-data="{ selected: {{ $valPrevio ?? 'null' }} }">
                                            
                                            {{-- Header de la Pregunta --}}
                                            <div class="flex items-start justify-between mb-4">
                                                <div class="flex gap-4">
                                                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm border border-slate-200">
                                                        {{ $loop->iteration }}
                                                    </span>
                                                    <div>
                                                        <h4 class="text-base font-bold text-slate-800 leading-tight">{{ $criterio->criterio }}</h4>
                                                        <p class="text-sm text-slate-500 mt-1 leading-relaxed">{{ $criterio->descripcion }}</p>
                                                    </div>
                                                </div>
                                                <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                    Peso: {{ $criterio->peso }}%
                                                </span>
                                            </div>

                                            {{-- Opciones de Respuesta --}}
                                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-4">
                                                @php
                                                    $opciones = [
                                                        ['val' => 100, 'label' => 'Muy de acuerdo', 'icon' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'container_classes' => 'hover:border-emerald-200 hover:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50', 'icon_classes' => 'group-hover:text-emerald-400 peer-checked:text-emerald-600', 'label_classes' => 'group-hover:text-emerald-700 peer-checked:text-emerald-800', 'check_color' => 'text-emerald-600'],
                                                        ['val' => 75, 'label' => 'De acuerdo', 'icon' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'container_classes' => 'hover:border-green-200 hover:bg-green-50 peer-checked:border-green-500 peer-checked:bg-green-50', 'icon_classes' => 'group-hover:text-green-400 peer-checked:text-green-600', 'label_classes' => 'group-hover:text-green-700 peer-checked:text-green-800', 'check_color' => 'text-green-600'],
                                                        ['val' => 50, 'label' => 'Neutral', 'icon' => 'M10 14H14M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9 10h.01M15 10h.01', 'container_classes' => 'hover:border-yellow-200 hover:bg-yellow-50 peer-checked:border-yellow-500 peer-checked:bg-yellow-50', 'icon_classes' => 'group-hover:text-yellow-400 peer-checked:text-yellow-600', 'label_classes' => 'group-hover:text-yellow-700 peer-checked:text-yellow-800', 'check_color' => 'text-yellow-600'],
                                                        ['val' => 25, 'label' => 'En desacuerdo', 'icon' => 'M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'container_classes' => 'hover:border-orange-200 hover:bg-orange-50 peer-checked:border-orange-500 peer-checked:bg-orange-50', 'icon_classes' => 'group-hover:text-orange-400 peer-checked:text-orange-600', 'label_classes' => 'group-hover:text-orange-700 peer-checked:text-orange-800', 'check_color' => 'text-orange-600'],
                                                        ['val' => 0, 'label' => 'Muy en desacuerdo', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', 'container_classes' => 'hover:border-red-200 hover:bg-red-50 peer-checked:border-red-500 peer-checked:bg-red-50', 'icon_classes' => 'group-hover:text-red-400 peer-checked:text-red-600', 'label_classes' => 'group-hover:text-red-700 peer-checked:text-red-800', 'check_color' => 'text-red-600']
                                                    ];
                                                @endphp

                                                @foreach($opciones as $op)
                                                    <label class="cursor-pointer group relative {{ $is_locked ? 'opacity-60 cursor-not-allowed' : '' }}">
                                                        <input type="radio" 
                                                               name="calificaciones[{{ $criterio->id }}]" 
                                                               value="{{ $op['val'] }}" 
                                                               class="peer sr-only" 
                                                               x-model="selected" 
                                                               required
                                                               {{ $is_locked ? 'disabled' : '' }}>
                                                        
                                                        <div class="h-full flex flex-col items-center justify-center p-3 rounded-xl border-2 border-slate-100 bg-white transition-all duration-200 peer-checked:shadow-md {{ $op['container_classes'] }}">
                                                            <div class="mb-1 text-slate-300 transition-colors {{ $op['icon_classes'] }}">
                                                                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $op['icon'] }}"></path></svg>
                                                            </div>
                                                            <span class="text-[10px] font-bold text-center text-slate-500 leading-tight block {{ $op['label_classes'] }}">
                                                                {{ $op['label'] }}
                                                            </span>
                                                        </div>
                                                        
                                                        <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity {{ $op['check_color'] }}">
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>

                                            <div class="mt-3 pl-12">
                                                <input type="text" 
                                                       name="observaciones[{{ $criterio->id }}]" 
                                                       value="{{ $obsPrevia }}"
                                                       class="w-full text-sm border-0 border-b border-slate-200 focus:border-indigo-500 focus:ring-0 bg-transparent placeholder-slate-400 transition-colors disabled:text-slate-400" 
                                                       placeholder="Añadir comentario (opcional)..."
                                                       {{ $is_locked ? 'disabled' : '' }}>
                                            </div>
                                        </div>
                                        @if(!$loop->last) <hr class="border-slate-100"> @endif
                                    @endforeach
                                </div>

                                {{-- Sección de Comentarios Generales --}}
                                <div class="mt-8 pt-6 border-t border-slate-200">
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Comentarios Generales / Feedback</label>
                                    <textarea name="comentarios_generales" 
                                              rows="4" 
                                              class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm disabled:bg-slate-50 disabled:text-slate-500" 
                                              placeholder="Escriba aquí sus observaciones sobre el desempeño..."
                                              {{ $is_locked ? 'disabled' : '' }}>{{ $evaluacion->comentarios_generales ?? '' }}</textarea>
                                </div>

                                {{-- Botones de Acción --}}
                                @if(!$is_locked)
                                    <div class="mt-12 flex justify-end items-center gap-4 pt-6 border-t border-slate-200">
                                        <a href="{{ route('rh.evaluacion.index') }}" class="px-6 py-3 text-sm font-bold text-slate-600 hover:text-slate-800 transition">Cancelar</a>
                                        <button type="submit" class="inline-flex items-center px-8 py-3 bg-indigo-600 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shadow-lg shadow-indigo-200 transform hover:-translate-y-0.5">
                                            {{ isset($evaluacion) ? 'Actualizar Evaluación (Única vez)' : 'Guardar Resultados' }}
                                        </button>
                                    </div>
                                @else
                                    <div class="mt-12 flex justify-end pt-6 border-t border-slate-200">
                                        <a href="{{ route('rh.evaluacion.index') }}" class="px-6 py-3 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition">
                                            Volver al listado
                                        </a>
                                    </div>
                                @endif

                            @else
                                {{-- Estado vacío: Sin criterios definidos --}}
                                <div class="text-center py-16">
                                    <div class="inline-block p-4 rounded-full bg-slate-50 mb-4">
                                        <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-slate-900">Sin Criterios</h3>
                                    <p class="text-slate-500 mt-2">No se han definido preguntas para el área de <span class="font-bold">{{ $area }}</span>.</p>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection