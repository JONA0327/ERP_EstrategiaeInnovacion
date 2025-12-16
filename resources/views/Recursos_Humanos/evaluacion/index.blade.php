@extends('layouts.erp')

@section('title', 'Evaluaciones - Recursos Humanos')

@section('content')
<div class="container mx-auto px-4 py-8">
    
    <!-- Encabezado Limpio -->
    <div class="mb-8 text-center bg-white rounded-lg p-6 shadow-sm border border-gray-200">
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Evaluaciones de Personal</h1>
        <p class="text-gray-500 mt-2 max-w-2xl mx-auto">Gestión de desempeño y evaluaciones por área.</p>
    </div>

    <!-- Barra de Filtros (Categorías/Áreas) -->
    <div class="mb-8">
        <div class="flex flex-wrap justify-center gap-2">
            {{-- Botón para ver Todos --}}
            <a href="{{ route('rh.evaluacion.index', ['area' => 'Todos']) }}" 
               class="px-5 py-2 rounded-md text-sm font-medium transition-colors duration-200 border 
               {{ !request('area') || request('area') == 'Todos' 
                  ? 'bg-gray-100 text-gray-900 border-gray-300 shadow-inner' 
                  : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:text-gray-900 hover:border-gray-300' }}">
                Todos
            </a>

            {{-- Botones dinámicos por Área --}}
            @foreach($areas as $area)
                <a href="{{ route('rh.evaluacion.index', ['area' => $area]) }}" 
                   class="px-5 py-2 rounded-md text-sm font-medium transition-colors duration-200 border
                   {{ request('area') == $area 
                      ? 'bg-gray-100 text-gray-900 border-gray-300 shadow-inner' 
                      : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:text-gray-900 hover:border-gray-300' }}">
                    {{ $area }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Grid de Empleados -->
    @if($empleados->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($empleados as $empleado)
                <div class="group bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 border border-gray-200 flex flex-col overflow-hidden">
                    
                    <div class="p-6 flex-grow flex flex-col items-center text-center">
                        {{-- Avatar / Foto Simple --}}
                        <div class="w-24 h-24 rounded-full bg-gray-50 mb-4 overflow-hidden border border-gray-100 group-hover:border-gray-300 transition-colors">
                            @if(isset($empleado->foto_path) && $empleado->foto_path)
                                <img src="{{ asset('storage/' . $empleado->foto_path) }}" alt="{{ $empleado->nombre }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                </div>
                            @endif
                        </div>

                        <h3 class="text-base font-bold text-gray-900 mb-1 group-hover:text-black transition-colors">
                            {{ $empleado->nombre }}
                        </h3>
                        
                        <div class="flex flex-col gap-2 mb-6 w-full items-center">
                            <span class="text-sm text-gray-500 truncate max-w-full font-medium">
                                {{ $empleado->posicion ?? 'Puesto no asignado' }}
                            </span>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">
                                {{ $empleado->area }}
                            </span>
                        </div>
                        
                        <div class="mt-auto w-full">
                            <a href="{{ route('rh.evaluacion.show', $empleado->id) }}" class="block w-full py-2 px-4 bg-white border border-gray-200 hover:border-gray-400 text-gray-700 hover:text-black text-sm font-medium rounded-md transition-all duration-200">
                                Evaluar
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Estado Vacío -->
        <div class="flex flex-col items-center justify-center py-20 bg-white rounded-lg border border-gray-200 text-center border-dashed">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900">Sin registros</h3>
            <p class="mt-1 text-sm text-gray-500">No hay empleados en esta categoría.</p>
            <a href="{{ route('rh.evaluacion.index', ['area' => 'Todos']) }}" class="mt-4 text-sm text-gray-600 font-medium hover:text-black hover:underline">Ver todos</a>
        </div>
    @endif
</div>
@endsection