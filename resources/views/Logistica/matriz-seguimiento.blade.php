@extends('layouts.erp')

@section('title', 'Matriz de Seguimiento - Logística')

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
                <p class="text-slate-600 mt-2">Control y seguimiento de operaciones logísticas</p>
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
                        <button class="inline-flex items-center px-4 py-2 bg-slate-600 text-white rounded-xl hover:bg-slate-700 transition-colors shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Exportar
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
            <div class="bg-white/90 backdrop-blur rounded-2xl border border-blue-100 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
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
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Status</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Fecha de Modulación</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Fecha de Arribo a Planta</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Resultado</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[80px]">Target</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Días en Tránsito</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Pendientes Pos-Operaciones</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 min-w-[200px]">Comentarios</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200" id="operacionesTable">
                            @forelse($operaciones as $operacion)
                            <tr class="hover:bg-blue-50/50 transition-colors">
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->id }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-900 font-medium">{{ $operacion->ejecutivo->nombre ?? 'Sin asignar' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->numero_operacion }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->cliente->cliente }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->proveedor }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->fecha_factura ? $operacion->fecha_factura->format('d/m/Y') : '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->numero_factura }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->tipo_operacion }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->referencia_cliente }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->referencia_interna }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->aduana }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->agenteAduanal->agente_aduanal }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->referencia_aa }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->pedimento }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->transporte->transporte }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->fecha_entrega ? $operacion->fecha_entrega->format('d/m/Y') : '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->numero_guia }}</td>
                                <td class="px-3 py-4 border-r border-slate-200">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $operacion->getStatusColor() }}">
                                        {{ $operacion->status }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->fecha_arribo_aduana ? $operacion->fecha_arribo_aduana->format('d/m/Y') : '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->fecha_modulacion ? $operacion->fecha_modulacion->format('d/m/Y') : '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->resultado_previo }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->dias_transito }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->resultado ?? '-' }}</td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->incidencias ?? '-' }}</td>
                                <td class="px-3 py-4 text-slate-600">{{ $operacion->observaciones ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="25" class="px-3 py-8 text-center text-slate-500">
                                    <div class="flex flex-col items-center space-y-2">
                                        <i class="fas fa-inbox text-3xl text-slate-400"></i>
                                        <p class="text-sm font-medium">No hay operaciones registradas</p>
                                        <p class="text-xs">Haga clic en "Añadir Nueva Operación" para comenzar</p>
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
                        Mostrando <span class="font-medium">1-10</span> de <span class="font-medium">10</span> operaciones
                    </div>
                    <div class="flex gap-2">
                        <button class="px-3 py-2 text-slate-400 border border-slate-300 rounded-lg cursor-not-allowed">
                            Anterior
                        </button>
                        <button class="px-3 py-2 bg-blue-600 text-white border border-blue-600 rounded-lg">
                            1
                        </button>
                        <button class="px-3 py-2 text-slate-400 border border-slate-300 rounded-lg cursor-not-allowed">
                            Siguiente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para Añadir Nueva Operación -->
    <div id="modalOperacion" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
        <div class="flex items-start justify-center min-h-screen p-4 pt-8">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[85vh] overflow-hidden">
                <div class="max-h-[85vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-slate-200 p-4 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-slate-800">
                        <span class="text-blue-600 mr-2 text-xl">⊕</span>
                        Añadir Nueva Operación
                    </h2>
                    <button onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="p-4">

                    <form id="formOperacion" class="space-y-4">
                        @csrf
                        
                        <!-- Información Básica -->
                        <div class="bg-slate-50 rounded-lg p-3">
                            <h3 class="text-base font-medium text-slate-700 mb-3">Información Básica</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Operación *</label>
                                    <input type="text" name="operacion" required 
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">T. Operación *</label>
                                    <select name="operacion_tipo" required onchange="actualizarTransportes()" 
                                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Seleccionar...</option>
                                        <option value="EXPORTACION">Exportación</option>
                                        <option value="IMPORTACION">Importación</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Status *</label>
                                    <select name="status" required 
                                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Seleccionar...</option>
                                        <option value="En Proceso">En Proceso</option>
                                        <option value="Completado">Completado</option>
                                        <option value="Cancelado">Cancelado</option>
                                        <option value="Pendiente">Pendiente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Cliente y Ejecutivo -->
                        <div class="bg-blue-50 rounded-lg p-3">
                            <h3 class="text-base font-medium text-slate-700 mb-3">Cliente y Ejecutivo</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Cliente *</label>
                                    <div class="flex gap-2">
                                        <select name="cliente_id" required class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Seleccionar cliente...</option>
                                            @foreach($clientes as $cliente)
                                                <option value="{{ $cliente->id }}">{{ $cliente->cliente }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" onclick="mostrarNuevoCliente()" 
                                                class="flex-shrink-0 w-10 h-10 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center justify-center text-lg font-bold">
                                            <span>+</span>
                                        </button>
                                    </div>
                                    <!-- Formulario para nuevo cliente -->
                                    <div id="nuevoClienteForm" class="hidden mt-2 p-3 bg-white border rounded-lg">
                                        <input type="text" id="nuevoClienteNombre" placeholder="Nombre del nuevo cliente"
                                               class="w-full border border-slate-300 rounded px-3 py-2 mb-2">
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
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Proveedor o Cliente</label>
                                    <input type="text" name="proveedor_o_cliente" 
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Ejecutivo</label>
                                    <select name="ejecutivo_empleado_id" 
                                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Seleccionar ejecutivo...</option>
                                        @foreach($empleados as $empleado)
                                            <option value="{{ $empleado->id }}">{{ $empleado->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Facturación y Embarque -->
                        <div class="bg-green-50 rounded-lg p-3">
                            <h3 class="text-base font-medium text-slate-700 mb-3">Facturación y Embarque</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de Embarque</label>
                                    <input type="date" name="fecha_embarque" 
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">No. De Factura</label>
                                    <input type="text" name="no_factura" 
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Clave</label>
                                    <input type="text" name="clave" 
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Referencias -->
                        <div class="bg-yellow-50 rounded-lg p-3">
                            <h3 class="text-base font-medium text-slate-700 mb-3">Referencias</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Referencia Interna</label>
                                    <input type="text" name="referencia_interna" 
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Referencia A.A</label>
                                    <input type="text" name="referencia_aa" 
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Agente Aduanal y Transporte -->
                        <div class="bg-purple-50 rounded-lg p-3">
                            <h3 class="text-base font-medium text-slate-700 mb-3">Agente Aduanal y Transporte</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Aduana</label>
                                    <input type="text" name="aduana" 
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">A.A (Agente Aduanal) *</label>
                                    <div class="flex gap-2">
                                        <select name="agente_aduanal_id" required class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Seleccionar agente...</option>
                                            @foreach($agentesAduanales as $agente)
                                                <option value="{{ $agente->id }}">{{ $agente->agente_aduanal }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" onclick="mostrarNuevoAgente()" 
                                                class="flex-shrink-0 w-10 h-10 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center justify-center text-lg font-bold">
                                            <span>+</span>
                                        </button>
                                    </div>
                                    <!-- Formulario para nuevo agente -->
                                    <div id="nuevoAgenteForm" class="hidden mt-2 p-3 bg-white border rounded-lg">
                                        <input type="text" id="nuevoAgenteNombre" placeholder="Nombre del nuevo agente aduanal"
                                               class="w-full border border-slate-300 rounded px-3 py-2 mb-2">
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="guardarNuevoAgente()" 
                                                    class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 flex items-center">
                                                    <span class="mr-1 font-bold">+</span>Guardar</button>
                                            <button type="button" onclick="cancelarNuevoAgente()" 
                                                    class="px-3 py-1 bg-gray-600 text-white rounded text-sm">Cancelar</button>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">No Ped (Pedimento)</label>
                                    <input type="text" name="no_pedimento" 
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Transporte *</label>
                                    <select name="transporte_id" required 
                                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Primero seleccione tipo de operación...</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Fechas y Documentos -->
                        <div class="bg-indigo-50 rounded-lg p-3">
                            <h3 class="text-base font-medium text-slate-700 mb-3">Fechas y Documentos</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de Arribo a Aduana</label>
                                    <input type="date" name="fecha_arribo_aduana" onchange="calcularResultado()"
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Guía //BL</label>
                                    <input type="text" name="guia_bl" 
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de Modulación</label>
                                    <input type="date" name="fecha_modulacion" onchange="calcularResultado()"
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Resultados y Métricas -->
                        <div class="bg-red-50 rounded-lg p-3">
                            <h3 class="text-base font-medium text-slate-700 mb-3">Resultados y Métricas</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de Arribo a Planta</label>
                                    <input type="date" name="fecha_arribo_planta" 
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Resultado (Días)</label>
                                    <input type="number" name="resultado" readonly 
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-gray-100">
                                    <small class="text-xs text-slate-500">Se calcula automáticamente</small>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Target</label>
                                    <input type="number" name="target" 
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Días en Tránsito</label>
                                    <input type="number" name="dias_transito" readonly
                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-gray-100">
                                </div>
                            </div>
                        </div>

                        <!-- Comentarios -->
                        <div class="bg-gray-50 rounded-lg p-3">
                            <h3 class="text-base font-medium text-slate-700 mb-3">Comentarios y Observaciones</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-3">Pendientes Pos-Operaciones</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="pendientes_pos_operaciones" value="1" 
                                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                            <span class="ml-2 text-sm text-gray-700">Completado / Sin pendientes</span>
                                        </label>
                                        <p class="text-xs text-gray-500 mt-1">Marque si no hay pendientes pos-operaciones o están completados</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Comentarios Generales</label>
                                    <textarea name="comentarios" rows="3" placeholder="Comentarios adicionales, observaciones especiales..."
                                              class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Botones fijos en la parte inferior -->
                <div class="sticky bottom-0 bg-white border-t border-slate-200 p-4 flex justify-end gap-3">
                    <button type="button" onclick="cerrarModal()" 
                            class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">
                        Cancelar
                    </button>
                    <button type="submit" form="formOperacion"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                        <span class="mr-2 font-bold">💾</span>
                        Guardar Operación
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
// Variables globales
let transportes = @json($transportes->groupBy('tipo_operacion'));

// Función para abrir el modal
function abrirModal() {
    document.getElementById('modalOperacion').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

// Función para cerrar el modal
function cerrarModal() {
    document.getElementById('modalOperacion').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('formOperacion').reset();
    // Ocultar formularios de nuevos elementos
    document.getElementById('nuevoClienteForm').classList.add('hidden');
    document.getElementById('nuevoAgenteForm').classList.add('hidden');
}

// Función para actualizar transportes según el tipo de operación
function actualizarTransportes() {
    const tipoOperacion = document.querySelector('select[name="tipo_operacion"]').value;
    const selectTransporte = document.querySelector('select[name="transporte_id"]');
    
    // Limpiar opciones
    selectTransporte.innerHTML = '<option value="">Seleccionar transporte...</option>';
    
    if (tipoOperacion && transportes[tipoOperacion]) {
        transportes[tipoOperacion].forEach(transporte => {
            const option = document.createElement('option');
            option.value = transporte.id;
            option.textContent = transporte.transporte;
            selectTransporte.appendChild(option);
        });
    }
}

// Función para calcular resultado automáticamente
function calcularResultado() {
    const fechaArribo = document.querySelector('input[name="fecha_arribo_aduana"]').value;
    const fechaModulacion = document.querySelector('input[name="fecha_modulacion"]').value;
    
    if (fechaArribo && fechaModulacion) {
        const fecha1 = new Date(fechaArribo);
        const fecha2 = new Date(fechaModulacion);
        const diferenciaTiempo = Math.abs(fecha2.getTime() - fecha1.getTime());
        const diferenciaDias = Math.ceil(diferenciaTiempo / (1000 * 3600 * 24));
        
        document.querySelector('input[name="resultado"]').value = diferenciaDias;
    }
}

// Funciones para nuevo cliente
function mostrarNuevoCliente() {
    document.getElementById('nuevoClienteForm').classList.toggle('hidden');
}

function cancelarNuevoCliente() {
    document.getElementById('nuevoClienteForm').classList.add('hidden');
    document.getElementById('nuevoClienteNombre').value = '';
}

function guardarNuevoCliente() {
    const nombre = document.getElementById('nuevoClienteNombre').value.trim();
    if (!nombre) {
        alert('Por favor ingrese el nombre del cliente');
        return;
    }

    fetch('{{ route("logistica.clientes.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ cliente: nombre })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Añadir el nuevo cliente al select
            const select = document.querySelector('select[name="cliente_id"]');
            const option = document.createElement('option');
            option.value = data.cliente.id;
            option.textContent = data.cliente.cliente;
            option.selected = true;
            select.appendChild(option);
            
            // Ocultar formulario
            cancelarNuevoCliente();
        } else {
            alert('Error al guardar el cliente: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar el cliente');
    });
}

// Funciones para nuevo agente
function mostrarNuevoAgente() {
    document.getElementById('nuevoAgenteForm').classList.toggle('hidden');
}

function cancelarNuevoAgente() {
    document.getElementById('nuevoAgenteForm').classList.add('hidden');
    document.getElementById('nuevoAgenteNombre').value = '';
}

function guardarNuevoAgente() {
    const nombre = document.getElementById('nuevoAgenteNombre').value.trim();
    if (!nombre) {
        alert('Por favor ingrese el nombre del agente aduanal');
        return;
    }

    fetch('{{ route("logistica.agentes.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ agente_aduanal: nombre })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Añadir el nuevo agente al select
            const select = document.querySelector('select[name="agente_aduanal_id"]');
            const option = document.createElement('option');
            option.value = data.agente.id;
            option.textContent = data.agente.agente_aduanal;
            option.selected = true;
            select.appendChild(option);
            
            // Ocultar formulario
            cancelarNuevoAgente();
        } else {
            alert('Error al guardar el agente: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar el agente aduanal');
    });
}

// Manejar envío del formulario
document.getElementById('formOperacion').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("logistica.operaciones.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Operación guardada exitosamente');
            cerrarModal();
            // Recargar la página para mostrar la nueva operación
            window.location.reload();
        } else {
            alert('Error al guardar la operación: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar la operación');
    });
});

// Event listeners para cerrar modal con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModal();
    }
});

// Cerrar modal al hacer clic fuera
document.getElementById('modalOperacion').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModal();
    }
});
</script>
@endpush