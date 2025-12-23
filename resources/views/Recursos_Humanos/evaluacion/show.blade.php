@extends('layouts.erp')

@section('title', 'Evaluación de Desempeño')

@section('content')
<div class="min-h-screen bg-slate-50 py-8">
    <div class="container mx-auto px-4 max-w-5xl">
        
        {{-- Header con Botón Regresar --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('rh.evaluacion.index', ['periodo' => $periodo]) }}" class="flex items-center text-slate-500 hover:text-slate-700 transition font-bold">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Regresar al listado
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm">
                <p class="font-bold">¡Éxito!</p><p>{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                <p class="font-bold">Error</p><p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
            {{-- Encabezado de la Tarjeta --}}
            <div class="bg-slate-900 px-8 py-6 text-white flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center text-2xl font-bold border-2 border-white/20">
                        {{ substr($empleado->nombre, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">{{ $empleado->nombre }} {{ $empleado->apellido_paterno }}</h2>
                        <p class="text-indigo-300 font-medium">{{ $empleado->posicion ?? 'Puesto no definido' }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="inline-block px-4 py-1.5 rounded-full bg-indigo-600 border border-indigo-500 text-sm font-bold shadow-sm">
                        {{ $periodo }}
                    </div>
                    <p class="text-xs text-slate-400 mt-2 text-center md:text-right">Evaluación Confidencial</p>
                </div>
            </div>

            <div class="p-8">
                @php 
                    $actionRoute = isset($evaluacion) ? route('rh.evaluacion.update', $evaluacion->id) : route('rh.evaluacion.store'); 
                @endphp

                <form method="POST" action="{{ $actionRoute }}">
                    @csrf
                    @if(isset($evaluacion)) @method('PUT') @endif
                    
                    <input type="hidden" name="empleado_id" value="{{ $empleado->id }}">
                    <input type="hidden" name="periodo" value="{{ $periodo }}">

                    @if(isset($is_locked) && $is_locked)
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 rounded-r">
                            <div class="flex">
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700 font-bold">Evaluación Finalizada</p>
                                    <p class="text-sm text-yellow-700">Ya has enviado esta evaluación y no se puede editar.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($criterios) && $criterios->isNotEmpty())
                        <div class="space-y-10">
                            @foreach($criterios as $criterio)
                                @php
                                    $valPrevio = $respuestas[$criterio->id] ?? null;
                                    $obsPrevia = $observaciones[$criterio->id] ?? '';
                                @endphp
                                <div class="relative bg-slate-50 p-6 rounded-2xl border border-slate-100 hover:border-indigo-100 transition-colors" x-data="{ selected: {{ $valPrevio ?? 'null' }} }">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h4 class="text-lg font-bold text-slate-800">{{ $criterio->criterio }}</h4>
                                            <p class="text-sm text-slate-500 mt-1 max-w-3xl">{{ $criterio->descripcion }}</p>
                                        </div>
                                        <span class="text-xs font-bold bg-white text-slate-400 px-2 py-1 rounded border border-slate-200 shadow-sm">Peso: {{ $criterio->peso }}%</span>
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                                        @php
                                            $opciones = [
                                                ['val' => 100, 'label' => 'Excelente'],
                                                ['val' => 75, 'label' => 'Bueno'],
                                                ['val' => 50, 'label' => 'Regular'],
                                                ['val' => 25, 'label' => 'Deficiente'],
                                                ['val' => 0, 'label' => 'Inaceptable']
                                            ];
                                        @endphp
                                        @foreach($opciones as $op)
                                            <label class="cursor-pointer group relative">
                                                <input type="radio" name="calificaciones[{{ $criterio->id }}]" value="{{ $op['val'] }}" class="peer sr-only" x-model="selected" required {{ $is_locked ? 'disabled' : '' }}>
                                                <div class="h-full flex flex-col items-center justify-center p-3 rounded-xl border-2 border-white bg-white shadow-sm transition-all duration-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 hover:shadow-md">
                                                    <span class="text-lg font-bold mb-1">{{ $op['val'] }}</span>
                                                    <span class="text-[10px] uppercase font-bold tracking-wider opacity-70">{{ $op['label'] }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                    
                                    <div class="mt-4">
                                        <input type="text" name="observaciones[{{ $criterio->id }}]" value="{{ $obsPrevia }}" 
                                            class="w-full text-sm rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 placeholder-slate-400" 
                                            placeholder="Observaciones específicas para este punto (opcional)..." {{ $is_locked ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-10 pt-8 border-t border-slate-200">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Comentarios Generales y Feedback</label>
                            <textarea name="comentarios_generales" rows="4" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm" placeholder="Escribe aquí tus conclusiones generales..." {{ $is_locked ? 'disabled' : '' }}>{{ $evaluacion->comentarios_generales ?? '' }}</textarea>
                        </div>

                        @if(!$is_locked)
                            <div class="mt-10 flex justify-end gap-4">
                                <a href="{{ route('rh.evaluacion.index') }}" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition">Cancelar</a>
                                <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 transform hover:-translate-y-0.5 transition-all">
                                    Enviar Evaluación
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-20 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <h3 class="text-lg font-bold text-slate-600">No hay criterios asignados</h3>
                            <p class="text-slate-400">Este puesto ({{ $area }}) no tiene criterios de evaluación definidos en el sistema.</p>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection