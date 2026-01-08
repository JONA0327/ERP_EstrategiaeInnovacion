@extends('layouts.erp')
@section('title','Expediente de ' . $empleado->nombre)
@section('content')
<main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 relative">
    
    {{-- HEADER --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            @if($empleado->foto_path)
                <img src="{{ asset('storage/'.$empleado->foto_path) }}" class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-md">
            @else
                <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xl shadow-sm">
                    {{ substr($empleado->nombre, 0, 1) }}
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ $empleado->nombre }}</h1>
                <p class="text-sm text-slate-500">{{ $empleado->posicion }} • {{ $empleado->area }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-xs text-slate-400">{{ $empleado->correo }}</p>
                    {{-- Botón para abrir modal de edición --}}
                    <button onclick="openEditModal()" class="text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-600 px-2 py-0.5 rounded border border-slate-300 transition flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Editar Info
                    </button>
                </div>
            </div>
        </div>
        
        <div class="flex gap-2">
            <a href="{{ route('rh.expedientes.index') }}" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 text-sm font-medium hover:bg-slate-50 shadow-sm transition">Volver</a>
        </div>
    </div>

    {{-- ALERTA INTELIGENTE DE FALTANTES --}}
    @php
        $faltantes = [];
        if(empty($empleado->telefono)) $faltantes[] = 'Teléfono Celular';
        if(empty($empleado->direccion)) $faltantes[] = 'Domicilio';
        if(empty($empleado->contacto_emergencia_nombre)) $faltantes[] = 'Nombre Emergencia';
        if(empty($empleado->contacto_emergencia_numero)) $faltantes[] = 'Tel. Emergencia';
        
        if($empleado->es_practicante) {
            if(empty($empleado->curp)) $faltantes[] = 'CURP (Texto)';
        } else {
            if(empty($empleado->nss)) $faltantes[] = 'NSS';
            if(empty($empleado->rfc)) $faltantes[] = 'RFC';
            if(empty($empleado->curp)) $faltantes[] = 'CURP';
        }
    @endphp

    @if(count($faltantes) > 0)
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm animate-pulse">
        <div class="flex items-start gap-4">
            <div class="p-2 bg-red-100 rounded-full text-red-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-bold text-red-800">Acción Requerida: Faltan Datos en el Perfil</h3>
                <p class="text-xs text-red-600 mt-1">Tu expediente está incompleto. Captura estos datos para llegar al 100%:</p>
                <ul class="mt-2 list-disc list-inside text-xs text-red-700 font-medium grid grid-cols-1 sm:grid-cols-2 gap-1">
                    @foreach($faltantes as $falta)
                        <li>{{ $falta }}</li>
                    @endforeach
                </ul>
                <div class="mt-3">
                    <button onclick="openEditModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-lg shadow transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Completar Información Ahora
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- BARRA DE PROGRESO --}}
    @php 
        $porcentaje = $empleado->porcentaje_expediente;
        $color = $porcentaje < 50 ? 'bg-red-500' : ($porcentaje < 90 ? 'bg-yellow-500' : 'bg-green-500');
        $textoColor = $porcentaje < 50 ? 'text-red-600' : ($porcentaje < 90 ? 'text-yellow-600' : 'text-green-600');
    @endphp
    <div class="mb-8 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex justify-between items-end mb-2">
            <div>
                <h3 class="font-semibold text-slate-800">Completitud del Expediente</h3>
                <p class="text-xs text-slate-500">Evaluando requisitos para: 
                    <strong class="text-slate-700">{{ $empleado->es_practicante ? 'Practicantes / Becarios' : 'Empleados de Nómina' }}</strong>
                </p>
            </div>
            <span class="text-3xl font-bold {{ $textoColor }}">{{ $porcentaje }}%</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-3">
            <div class="{{ $color }} h-3 rounded-full transition-all duration-1000 shadow-sm" style="width: {{ $porcentaje }}%"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- COLUMNA IZQUIERDA: Herramientas --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- Switch Tipo Empleado --}}
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-bold text-slate-700">Tipo de Expediente</h4>
                    <p class="text-[10px] text-slate-400">Define los documentos requeridos.</p>
                </div>
                <form action="{{ route('rh.expedientes.update', $empleado->id) }}" method="POST" id="form-toggle-tipo">
                    @csrf @method('PUT')
                    <input type="hidden" name="toggle_practicante" value="1">
                    <input type="hidden" name="es_practicante" value="0">
                    
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="es_practicante" value="1" class="sr-only peer" onchange="document.getElementById('form-toggle-tipo').submit()" {{ $empleado->es_practicante ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-2 text-xs font-medium text-slate-600">{{ $empleado->es_practicante ? 'Practicante' : 'Empleado' }}</span>
                    </label>
                </form>
            </div>

            {{-- Checklist Status --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 px-4 py-3 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800 text-sm">Lista de Requisitos</h3>
                </div>
                <div class="p-4 space-y-2">
                    @foreach($checklistDocs as $reqDoc)
                        @php
                            $subido = $empleado->documentos->contains(function($doc) use ($reqDoc) {
                                $kwd = Str::lower($reqDoc);
                                if(Str::contains($kwd, 'titulo')) return Str::contains(Str::lower($doc->nombre), ['titulo', 'cedula', 'pasante']);
                                if(Str::contains($kwd, 'nss')) return Str::contains(Str::lower($doc->nombre), ['nss', 'imss', 'seguro']);
                                return Str::contains(Str::lower($doc->nombre), $kwd);
                            });
                        @endphp
                        <div class="flex items-center justify-between text-sm">
                            <span class="{{ $subido ? 'text-slate-500 line-through' : 'text-slate-800 font-medium' }}">{{ $reqDoc }}</span>
                            @if($subido)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> OK
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Falta
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Carga Masiva --}}
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold text-indigo-900 flex items-center gap-2 text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Auto-Indexar Carpeta
                    </h3>
                    <span class="text-[10px] font-bold bg-indigo-200 text-indigo-800 px-1.5 py-0.5 rounded">BETA</span>
                </div>
                <div class="relative group">
                    <input type="file" id="folderInput" webkitdirectory directory multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 rounded-lg shadow transition flex justify-center items-center gap-2">
                        Seleccionar Carpeta
                    </button>
                </div>
                <div id="loading-indicator" class="hidden mt-3 text-center">
                    <span class="flex items-center justify-center gap-2 text-xs font-bold text-indigo-800 animate-pulse">
                        <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Procesando...
                    </span>
                </div>
                <ul id="results-list" class="mt-3 space-y-1 text-[10px] max-h-32 overflow-y-auto custom-scrollbar"></ul>
            </div>

            {{-- Carga Manual --}}
            <div class="bg-blue-50 rounded-xl border border-blue-100 p-5 shadow-sm">
                <h3 class="font-bold text-blue-900 mb-4 flex items-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    Subir Manual
                </h3>
                <form action="{{ route('rh.expedientes.upload', $empleado->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <select name="nombre" class="w-full text-xs rounded-lg border-blue-200 focus:ring-blue-500 bg-white py-1.5">
                            <option value="">-- Seleccionar Requisito --</option>
                            @foreach($checklistDocs as $doc)
                                <option value="{{ $doc }}">{{ $doc }}</option>
                            @endforeach
                            <option value="Otro">Otro (Escribir manual)</option>
                        </select>
                    </div>
                    <div>
                        <select name="categoria" class="w-full text-xs rounded-lg border-blue-200 focus:ring-blue-500 bg-white py-1.5">
                            <option value="Identificación">Identificación</option>
                            <option value="Legal">Legal / Contratos</option>
                            <option value="Fiscal">Fiscal / IMSS</option>
                            <option value="Académico">Académico</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <input type="file" name="documento" required accept=".pdf,.jpg,.jpeg,.png,.xlsx,.xls,.csv,.doc,.docx" class="block w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-full file:border-0 file:text-[10px] file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200"/>
                    <button type="submit" class="w-full bg-blue-600 text-white rounded-lg py-2 text-xs font-bold shadow hover:bg-blue-700 transition">Guardar</button>
                </form>
            </div>
            
             {{-- Importar Excel --}}
             <div class="bg-white border border-slate-200 rounded-lg p-4 shadow-sm">
                 <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Formato ID</h4>
                 <form action="{{ route('rh.expedientes.import-excel', $empleado->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                    @csrf
                    <input type="file" name="archivo_excel" required accept=".xlsx,.xls,.csv" class="block w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200"/>
                    <button type="submit" class="w-full px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded hover:bg-slate-700 transition">Subir Formato ID</button>
                 </form>
            </div>
        </div>

        {{-- COLUMNA DERECHA: Documentos --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Info Card Resumen --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-4 relative group">
                 <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                    <button onclick="openEditModal()" class="bg-blue-50 text-blue-600 p-1.5 rounded-lg border border-blue-100 hover:bg-blue-100" title="Editar Información">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </button>
                </div>
                <div class="bg-slate-50 px-4 py-2 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800 text-sm">Resumen de Datos Capturados</h3>
                </div>
                <div class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                     <div>
                        <p class="font-bold text-slate-400">Domicilio</p>
                        <p class="text-slate-800">{{ $empleado->direccion ?? 'NR' }}</p>
                     </div>
                     <div>
                        <p class="font-bold text-slate-400">Teléfono</p>
                        <p class="text-slate-800">{{ $empleado->telefono ?? 'NR' }}</p>
                     </div>
                     <div>
                        <p class="font-bold text-slate-400">Emergencia</p>
                        <p class="text-slate-800">{{ $empleado->contacto_emergencia_nombre ?? 'NR' }}</p>
                        <p class="text-slate-500">{{ $empleado->contacto_emergencia_numero ?? '' }}</p>
                     </div>
                </div>
            </div>

            @if($docsGrouped->isEmpty())
                <div class="bg-white rounded-xl border-2 border-dashed border-slate-300 p-12 text-center h-64 flex flex-col items-center justify-center">
                    <div class="bg-slate-50 w-20 h-20 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 012 2v-5a2 2 0 01-2-2H9a2 2 0 01-2-2v-5a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900">Expediente vacío</h3>
                    <p class="text-slate-500 mt-1 max-w-xs mx-auto text-sm">Sube documentos para comenzar.</p>
                </div>
            @else
                @foreach($docsGrouped as $categoria => $docs)
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition duration-300">
                        <div class="bg-slate-50 px-6 py-3 border-b border-slate-200 flex justify-between items-center">
                            <h3 class="font-bold text-slate-700 flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                                {{ $categoria }}
                            </h3>
                            <span class="bg-white border border-slate-200 px-2 py-0.5 rounded-full text-[10px] font-bold text-slate-500">{{ count($docs) }}</span>
                        </div>
                        <ul class="divide-y divide-slate-100">
                            @foreach($docs as $doc)
                                <li class="px-6 py-3 flex flex-col sm:flex-row sm:items-center justify-between hover:bg-slate-50 transition group gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="bg-red-50 text-red-600 p-2 rounded-lg">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800">{{ $doc->nombre }}</p>
                                            <p class="text-[10px] text-slate-400 flex items-center gap-2 mt-0.5">
                                                <span>{{ $doc->created_at->format('d/m/Y') }}</span>
                                                @if($doc->fecha_vencimiento)
                                                    <span class="text-slate-300">•</span>
                                                    <span class="{{ $doc->fecha_vencimiento->isPast() ? 'text-red-500 font-bold' : 'text-orange-500' }}">Vence: {{ $doc->fecha_vencimiento->format('d/m/Y') }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-200">
                                        <a href="{{ asset('storage/'.$doc->ruta_archivo) }}" target="_blank" class="px-3 py-1 bg-white border border-slate-200 rounded text-xs text-slate-600 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition shadow-sm">Ver</a>
                                        <form action="{{ route('rh.expedientes.delete-doc', $doc->id) }}" method="POST" onsubmit="return confirm('¿Eliminar documento?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="px-3 py-1 bg-white border border-slate-200 rounded text-xs text-slate-600 hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition shadow-sm">X</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</main>

{{-- MODAL DE EDICIÓN DE DATOS (FUSIÓN DEL EDIT) --}}
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeEditModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        {{-- Modal Content --}}
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center mb-5 border-b pb-2 border-slate-100">
                    <h3 class="text-lg leading-6 font-bold text-slate-900" id="modal-title">Editar Información Completa</h3>
                    <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('rh.expedientes.update',$empleado) }}" id="editForm" class="space-y-6">
                    @csrf @method('PUT')

                    {{-- 1. Datos Corporativos --}}
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-3">Corporativo</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Nombre</label>
                                <input type="text" value="{{ $empleado->nombre }}" disabled class="w-full rounded-lg border-slate-200 bg-slate-100 text-xs" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">ID Empleado</label>
                                <input type="text" name="id_empleado" value="{{ $empleado->id_empleado }}" class="w-full rounded-lg border-slate-300 text-xs focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Puesto</label>
                                <input type="text" name="posicion" value="{{ $empleado->posicion }}" class="w-full rounded-lg border-slate-300 text-xs focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                        </div>
                    </div>

                    {{-- 2. Datos Personales --}}
                    <div>
                         <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider mb-3">Información Personal</h4>
                         <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Domicilio Completo</label>
                                <input type="text" name="direccion" value="{{ $empleado->direccion }}" placeholder="Calle, Número, Colonia" class="w-full rounded-lg border-slate-300 text-xs focus:ring-indigo-500" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Ciudad</label>
                                <input type="text" name="ciudad" value="{{ $empleado->ciudad }}" class="w-full rounded-lg border-slate-300 text-xs focus:ring-indigo-500" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Teléfono Celular <span class="text-red-500">*</span></label>
                                <input type="text" name="telefono" value="{{ $empleado->telefono }}" required class="w-full rounded-lg border-slate-300 text-xs focus:ring-indigo-500" />
                            </div>
                         </div>
                    </div>

                    {{-- 3. Fiscales --}}
                    <div>
                         <h4 class="text-xs font-bold text-green-900 uppercase tracking-wider mb-3">Datos Fiscales</h4>
                         <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">CURP <span class="text-red-500">*</span></label>
                                <input type="text" name="curp" value="{{ $empleado->curp }}" required class="w-full rounded-lg border-slate-300 text-xs focus:ring-green-500 uppercase" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">RFC</label>
                                <input type="text" name="rfc" value="{{ $empleado->rfc }}" class="w-full rounded-lg border-slate-300 text-xs focus:ring-green-500 uppercase" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">NSS</label>
                                <input type="text" name="nss" value="{{ $empleado->nss }}" class="w-full rounded-lg border-slate-300 text-xs focus:ring-green-500" />
                            </div>
                         </div>
                    </div>

                    {{-- 4. Emergencia --}}
                    <div class="bg-red-50 p-4 rounded-xl border border-red-100">
                         <h4 class="text-xs font-bold text-red-900 uppercase tracking-wider mb-3">Emergencia & Salud</h4>
                         <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Contacto Emergencia</label>
                                <input type="text" name="contacto_emergencia_nombre" value="{{ $empleado->contacto_emergencia_nombre }}" required class="w-full rounded-lg border-slate-300 text-xs focus:ring-red-500" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Teléfono Emergencia</label>
                                <input type="text" name="contacto_emergencia_numero" value="{{ $empleado->contacto_emergencia_numero }}" required class="w-full rounded-lg border-slate-300 text-xs focus:ring-red-500" />
                            </div>
                             <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Alergias</label>
                                <input type="text" name="alergias" value="{{ $empleado->alergias }}" class="w-full rounded-lg border-slate-300 text-xs focus:ring-red-500" />
                            </div>
                         </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPTS (Modal + Carga Inteligente) --}}
<script>
    // --- LÓGICA DEL MODAL ---
    function openEditModal() {
        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    // --- LÓGICA DE CARGA INTELIGENTE ---
    document.addEventListener('DOMContentLoaded', () => {
        const folderInput = document.getElementById('folderInput');
        const resultsList = document.getElementById('results-list');
        const loader = document.getElementById('loading-indicator');
        
        const reglas = [
            { keys: ['ine', 'ife', 'identificacion', 'id_oficial'], name: 'INE', cat: 'Identificación' },
            { keys: ['curp'], name: 'CURP', cat: 'Identificación' },
            { keys: ['domicilio', 'agua', 'luz', 'cfe', 'predial'], name: 'Comprobante de Domicilio', cat: 'Identificación' },
            { keys: ['nss', 'imss', 'seguro', 'social'], name: 'NSS', cat: 'Fiscal' },
            { keys: ['rfc', 'csf', 'situacion', 'fiscal', 'constancia'], name: 'Constancia de Situacion Fiscal', cat: 'Fiscal' },
            { keys: ['nacimiento', 'acta'], name: 'Acta de Nacimiento', cat: 'Identificación' },
            { keys: ['titulo', 'cedula', 'profesional'], name: 'Titulo', cat: 'Académico' },
            { keys: ['contrato', 'laboral'], name: 'Contrato', cat: 'Legal' },
            { keys: ['cuenta', 'banco', 'clabe', 'santander', 'bbva'], name: 'Estado de Cuenta', cat: 'Administrativo' },
            { keys: ['formato', 'ficha', 'ingreso', 'xls', 'xlsx'], name: 'Formato ID', cat: 'Interno' }
        ];

        folderInput.addEventListener('change', async function(e) {
            const files = Array.from(e.target.files).filter(f => !f.name.startsWith('.'));
            if(files.length === 0) return;

            if(!confirm(`Se encontraron ${files.length} archivos. ¿Procesar carga inteligente?`)) {
                this.value = ''; return;
            }

            loader.classList.remove('hidden');
            resultsList.innerHTML = '';
            let subidos = 0;

            for (let file of files) {
                let filename = file.name.toLowerCase();
                let match = reglas.find(r => r.keys.some(k => filename.includes(k)));

                if (match) {
                    // Si es Excel, usamos la ruta especial de importación
                    if(match.name === 'Formato ID' && (filename.endsWith('xls') || filename.endsWith('xlsx'))) {
                         const exito = await subirExcel(file);
                         agregarLog(file.name, 'Formato ID (Procesado)', exito);
                         if(exito) subidos++;
                    } else {
                         const exito = await subirArchivo(file, match.name, match.cat);
                         agregarLog(file.name, match.name, exito);
                         if(exito) subidos++;
                    }
                } else {
                    agregarLog(file.name, 'No reconocido', false);
                }
            }

            loader.classList.add('hidden');
            setTimeout(() => { 
                alert(`Proceso finalizado. ${subidos} documentos importados.`);
                window.location.reload(); 
            }, 500);
        });

        // Subida normal de PDF/Img
        async function subirArchivo(file, nombreDoc, categoria) {
            let formData = new FormData();
            formData.append('documento', file);
            formData.append('nombre', nombreDoc);
            formData.append('categoria', categoria);
            formData.append('_token', '{{ csrf_token() }}'); 
            try {
                let r = await fetch("{{ route('rh.expedientes.upload', $empleado->id) }}", { method: 'POST', body: formData });
                return r.ok;
            } catch (e) { return false; }
        }

        // Subida especial de Excel
        async function subirExcel(file) {
            let formData = new FormData();
            formData.append('archivo_excel', file);
            formData.append('_token', '{{ csrf_token() }}');
            try {
                let r = await fetch("{{ route('rh.expedientes.import-excel', $empleado->id) }}", { method: 'POST', body: formData });
                return r.ok;
            } catch (e) { return false; }
        }

        function agregarLog(archivo, resultado, exito) {
            const li = document.createElement('li');
            li.className = exito ? 'text-green-600 flex items-center gap-2' : 'text-slate-400 flex items-center gap-2 italic opacity-60';
            li.innerHTML = `<span class="${exito?'bg-green-100 text-green-700':'bg-slate-100 text-slate-500'} px-1 rounded font-bold text-[9px]">${exito?'OK':'SKIP'}</span> <span class="truncate max-w-[150px]">${archivo}</span> ➜ ${resultado}`;
            resultsList.prepend(li);
        }
    });
</script>
@endsection