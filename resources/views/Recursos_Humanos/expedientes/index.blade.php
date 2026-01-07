@extends('layouts.erp')
@section('title','Expedientes - Recursos Humanos')
@section('content')
<main class="relative max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-10">
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">Expedientes
                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-600 border border-blue-100">{{ $empleados->total() }} registros</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Gestión centralizada de empleados corporativos.</p>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('rh.expedientes.refresh') }}" onsubmit="return confirm('¿Sincronizar nuevos usuarios?')" class="inline-block">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2 text-sm font-semibold text-white shadow hover:from-blue-700 hover:to-blue-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5 13a7 7 0 0114 0 7 7 0 01-14 0z"/></svg>
                    Refrescar
                </button>
            </form>
        </div>
    </div>

    <form method="GET" class="mb-5">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar nombre, correo o área..." class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 pr-10 text-sm focus:border-blue-400 focus:ring-0 shadow-sm" />
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M9.5 17A7.5 7.5 0 109.5 2a7.5 7.5 0 000 15z"/></svg>
            </div>
            <button class="rounded-2xl border border-blue-200 bg-blue-50 px-6 py-2.5 text-sm font-semibold text-blue-700 shadow-sm hover:bg-blue-100">Buscar</button>
        </div>
    </form>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-lg shadow-slate-200/40">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-2 text-left text-[11px] font-semibold tracking-wide text-slate-600">NOMBRE</th>
                    <th class="px-5 py-2 text-left text-[11px] font-semibold tracking-wide text-slate-600">CORREO</th>
                    <th class="px-5 py-2 text-left text-[11px] font-semibold tracking-wide text-slate-600">ÁREA</th>
                    <th class="px-5 py-2 text-left text-[11px] font-semibold tracking-wide text-slate-600">EXPEDIENTE</th>
                    <th class="px-5 py-2 text-right text-[11px] font-semibold tracking-wide text-slate-600">ACCIONES</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($empleados as $empleado)
                    <tr class="group hover:bg-blue-50/50 transition-colors">
                        <td class="px-5 py-2 text-sm font-medium text-slate-800">{{ $empleado->nombre }}</td>
                        <td class="px-5 py-2 text-xs text-slate-600">{{ $empleado->correo }}</td>
                        <td class="px-5 py-2 text-xs">
                            @php $areaLabel = $empleado->area; @endphp
                            @if($areaLabel)
                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-medium
                                    @class([
                                        'bg-blue-50 border-blue-200 text-blue-700'=> str_starts_with($areaLabel,'S'),
                                        'bg-green-50 border-green-200 text-green-700'=> str_starts_with($areaLabel,'R'),
                                        'bg-purple-50 border-purple-200 text-purple-700'=> str_starts_with($areaLabel,'L'),
                                        'bg-orange-50 border-orange-200 text-orange-700'=> str_starts_with($areaLabel,'C'),
                                        ])">{{ $areaLabel }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        
                        {{-- NUEVA COLUMNA: BARRA DE PROGRESO --}}
                        <td class="px-5 py-2 align-middle">
                            @php 
                                $porcentaje = $empleado->porcentaje_expediente; 
                                // Lógica de colores semáforo
                                $colorBarra = 'bg-red-500';
                                $textoColor = 'text-red-600';
                                
                                if($porcentaje > 50) { 
                                    $colorBarra = 'bg-yellow-400'; 
                                    $textoColor = 'text-yellow-600'; 
                                }
                                if($porcentaje >= 90) { 
                                    $colorBarra = 'bg-green-500'; 
                                    $textoColor = 'text-green-600'; 
                                }
                            @endphp

                            <div class="w-full max-w-[120px]">
                                <div class="flex justify-between mb-1">
                                    <span class="text-[10px] font-bold {{ $textoColor }}">{{ $porcentaje }}%</span>
                                    <span class="text-[10px] text-slate-400" title="{{ $empleado->es_practicante ? 'Evaluado como Practicante' : 'Evaluado como Empleado' }}">
                                        {{ $empleado->es_practicante ? 'Prac.' : 'Emp.' }}
                                    </span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-1.5">
                                    <div class="{{ $colorBarra }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $porcentaje }}%"></div>
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-2 text-xs text-right space-x-1">
                            <a href="{{ route('rh.expedientes.show',$empleado) }}" class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2 py-1 text-blue-600 hover:bg-blue-100 shadow-sm">
                                <x-ui.icon name="eye" class="h-4 w-4" />Ver
                            </a>
                            <a href="{{ route('rh.expedientes.edit',$empleado) }}" class="inline-flex items-center gap-1 rounded-lg bg-yellow-50 px-2 py-1 text-yellow-700 hover:bg-yellow-100 shadow-sm">
                                <x-ui.icon name="pencil-square" class="h-4 w-4" />Editar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">No hay expedientes registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $empleados->links() }}</div>
</main>
@endsection