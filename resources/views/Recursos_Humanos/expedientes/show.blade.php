@extends('layouts.erp')
@section('title','Expediente de ' . $empleado->nombre)
@section('content')
<main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    
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
                <p class="text-xs text-slate-400 mt-1">{{ $empleado->correo }}</p>
            </div>
        </div>
        
        <div class="flex gap-2">
            <a href="{{ route('rh.expedientes.index') }}" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 text-sm font-medium hover:bg-slate-50 shadow-sm transition">Volver</a>
        </div>
    </div>

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
        <div class="lg:col-span-1 space-y-6">
            
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-bold text-slate-700">Tipo de Expediente</h4>
                    <p class="text-[10px] text-slate-400">Define los documentos requeridos.</p>
                </div>
                <form action="{{ route('rh.expedientes.update', $empleado->id) }}" method="POST" id="form-toggle-tipo">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="toggle_practicante" value="1">
                    <input type="hidden" name="es_practicante" value="0">
                    
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="es_practicante" value="1" class="sr-only peer" onchange="document.getElementById('form-toggle-tipo').submit()" {{ $empleado->es_practicante ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-2 text-xs font-medium text-slate-600">{{ $empleado->es_practicante ? 'Practicante' : 'Empleado' }}</span>
                    </label>
                </form>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 px-4 py-3 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800 text-sm">Información General</h3>
                </div>
                <div class="p-4 space-y-3">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Domicilio</p>
                        <p class="text-sm text-slate-800">{{ $empleado->direccion ?? 'No registrado' }}</p>
                        <div class="flex gap-2 text-xs text-slate-500 mt-0.5">
                            <span>{{ $empleado->ciudad }}</span>
                            <span>•</span>
                            <span>{{ $empleado->estado_federativo }}</span>
                            <span>•</span>
                            <span>CP: {{ $empleado->codigo_postal }}</span>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 my-2"></div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Celular</p>
                            <p class="text-sm text-slate-800">{{ $empleado->telefono ?? '--' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tel. Casa</p>
                            <p class="text-sm text-slate-800">{{ $empleado->telefono_casa ?? '--' }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Correo Personal</p>
                        <p class="text-sm text-slate-800 break-all">{{ $empleado->correo_personal ?? '--' }}</p>
                    </div>

                    <div class="border-t border-slate-100 my-2"></div>

                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">En caso de emergencia</p>
                        <p class="text-sm font-medium text-slate-800">{{ $empleado->contacto_emergencia_nombre ?? 'No registrado' }}</p>
                        <p class="text-xs text-slate-500">{{ $empleado->contacto_emergencia_parentesco }} • {{ $empleado->contacto_emergencia_numero }}</p>
                    </div>

                    <div class="border-t border-slate-100 my-2"></div>

                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Información Médica</p>
                        <div class="text-xs space-y-1">
                            <p class="text-slate-700"><span class="font-semibold">Alergias:</span> {{ $empleado->alergias ?? 'Ninguna' }}</p>
                            <p class="text-slate-700"><span class="font-semibold">Crónicas:</span> {{ $empleado->enfermedades_cronicas ?? 'Ninguna' }}</p>
                        </div>
                    </div>
                </div>
            </div>

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
                                    <x-ui.icon name="check" class="w-3 h-3"/> Completo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">
                                    <x-ui.icon name="x-mark" class="w-3 h-3"/> Falta
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-blue-50 rounded-xl border border-blue-100 p-5 shadow-sm">
                <h3 class="font-bold text-blue-900 mb-4 flex items-center gap-2">
                    <x-ui.icon name="cloud-arrow-up" class="w-5 h-5"/> Subir Documento
                </h3>
                <form action="{{ route('rh.expedientes.upload', $empleado->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-blue-800 mb-1">Nombre del Archivo</label>
                        <select name="nombre" class="w-full text-sm rounded-lg border-blue-200 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="">-- Seleccionar Requisito --</option>
                            @foreach($checklistDocs as $doc)
                                <option value="{{ $doc }}">{{ $doc }}</option>
                            @endforeach
                            <option value="Otro">Otro (Escribir manual)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-blue-800 mb-1">Categoría</label>
                        <select name="categoria" class="w-full text-sm rounded-lg border-blue-200 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="Identificación">Identificación</option>
                            <option value="Legal">Legal / Contratos</option>
                            <option value="Fiscal">Fiscal / IMSS</option>
                            <option value="Académico">Académico</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-blue-800 mb-1">Seleccionar Archivo</label>
                        <input type="file" name="documento" required class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition"/>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-blue-800 mb-1">Fecha Vencimiento (Opcional)</label>
                        <input type="date" name="fecha_vencimiento" class="w-full text-sm rounded-lg border-blue-200 focus:ring-blue-500 bg-white"/>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white rounded-lg py-2.5 text-sm font-bold shadow-md hover:bg-blue-700 hover:shadow-lg transition transform hover:-translate-y-0.5">Guardar Archivo</button>
                </form>
            </div>
            
            <div class="bg-white border border-slate-200 rounded-lg p-4 shadow-sm">
                 <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Utilidades</h4>
                 <p class="text-xs text-slate-600 mb-3">Actualizar datos generales importando el "Formato ID" (Excel).</p>
                 <form action="{{ route('rh.expedientes.import-excel', $empleado->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                    @csrf
                    <input type="file" name="archivo_excel" required class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200"/>
                    <button type="submit" class="w-full px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded hover:bg-slate-700 transition">Procesar Excel</button>
                 </form>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            @if($docsGrouped->isEmpty())
                <div class="bg-white rounded-xl border-2 border-dashed border-slate-300 p-12 text-center h-full flex flex-col items-center justify-center">
                    <div class="bg-slate-50 w-20 h-20 rounded-full flex items-center justify-center mb-4">
                        <x-ui.icon name="folder-open" class="w-10 h-10 text-slate-400"/>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900">Expediente vacío</h3>
                    <p class="text-slate-500 mt-1 max-w-xs mx-auto">El expediente digital aun no tiene archivos. Usa el formulario de la izquierda para subir documentos.</p>
                </div>
            @else
                @foreach($docsGrouped as $categoria => $docs)
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition duration-300">
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                            <h3 class="font-bold text-slate-700 flex items-center gap-2">
                                <x-ui.icon name="folder" class="w-5 h-5 text-blue-500"/>
                                {{ $categoria }}
                            </h3>
                            <span class="bg-white border border-slate-200 px-2.5 py-0.5 rounded-full text-xs font-bold text-slate-500">{{ count($docs) }} docs</span>
                        </div>
                        <ul class="divide-y divide-slate-100">
                            @foreach($docs as $doc)
                                <li class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between hover:bg-slate-50 transition group gap-4">
                                    <div class="flex items-center gap-4">
                                        <div class="bg-red-50 text-red-600 p-3 rounded-xl">
                                            <x-ui.icon name="document-text" class="w-6 h-6"/>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800">{{ $doc->nombre }}</p>
                                            <p class="text-xs text-slate-400 flex items-center gap-2 mt-0.5">
                                                <span>Subido {{ $doc->created_at->diffForHumans() }}</span>
                                                @if($doc->fecha_vencimiento)
                                                    <span class="text-slate-300">•</span>
                                                    <span class="{{ $doc->fecha_vencimiento->isPast() ? 'text-red-500 font-bold' : 'text-orange-500' }}">
                                                        Vence: {{ $doc->fecha_vencimiento->format('d/m/Y') }}
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- BOTONES DE ACCIÓN: VER Y BORRAR --}}
                                    <div class="flex items-center gap-2 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-200">
                                        <a href="{{ asset('storage/'.$doc->ruta_archivo) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-600 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition shadow-sm">
                                            <x-ui.icon name="eye" class="w-4 h-4"/> Ver
                                        </a>
                                        
                                        <form action="{{ route('rh.expedientes.delete-doc', $doc->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de ELIMINAR este documento? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-600 hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition shadow-sm">
                                                <x-ui.icon name="trash" class="w-4 h-4"/> Eliminar
                                            </button>
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
@endsection