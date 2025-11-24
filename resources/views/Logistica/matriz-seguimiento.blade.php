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
                <p class="text-slate-600 mt-2">Control y seguimiento de operaciones logísticas con status automático</p>
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
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Status Manual</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Status Automático</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Días Transcurridos</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Fecha de Modulación</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Fecha de Arribo a Planta</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Resultado</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[80px]">Target</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Días en Tránsito</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200" id="operacionesTable">
                            @forelse($operaciones as $operacion)
                            <tr class="table-row">
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
                                    <span class="status-badge status-sin-fecha">
                                        {{ $operacion->status_enum ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 border-r border-slate-200">
                                    <span class="status-badge {{ 
                                        $operacion->color_status === 'verde' ? 'status-verde' : 
                                        ($operacion->color_status === 'amarillo' ? 'status-amarillo' : 
                                        ($operacion->color_status === 'rojo' ? 'status-rojo' : 'status-sin-fecha')) 
                                    }}">
                                        {{ 
                                            $operacion->color_status === 'verde' ? 'Completado' : 
                                            ($operacion->color_status === 'amarillo' ? 'En Proceso' : 
                                            ($operacion->color_status === 'rojo' ? 'Fuera de Métrica' : 'Sin Fecha')) 
                                        }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 border-r border-slate-200 text-center">
                                    @if($operacion->dias_transcurridos_calculados !== null)
                                        <span class="dias-indicator {{ 
                                            $operacion->color_status === 'verde' ? 'dias-verde' : 
                                            ($operacion->color_status === 'amarillo' ? 'dias-amarillo' : 'dias-rojo') 
                                        }}">
                                            {{ $operacion->dias_transcurridos_calculados }} días
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->fecha_modulacion ? $operacion->fecha_modulacion->format('d/m/Y') : '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->fecha_arribo_planta ? $operacion->fecha_arribo_planta->format('d/m/Y') : '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->resultado ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->target ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->dias_transito ?? '-' }}</td>
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
                                        @if($operacion->status_calculado !== 'Done')
                                        <button onclick="editarOperacion({{ $operacion->id }})" 
                                                class="action-button btn-edit" 
                                                title="Marcar como Done">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                        @endif
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
                                <td colspan="26" class="px-3 py-8 text-center text-slate-500">
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
                        Mostrando operaciones con status automático calculado
                    </div>
                    <div class="flex gap-2 text-xs">
                        <span class="status-badge status-verde">Verde: Completado</span>
                        <span class="status-badge status-amarillo">Amarillo: En Proceso</span>
                        <span class="status-badge status-rojo">Rojo: Fuera de Métrica</span>
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
                                        <p class="text-sm text-blue-700 font-medium">📊 Status Automático</p>
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

                        <!-- Fechas Importantes -->
                        <div class="form-section" style="background: #f0fdf4;">
                            <h3>Fechas Importantes (Afectan el cálculo automático)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Embarque *</label>
                                    <input type="date" name="fecha_embarque" required class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Arribo Aduana *</label>
                                    <input type="date" name="fecha_arribo_aduana" onchange="calcularResultado()" class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Modulación</label>
                                    <input type="date" name="fecha_modulacion" onchange="calcularResultado()" class="form-input">
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="form-section">
                            <h3>Información Adicional</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Proveedor/Cliente</label>
                                    <input type="text" name="proveedor_o_cliente" class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">No. Factura</label>
                                    <input type="text" name="no_factura" class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Clave</label>
                                    <input type="text" name="clave" class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Referencia Interna</label>
                                    <input type="text" name="referencia_interna" class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Aduana</label>
                                    <input type="text" name="aduana" class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Referencia A.A</label>
                                    <input type="text" name="referencia_aa" class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">No. Pedimento</label>
                                    <input type="text" name="no_pedimento" class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Guía/BL</label>
                                    <input type="text" name="guia_bl" class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Target (días)</label>
                                    <input type="number" name="target" min="0" readonly class="form-input bg-gray-100" title="Se calcula automáticamente según el tipo de operación">
                                    <p class="text-xs text-gray-600 mt-1">Se asigna automáticamente: Terrestre/Aérea/Tren=2-3 días, Marítima=5-7 días</p>
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

                        <!-- Fechas Adicionales -->
                        <div class="form-section" style="background: #f3e8ff;">
                            <h3>Fechas Adicionales</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Arribo a Planta</label>
                                    <input type="date" name="fecha_arribo_planta" onchange="calcularDiasTransito()" class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Días en Tránsito</label>
                                    <input type="number" name="dias_transito" min="0" readonly class="form-input bg-gray-100" title="Se calcula automáticamente">
                                </div>
                            </div>
                        </div>

                        <!-- Resultados y Métricas -->
                        <div class="form-section" style="background: #fef2f2;">
                            <h3>Resultados y Métricas</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Resultado (días)</label>
                                    <input type="number" name="resultado" min="0" readonly class="form-input bg-gray-100" title="Se calcula automáticamente entre arribo aduana y modulación">
                                </div>
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
@endsection