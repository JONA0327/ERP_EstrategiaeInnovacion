@extends('layouts.erp')

@section('title', 'Matriz de Seguimiento - Logística')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/Logistica/matriz-seguimiento.css') }}?v={{ md5(time()) }}">
@endpush

@push('scripts')
    <script>
        // Variable global para transportes
        window.transportes = @json($transportes->groupBy('tipo_operacion'));
        // Idioma de las columnas
        window.idiomaColumnas = '{{ $idiomaColumnas ?? "es" }}';
        // Nombres de las columnas según idioma
        window.nombresColumnas = @json($nombresColumnas ?? []);
        // Columnas ordenadas para el ejecutivo actual
        window.columnasOrdenadasConfig = @json($columnasOrdenadas ?? []);
        // ID del empleado actual para guardar configuración de columnas
        window.empleadoIdActual = {{ $empleadoActual ? $empleadoActual->id : 'null' }};
    </script>
    <script src="{{ asset('js/Logistica/matriz-seguimiento.js') }}?v={{ md5(time()) }}"></script>
@endpush

@section('content')
    {{-- Campo oculto con el ID del empleado actual --}}
    <input type="hidden" id="empleadoIdActual" value="{{ $empleadoActual ? $empleadoActual->id : '' }}">
    
    <main class="relative max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="space-y-8">
            {{-- Banner de modo Preview --}}
            @if(isset($modoPreview) && $modoPreview && isset($empleadoPreview))
            <div class="bg-amber-100 border border-amber-200 rounded-3xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <div>
                            <h3 class="font-bold text-amber-800">Modo Previsualización</h3>
                            <p class="text-amber-700 text-sm">Estás viendo la matriz como la vería: <strong>{{ $empleadoPreview->nombre }}</strong></p>
                        </div>
                    </div>
                    <a href="{{ route('logistica.matriz-seguimiento') }}" class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded-xl hover:bg-amber-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Salir de Previsualización
                    </a>
                </div>
            </div>
            @endif

            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                        Matriz de Seguimiento
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-600 border border-emerald-100">{{ count($operaciones) }} operaciones</span>
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">Control y seguimiento de operaciones logísticas con cálculo automático de días de tránsito.</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('logistica.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-800 shadow-sm transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Regresar
                    </a>
                </div>
            </div>

            <!-- Controles y Filtros -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center justify-between">
                    <!-- Botones de acción -->
                    <div class="flex flex-wrap gap-3">
                        <button onclick="abrirModal()" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:from-emerald-700 hover:to-emerald-800 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Nueva Operación
                        </button>
                        <button onclick="abrirModalPostOperaciones()" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-purple-600 to-purple-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:from-purple-700 hover:to-purple-800 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            Post-Operaciones
                        </button>
                        @if(isset($esAdmin) && $esAdmin)
                        <button onclick="abrirModalCamposPersonalizados()" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-slate-600 to-slate-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:from-slate-700 hover:to-slate-800 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Configurar Campos
                        </button>
                        @endif
                    </div>
                    
                    <!-- Filtros -->
                    <div class="flex flex-wrap gap-4 items-center">
                        <!-- Filtro por Cliente -->
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Cliente:</label>
                            <select id="filtroCliente" class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-400 focus:ring-0 shadow-sm min-w-[200px]" onchange="aplicarFiltros()">
                                <option value="todos" {{ (!isset($filtroCliente) || $filtroCliente === 'todos') ? 'selected' : '' }}>Todos los clientes</option>
                                @foreach($clientesUnicos ?? [] as $clienteUnico)
                                    <option value="{{ $clienteUnico }}" {{ (isset($filtroCliente) && $filtroCliente === $clienteUnico) ? 'selected' : '' }}>{{ $clienteUnico }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        @if($esAdmin)
                        <!-- Filtro por Ejecutivo (solo admin) -->
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Ejecutivo:</label>
                            <select id="filtroEjecutivo" class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-400 focus:ring-0 shadow-sm min-w-[200px]" onchange="aplicarFiltros()">
                                <option value="todos" {{ (!isset($filtroEjecutivo) || $filtroEjecutivo === 'todos') ? 'selected' : '' }}>Todos los ejecutivos</option>
                                @foreach($ejecutivosUnicos ?? [] as $ejecutivoUnico)
                                    <option value="{{ $ejecutivoUnico }}" {{ (isset($filtroEjecutivo) && $filtroEjecutivo === $ejecutivoUnico) ? 'selected' : '' }}>{{ $ejecutivoUnico }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        
                        <!-- Botón limpiar filtros -->
                        <button type="button" onclick="limpiarFiltros()" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 transition-colors shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            @php
                // Determinar qué campos personalizados mostrar según el usuario
                $camposVisibles = collect();
                if (isset($camposPersonalizados)) {
                    if (isset($esAdmin) && $esAdmin) {
                        // Admin ve todos los campos activos
                        $camposVisibles = $camposPersonalizados;
                    } elseif (isset($empleadoActual) && $empleadoActual) {
                        // Usuario normal ve solo campos asignados a él
                        $camposVisibles = $camposPersonalizados->filter(function($campo) use ($empleadoActual) {
                            return $campo->ejecutivos->contains('id', $empleadoActual->id);
                        });
                    }
                }
                
                // Agrupar campos personalizados por su posición (mostrar_despues_de)
                $camposPorPosicion = $camposVisibles->groupBy(function($campo) {
                    return $campo->mostrar_despues_de ?? '__al_final__';
                });
            @endphp

            <!-- Tabla Principal -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Scroll superior sincronizado -->
                <div id="scrollSuperior" class="overflow-x-auto" style="overflow-y: hidden; height: 20px; margin-bottom: -1px;">
                    <div id="scrollSuperiorInner" style="height: 1px;"></div>
                </div>
                <!-- Contenedor de la tabla con scroll -->
                <div id="scrollInferior" class="overflow-x-auto">
                    <table class="w-full text-sm" id="tablaMatriz">
                        <thead class="bg-slate-50">
                            <tr>
                                @if(!in_array('id', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-5 py-3 text-left text-[11px] font-bold tracking-wide text-slate-600 uppercase border-r border-slate-200 min-w-[50px]" data-columna="id">{{ $nombresColumnas['id'] ?? 'No.' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('id', collect()) as $campo)
                                <th class="px-5 py-3 text-left text-[11px] font-bold tracking-wide text-slate-600 uppercase border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('ejecutivo', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-5 py-3 text-left text-[11px] font-bold tracking-wide text-slate-600 uppercase border-r border-slate-200 min-w-[120px]" data-columna="ejecutivo">{{ $nombresColumnas['ejecutivo'] ?? 'Ejecutivo' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('ejecutivo', collect()) as $campo)
                                <th class="px-5 py-3 text-left text-[11px] font-bold tracking-wide text-slate-600 uppercase border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('operacion', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-5 py-3 text-left text-[11px] font-bold tracking-wide text-slate-600 uppercase border-r border-slate-200 min-w-[100px]" data-columna="operacion">{{ $nombresColumnas['operacion'] ?? 'Operación' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('operacion', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('cliente', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]" data-columna="cliente">{{ $nombresColumnas['cliente'] ?? 'Cliente' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('cliente', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('proveedor_o_cliente', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]" data-columna="proveedor_o_cliente">{{ $nombresColumnas['proveedor_o_cliente'] ?? 'Proveedor o Cliente' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('proveedor', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('fecha_embarque', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]" data-columna="fecha_embarque">{{ $nombresColumnas['fecha_embarque'] ?? 'Fecha de Embarque' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('fecha_embarque', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('no_factura', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]" data-columna="no_factura">{{ $nombresColumnas['no_factura'] ?? 'No. De Factura' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('no_factura', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(in_array('tipo_carga', $columnasOpcionalesVisibles ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-purple-50" data-columna="tipo_carga">{{ $nombresColumnas['tipo_carga'] ?? 'Tipo de Carga' }}</th>
                                @endif
                                @if(in_array('tipo_incoterm', $columnasOpcionalesVisibles ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px] bg-purple-50" data-columna="tipo_incoterm">{{ $nombresColumnas['tipo_incoterm'] ?? 'Incoterm' }}</th>
                                @endif
                                
                                @if(!in_array('tipo_operacion_enum', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]" data-columna="tipo_operacion_enum">{{ $nombresColumnas['tipo_operacion_enum'] ?? 'T. Operación' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('tipo_operacion', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('clave', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[80px]" data-columna="clave">{{ $nombresColumnas['clave'] ?? 'Clave' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('clave', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('referencia_interna', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]" data-columna="referencia_interna">{{ $nombresColumnas['referencia_interna'] ?? 'Referencia Interna' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('referencia_interna', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('aduana', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]" data-columna="aduana">{{ $nombresColumnas['aduana'] ?? 'Aduana' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('aduana', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('agente_aduanal', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[80px]" data-columna="agente_aduanal">{{ $nombresColumnas['agente_aduanal'] ?? 'A.A' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('agente_aduanal', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('referencia_aa', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]" data-columna="referencia_aa">{{ $nombresColumnas['referencia_aa'] ?? 'Referencia A.A' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('referencia_aa', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('no_pedimento', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[80px]" data-columna="no_pedimento">{{ $nombresColumnas['no_pedimento'] ?? 'No Ped' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('no_pedimento', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('transporte', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]" data-columna="transporte">{{ $nombresColumnas['transporte'] ?? 'Transporte' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('transporte', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('fecha_arribo_aduana', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]" data-columna="fecha_arribo_aduana">{{ $nombresColumnas['fecha_arribo_aduana'] ?? 'Fecha de Arribo a Aduana' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('fecha_arribo_aduana', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('guia_bl', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]" data-columna="guia_bl">{{ $nombresColumnas['guia_bl'] ?? 'Guía/BL' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('guia_bl', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(in_array('puerto_salida', $columnasOpcionalesVisibles ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-purple-50" data-columna="puerto_salida">{{ $nombresColumnas['puerto_salida'] ?? 'Puerto de Salida' }}</th>
                                @endif
                                {{-- NUEVOS CAMPOS OPCIONALES --}}
                                @if(in_array('in_charge', $columnasOpcionalesVisibles ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-purple-50" data-columna="in_charge">{{ $nombresColumnas['in_charge'] ?? 'Responsable' }}</th>
                                @endif
                                @if(in_array('proveedor', $columnasOpcionalesVisibles ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-purple-50" data-columna="proveedor">{{ $nombresColumnas['proveedor'] ?? 'Proveedor' }}</th>
                                @endif
                                @if(in_array('tipo_previo', $columnasOpcionalesVisibles ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-purple-50" data-columna="tipo_previo">{{ $nombresColumnas['tipo_previo'] ?? 'Modalidad/Previo' }}</th>
                                @endif
                                @if(in_array('fecha_etd', $columnasOpcionalesVisibles ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-purple-50" data-columna="fecha_etd">{{ $nombresColumnas['fecha_etd'] ?? 'Fecha ETD' }}</th>
                                @endif
                                @if(in_array('fecha_zarpe', $columnasOpcionalesVisibles ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-purple-50" data-columna="fecha_zarpe">{{ $nombresColumnas['fecha_zarpe'] ?? 'Fecha Zarpe' }}</th>
                                @endif
                                @if(in_array('pedimento_en_carpeta', $columnasOpcionalesVisibles ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-purple-50" data-columna="pedimento_en_carpeta">{{ $nombresColumnas['pedimento_en_carpeta'] ?? 'Ped. en Carpeta' }}</th>
                                @endif
                                @if(in_array('referencia_cliente', $columnasOpcionalesVisibles ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-purple-50" data-columna="referencia_cliente">{{ $nombresColumnas['referencia_cliente'] ?? 'Ref. Cliente' }}</th>
                                @endif
                                @if(in_array('mail_subject', $columnasOpcionalesVisibles ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px] bg-purple-50" data-columna="mail_subject">{{ $nombresColumnas['mail_subject'] ?? 'Asunto Correo' }}</th>
                                @endif
                                
                                @if(!in_array('status', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]" data-columna="status">{{ $nombresColumnas['status'] ?? 'Status' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('status', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('fecha_modulacion', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]" data-columna="fecha_modulacion">{{ $nombresColumnas['fecha_modulacion'] ?? 'Fecha de Modulación' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('fecha_modulacion', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('fecha_arribo_planta', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]" data-columna="fecha_arribo_planta">{{ $nombresColumnas['fecha_arribo_planta'] ?? 'Fecha de Arribo a Planta' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('fecha_arribo_planta', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('resultado', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]" data-columna="resultado">{{ $nombresColumnas['resultado'] ?? 'Resultado' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('resultado', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('target', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[80px]" data-columna="target">{{ $nombresColumnas['target'] ?? 'Target' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('target', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('dias_transito', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]" data-columna="dias_transito">{{ $nombresColumnas['dias_transito'] ?? 'Días en Tránsito' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('dias_transito', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('post_operaciones', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]" data-columna="post_operaciones">{{ $nombresColumnas['post_operaciones'] ?? 'Post-Operaciones' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('post_operaciones', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                @if(!in_array('comentarios', $columnasPredeterminadasOcultas ?? []))
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]" data-columna="comentarios">{{ $nombresColumnas['comentarios'] ?? 'Comentarios' }}</th>
                                @endif
                                @foreach($camposPorPosicion->get('comentarios', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                {{-- Campos personalizados sin posición definida (al final) --}}
                                @foreach($camposPorPosicion->get('__al_final__', collect()) as $campo)
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="{{ $campo->id }}">
                                    <div class="flex items-center"><span class="text-indigo-600 mr-1">★</span>{{ $campo->nombre }}</div>
                                </th>
                                @endforeach
                                
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]" data-columna="acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200" id="operacionesTable">
                            @forelse($operaciones as $operacion)
                            <tr class="table-row" data-operacion-id="{{ $operacion->id }}">
                                @if(!in_array('id', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->id }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('id', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('ejecutivo', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-900 font-medium">{{ $operacion->ejecutivo ?? 'Sin asignar' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('ejecutivo', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('operacion', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->operacion ?? '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('operacion', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('cliente', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->cliente ?? 'Sin cliente' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('cliente', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('proveedor_o_cliente', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->proveedor_o_cliente ?? '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('proveedor', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('fecha_embarque', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->fecha_embarque ? $operacion->fecha_embarque->format('d/m/Y') : '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('fecha_embarque', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('no_factura', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->no_factura ?? '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('no_factura', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(in_array('tipo_carga', $columnasOpcionalesVisibles ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600 bg-purple-50/30">{{ $operacion->tipo_carga ?? '-' }}</td>
                                @endif
                                @if(in_array('tipo_incoterm', $columnasOpcionalesVisibles ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600 bg-purple-50/30">{{ $operacion->tipo_incoterm ?? '-' }}</td>
                                @endif
                                
                                @if(!in_array('tipo_operacion_enum', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->tipo_operacion_enum ?? '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('tipo_operacion', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('clave', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->clave ?? '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('clave', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('referencia_interna', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->referencia_interna ?? '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('referencia_interna', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('aduana', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->aduana ?? '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('aduana', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('agente_aduanal', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->agente_aduanal ?? '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('agente_aduanal', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('referencia_aa', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->referencia_aa ?? '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('referencia_aa', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('no_pedimento', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->no_pedimento ?? '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('no_pedimento', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('transporte', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->transporte ?? '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('transporte', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('fecha_arribo_aduana', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->fecha_arribo_aduana ? $operacion->fecha_arribo_aduana->format('d/m/Y') : '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('fecha_arribo_aduana', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('guia_bl', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->guia_bl ?? '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('guia_bl', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(in_array('puerto_salida', $columnasOpcionalesVisibles ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600 bg-purple-50/30">{{ $operacion->puerto_salida ?? '-' }}</td>
                                @endif
                                {{-- NUEVOS CAMPOS OPCIONALES (Body) --}}
                                @if(in_array('in_charge', $columnasOpcionalesVisibles ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600 bg-purple-50/30">{{ $operacion->in_charge ?? '-' }}</td>
                                @endif
                                @if(in_array('proveedor', $columnasOpcionalesVisibles ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600 bg-purple-50/30">{{ $operacion->proveedor ?? '-' }}</td>
                                @endif
                                @if(in_array('tipo_previo', $columnasOpcionalesVisibles ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600 bg-purple-50/30">{{ $operacion->tipo_previo ?? '-' }}</td>
                                @endif
                                @if(in_array('fecha_etd', $columnasOpcionalesVisibles ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600 bg-purple-50/30">{{ $operacion->fecha_etd ? $operacion->fecha_etd->format('d/m/Y') : '-' }}</td>
                                @endif
                                @if(in_array('fecha_zarpe', $columnasOpcionalesVisibles ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600 bg-purple-50/30">{{ $operacion->fecha_zarpe ? $operacion->fecha_zarpe->format('d/m/Y') : '-' }}</td>
                                @endif
                                @if(in_array('pedimento_en_carpeta', $columnasOpcionalesVisibles ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600 bg-purple-50/30">
                                    @if($operacion->pedimento_en_carpeta === true)
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Sí</span>
                                    @elseif($operacion->pedimento_en_carpeta === false)
                                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">No</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                @endif
                                @if(in_array('referencia_cliente', $columnasOpcionalesVisibles ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600 bg-purple-50/30">{{ $operacion->referencia_cliente ?? '-' }}</td>
                                @endif
                                @if(in_array('mail_subject', $columnasOpcionalesVisibles ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600 bg-purple-50/30 max-w-[200px] truncate" title="{{ $operacion->mail_subject ?? '' }}">{{ $operacion->mail_subject ?? '-' }}</td>
                                @endif
                                
                                @if(!in_array('status', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200">
                                    <div class="flex flex-col space-y-1">
                                        <!-- Status Manual (prevalece si está en Done) -->
                                        @php
                                            // Priorizar status_manual si existe y es Done, sino usar status_calculado
                                            $statusFinal = ($operacion->status_manual === 'Done') ? 'Done' : $operacion->status_calculado;
                                            $colorFinal = ($operacion->status_manual === 'Done') ? 'verde' : $operacion->color_status;
                                            $statusDisplay = match($statusFinal) {
                                                'In Process' => 'En Proceso',
                                                'Out of Metric' => 'Fuera de Métrica',
                                                'Done' => 'Completado',
                                                default => $statusFinal ?? 'En Proceso'
                                            };
                                        @endphp
                                        <span class="status-badge {{
                                            $colorFinal === 'verde' ? 'status-verde' :
                                            ($colorFinal === 'amarillo' ? 'status-amarillo' :
                                            ($colorFinal === 'rojo' ? 'status-rojo' : 'status-sin-fecha'))
                                        }} text-xs">
                                            {{ $statusDisplay }}@if($operacion->status_manual === 'Done') <span class="ml-1">(Manual)</span>@endif
                                        </span>
                                    </div>
                                </td>
                                @endif
                                @foreach($camposPorPosicion->get('status', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('fecha_modulacion', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->fecha_modulacion ? $operacion->fecha_modulacion->format('d/m/Y') : '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('fecha_modulacion', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('fecha_arribo_planta', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->fecha_arribo_planta ? $operacion->fecha_arribo_planta->format('d/m/Y') : '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('fecha_arribo_planta', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('resultado', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->resultado ?? '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('resultado', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('target', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600">{{ $operacion->target ?? '-' }}</td>
                                @endif
                                @foreach($camposPorPosicion->get('target', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('dias_transito', $columnasPredeterminadasOcultas ?? []))
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
                                @endif
                                @foreach($camposPorPosicion->get('dias_transito', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('post_operaciones', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-center">
                                    <button onclick="verPostOperaciones({{ $operacion->id }})"
                                            class="action-button btn-view"
                                            title="Ver post-operaciones">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                        </svg>
                                    </button>
                                </td>
                                @endif
                                @foreach($camposPorPosicion->get('post_operaciones', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                @if(!in_array('comentarios', $columnasPredeterminadasOcultas ?? []))
                                <td class="px-3 py-4 border-r border-slate-200 text-center">
                                    <button onclick="verComentarios({{ $operacion->id }})"
                                            class="action-button btn-view"
                                            title="Ver comentarios">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    </button>
                                </td>
                                @endif
                                @foreach($camposPorPosicion->get('comentarios', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
                                {{-- Campos personalizados sin posición definida (al final) --}}
                                @foreach($camposPorPosicion->get('__al_final__', collect()) as $campo)
                                    @include('Logistica.partials.campo-personalizado-celda', ['operacion' => $operacion, 'campo' => $campo])
                                @endforeach
                                
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
                                        <button onclick="editarOperacion({{ $operacion->id }})"
                                                class="action-button btn-edit"
                                                title="Editar operación">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        @if($operacion->status_manual !== 'Done')
                                        <button onclick="marcarComoDone({{ $operacion->id }})"
                                                class="action-button btn-done"
                                                title="Marcar como Done (Manual)">
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
                                <td colspan="{{ 25 + count($camposVisibles ?? []) }}" class="px-3 py-8 text-center text-slate-500">
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

            <!-- Controles de Paginación -->
            @if($operaciones->hasPages())
            <div class="mt-4 bg-white/90 backdrop-blur rounded-2xl border border-blue-100 shadow-lg p-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-slate-600">
                        Mostrando <span class="font-semibold text-blue-600">{{ $operaciones->firstItem() ?? 0 }}</span> 
                        a <span class="font-semibold text-blue-600">{{ $operaciones->lastItem() ?? 0 }}</span> 
                        de <span class="font-semibold text-blue-600">{{ $operaciones->total() }}</span> operaciones
                    </div>
                    
                    <div class="flex items-center gap-2">
                        {{-- Botón Primera Página --}}
                        @if($operaciones->onFirstPage())
                            <span class="px-3 py-2 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                                </svg>
                            </span>
                        @else
                            <a href="{{ $operaciones->url(1) }}" class="px-3 py-2 bg-white border border-blue-300 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                                </svg>
                            </a>
                        @endif
                        
                        {{-- Botón Anterior --}}
                        @if($operaciones->onFirstPage())
                            <span class="px-3 py-2 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </span>
                        @else
                            <a href="{{ $operaciones->previousPageUrl() }}" class="px-3 py-2 bg-white border border-blue-300 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>
                        @endif
                        
                        {{-- Números de Página --}}
                        <div class="flex items-center gap-1">
                            @php
                                $currentPage = $operaciones->currentPage();
                                $lastPage = $operaciones->lastPage();
                                $start = max(1, $currentPage - 2);
                                $end = min($lastPage, $currentPage + 2);
                            @endphp
                            
                            @if($start > 1)
                                <a href="{{ $operaciones->url(1) }}" class="px-3 py-2 bg-white border border-gray-200 text-slate-600 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors">1</a>
                                @if($start > 2)
                                    <span class="px-2 text-slate-400">...</span>
                                @endif
                            @endif
                            
                            @for($i = $start; $i <= $end; $i++)
                                @if($i == $currentPage)
                                    <span class="px-3 py-2 bg-blue-600 text-white rounded-lg font-semibold">{{ $i }}</span>
                                @else
                                    <a href="{{ $operaciones->url($i) }}" class="px-3 py-2 bg-white border border-gray-200 text-slate-600 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors">{{ $i }}</a>
                                @endif
                            @endfor
                            
                            @if($end < $lastPage)
                                @if($end < $lastPage - 1)
                                    <span class="px-2 text-slate-400">...</span>
                                @endif
                                <a href="{{ $operaciones->url($lastPage) }}" class="px-3 py-2 bg-white border border-gray-200 text-slate-600 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors">{{ $lastPage }}</a>
                            @endif
                        </div>
                        
                        {{-- Botón Siguiente --}}
                        @if($operaciones->hasMorePages())
                            <a href="{{ $operaciones->nextPageUrl() }}" class="px-3 py-2 bg-white border border-blue-300 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @else
                            <span class="px-3 py-2 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        @endif
                        
                        {{-- Botón Última Página --}}
                        @if($operaciones->hasMorePages())
                            <a href="{{ $operaciones->url($operaciones->lastPage()) }}" class="px-3 py-2 bg-white border border-blue-300 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @else
                            <span class="px-3 py-2 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Footer/Leyenda -->
            <div class="mt-4 bg-white/90 backdrop-blur rounded-2xl border border-blue-100 shadow-lg p-4">
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
            <div class="bg-white border-b border-slate-200 p-4 rounded-t-xl">
                <div class="flex justify-between items-center mb-2">
                    <h2 id="modalTitle" class="text-lg font-semibold text-slate-800">
                        <span class="text-blue-600 mr-2 text-xl">⊕</span>
                        Añadir Nueva Operación
                    </h2>
                    <button onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold" title="Cerrar modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="flex items-center space-x-2 text-xs text-amber-700 bg-amber-50 px-3 py-2 rounded-lg border border-amber-200">
                    <span>🔒</span>
                    <span><strong>Protegido:</strong> Este modal no se cierra al hacer clic fuera para evitar pérdida de datos. Use el botón × para cerrar.</span>
                </div>
            </div>

            <!-- Contenido del formulario con scroll -->
            <div class="flex-1 overflow-y-auto p-6">
                <form id="formOperacion" class="space-y-6">
                        @csrf
                        <input type="hidden" id="operacionId" name="operacion_id" value="">
                        <input type="hidden" id="isEditing" name="_method" value="">

                        <!-- PASO 1: Tipo de Operación -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-5 border-l-4 border-blue-500">
                            <div class="flex items-center mb-4">
                                <div class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">1</div>
                                <h3 class="text-lg font-bold text-slate-800">Tipo de Operación</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">¿Qué tipo de operación es? *</label>
                                    <select name="operacion" required class="form-input text-base">
                                        <option value="">Seleccionar...</option>
                                        <option value="IMPORTACION">📦 Importación</option>
                                        <option value="EXPORTACION">🚚 Exportación</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">¿Qué transporte utilizará? *</label>
                                    <select name="tipo_operacion_enum" required onchange="actualizarTransportes(); calcularTargetAutomatico();" class="form-input text-base">
                                        <option value="">Seleccionar...</option>
                                        <option value="Terrestre" data-target="3">🚛 Terrestre (3 días)</option>
                                        <option value="Aerea" data-target="3">✈️ Aérea (3 días)</option>
                                        <option value="Ferrocarril" data-target="3">🚂 Ferrocarril (3 días)</option>
                                        <option value="Maritima" data-target="7">🚢 Marítima (7 días)</option>
                                    </select>
                                    <p class="text-xs text-slate-500 mt-1">💡 El tiempo estimado se calcula automáticamente</p>
                                </div>
                            </div>
                        </div>

                        <!-- PASO 2: Cliente y Responsable -->
                        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl p-5 border-l-4 border-emerald-500">
                            <div class="flex items-center mb-4">
                                <div class="bg-emerald-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">2</div>
                                <h3 class="text-lg font-bold text-slate-800">Cliente y Responsable</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold text-slate-700">Cliente *</label>
                                        @if(isset($esAdmin) && $esAdmin)
                                        <button type="button" onclick="mostrarNuevoCliente()"
                                                class="text-xs text-emerald-600 hover:text-emerald-800 font-semibold flex items-center">
                                            <span class="mr-1">+</span> Agregar nuevo
                                        </button>
                                        @endif
                                    </div>
                                    <select name="cliente" id="clienteSelect" required class="form-input text-base w-full">
                                        <option value="">Selecciona un cliente</option>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->cliente }}">{{ $cliente->cliente }}</option>
                                        @endforeach
                                    </select>
                                    <div id="nuevoClienteForm" class="hidden mt-3 p-3 bg-white border-2 border-emerald-200 rounded-lg shadow-sm">
                                        <input type="text" id="nuevoClienteNombre" placeholder="Nombre del nuevo cliente" class="form-input mb-2">
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="guardarNuevoCliente()"
                                                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 flex items-center">
                                                    ✓ Guardar</button>
                                            <button type="button" onclick="cancelarNuevoCliente()"
                                                    class="px-4 py-2 bg-slate-400 text-white rounded-lg text-sm hover:bg-slate-500">Cancelar</button>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Ejecutivo Responsable *</label>
                                    @php
                                        $valorEjecutivo = '';
                                        $soloLectura = false;
                                        
                                        if (isset($esAdmin) && isset($empleadoActual)) {
                                            if (!$esAdmin && $empleadoActual) {
                                                $valorEjecutivo = $empleadoActual->nombre;
                                                $soloLectura = true;
                                            }
                                        }
                                    @endphp
                                    @if(isset($esAdmin) && $esAdmin)
                                        <select name="ejecutivo" id="ejecutivoSelect" required class="form-input text-base w-full">
                                            <option value="">Selecciona un ejecutivo</option>
                                            @foreach($empleados as $empleado)
                                                <option value="{{ $empleado->nombre }}" {{ $empleado->nombre == $valorEjecutivo ? 'selected' : '' }}>{{ $empleado->nombre }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" name="ejecutivo" required class="form-input text-base" 
                                               placeholder="Nombre del ejecutivo" 
                                               value="{{ $valorEjecutivo }}"
                                               readonly>
                                    @endif
                                    @if($soloLectura)
                                        <p class="text-xs text-slate-500 mt-1">📌 Tu nombre está asignado automáticamente</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- PASO 3: Información de la Operación -->
                        <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-xl p-5 border-l-4 border-amber-500">
                            <div class="flex items-center mb-4">
                                <div class="bg-amber-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">3</div>
                                <h3 class="text-lg font-bold text-slate-800">Detalles de la Operación</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Proveedor/Cliente Final *</label>
                                    <input type="text" name="proveedor_o_cliente" required class="form-input text-base" placeholder="Nombre del proveedor o cliente">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Número de Factura *</label>
                                    <input type="text" name="no_factura" required class="form-input text-base" placeholder="Ej: FAC-12345">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tipo de Carga</label>
                                    <select name="tipo_carga" class="form-input text-base w-full">
                                        <option value="">Seleccione...</option>
                                        <option value="FCL">FCL (Full Container Load)</option>
                                        <option value="LCL">LCL (Less than Container Load)</option>
                                    </select>
                                    <input type="text" name="tipo_carga_detalle" class="form-input text-base mt-2" placeholder="Cantidad de pallets (opcional)">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tipo de Incoterm</label>
                                    <select name="tipo_incoterm" class="form-input text-base w-full">
                                        <option value="">Seleccione...</option>
                                        <option value="EXW">EXW - Ex Works</option>
                                        <option value="FCA">FCA - Free Carrier</option>
                                        <option value="FAS">FAS - Free Alongside Ship</option>
                                        <option value="FOB">FOB - Free On Board</option>
                                        <option value="CFR">CFR - Cost and Freight</option>
                                        <option value="CIF">CIF - Cost, Insurance and Freight</option>
                                        <option value="CPT">CPT - Carriage Paid To</option>
                                        <option value="CIP">CIP - Carriage and Insurance Paid To</option>
                                        <option value="DAP">DAP - Delivered at Place</option>
                                        <option value="DPU">DPU - Delivered at Place Unloaded</option>
                                        <option value="DDP">DDP - Delivered Duty Paid</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Clave del Pedimento *</label>
                                    <select name="clave" required class="w-full px-4 py-3 text-base border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                        <option value="">Seleccione una clave</option>
                                        @foreach($pedimentos ?? [] as $pedimento)
                                            <option value="{{ $pedimento->clave }}">{{ $pedimento->clave }} - {{ $pedimento->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Referencia Interna *</label>
                                    <input type="text" name="referencia_interna" required class="form-input text-base" placeholder="Referencia para seguimiento">
                                </div>
                            </div>
                        </div>

                        <!-- PASO 4: Fecha y Aduana -->
                        <div class="bg-gradient-to-r from-violet-50 to-purple-50 rounded-xl p-5 border-l-4 border-violet-500">
                            <div class="flex items-center mb-4">
                                <div class="bg-violet-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">4</div>
                                <h3 class="text-lg font-bold text-slate-800">Fecha y Ubicación</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">📅 Fecha de Embarque *</label>
                                    <input type="date" name="fecha_embarque" required class="form-input text-base">
                                    <p class="text-xs text-violet-600 mt-1 font-medium">✓ Esta es la única fecha obligatoria</p>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold text-slate-700">Aduana de Despacho *</label>
                                        @if(isset($esAdmin) && $esAdmin)
                                        <button type="button" onclick="mostrarNuevaAduana()"
                                                class="text-xs text-violet-600 hover:text-violet-800 font-semibold flex items-center">
                                            <span class="mr-1">+</span> Agregar nueva
                                        </button>
                                        @endif
                                    </div>
                                    <select name="aduana" id="aduanaSelect" required class="form-input text-base w-full">
                                        <option value="">Selecciona una aduana</option>
                                        @foreach($aduanas ?? [] as $aduana)
                                            <option value="{{ $aduana->aduana }}{{ $aduana->seccion }}" data-denominacion="{{ $aduana->denominacion }}">{{ $aduana->aduana }}{{ $aduana->seccion }} - {{ $aduana->denominacion }}</option>
                                        @endforeach
                                    </select>
                                    <div id="nuevaAduanaForm" class="hidden mt-3 p-3 bg-white border-2 border-violet-200 rounded-lg shadow-sm">
                                        <div class="grid grid-cols-3 gap-2 mb-2">
                                            <input type="text" id="nuevaAduanaCodigo" placeholder="Código" class="form-input text-sm" maxlength="2">
                                            <input type="text" id="nuevaAduanaSeccion" placeholder="Sección" class="form-input text-sm" maxlength="1" value="0">
                                            <input type="text" id="nuevaAduanaDenominacion" placeholder="Nombre" class="form-input text-sm col-span-3">
                                        </div>
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="guardarNuevaAduana()"
                                                    class="px-4 py-2 bg-violet-600 text-white rounded-lg text-sm hover:bg-violet-700 flex items-center">
                                                    ✓ Guardar</button>
                                            <button type="button" onclick="cancelarNuevaAduana()"
                                                    class="px-4 py-2 bg-slate-400 text-white rounded-lg text-sm hover:bg-slate-500">Cancelar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PASO 5: Agente y Transporte -->
                        <div class="bg-gradient-to-r from-sky-50 to-cyan-50 rounded-xl p-5 border-l-4 border-sky-500">
                            <div class="flex items-center mb-4">
                                <div class="bg-sky-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">5</div>
                                <h3 class="text-lg font-bold text-slate-800">Agente Aduanal y Transporte</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold text-slate-700">Agente Aduanal *</label>
                                        <button type="button" onclick="mostrarNuevoAgente()"
                                                class="text-xs text-sky-600 hover:text-sky-800 font-semibold flex items-center">
                                            <span class="mr-1">+</span> Agregar nuevo
                                        </button>
                                    </div>
                                    <select name="agente_aduanal" id="agenteSelect" required class="form-input text-base w-full">
                                        <option value="">Selecciona un agente aduanal</option>
                                        @foreach($agentesAduanales as $agente)
                                            <option value="{{ $agente->agente_aduanal }}">{{ $agente->agente_aduanal }}</option>
                                        @endforeach
                                    </select>
                                    <div id="nuevoAgenteForm" class="hidden mt-3 p-3 bg-white border-2 border-sky-200 rounded-lg shadow-sm">
                                        <input type="text" id="nuevoAgenteNombre" placeholder="Nombre del nuevo agente" class="form-input mb-2">
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="guardarNuevoAgente()"
                                                    class="px-4 py-2 bg-sky-600 text-white rounded-lg text-sm hover:bg-sky-700 flex items-center">
                                                    ✓ Guardar</button>
                                            <button type="button" onclick="cancelarNuevoAgente()"
                                                    class="px-4 py-2 bg-slate-400 text-white rounded-lg text-sm hover:bg-slate-500">Cancelar</button>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold text-slate-700">Empresa de Transporte</label>
                                        <button type="button" onclick="mostrarNuevoTransporte()"
                                                class="text-xs text-sky-600 hover:text-sky-800 font-semibold flex items-center">
                                            <span class="mr-1">+</span> Agregar nuevo
                                        </button>
                                    </div>
                                    <select name="transporte" id="transporteSelect" class="form-input text-base w-full">
                                        <option value="">Selecciona una empresa de transporte</option>
                                        @foreach($transportes as $transporte)
                                            <option value="{{ $transporte->transporte }}" data-tipo="{{ $transporte->tipo_operacion }}">{{ $transporte->transporte }}</option>
                                        @endforeach
                                    </select>
                                    <div id="nuevoTransporteForm" class="hidden mt-3 p-3 bg-white border-2 border-sky-200 rounded-lg shadow-sm">
                                        <input type="text" id="nuevoTransporteNombre" placeholder="Nombre de la empresa" class="form-input mb-2">
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="guardarNuevoTransporte()"
                                                    class="px-4 py-2 bg-sky-600 text-white rounded-lg text-sm hover:bg-sky-700 flex items-center">
                                                    ✓ Guardar</button>
                                            <button type="button" onclick="cancelarNuevoTransporte()"
                                                    class="px-4 py-2 bg-slate-400 text-white rounded-lg text-sm hover:bg-slate-500">Cancelar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PASO 6: Información Adicional (Opcional) -->
                        <div class="bg-gradient-to-r from-slate-50 to-gray-50 rounded-xl p-5 border-l-4 border-slate-400 border-dashed">
                            <div class="flex items-center mb-4">
                                <div class="bg-slate-400 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">6</div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-800">Información Adicional</h3>
                                    <p class="text-xs text-slate-600">Opcional - Se puede completar después</p>
                                </div>
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                                <p class="text-sm text-blue-700 flex items-center">
                                    <span class="mr-2">💡</span>
                                    <strong>Tip:</strong> Puedes guardar ahora y completar estos datos más tarde durante el proceso
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-2">Fecha Arribo a Aduana</label>
                                    <input type="date" name="fecha_arribo_aduana" class="form-input bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-2">Fecha de Modulación</label>
                                    <input type="date" name="fecha_modulacion" class="form-input bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-2">Fecha Arribo a Planta</label>
                                    <input type="date" name="fecha_arribo_planta" class="form-input bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-2">Número de Pedimento</label>
                                    <input type="text" name="no_pedimento" id="no_pedimento" class="form-input bg-white" placeholder="Ej: 25 24 1029 5002294" maxlength="18">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-2">Referencia del Agente</label>
                                    <input type="text" name="referencia_aa" class="form-input bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-2">Guía/BL</label>
                                    <input type="text" name="guia_bl" class="form-input bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-2">Puerto de Salida</label>
                                    <input type="text" name="puerto_salida" class="form-input bg-white" placeholder="Ej: Shanghai, China">
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-slate-600 mb-2">Comentarios</label>
                                <textarea name="comentarios" rows="2" class="form-input w-full bg-white"
                                         placeholder="Agrega cualquier nota o comentario relevante..."></textarea>
                            </div>
                        </div>

                        <!-- PASO 7: Campos Adicionales del Ejecutivo (dinámico, según ejecutivo) -->
                        <div id="camposPersonalizadosSection" class="hidden bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-5 border-l-4 border-indigo-500">
                            <div class="flex items-center mb-4">
                                <div class="bg-indigo-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">7</div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-800">
                                        Campos Adicionales de <span id="nombreEjecutivoCampos" class="text-indigo-600"></span>
                                    </h3>
                                    <p class="text-xs text-slate-600">Campos y columnas configurados específicamente para ti</p>
                                </div>
                            </div>
                            
                            <!-- Subsección: Columnas Opcionales Activadas -->
                            <div id="columnasOpcionalesSubsection" class="hidden mb-4">
                                <h4 class="text-sm font-semibold text-indigo-700 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Columnas Activadas
                                </h4>
                                <div id="columnasOpcionalesContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Las columnas opcionales se cargarán dinámicamente -->
                                </div>
                            </div>
                            
                            <!-- Subsección: Campos Personalizados -->
                            <div id="camposPersonalizadosSubsection" class="hidden">
                                <h4 class="text-sm font-semibold text-indigo-700 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                    </svg>
                                    Campos Personalizados
                                </h4>
                                <div id="camposPersonalizadosContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Los campos se cargarán dinámicamente según el ejecutivo -->
                                </div>
                            </div>
                            
                            <!-- Mensaje cuando no hay campos -->
                            <div id="sinCamposAdicionales" class="hidden text-center py-4 text-slate-500">
                                <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <p>No tienes campos adicionales configurados</p>
                            </div>
                        </div>

                        <!-- Status Manual (solo visible al editar) -->
                        <div id="statusManualSection" class="hidden bg-gradient-to-r from-rose-50 to-pink-50 rounded-xl p-5 border-l-4 border-rose-500">
                            <div class="flex items-center mb-4">
                                <div class="bg-rose-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">⚙</div>
                                <h3 class="text-lg font-bold text-slate-800">Control de Estado</h3>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Status Manual</label>
                                <select name="status_manual" id="statusManualSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 bg-white">
                                    <option value="In Process">🔄 In Process (En Proceso)</option>
                                    <option value="Done">✅ Done (Completado)</option>
                                    <option value="Out of Metric">🔴 Out of Metric (Fuera de Métrica)</option>
                                </select>
                                <p class="text-xs text-rose-600 mt-2 font-medium">💡 Cambia manualmente el estado si es necesario</p>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex justify-between items-center pt-6 border-t-2 border-slate-200">
                            <button type="button" onclick="cerrarModal()"
                                    class="px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-700 font-semibold hover:bg-slate-50 transition-all">
                                ✕ Cancelar
                            </button>
                            <button type="submit" id="submitButton"
                                    class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-bold hover:from-blue-700 hover:to-indigo-700 shadow-lg hover:shadow-xl transition-all flex items-center">
                                <span class="mr-2">✓</span> <span id="submitButtonText">Guardar Operación</span>
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
            <div class="bg-white border-b border-slate-200 p-4 rounded-t-xl">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-lg font-semibold text-slate-800">
                        <span class="text-green-600 mr-2 text-xl">📋</span>
                        Observaciones - Operación #<span id="operacionIdComentarios"></span>
                    </h2>
                    <button onclick="cerrarModalComentarios()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold" title="Cerrar modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="flex items-center space-x-2 text-xs text-green-700 bg-green-50 px-3 py-2 rounded-lg border border-green-200">
                    <span>🔒</span>
                    <span><strong>Protegido:</strong> Este modal no se cierra al hacer clic fuera para evitar pérdida de observaciones. Use el botón × para cerrar.</span>
                </div>
            </div>

            <!-- Contenido -->
            <div class="flex-1 overflow-y-auto p-4">
                <!-- Observaciones actuales -->
                <div class="mb-6">
                    <div id="listaComentarios" class="space-y-3">
                        <!-- Se carga dinámicamente -->
                    </div>
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

    <!-- Modal de Alerta (Reemplazo de alert) -->
    <div id="modalAlert" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div id="modalAlertIcon" class="flex-shrink-0">
                        <!-- Icon will be inserted here -->
                    </div>
                    <h3 id="modalAlertTitle" class="text-xl font-semibold text-slate-900"></h3>
                </div>
                <p id="modalAlertMessage" class="text-slate-600 mb-6"></p>
                <div class="flex justify-end">
                    <button onclick="cerrarModalAlert()" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium">
                        Aceptar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación (Reemplazo de confirm) -->
    <div id="modalConfirm" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex-shrink-0">
                        <svg class="w-12 h-12 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h3 id="modalConfirmTitle" class="text-xl font-semibold text-slate-900">Confirmar acción</h3>
                </div>
                <p id="modalConfirmMessage" class="text-slate-600 mb-6"></p>
                <div class="flex justify-end gap-3">
                    <button onclick="cerrarModalConfirm(false)" class="px-6 py-2.5 bg-slate-200 text-slate-700 rounded-xl hover:bg-slate-300 transition-colors font-medium">
                        Cancelar
                    </button>
                    <button id="modalConfirmBtn" class="px-6 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Configuración de Columnas y Campos (Solo Admin) -->
    @if(isset($esAdmin) && $esAdmin)
    <div id="modalCamposPersonalizados" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-800">
                    <svg class="w-5 h-5 inline-block mr-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Configuración de Campos
                </h2>
                <button onclick="cerrarModalCamposPersonalizados()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Pestañas -->
            <div class="border-b border-slate-200 bg-slate-50">
                <nav class="flex px-6">
                    <button id="tabColumnas" onclick="cambiarTabConfig('columnas')" class="px-4 py-3 text-sm font-medium border-b-2 border-blue-600 text-blue-600 bg-blue-50 rounded-t-lg">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"></path>
                        </svg>
                        Visibilidad de Columnas
                    </button>
                    <button id="tabCampos" onclick="cambiarTabConfig('campos')" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Campos Personalizados
                    </button>
                </nav>
            </div>
            
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-180px)]">
                <!-- Panel de Visibilidad de Columnas -->
                <div id="panelColumnas">
                    <div class="mb-4">
                        <p class="text-sm text-slate-500">Configure qué columnas ver para cada ejecutivo. Las columnas predeterminadas pueden ocultarse y las adicionales (en morado) pueden habilitarse.</p>
                    </div>
                    
                    <!-- Seleccionar Ejecutivo -->
                    <div class="bg-blue-50 rounded-xl p-4 mb-6 border border-blue-200">
                        <h4 class="font-semibold text-blue-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Seleccionar Ejecutivo
                        </h4>
                        <select id="selectEjecutivoColumnas" class="w-full md:w-1/2 px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500" onchange="cargarColumnasEjecutivo()">
                            <option value="">-- Seleccione un ejecutivo --</option>
                        </select>
                    </div>
                    
                    <div id="configuracionColumnasContainer" class="hidden">
                        <!-- Selector de Idioma -->
                        <div class="mb-4 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                            <label class="block text-sm font-medium text-indigo-700 mb-2">
                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                                </svg>
                                Idioma de nombres de columnas:
                            </label>
                            <div class="flex gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="idiomaColumnas" value="es" id="idiomaEs" class="mr-2 text-indigo-600" onchange="cambiarIdiomaColumnas()" checked>
                                    <span class="text-sm">🇲🇽 Español</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="idiomaColumnas" value="en" id="idiomaEn" class="mr-2 text-indigo-600" onchange="cambiarIdiomaColumnas()">
                                    <span class="text-sm">🇺🇸 English</span>
                                </label>
                            </div>
                            <p class="text-xs text-indigo-500 mt-2">Los nombres de las columnas se mostrarán en el idioma seleccionado.</p>
                        </div>
                        
                        <!-- Columnas Predeterminadas -->
                        <div class="bg-slate-50 rounded-xl p-4 mb-6 border border-slate-200">
                            <h4 class="font-semibold text-slate-700 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Columnas Predeterminadas (Desmarque para ocultar)
                            </h4>
                            <p class="text-xs text-slate-500 mb-3">Estas columnas están visibles por defecto. Desmarque las que desee ocultar para este ejecutivo.</p>
                            <div id="columnasPredeterminadasGrid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
                            </div>
                        </div>
                    
                        <!-- Columnas Opcionales -->
                        <div class="bg-purple-50 rounded-xl p-4 mb-6 border border-purple-200">
                            <h4 class="font-semibold text-purple-800 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                </svg>
                                Columnas Adicionales (Marque para mostrar)
                            </h4>
                            <p class="text-xs text-purple-600 mb-3">Estas columnas están ocultas por defecto. Marque las que desee mostrar para este ejecutivo.</p>
                            <div id="columnasOpcionalesGrid" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                            </div>
                        </div>
                            
                        <div class="flex justify-between items-center flex-wrap gap-2">
                            <button type="button" onclick="resetearConfiguracionColumnas()" class="px-4 py-2 bg-red-100 text-red-700 border border-red-300 rounded-lg hover:bg-red-200 transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Resetear a Predeterminados
                            </button>
                            <div class="flex gap-2">
                                <button type="button" onclick="previsualizarConfiguracion()" class="px-4 py-2 bg-blue-100 text-blue-700 border border-blue-300 rounded-lg hover:bg-blue-200 transition-colors flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Previsualizar
                                </button>
                                <button type="button" onclick="guardarConfiguracionColumnas()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Guardar Configuración
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Panel de Campos Personalizados -->
                <div id="panelCampos" class="hidden">
                    <div class="mb-4">
                        <p class="text-sm text-slate-500">Cree campos personalizados que aparecerán al final de la tabla para los ejecutivos asignados.</p>
                    </div>
                    
                    <!-- Formulario Nuevo Campo -->
                    <div class="bg-green-50 rounded-xl p-4 mb-6 border border-green-200">
                        <h4 class="font-semibold text-green-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Crear Nuevo Campo
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-green-700 mb-1">Nombre del Campo <span class="text-red-500">*</span></label>
                                <input type="text" id="nombreNuevoCampo" class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Ej: Fecha de Entrega">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-green-700 mb-1">Tipo de Campo <span class="text-red-500">*</span></label>
                                <select id="tipoNuevoCampo" onchange="mostrarOpcionesTipo()" class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    <option value="texto">📝 Texto corto</option>
                                    <option value="descripcion">📄 Descripción (multilínea)</option>
                                    <option value="numero">🔢 Número entero</option>
                                    <option value="decimal">💲 Número decimal</option>
                                    <option value="moneda">💰 Moneda</option>
                                    <option value="fecha">📅 Fecha</option>
                                    <option value="booleano">✅ Sí/No</option>
                                    <option value="selector">📋 Lista de opciones</option>
                                    <option value="email">📧 Correo electrónico</option>
                                    <option value="telefono">📞 Teléfono</option>
                                    <option value="url">🔗 URL/Enlace</option>
                                </select>
                            </div>
                            
                            <!-- Opciones para Selector y Múltiple -->
                            <div id="opcionesSelectorContainer" class="md:col-span-2 hidden">
                                <label class="block text-sm font-medium text-green-700 mb-1">Opciones disponibles <span class="text-red-500">*</span></label>
                                <div class="flex gap-2 mb-2">
                                    <input type="text" id="nuevaOpcionInput" class="flex-1 px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Escribir opción y presionar Agregar">
                                    <button type="button" onclick="agregarOpcion()" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div id="listaOpciones" class="flex flex-wrap gap-2 min-h-[40px] p-2 bg-white rounded-lg border border-green-200">
                                    <span class="text-sm text-gray-400 italic">Las opciones aparecerán aquí...</span>
                                </div>
                                <!-- Opción para selección múltiple o única -->
                                <div class="mt-3 flex items-center gap-4">
                                    <span class="text-sm font-medium text-green-700">Permitir seleccionar:</span>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" name="tipoSeleccion" value="unico" id="seleccionUnica" class="mr-1 text-green-600" checked>
                                        <span class="text-sm">Solo uno</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" name="tipoSeleccion" value="multiple" id="seleccionMultiple" class="mr-1 text-green-600">
                                        <span class="text-sm">Varios</span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Configuración para Decimal/Moneda -->
                            <div id="configDecimalContainer" class="hidden">
                                <label class="block text-sm font-medium text-green-700 mb-1">Decimales</label>
                                <input type="number" id="decimalesInput" min="0" max="6" value="2" class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>
                            
                            <!-- Configuración para Moneda -->
                            <div id="configMonedaContainer" class="hidden">
                                <label class="block text-sm font-medium text-green-700 mb-1">Moneda</label>
                                <select id="monedaSelect" class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    <option value="MXN">🇲🇽 MXN - Peso Mexicano</option>
                                    <option value="USD">🇺🇸 USD - Dólar Americano</option>
                                    <option value="EUR">🇪🇺 EUR - Euro</option>
                                    <option value="GBP">🇬🇧 GBP - Libra Esterlina</option>
                                    <option value="CNY">🇨🇳 CNY - Yuan Chino</option>
                                    <option value="JPY">🇯🇵 JPY - Yen Japonés</option>
                                </select>
                            </div>
                            
                            <!-- Configuración para Número -->
                            <div id="configNumeroContainer" class="hidden md:col-span-2">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-green-700 mb-1">Valor mínimo (opcional)</label>
                                        <input type="number" id="minNumeroInput" class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Sin límite">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-green-700 mb-1">Valor máximo (opcional)</label>
                                        <input type="number" id="maxNumeroInput" class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Sin límite">
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-green-700 mb-1">Mostrar después de</label>
                                <select id="posicionNuevoCampo" class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    <option value="">-- Al final de la tabla --</option>
                                    <!-- Se cargará dinámicamente -->
                                </select>
                                <p class="text-xs text-green-600 mt-1">Las opciones se cargan según las columnas activas</p>
                            </div>
                            <div>
                                <label class="flex items-center text-sm font-medium text-green-700 mt-6">
                                    <input type="checkbox" id="campoRequerido" class="mr-2 w-4 h-4 text-green-600 rounded focus:ring-green-500">
                                    Campo requerido
                                </label>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-green-700 mb-1">Asignar a Ejecutivos</label>
                                <select id="selectEjecutivosNuevoCampo" multiple class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 min-h-[80px]">
                                </select>
                                <p class="text-xs text-green-600 mt-1">Ctrl+Click para seleccionar varios</p>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="button" onclick="crearCampoPersonalizado()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Crear Campo
                            </button>
                        </div>
                    </div>
                    
                    <!-- Lista de Campos Existentes -->
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 mb-6">
                        <h4 class="font-semibold text-slate-700 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            Campos Personalizados Existentes
                        </h4>
                        <div id="listaCamposPersonalizados" class="space-y-3">
                            <p class="text-slate-400 text-sm text-center py-4">Cargando campos...</p>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex justify-between items-center flex-wrap gap-2">
                        <button type="button" onclick="resetearCamposPersonalizados()" class="px-4 py-2 bg-red-100 text-red-700 border border-red-300 rounded-lg hover:bg-red-200 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Resetear a Predeterminados
                        </button>
                        <div class="flex gap-2">
                            <button type="button" onclick="previsualizarCamposModal()" class="px-4 py-2 bg-blue-100 text-blue-700 border border-blue-300 rounded-lg hover:bg-blue-200 transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Previsualizar
                            </button>
                            <button type="button" onclick="cerrarModalCamposPersonalizados()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Guardar Configuración
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Campo Personalizado -->
    <div id="modalEditarCampo" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-800">
                    <svg class="w-5 h-5 inline-block mr-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar Campo Personalizado
                </h2>
                <button onclick="cerrarModalEditarCampo()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="formEditarCampo" class="p-6">
                <input type="hidden" id="editarCampoId">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del Campo</label>
                        <input type="text" id="editarCampoNombre" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de Campo</label>
                        <select id="editarCampoTipo" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500" disabled>
                            <option value="texto">Texto corto</option>
                            <option value="descripcion">Descripción</option>
                            <option value="numero">Número</option>
                            <option value="decimal">Decimal</option>
                            <option value="moneda">Moneda</option>
                            <option value="fecha">Fecha</option>
                            <option value="booleano">Sí/No</option>
                            <option value="selector">Selector</option>
                            <option value="multiple">Múltiple</option>
                            <option value="email">Email</option>
                            <option value="telefono">Teléfono</option>
                            <option value="url">URL</option>
                        </select>
                        <p class="text-xs text-slate-500 mt-1">El tipo no se puede cambiar después de crear</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
                        <select id="editarCampoActivo" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="1">Activo (visible en modal)</option>
                            <option value="0">Inactivo (oculto en modal)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Mostrar después de</label>
                        <select id="editarCampoMostrarDespuesDe" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Al final de la tabla --</option>
                        </select>
                    </div>
                    <div>
                        <label class="flex items-center text-sm font-medium text-slate-700 mt-6">
                            <input type="checkbox" id="editarCampoRequerido" class="mr-2 w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                            Campo requerido
                        </label>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Asignar a Ejecutivos</label>
                        <select id="selectEjecutivosEditarCampo" multiple class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 min-h-[80px]">
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Ctrl+Click para seleccionar varios</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="cerrarModalEditarCampo()" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endsection
