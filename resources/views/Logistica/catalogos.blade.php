@extends('layouts.erp')

@section('title', 'Catálogos Maestros - Logística')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/Logistica/catalogos.css') }}">
@endpush

@section('content')
    <main class="relative overflow-hidden bg-gradient-to-br from-white via-blue-50 to-blue-100 min-h-screen">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-32 -left-20 w-96 h-96 bg-blue-200/40 blur-3xl rounded-full"></div>
            <div class="absolute top-40 -right-24 w-96 h-96 bg-blue-300/30 blur-3xl rounded-full"></div>
        </div>

        <!-- Header -->
        <div class="relative max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-10">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <a href="{{ route('logistica.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4 transition-colors">
                        <span class="mr-2">←</span> Regresar
                    </a>
                    <h1 class="text-2xl font-bold text-slate-900">Catálogos Maestros</h1>
                    <p class="text-sm text-slate-600 mt-1">Administración de datos maestros del área de logística</p>
                </div>
            </div>

            <!-- Pestañas -->
            <div class="bg-white/90 backdrop-blur rounded-2xl border border-blue-100/80 shadow-lg shadow-blue-500/10 overflow-hidden">
                <!-- Tab Headers -->
                <div class="border-b border-slate-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button data-tab="clientes" id="tab-clientes" 
                                class="tab-button active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                            <svg class="w-4 h-4 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            Clientes
                            <span class="ml-2 bg-blue-100 text-blue-600 py-0.5 px-2 rounded-full text-xs">{{ $clientes->total() }}</span>
                        </button>
                        <button data-tab="agentes" id="tab-agentes"
                                class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                            <svg class="w-4 h-4 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Agentes Aduanales
                            <span class="ml-2 bg-slate-100 text-slate-600 py-0.5 px-2 rounded-full text-xs">{{ $agentesAduanales->total() }}</span>
                        </button>
                        <button data-tab="transportes" id="tab-transportes"
                                class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                            <svg class="w-4 h-4 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Transportes
                            <span class="ml-2 bg-slate-100 text-slate-600 py-0.5 px-2 rounded-full text-xs">{{ $transportes->total() }}</span>
                        </button>
                        <button data-tab="ejecutivos" id="tab-ejecutivos"
                                class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                            <svg class="w-4 h-4 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Ejecutivos
                            <span class="ml-2 bg-slate-100 text-slate-600 py-0.5 px-2 rounded-full text-xs">{{ $ejecutivos->total() }}</span>
                        </button>
                    </nav>
                </div>

                <!-- Tab Contents -->
                
                <!-- Clientes Tab -->
                <div id="clientes-content" class="tab-content">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold text-slate-800">Gestión de Clientes</h2>
                            <div class="flex space-x-3">
                                @if(auth()->user() && auth()->user()->hasRole('admin'))
                                <button id="assignExecutiveBtn" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors shadow-sm" disabled>
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Asignar Ejecutivo
                                </button>
                                @endif
                                <button class="btn-add inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors shadow-sm" data-type="clientes">
                                    <span class="mr-2 font-bold">+</span>
                                    Agregar Cliente
                                </button>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        @if(auth()->user() && auth()->user()->hasRole('admin'))
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">
                                            <input type="checkbox" id="selectAllClientes" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        </th>
                                        @endif
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">ID</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Cliente</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Ejecutivo Asignado</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Fecha Creación</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @forelse($clientes as $cliente)
                                    <tr class="hover:bg-blue-50/50 transition-colors">
                                        @if(auth()->user() && auth()->user()->hasRole('admin'))
                                        <td class="px-4 py-3">
                                            <input type="checkbox" class="cliente-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" value="{{ $cliente->id }}">
                                        </td>
                                        @endif
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $cliente->id }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $cliente->cliente }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">
                                            @if($cliente->ejecutivoAsignado)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ $cliente->ejecutivoAsignado->nombre }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                    Sin asignar
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $cliente->created_at->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex space-x-2">
                                                <button class="btn-edit px-3 py-1 rounded-lg text-sm font-medium transition-all" 
                                                        data-id="{{ $cliente->id }}" 
                                                        data-type="clientes" 
                                                        data-name="{{ $cliente->cliente }}"
                                                        data-ejecutivo-id="{{ $cliente->ejecutivo_asignado_id }}">
                                                    Editar
                                                </button>
                                                <button class="btn-delete px-3 py-1 rounded-lg text-sm font-medium transition-all" 
                                                        data-id="{{ $cliente->id }}" 
                                                        data-type="clientes" 
                                                        data-name="{{ $cliente->cliente }}">
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="{{ auth()->user() && auth()->user()->hasRole('admin') ? 6 : 5 }}" class="px-4 py-8 text-center text-slate-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                                </svg>
                                                <p>No hay clientes registrados</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($clientes->hasPages())
                        <div class="mt-6 flex justify-center">
                            {{ $clientes->links() }}
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Agentes Tab -->
                <div id="agentes-content" class="tab-content hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold text-slate-800">Gestión de Agentes Aduanales</h2>
                            <button class="btn-add inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors shadow-sm" data-type="agentes">
                                <span class="mr-2 font-bold">+</span>
                                Agregar Agente
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">ID</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Agente Aduanal</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Fecha Creación</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @forelse($agentesAduanales as $agente)
                                    <tr class="hover:bg-blue-50/50 transition-colors">
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $agente->id }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $agente->agente_aduanal }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $agente->created_at->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex space-x-2">
                                                <button class="btn-edit px-3 py-1 rounded-lg text-sm font-medium transition-all" 
                                                        data-id="{{ $agente->id }}" 
                                                        data-type="agentes" 
                                                        data-name="{{ $agente->agente_aduanal }}">
                                                    Editar
                                                </button>
                                                <button class="btn-delete px-3 py-1 rounded-lg text-sm font-medium transition-all" 
                                                        data-id="{{ $agente->id }}" 
                                                        data-type="agentes" 
                                                        data-name="{{ $agente->agente_aduanal }}">
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                                <p>No hay agentes aduanales registrados</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($agentesAduanales->hasPages())
                        <div class="mt-6 flex justify-center">
                            {{ $agentesAduanales->links() }}
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Transportes Tab -->
                <div id="transportes-content" class="tab-content hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold text-slate-800">Gestión de Transportes</h2>
                            <button class="btn-add inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors shadow-sm" data-type="transportes">
                                <span class="mr-2 font-bold">+</span>
                                Agregar Transporte
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">ID</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Transporte</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Tipo Operación</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Fecha Creación</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @forelse($transportes as $transporte)
                                    <tr class="hover:bg-blue-50/50 transition-colors">
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $transporte->id }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $transporte->transporte }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $transporte->tipo_operacion == 'EXPORTACION' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $transporte->tipo_operacion }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $transporte->created_at->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex space-x-2">
                                                <button class="btn-edit px-3 py-1 rounded-lg text-sm font-medium transition-all" 
                                                        data-id="{{ $transporte->id }}" 
                                                        data-type="transportes" 
                                                        data-name="{{ $transporte->transporte }}">
                                                    Editar
                                                </button>
                                                <button class="btn-delete px-3 py-1 rounded-lg text-sm font-medium transition-all" 
                                                        data-id="{{ $transporte->id }}" 
                                                        data-type="transportes" 
                                                        data-name="{{ $transporte->transporte }}">
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                </svg>
                                                <p>No hay transportes registrados</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($transportes->hasPages())
                        <div class="mt-6 flex justify-center">
                            {{ $transportes->links() }}
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Ejecutivos Tab -->
                <div id="ejecutivos-content" class="tab-content hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold text-slate-800">Ejecutivos de Logística</h2>
                            <p class="text-sm text-slate-600">Solo se muestran empleados del área de logística</p>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">ID</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Nombre</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Área</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Correo</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">ID Empleado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @forelse($ejecutivos as $ejecutivo)
                                    <tr class="hover:bg-blue-50/50 transition-colors">
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $ejecutivo->id }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $ejecutivo->nombre }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $ejecutivo->area }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $ejecutivo->correo ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $ejecutivo->id_empleado ?? 'N/A' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <p>No hay ejecutivos de logística registrados</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($ejecutivos->hasPages())
                        <div class="mt-6 flex justify-center">
                            {{ $ejecutivos->links() }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Edición -->
    <div id="editModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-md transform scale-95 transition-all duration-300">
            <div class="modal-header p-6 border-b border-gray-200">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Editar Item</h3>
            </div>
            
            <form id="editForm" class="p-6">
                <div class="mb-4">
                    <label for="editName" class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                    <input type="text" id="editName" name="name" required 
                           class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Campo de ejecutivo asignado solo para clientes -->
                <div id="ejecutivoField" class="mb-4 hidden">
                    <label for="editEjecutivo" class="block text-sm font-medium text-gray-700 mb-2">Ejecutivo Asignado</label>
                    <select id="editEjecutivo" name="ejecutivo_asignado_id" 
                           class="form-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Sin asignar</option>
                        @foreach($todosEjecutivos as $ejecutivo)
                        <option value="{{ $ejecutivo->id }}">{{ $ejecutivo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="modal-footer flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" class="btn-cancel px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" id="submitEditBtn" class="btn-primary px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Eliminación -->
    <div id="deleteModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-md transform scale-95 transition-all duration-300">
            <div class="modal-header p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Confirmar Eliminación</h3>
            </div>
            
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p id="deleteMessage" class="text-sm text-gray-700">
                            ¿Estás seguro de que deseas eliminar este elemento?
                        </p>
                    </div>
                </div>
                
                <div class="modal-footer flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" class="btn-cancel px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="button" id="confirmDeleteBtn" class="btn-delete px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Asignación de Ejecutivo -->
    <div id="assignExecutiveModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-md transform scale-95 transition-all duration-300">
            <div class="modal-header p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Asignar Ejecutivo a Clientes</h3>
            </div>
            
            <form id="assignExecutiveForm" class="p-6">
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-4">
                        Se asignarán <span id="selectedClientsCount" class="font-semibold text-blue-600">0</span> clientes seleccionados al ejecutivo elegido.
                    </p>
                    
                    <label for="selectEjecutivo" class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Ejecutivo</label>
                    <select id="selectEjecutivo" name="ejecutivo_id" required 
                           class="form-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccione un ejecutivo...</option>
                        @foreach($todosEjecutivos as $ejecutivo)
                        <option value="{{ $ejecutivo->id }}">{{ $ejecutivo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="modal-footer flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" class="btn-cancel px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-primary px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                        Asignar Ejecutivo
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('js/Logistica/catalogos.js') }}"></script>
@endpush