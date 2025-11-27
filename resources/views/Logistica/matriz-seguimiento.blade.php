@extends('layouts.erp')

@section('title', 'Matriz de Seguimiento - Logística')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/logistica/matriz-seguimiento.css') }}">
@endpush

@push('scripts')
    <script>
        // Variable global para transportes
        window.transportes = @json($transportes->groupBy('tipo_operacion'));
    </script>
    <script src="{{ asset('js/logistica/matriz-seguimiento.js') }}?v={{ md5(time()) }}"></script>
@endpush

@section('content')
    <main class="relative overflow-hidden bg-gradient-to-br from-white via-blue-50 to-blue-100 min-h-screen">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-32 -left-20 w-96 h-96 bg-blue-200/40 blur-3xl rounded-full"></div>
            <div class="absolute top-40 -right-24 w-96 h-96 bg-blue-300/30 blur-3xl rounded-full"></div>
        </div>

        <div class="relative max-w-full mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <a href="{{ route('logistica.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Regresar
                    </a>
                </div>
                <h1 class="text-3xl font-bold text-slate-900">Matriz de Seguimiento</h1>
                <p class="text-slate-600 mt-2">Control y seguimiento de operaciones logísticas con cálculo automático de días de tránsito</p>
            </div>

            <!-- Controles -->
            <div class="mb-6 bg-white/90 backdrop-blur rounded-2xl border border-blue-100 shadow-lg p-6">
                <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                    <div class="flex flex-wrap gap-3">
                        <button onclick="abrirModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Nueva Operación
                        </button>
                        <button class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                            Importar
                        </button>
                        <button onclick="recalcularTodosLosStatus()" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-xl hover:bg-orange-700 transition-colors shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Recalcular Status
                        </button>
                        <button onclick="abrirModalPostOperaciones()" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            Gestionar Post-Operaciones
                        </button>
                        <button onclick="abrirModalReportes()" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Generar Reportes Word
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" placeholder="Buscar..." class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                        <button class="px-4 py-2 bg-slate-100 border border-slate-300 rounded-xl hover:bg-slate-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla Principal -->
            <div class="table-container rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="table-header">
                            <tr>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[50px]">No.</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Ejecutivo</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Operación</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Cliente</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Proveedor o Cliente</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Fecha de Embarque</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">No. De Factura</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">T. Operación</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[80px]">Clave</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Referencia Interna</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Aduana</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[80px]">A.A</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Referencia A.A</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[80px]">No Ped</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Transporte</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Fecha de Arribo a Aduana</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Guía //BL</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Status</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Fecha de Modulación</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Fecha de Arribo a Planta</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Resultado</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[80px]">Target</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Días en Tránsito</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Post-Operaciones</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Comentarios</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200" id="operacionesTable">
                            @forelse($operaciones as $operacion)
                            <tr class="table-row" data-operacion-id="{{ $operacion->id }}">
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->id }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-900 font-medium">{{ $operacion->ejecutivo ?? 'Sin asignar' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->operacion ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->cliente ?? 'Sin cliente' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->proveedor_o_cliente ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->fecha_embarque ? $operacion->fecha_embarque->format('d/m/Y') : '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->no_factura ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->tipo_operacion_enum ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->clave ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->referencia_interna ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->aduana ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->agente_aduanal ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->referencia_aa ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->no_pedimento ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->transporte ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->fecha_arribo_aduana ? $operacion->fecha_arribo_aduana->format('d/m/Y') : '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->guia_bl ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200">
                                    <div class="flex flex-col space-y-1">
                                        <!-- Status Manual (prevalece si está en Done) -->
                                        @if($operacion->status_manual === 'Done')
                                            <span class="status-badge status-verde text-xs">
                                                ✓ Done (Manual)
                                            </span>
                                        @else
                                            <!-- Status Automático -->
                                            <span class="status-badge {{
                                                $operacion->color_status === 'verde' ? 'status-verde' :
                                                ($operacion->color_status === 'amarillo' ? 'status-amarillo' :
                                                ($operacion->color_status === 'rojo' ? 'status-rojo' : 'status-sin-fecha'))
                                            }} text-xs">
                                                @php
                                                    $statusDisplay = match($operacion->status_calculado) {
                                                        'In Process' => 'En Proceso',
                                                        'Out of Metric' => 'Fuera de Métrica',
                                                        'Done' => 'Completado',
                                                        default => $operacion->status_calculado ?? 'En Proceso'
                                                    };
                                                @endphp
                                                {{ $statusDisplay }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                Manual: {{ $operacion->status_manual ?? 'In Process' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->fecha_modulacion ? $operacion->fecha_modulacion->format('d/m/Y') : '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->fecha_arribo_planta ? $operacion->fecha_arribo_planta->format('d/m/Y') : '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->resultado ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->target ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-center">
                                    @if($operacion->dias_transito !== null)
                                        <span class="dias-indicator {{
                                            $operacion->color_status === 'verde' ? 'dias-verde' :
                                            ($operacion->color_status === 'amarillo' ? 'dias-amarillo' : 'dias-rojo')
                                        }}">
                                            {{ abs($operacion->dias_transito) }} días
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-4 border-r border-slate-200 text-center">
                                    <button onclick="verPostOperaciones({{ $operacion->id }})"
                                            class="action-button btn-view"
                                            title="Ver post-operaciones">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                        </svg>
                                    </button>
                                </td>
                                <td class="px-3 py-4 border-r border-slate-200 text-center">
                                    <button onclick="verComentarios({{ $operacion->id }})"
                                            class="action-button btn-view"
                                            title="Ver comentarios">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    </button>
                                </td>
                                <td class="px-3 py-4 border-r border-slate-200">
                                    <div class="flex space-x-1">
                                        <button onclick="verHistorial({{ $operacion->id }})"
                                                class="action-button btn-view"
                                                title="Ver historial">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        @if($operacion->status_manual !== 'Done')
                                        <button onclick="marcarComoDone({{ $operacion->id }})"
                                                class="action-button btn-edit"
                                                title="Marcar como Done (Manual)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                        @endif

                                        <button onclick="generarReporteIndividual({{ $operacion->id }})"
                                                class="action-button btn-view"
                                                title="Generar reporte Word">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </button>

                                        <button onclick="eliminarOperacion({{ $operacion->id }})"
                                                class="action-button btn-delete"
                                                title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="25" class="px-3 py-8 text-center text-slate-500">
                                    <div class="flex flex-col items-center space-y-2">
                                        <i class="fas fa-inbox text-3xl text-slate-400"></i>
                                        <p class="text-sm font-medium">No hay operaciones registradas</p>
                                        <p class="text-xs">Haga clic en "Nueva Operación" para comenzar</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>



            <!-- Footer/Paginación -->
            <div class="mt-6 bg-white/90 backdrop-blur rounded-2xl border border-blue-100 shadow-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-600">
                        Mostrando operaciones con días de tránsito calculados automáticamente
                    </div>
                    <div class="flex gap-2 text-xs flex-wrap">
                        <span class="status-badge status-verde">✓ Done Manual: Completado por usuario</span>
                        <span class="status-badge status-amarillo">En Proceso: Días ≤ target desde aduana</span>
                        <span class="status-badge status-rojo">Fuera Métrica: Días > target desde aduana</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para Ver Historial -->
    <div id="modalHistorial" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-6xl max-h-[90vh] flex flex-col">
            <!-- Header fijo -->
            <div class="bg-white border-b border-slate-200 p-4 flex justify-between items-center rounded-t-xl">
                <h2 class="text-lg font-semibold text-slate-800">
                    <span class="text-blue-600 mr-2 text-xl">📊</span>
                    Historial de Operación
                </h2>
                <button onclick="cerrarModalHistorial()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">
                    <span>&times;</span>
                </button>
            </div>

            <!-- Contenido del historial con scroll -->
            <div class="flex-1 overflow-y-auto p-4">
                <div id="historialContent">
                    <div class="text-center py-8">
                        <div class="loading-spinner"></div>
                        <p class="text-slate-500 mt-2">Cargando historial...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Añadir Post-Operación -->
    <div id="modalPostOperacion" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
            <!-- Header fijo -->
            <div class="bg-white border-b border-slate-200 p-4 flex justify-between items-center rounded-t-xl">
                <h2 class="text-lg font-semibold text-slate-800">
                    <span class="text-green-600 mr-2 text-xl">➕</span>
                    Añadir Post-Operación
                </h2>
                <button onclick="cerrarModalPostOperacion()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">
                    <span>&times;</span>
                </button>
            </div>

            <!-- Contenido del formulario -->
            <div class="flex-1 overflow-y-auto p-4">
                <form id="formPostOperacion" onsubmit="guardarPostOperacion(event)" class="space-y-4">
                    @csrf

                    <!-- Nombre de Post-Operación -->
                    <div>
                        <label for="nombre_post_operacion" class="block text-sm font-medium text-slate-700 mb-1">
                            Nombre de Post-Operación <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre" id="nombre_post_operacion" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ej: Entrega de documentos, Revisión final...">
                    </div>

                    <!-- Operación Relacionada -->
                    <div>
                        <label for="operacion_relacionada" class="block text-sm font-medium text-slate-700 mb-1">
                            Operación Relacionada
                        </label>
                        <select name="operacion_logistica_id" id="operacion_relacionada"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Sin operación específica</option>
                            @foreach($operaciones as $operacion)
                                <option value="{{ $operacion->id }}">
                                    {{ $operacion->operacion ?? 'Operación #' . $operacion->id }} - {{ $operacion->cliente ?? 'Sin cliente' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="descripcion_post_operacion" class="block text-sm font-medium text-slate-700 mb-1">
                            Descripción
                        </label>
                        <textarea name="descripcion" id="descripcion_post_operacion" rows="3"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Descripción detallada de la post-operación..."></textarea>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" onclick="cerrarModalPostOperacion()"
                                class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Guardar Post-Operación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Añadir Nueva Operación -->
    <div id="modalOperacion" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
            <!-- Header fijo -->
            <div class="bg-white border-b border-slate-200 p-4 flex justify-between items-center rounded-t-xl">
                    <h2 class="text-lg font-semibold text-slate-800">
                        <span class="text-blue-600 mr-2 text-xl">⊕</span>
                        Añadir Nueva Operación
                    </h2>
                    <button onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">
                        <span>&times;</span>
                    </button>
            </div>

            <!-- Contenido del formulario con scroll -->
            <div class="flex-1 overflow-y-auto p-4">
                <form id="formOperacion" class="space-y-4">
                        @csrf

                        <!-- Información Básica -->
                        <div class="form-section">
                            <h3>Información Básica</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Operación *</label>
                                    <select name="operacion" required class="form-input">
                                        <option value="">Seleccionar...</option>
                                        <option value="EXPORTACION">Exportación</option>
                                        <option value="IMPORTACION">Importación</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">T. Operación *</label>
                                    <select name="tipo_operacion_enum" required onchange="actualizarTransportes(); calcularTargetAutomatico();" class="form-input">
                                        <option value="">Seleccionar...</option>
                                        <option value="Terrestre" data-target="3">Terrestre (Land)</option>
                                        <option value="Aerea" data-target="3">Aérea (Air)</option>
                                        <option value="Ferrocarril" data-target="3">Ferrocarril (Railway)</option>
                                        <option value="Maritima" data-target="7">Marítima (Sea)</option>
                                    </select>
                                </div>
                                <div>
                                    <div class="bg-blue-50 p-3 rounded-lg">
                                        <p class="text-sm text-blue-700 font-medium">📊 Control de Status</p>
                                        <p class="text-xs text-blue-600">El status se calculará automáticamente según las fechas ingresadas</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cliente y Ejecutivo -->
                        <div class="form-section" style="background: #dbeafe;">
                            <h3>Cliente y Ejecutivo</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-sm font-medium text-slate-700">Cliente *</label>
                                        <button type="button" onclick="mostrarNuevoCliente()"
                                                class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                            + Nuevo cliente
                                        </button>
                                    </div>
                                    <input type="text" name="cliente" required class="form-input" placeholder="Nombre del cliente" list="clientesList">
                                    <datalist id="clientesList">
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->cliente }}">
                                        @endforeach
                                    </datalist>
                                    <!-- Formulario para nuevo cliente -->
                                    <div id="nuevoClienteForm" class="hidden mt-2 p-3 bg-white border rounded-lg">
                                        <input type="text" id="nuevoClienteNombre" placeholder="Nombre del nuevo cliente" class="form-input mb-2">
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="guardarNuevoCliente()"
                                                    class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 flex items-center">
                                                    <span class="mr-1 font-bold">+</span>Guardar</button>
                                            <button type="button" onclick="cancelarNuevoCliente()"
                                                    class="px-3 py-1 bg-gray-600 text-white rounded text-sm">Cancelar</button>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Ejecutivo *</label>
                                    <input type="text" name="ejecutivo" required class="form-input" placeholder="Nombre del ejecutivo" list="ejecutivosList">
                                    <datalist id="ejecutivosList">
                                        @foreach($empleados as $empleado)
                                            <option value="{{ $empleado->nombre }}">
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>
                        </div>

                        <!-- Fechas Iniciales -->
                        <div class="form-section" style="background: #f0fdf4;">
                            <h3>📅 Fechas Iniciales (Solo la obligatoria)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-1 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Embarque *</label>
                                    <input type="date" name="fecha_embarque" required class="form-input">
                                    <p class="text-xs text-green-600 mt-1">✓ Esta es la única fecha obligatoria al crear la operación</p>
                                </div>
                            </div>
                        </div>

                        <!-- Información Inicial Obligatoria -->
                        <div class="form-section" style="background: #fef3c7;">
                            <h3>📋 Información Inicial Obligatoria</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Proveedor/Cliente *</label>
                                    <input type="text" name="proveedor_o_cliente" required class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">No. Factura *</label>
                                    <input type="text" name="no_factura" required class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Clave *</label>
                                    <input type="text" name="clave" required class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Referencia Interna *</label>
                                    <input type="text" name="referencia_interna" required class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Aduana *</label>
                                    <input type="text" name="aduana" required class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Target (días)</label>
                                    <input type="number" name="target" min="0" readonly class="form-input bg-gray-100" title="Se calcula automáticamente según el tipo de operación">
                                    <p class="text-xs text-gray-600 mt-1">✓ Automático: Terrestre/Aérea/Tren=3 días, Marítima=7 días</p>
                                </div>
                            </div>
                        </div>

                        <!-- Agente Aduanal -->
                        <div class="form-section" style="background: #fef3c7;">
                            <h3>Agente Aduanal</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-sm font-medium text-slate-700">Agente Aduanal</label>
                                        <button type="button" onclick="mostrarNuevoAgente()"
                                                class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                            + Nuevo agente
                                        </button>
                                    </div>
                                    <input type="text" name="agente_aduanal" class="form-input" placeholder="Nombre del agente aduanal" list="agentesList">
                                    <datalist id="agentesList">
                                        @foreach($agentesAduanales as $agente)
                                            <option value="{{ $agente->agente_aduanal }}">
                                        @endforeach
                                    </datalist>
                                    <!-- Formulario para nuevo agente -->
                                    <div id="nuevoAgenteForm" class="hidden mt-2 p-3 bg-white border rounded-lg">
                                        <input type="text" id="nuevoAgenteNombre" placeholder="Nombre del nuevo agente aduanal" class="form-input mb-2">
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="guardarNuevoAgente()"
                                                    class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 flex items-center">
                                                    <span class="mr-1 font-bold">+</span>Guardar</button>
                                            <button type="button" onclick="cancelarNuevoAgente()"
                                                    class="px-3 py-1 bg-gray-600 text-white rounded text-sm">Cancelar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transporte -->
                        <div class="form-section" style="background: #ecfdf5;">
                            <h3>Transporte</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-sm font-medium text-slate-700">Transporte</label>
                                        <button type="button" onclick="mostrarNuevoTransporte()"
                                                class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                            + Nuevo transporte
                                        </button>
                                    </div>
                                    <input type="text" name="transporte" class="form-input" placeholder="Nombre del transporte" list="transportesList">
                                    <datalist id="transportesList">
                                        @foreach($transportes as $transporte)
                                            <option value="{{ $transporte->transporte }}" data-tipo="{{ $transporte->tipo_operacion }}">
                                        @endforeach
                                    </datalist>
                                    <!-- Formulario para nuevo transporte -->
                                    <div id="nuevoTransporteForm" class="hidden mt-2 p-3 bg-white border rounded-lg">
                                        <input type="text" id="nuevoTransporteNombre" placeholder="Nombre del nuevo transporte" class="form-input mb-2">
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="guardarNuevoTransporte()"
                                                    class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 flex items-center">
                                                    <span class="mr-1 font-bold">+</span>Guardar</button>
                                            <button type="button" onclick="cancelarNuevoTransporte()"
                                                    class="px-3 py-1 bg-gray-600 text-white rounded text-sm">Cancelar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información Posterior (NO OBLIGATORIA AL INICIO) -->
                        <div class="form-section" style="background: #f3e8ff; border: 2px dashed #a855f7;">
                            <h3>🔄 Información Posterior (Opcional al crear)</h3>
                            <div class="bg-purple-50 p-3 rounded-lg mb-4">
                                <p class="text-sm text-purple-700 font-medium">ℹ️ Estos campos se llenan durante el proceso</p>
                                <p class="text-xs text-purple-600">Puede crear la operación sin estos datos y actualizarlos después</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Arribo Aduana</label>
                                    <input type="date" name="fecha_arribo_aduana" class="form-input">
                                    <p class="text-xs text-gray-500">Se llena cuando llega la carga</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Modulación</label>
                                    <input type="date" name="fecha_modulacion" class="form-input">
                                    <p class="text-xs text-gray-500">Cuando A.A procesa pedimento</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Arribo a Planta</label>
                                    <input type="date" name="fecha_arribo_planta" class="form-input">
                                    <p class="text-xs text-gray-500">Cuando se entrega al cliente</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">No. Pedimento</label>
                                    <input type="text" name="no_pedimento" class="form-input">
                                    <p class="text-xs text-gray-500">Solo después de modulación</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Referencia A.A</label>
                                    <input type="text" name="referencia_aa" class="form-input">
                                    <p class="text-xs text-gray-500">Referencia del agente</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Guía/BL</label>
                                    <input type="text" name="guia_bl" class="form-input">
                                    <p class="text-xs text-gray-500">Documento de transporte</p>
                                </div>
                            </div>

                            <!-- Campo de Comentarios -->
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Comentarios Iniciales</label>
                                <textarea name="comentarios" rows="2" class="form-input w-full"
                                         placeholder="Comentarios opcionales al crear la operación..."></textarea>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button type="button" onclick="cerrarModal()"
                                    class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Guardar Operación
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Post-Operaciones -->
    <div id="modalPostOperaciones" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
            <!-- Header fijo -->
            <div class="bg-white border-b border-slate-200 p-4 flex justify-between items-center rounded-t-xl">
                <h2 class="text-lg font-semibold text-slate-800">
                    <span class="text-purple-600 mr-2 text-xl">📋</span>
                    Post-Operaciones - Operación #<span id="operacionIdPostOp"></span>
                </h2>
                <button onclick="cerrarModalPostOperaciones()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">
                    <span>&times;</span>
                </button>
            </div>

            <!-- Contenido -->
            <div class="flex-1 overflow-y-auto p-4">
                <!-- Información -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                        <div>
                            <h4 class="text-blue-800 font-semibold mb-1">Gestión de Post-Operaciones</h4>
                            <p class="text-blue-700 text-sm">
                                Aquí puede actualizar el estado de las post-operaciones asignadas a esta operación específica.
                                Los cambios se guardan por operación usando el número de pedimento.
                            </p>
                        </div>
                    </div>
                </div>

                <div id="contenidoPostOperaciones">
                    <!-- Se carga dinámicamente -->
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-slate-50 border-t border-slate-200 p-4 flex justify-between items-center rounded-b-xl">
                <button onclick="cerrarModalPostOperaciones()" class="px-4 py-2 text-slate-600 hover:text-slate-800 transition-colors">
                    Cerrar
                </button>
                <button id="guardarCambiosPostOperaciones" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para Ver/Editar Comentarios -->
    <div id="modalComentarios" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col">
            <!-- Header fijo -->
            <div class="bg-white border-b border-slate-200 p-4 flex justify-between items-center rounded-t-xl">
                <h2 class="text-lg font-semibold text-slate-800">
                    <span class="text-green-600 mr-2 text-xl">💬</span>
                    Comentarios - Operación #<span id="operacionIdComentarios"></span>
                </h2>
                <button onclick="cerrarModalComentarios()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">
                    <span>&times;</span>
                </button>
            </div>

            <!-- Contenido -->
            <div class="flex-1 overflow-y-auto p-4">
                <!-- Comentarios actuales -->
                <div class="mb-6">
                    <h3 class="text-md font-semibold text-slate-800 mb-3">Comentarios Actuales</h3>
                    <div id="listaComentarios" class="space-y-3">
                        <!-- Se carga dinámicamente -->
                    </div>
                </div>

                <!-- Formulario para añadir/editar comentario -->
                <div class="p-4 border-t border-slate-200">
                    <h3 class="text-md font-semibold text-slate-800 mb-3">
                        <span id="tituloComentario">Añadir Comentario</span>
                    </h3>
                    <form id="formComentario">
                        <input type="hidden" id="comentarioId" value="">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Comentario <span class="text-red-500">*</span>
                            </label>
                            <textarea id="textoComentario"
                                     name="comentario"
                                     rows="4"
                                     required
                                     class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                     placeholder="Escriba su comentario aquí..."></textarea>
                        </div>
                        <div class="flex justify-end mt-4 space-x-3">
                            <button type="button" onclick="cancelarEdicionComentario()" id="btnCancelarComentario" class="bg-slate-600 text-white px-4 py-2 rounded-lg hover:bg-slate-700 transition-colors hidden">
                                <i class="fas fa-times mr-2"></i>
                                Cancelar
                            </button>
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-comment mr-2"></i>
                                <span id="textoBotonComentario">Guardar Comentario</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Global para Gestionar Post-Operaciones -->
    <div id="modalGestionPostOp" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
            <!-- Header fijo -->
            <div class="bg-white border-b border-slate-200 p-4 flex justify-between items-center rounded-t-xl">
                <h2 class="text-lg font-semibold text-slate-800">
                    <span class="text-purple-600 mr-2 text-xl">🔧</span>
                    Gestionar Post-Operaciones Globales
                </h2>
                <button onclick="cerrarModalGestionPostOp()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">
                    <span>&times;</span>
                </button>
            </div>

            <!-- Contenido -->
            <div class="flex-1 overflow-y-auto p-4">
                <p class="text-slate-600 mb-4">Desde aquí puede crear post-operaciones estándar que estarán disponibles para todas las operaciones.</p>

                <!-- Lista de post-operaciones globales -->
                <div class="mb-6">
                    <h3 class="text-md font-semibold text-slate-800 mb-3">Post-Operaciones Disponibles</h3>
                    <div id="listaPostOpGlobales" class="space-y-2">
                        <!-- Se carga dinámicamente -->
                    </div>
                </div>

                <!-- Formulario para crear nueva post-operación global -->
                <div class="p-4 border-t border-slate-200">
                    <h3 class="text-md font-semibold text-slate-800 mb-3">Crear Nueva Post-Operación</h3>
                    <form id="formPostOpGlobal">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="nombrePostOpGlobal"
                                       name="nombre"
                                       required
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       placeholder="Ej: Revisión de documentos">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Descripción
                                </label>
                                <textarea id="descripcionPostOpGlobal"
                                         name="descripcion"
                                         rows="3"
                                         class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                         placeholder="Descripción detallada..."></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end mt-4">
                            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Crear Post-Operación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Generar Reportes Word -->
    <div id="modalReportes" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="bg-white border-b border-slate-200 p-4 flex justify-between items-center rounded-t-xl">
                <h2 class="text-lg font-semibold text-slate-800">
                    <span class="text-red-600 mr-2 text-xl">📄</span>
                    Generar Reportes Word
                </h2>
                <button onclick="cerrarModalReportes()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">
                    <span>&times;</span>
                </button>
            </div>

            <!-- Contenido -->
            <div class="flex-1 overflow-y-auto p-6">
                <div class="space-y-6">
                    <!-- Opción: Reporte Multiple con Filtros -->
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Reporte Múltiple con Filtros
                        </h3>
                        
                        <form id="formReporteMultiple">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Filtro Cliente -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Cliente</label>
                                    <select name="cliente_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Todos los clientes</option>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->id }}">{{ $cliente->cliente }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Filtro Status -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                                    <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Todos los status</option>
                                        <option value="Done">Done</option>
                                        <option value="En Proceso">En Proceso</option>
                                        <option value="Fuera Métrica">Fuera Métrica</option>
                                    </select>
                                </div>

                                <!-- Filtro Fecha Desde -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Desde</label>
                                    <input type="date" name="fecha_desde" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <!-- Filtro Fecha Hasta -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Hasta</label>
                                    <input type="date" name="fecha_hasta" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Generar Reporte Múltiple
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Separador -->
                    <div class="text-center">
                        <span class="text-slate-400 text-sm">o también puedes</span>
                    </div>

                    <!-- Opción: Todas las Operaciones -->
                    <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                        <h3 class="text-lg font-semibold text-green-800 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            Reporte de Todas las Operaciones
                        </h3>
                        <p class="text-green-700 text-sm mb-3">Genera un reporte con todas las operaciones (máximo 100 registros más recientes)</p>
                        <button onclick="generarReporteTodas()" class="inline-flex items-center px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Generar Reporte Completo
                        </button>
                    </div>

                    <!-- Nota informativa -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-yellow-800">Información sobre los reportes</h4>
                                <ul class="text-xs text-yellow-700 mt-1 space-y-1">
                                    <li>• Los reportes incluyen información completa de las operaciones</li>
                                    <li>• Se incluyen post-operaciones e historial cuando estén disponibles</li>
                                    <li>• Los archivos se descargan automáticamente en formato .docx</li>
                                    <li>• Para reportes individuales, usa el botón 📄 en cada fila de la tabla</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
