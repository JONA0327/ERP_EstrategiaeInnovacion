@extends('layouts.erp')

@section('content')
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">
                    {{ __('Evaluación de Desempeño') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">Gestión del talento y medición de competencias por área.</p>
            </div>
        </div>
    </x-slot>

    @php
        $categoriasPrincipales = ['Logistica', 'Legal', 'Pedimentos', 'Anexo 24', 'Auditoria', 'TI', 'Recursos Humanos'];
        $todosLosPuestos = $empleados->pluck('posicion')->unique()->values()->toArray();
        $todasLasCategorias = array_unique(array_merge($categoriasPrincipales, $todosLosPuestos));
    @endphp

    <div class="py-12 bg-slate-50 min-h-screen" x-data="{ activeTab: 'Logistica' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-2">
                <nav class="flex space-x-1 overflow-x-auto custom-scrollbar pb-2 md:pb-0" aria-label="Tabs">
                    @foreach($todasLasCategorias as $categoria)
                        @if(!empty($categoria))
                            <button 
                                @click="activeTab = '{{ $categoria }}'"
                                :class="activeTab === '{{ $categoria }}' 
                                    ? 'bg-indigo-50 text-indigo-700 shadow-sm ring-1 ring-indigo-200' 
                                    : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                                class="whitespace-nowrap px-5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 ease-in-out flex-shrink-0"
                            >
                                {{ $categoria }}
                            </button>
                        @endif
                    @endforeach
                </nav>
            </div>

            <div class="space-y-6">
                @foreach($todasLasCategorias as $categoria)
                    @if(!empty($categoria))
                        <div x-show="activeTab === '{{ $categoria }}'" 
                             x-transition:enter="transition ease-out duration-300" 
                             x-transition:enter-start="opacity-0 translate-y-2" 
                             x-transition:enter-end="opacity-100 translate-y-0"
                             style="display: none;">
                            
                            <div class="flex items-center justify-between mb-6 px-2">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </div>
                                    <h4 class="text-xl font-bold text-slate-800">{{ $categoria }}</h4>
                                </div>
                                <span class="bg-white text-slate-600 text-xs font-bold px-3 py-1 rounded-full border border-slate-200 shadow-sm">
                                    {{ $empleados->filter(fn($e) => str_contains($e->posicion, $categoria) || $e->posicion == $categoria)->count() }} Miembros
                                </span>
                            </div>

                            @php
                                $empleadosCategoria = $empleados->filter(fn($e) => $e->posicion === $categoria || str_contains($e->posicion, $categoria));
                            @endphp

                            @if($empleadosCategoria->isEmpty())
                                <div class="flex flex-col items-center justify-center py-16 bg-white rounded-3xl border border-dashed border-slate-300">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                    </div>
                                    <h3 class="text-slate-900 font-semibold">Sin colaboradores asignados</h3>
                                    <p class="text-slate-500 text-sm mt-1">No hay registros bajo la categoría {{ $categoria }}.</p>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                    @foreach($empleadosCategoria as $empleado)
                                        <div class="group bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
                                            
                                            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>

                                            <div class="relative z-10 flex flex-col h-full">
                                                <div class="flex items-start justify-between mb-4">
                                                    <div class="h-14 w-14 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xl shadow-sm">
                                                        @if(isset($empleado->foto_path) && $empleado->foto_path)
                                                            <img src="{{ asset('storage/' . $empleado->foto_path) }}" class="w-full h-full object-cover rounded-2xl">
                                                        @else
                                                            {{ substr($empleado->nombre, 0, 1) }}
                                                        @endif
                                                    </div>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-green-50 text-green-700 border border-green-100">
                                                        ACTIVO
                                                    </span>
                                                </div>

                                                <div class="flex-1">
                                                    <h5 class="text-lg font-bold text-slate-800 group-hover:text-indigo-600 transition-colors truncate" title="{{ $empleado->nombre }}">
                                                        {{ $empleado->nombre }}
                                                    </h5>
                                                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wide mt-1 truncate">
                                                        {{ $empleado->apellido_paterno }}
                                                    </p>
                                                    <div class="mt-3 flex items-center text-xs text-slate-500">
                                                        <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                                        <span class="truncate">{{ $empleado->posicion }}</span>
                                                    </div>
                                                </div>

                                                <div class="mt-5 pt-4 border-t border-slate-100">
                                                    <a href="{{ route('rh.evaluacion.show', $empleado->id) }}" class="flex items-center justify-center w-full px-4 py-2 bg-slate-900 hover:bg-indigo-600 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-colors duration-200">
                                                        Iniciar Evaluación
                                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endsection