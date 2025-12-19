@extends('layouts.erp')

@section('content')
<div class="min-h-screen bg-slate-50 pb-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        <!-- Header Principal -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 relative overflow-hidden">
            <div class="absolute right-0 top-0 h-full w-1/2 bg-gradient-to-l from-blue-50/80 to-transparent pointer-events-none"></div>
            
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('logistica.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-800 shadow-sm transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            <span>Regresar</span>
                        </a>
                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider border border-blue-200">
                            Gestión Logística
                        </span>
                        <span class="text-sm text-slate-400 font-medium">{{ date('F d\t\h, Y') }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="marcarPagadosSeleccionados()" id="btnMarcarPagados" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl transition duration-200 hidden shadow-sm">
                            ✅ Marcar como Pagados
                        </button>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-slate-900 tracking-tight">Control de Pagos de Pedimentos</h3>
                <p class="mt-2 text-slate-500 max-w-2xl text-lg leading-relaxed">
                    Gestiona el estado de pago de pedimentos extraídos de la matriz de seguimiento logístico.
                </p>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 text-center group hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 mx-auto group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-blue-600 mb-1">{{ $stats['total_claves'] ?? 0 }}</div>
                <div class="text-sm text-slate-500 font-medium">Tipos Operación</div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 text-center group hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 mx-auto group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-indigo-600 mb-1">{{ $stats['total_pedimentos'] ?? 0 }}</div>
                <div class="text-sm text-slate-500 font-medium">Total Pedimentos</div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 text-center group hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4 mx-auto group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-amber-600 mb-1">{{ $stats['pendientes'] }}</div>
                <div class="text-sm text-slate-500 font-medium">Por Pagar</div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 text-center group hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 mx-auto group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-emerald-600 mb-1">{{ $stats['pagados'] }}</div>
                <div class="text-sm text-slate-500 font-medium">Pagados</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </div>
                <h4 class="text-lg font-bold text-slate-800">Filtros de Búsqueda</h4>
                <span class="text-xs text-slate-500 bg-slate-50 px-2 py-1 rounded-md">Personaliza tu búsqueda</span>
            </div>
            
            <form method="GET" action="{{ route('logistica.pedimentos.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Buscar Pedimento</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" 
                           placeholder="Número de pedimento..." 
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-slate-50 font-medium">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Estado de Pago</label>
                    <select name="estado_pago" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-slate-50 font-medium">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" {{ request('estado_pago') == 'pendiente' ? 'selected' : '' }}>Por Pagar</option>
                        <option value="pagado" {{ request('estado_pago') == 'pagado' ? 'selected' : '' }}>Pagado</option>
                        <option value="vencido" {{ request('estado_pago') == 'vencido' ? 'selected' : '' }}>Vencido</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl transition duration-200 font-bold shadow-sm hover:shadow-lg flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filtrar
                    </button>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('logistica.pedimentos.index') }}" class="w-full bg-slate-500 hover:bg-slate-600 text-white px-6 py-2.5 rounded-xl transition duration-200 font-bold shadow-sm hover:shadow-lg flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabla de Pedimentos -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <div class="flex items-center justify-between">
                    <h4 class="text-lg font-bold text-slate-800">Lista de Pedimentos</h4>
                    <span class="text-sm text-slate-500 font-medium">{{ $paginatedPedimentos->total() ?? 0 }} registros</span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Clave Operación</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Total Pedimentos</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Por Pagar</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Pagados</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Clientes</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Ejecutivos</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Periodo</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Estado General</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($paginatedPedimentos as $pedimento)
                            <tr class="hover:bg-slate-50 transition-colors duration-200 group">
                                <td class="px-6 py-5">
                                    @if($pedimento->estado_pago !== 'pagado')
                                        <input type="checkbox" name="pedimentos[]" value="{{ $pedimento->id }}" onchange="updateSelectAll()" class="pedimento-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                                            {{ $pedimento->clave }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-lg">{{ $pedimento->clave }}</div>
                                            <div class="text-xs text-slate-500 font-medium">Tipo de Operación</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex flex-col items-center justify-center mx-auto group-hover:scale-105 transition-transform">
                                        <div class="font-bold text-xl">{{ $pedimento->total_pedimentos ?? 0 }}</div>
                                        <div class="text-xs font-medium">total</div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex flex-col items-center justify-center mx-auto group-hover:scale-105 transition-transform">
                                        <div class="font-bold text-xl">{{ $pedimento->pedimentos_por_pagar ?? 0 }}</div>
                                        <div class="text-xs font-medium">pendientes</div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex flex-col items-center justify-center mx-auto group-hover:scale-105 transition-transform">
                                        <div class="font-bold text-xl">{{ $pedimento->pedimentos_pagados ?? 0 }}</div>
                                        <div class="text-xs font-medium">pagados</div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 max-w-xs">
                                    <div class="font-bold text-slate-900 truncate" title="{{ $pedimento->clientes }}">
                                        {{ Str::limit($pedimento->clientes ?? 'Sin clientes', 40) }}
                                    </div>
                                    <div class="text-xs text-slate-500 font-medium mt-1">{{ Str::plural('Cliente', $pedimento->total_pedimentos) }}</div>
                                </td>
                                <td class="px-6 py-5 max-w-xs">
                                    <div class="font-bold text-slate-700 truncate" title="{{ $pedimento->ejecutivos }}">
                                        {{ Str::limit($pedimento->ejecutivos ?? 'Sin ejecutivos', 30) }}
                                    </div>
                                    <div class="text-xs text-slate-500 font-medium mt-1">{{ Str::plural('Ejecutivo', $pedimento->total_pedimentos) }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    @if($pedimento->primera_fecha && $pedimento->ultima_fecha)
                                        <div class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($pedimento->primera_fecha)->format('d/m/Y') }}</div>
                                        <div class="text-xs text-slate-500 font-medium">al {{ \Carbon\Carbon::parse($pedimento->ultima_fecha)->format('d/m/Y') }}</div>
                                    @else
                                        <div class="text-slate-500 font-medium">Sin fechas</div>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="px-3 py-2 inline-flex text-xs font-bold rounded-xl shadow-sm
                                        @if($pedimento->estado_pago == 'pagado') bg-emerald-100 text-emerald-800 border border-emerald-200
                                        @else bg-amber-100 text-amber-800 border border-amber-200 @endif">
                                        @if($pedimento->estado_pago == 'pagado') 
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Pagado
                                            </span>
                                        @else 
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Por Pagar
                                            </span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <button onclick="togglePedimentosClave('{{ $pedimento->clave }}')" class="bg-blue-50 hover:bg-blue-100 text-blue-600 hover:text-blue-700 px-4 py-2 rounded-xl font-bold text-sm transition-all duration-200 shadow-sm hover:shadow-md border border-blue-200">
                                        <span id="icon-{{ $pedimento->clave }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </span> <span id="text-{{ $pedimento->clave }}">Ver Pedimentos</span>
                                    </button>
                                </td>
                            </tr>
                            <!-- Fila expandible para mostrar pedimentos individuales -->
                            <tr id="pedimentos-{{ $pedimento->clave }}" class="hidden bg-slate-50">
                                <td colspan="10" class="px-6 py-6">
                                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                                        <div class="flex items-center gap-3 mb-4">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <h4 class="text-lg font-bold text-slate-800">
                                                Pedimentos de la clave: {{ $pedimento->clave }}
                                            </h4>
                                        </div>
                                        <div id="loading-{{ $pedimento->clave }}" class="text-center py-8">
                                            <div class="inline-flex items-center space-x-2">
                                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                                                <span class="text-slate-600 font-medium">Cargando pedimentos...</span>
                                            </div>
                                        </div>
                                        <div id="pedimentos-lista-{{ $pedimento->clave }}" class="hidden">
                                            <!-- Los pedimentos se cargarán aquí via AJAX -->
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center space-y-4">
                                        <div class="w-20 h-20 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-lg font-bold text-slate-600 mb-1">No hay pedimentos disponibles</div>
                                            <div class="text-sm text-slate-500">Los pedimentos aparecerán aquí cuando se generen operaciones logísticas</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($paginatedPedimentos->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $paginatedPedimentos->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Edición -->
<div id="modalEditar" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="cerrarModalEditar()"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="formEditar">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Editar Estado de Pago
                            </h3>
                            <div class="mt-4 space-y-4">
                                <input type="hidden" id="pedimentoId">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado de Pago</label>
                                    <select id="estadoPago" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500">
                                        <option value="pendiente">Pendiente</option>
                                        <option value="pagado">✅ Pagado</option>
                                        <option value="vencido">Vencido</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Pago</label>
                                    <input type="date" id="fechaPago" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Monto</label>
                                    <input type="number" id="monto" step="0.01" placeholder="0.00" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Vencimiento</label>
                                    <input type="date" id="fechaVencimiento" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                    <textarea id="observaciones" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500" placeholder="Observaciones sobre el pago..."></textarea>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Guardar Cambios
                    </button>
                    <button type="button" onclick="cerrarModalEditar()" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Detalles -->
<div id="modalDetalles" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="cerrarModalDetalles()"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Detalles del Pedimento
                        </h3>
                        <div class="mt-4" id="contenidoDetalles">
                            <!-- Contenido se carga dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="cerrarModalDetalles()" 
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>



<!-- Modal para editar pedimento individual -->
<div id="modalEditarPedimento" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="cerrarModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="formEditarPedimento" onsubmit="return guardarPedimento(event)">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Editar Estado de Pago - Clave de Operación
                            </h3>
                            <div class="mt-4">
                            
                            <input type="hidden" id="clave-operacion-hidden" name="clave_operacion">
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Clave de Operación</label>
                                    <input type="text" id="clave-operacion-display" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Total de Pedimentos</label>
                                    <input type="text" id="total-pedimentos" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Estado de Pago *</label>
                                        <select id="pedimento-estado" name="estado_pago" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="pendiente">Por Pagar</option>
                                            <option value="pagado">Pagado</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Moneda *</label>
                                        <select id="pedimento-moneda" name="moneda" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <!-- Se cargarán las monedas via AJAX -->
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Monto</label>
                                        <input type="number" step="0.01" min="0" id="pedimento-monto" name="monto" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Fecha Tentativa Pago</label>
                                        <input type="date" id="pedimento-fecha-tentativa" name="fecha_tentativa_pago" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                                    <textarea id="pedimento-observaciones" name="observaciones_pago" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Notas sobre el pago..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center items-center gap-2 rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Guardar
                    </button>
                    <button type="button" onclick="cerrarModal()" class="mt-3 w-full inline-flex justify-center items-center gap-2 rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Pedimento Individual -->
<div id="modalEditarPedimentoIndividual" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="cerrarModalPedimentoIndividual()"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="formEditarPedimentoIndividual" onsubmit="return guardarPedimentoIndividual(event)">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                📋 Editar Pago de Pedimento Individual
                            </h3>
                            
                            <input type="hidden" id="pedimento-individual-no" name="no_pedimento">
                            <input type="hidden" id="pedimento-individual-operacion-id" name="operacion_logistica_id">
                            <input type="hidden" id="pedimento-individual-clave" name="clave">
                            
                            <div class="space-y-4">
                                <div class="bg-blue-50 p-3 rounded-lg">
                                    <div class="text-sm text-blue-800">
                                        <strong>No. Pedimento:</strong> <span id="display-pedimento-no"></span><br>
                                        <strong>Clave:</strong> <span id="display-pedimento-clave"></span><br>
                                        <strong>Cliente:</strong> <span id="display-pedimento-cliente"></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Estado de Pago *</label>
                                    <select id="pedimento-individual-estado" name="estado_pago" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="pendiente">Por Pagar</option>
                                        <option value="pagado">✅ Pagado</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha de Pago</label>
                                    <input type="date" id="pedimento-individual-fecha-pago" name="fecha_pago" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Monto</label>
                                        <input type="number" step="0.01" min="0" id="pedimento-individual-monto" name="monto" 
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                               placeholder="0.00">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Moneda</label>
                                        <select id="pedimento-individual-moneda" name="moneda" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="MXN">💵 MXN - Peso Mexicano</option>
                                            <option value="USD">💰 USD - Dólar Estadounidense</option>
                                            <option value="EUR">💶 EUR - Euro</option>
                                            <option value="CAD">🍁 CAD - Dólar Canadiense</option>
                                            <option value="GBP">💷 GBP - Libra Esterlina</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                                    <textarea id="pedimento-individual-observaciones" name="observaciones" rows="3" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                              placeholder="Notas sobre el pago del pedimento..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center items-center gap-2 rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Guardar Cambios
                    </button>
                    <button type="button" onclick="cerrarModalPedimentoIndividual()" class="mt-3 w-full inline-flex justify-center items-center gap-2 rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/Logistica/pedimentos.js') }}?v={{ time() }}"></script>
@endpush
