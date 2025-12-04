@extends('layouts.erp')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/Logistica/reportes.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <button onclick="history.back()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center space-x-2">
                            <span>←</span>
                            <span>Regresar</span>
                        </button>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">📊 Centro de Reportes Logísticos</h1>
                            <p class="text-gray-600 mt-1">Selecciona el tipo de reporte que deseas generar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selector de Reportes -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Reporte Matriz de Seguimiento -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-500 to-blue-600">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <span class="mr-3">📋</span>
                        Matriz de Seguimiento Logístico
                    </h3>
                    <p class="text-blue-100 mt-2">Reporte completo de operaciones logísticas con seguimiento detallado</p>
                </div>
                <div class="p-6">
                    <button onclick="seleccionarReporte('matriz')" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-200 font-medium">
                        Generar Reporte de Matriz
                    </button>
                </div>
            </div>

            <!-- Reporte Pedimentos -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-green-500 to-green-600">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <span class="mr-3">📄</span>
                        Control de Pagos de Pedimentos
                    </h3>
                    <p class="text-green-100 mt-2">Reporte especializado en pedimentos, pagos y comportamiento temporal</p>
                </div>
                <div class="p-6">
                    <button onclick="seleccionarReporte('pedimentos')" class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition duration-200 font-medium">
                        Generar Reporte de Pedimentos
                    </button>
                </div>
            </div>
        </div>

        <!-- Panel de Filtros Matriz -->
        <div id="filtros-matriz" class="bg-white shadow rounded-lg mb-6 hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">🔍 Filtros - Matriz de Seguimiento</h2>
            </div>
            <div class="p-6">
                <form id="form-matriz" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha Inicio</label>
                            <input type="date" id="matriz-fecha-inicio" name="fecha_inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha Fin</label>
                            <input type="date" id="matriz-fecha-fin" name="fecha_fin" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cliente</label>
                            <select id="matriz-cliente" name="cliente" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todos los clientes</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="button" onclick="generarExcelMatriz()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition duration-200">
                            📊 Generar Excel
                        </button>
                        <button type="button" onclick="cancelarReporte()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panel de Filtros Pedimentos -->
        <div id="filtros-pedimentos" class="bg-white shadow rounded-lg mb-6 hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">🔍 Filtros - Reporte de Pedimentos</h2>
            </div>
            <div class="p-6">
                <form id="form-pedimentos" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estado de Pago</label>
                            <select id="ped-estado-pago" name="estado_pago" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Todos los estados</option>
                                <option value="pagado">✅ Pedimentos Pagados</option>
                                <option value="pendiente">⏳ Pendientes de Pago</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo de Operación</label>
                            <select id="ped-tipo-operacion" name="tipo_operacion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Todas las operaciones</option>
                                <option value="importacion">📥 Importaciones</option>
                                <option value="exportacion">📤 Exportaciones</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Clave de Pedimento</label>
                            <select id="ped-clave" name="clave" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Todas las claves</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Moneda</label>
                            <select id="ped-moneda" name="moneda" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Todas las monedas</option>
                                <option value="MXN">💵 MXN - Peso Mexicano</option>
                                <option value="USD">💰 USD - Dólar Estadounidense</option>
                                <option value="EUR">💶 EUR - Euro</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha Pago - Inicio</label>
                            <input type="date" id="ped-fecha-pago-inicio" name="fecha_pago_inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha Pago - Fin</label>
                            <input type="date" id="ped-fecha-pago-fin" name="fecha_pago_fin" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-blue-800 mb-2">📈 Análisis de Comportamiento</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" id="ped-incluir-tiempos" name="incluir_tiempos" class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <span class="ml-2 text-sm text-gray-700">Incluir análisis de tiempos de procesamiento</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="ped-agrupar-cliente" name="agrupar_cliente" class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <span class="ml-2 text-sm text-gray-700">Agrupar por cliente</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="button" onclick="generarExcelPedimentos()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition duration-200">
                            📊 Generar Excel de Pedimentos
                        </button>
                        <button type="button" onclick="cancelarReporte()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading-reportes" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Generando Reporte</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Por favor espera mientras procesamos los datos...
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/Logistica/reportes.js') }}?v={{ time() }}"></script>
@endpush