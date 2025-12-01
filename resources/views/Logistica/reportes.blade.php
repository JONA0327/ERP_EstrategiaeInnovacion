@extends('layouts.erp')

@section('title','Reportes - Logística')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/logistica/matriz-seguimiento.css') }}">
@endpush

{{-- Cargar Chart.js directamente (evitar conflictos con stacks) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

@section('content')
    <main class="bg-gradient-to-br from-white via-indigo-50 to-indigo-100 min-h-screen">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('logistica.index') }}" class="inline-flex items-center text-indigo-700 hover:text-indigo-900 mb-4">
                <span class="mr-2">&larr;</span> Regresar
            </a>
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-slate-800">Reportes de Operaciones</h1>
                <div class="flex gap-2">
                    <button onclick="openEmailModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-envelope mr-2"></i>Enviar por Correo
                    </button>
                    <a href="{{ route('logistica.reportes.export', request()->query()) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                        <i class="fas fa-download mr-2"></i>Descargar CSV
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-xl shadow p-6 mb-6">
                <form method="GET" action="{{ route('logistica.reportes') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Período -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Período</label>
                        <select name="periodo" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            <option value="semanal" {{ request('periodo') === 'semanal' ? 'selected' : '' }}>Última Semana</option>
                            <option value="mensual" {{ request('periodo') === 'mensual' ? 'selected' : '' }}>Último Mes</option>
                            <option value="anual" {{ request('periodo') === 'anual' ? 'selected' : '' }}>Último Año</option>
                        </select>
                    </div>

                    <!-- Mes y Año -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Mes/Año</label>
                        <div class="flex gap-2">
                            <select name="mes" class="w-1/2 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">Mes</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ request('mes') == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m)->format('M') }}</option>
                                @endfor
                            </select>
                            <select name="anio" class="w-1/2 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">Año</option>
                                @for($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}" {{ request('anio') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Cliente -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Cliente</label>
                        <select name="cliente" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todos los Clientes</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c }}" {{ request('cliente') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todos los Status</option>
                            <option value="In Process" {{ request('status') === 'In Process' ? 'selected' : '' }}>En Proceso</option>
                            <option value="Out of Metric" {{ request('status') === 'Out of Metric' ? 'selected' : '' }}>Fuera de Métrica</option>
                            <option value="Done" {{ request('status') === 'Done' ? 'selected' : '' }}>Completado</option>
                        </select>
                    </div>

                    <!-- Fecha Desde -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Desde</label>
                        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Fecha Hasta -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Hasta</label>
                        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Botones -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Filtrar
                        </button>
                        <a href="{{ route('logistica.reportes') }}" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="font-semibold text-slate-700 mb-2">Resumen por Status</h2>
                    <canvas id="statusChart" style="max-height: 280px"></canvas>
                </div>
                <div class="bg-white rounded-xl shadow p-6 lg:col-span-2">
                    <h2 class="font-semibold text-slate-700 mb-2">Últimas Operaciones</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-slate-100 text-slate-700">
                                    <th class="px-3 py-2 text-left">ID</th>
                                    <th class="px-3 py-2 text-left">Ejecutivo</th>
                                    <th class="px-3 py-2 text-left">Cliente</th>
                                    <th class="px-3 py-2 text-left">Tipo</th>
                                    <th class="px-3 py-2 text-left">Status Actual</th>
                                    <th class="px-3 py-2 text-left">Resultado</th>
                                    <th class="px-3 py-2 text-left">Días Tránsito</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($operaciones as $op)
                                    <tr class="border-b">
                                        <td class="px-3 py-2">{{ $op->id }}</td>
                                        <td class="px-3 py-2">{{ $op->ejecutivo }}</td>
                                        <td class="px-3 py-2">{{ $op->cliente }}</td>
                                        <td class="px-3 py-2">{{ $op->tipo_operacion_enum }}</td>
                                        <td class="px-3 py-2">
                                            @php
                                                $statusFinal = ($op->status_manual === 'Done') ? 'Done' : $op->status_calculado;
                                                $colorFinal = ($op->status_manual === 'Done') ? 'verde' : $op->color_status;
                                                $statusDisplay = match($statusFinal) {
                                                    'In Process' => 'En Proceso',
                                                    'Out of Metric' => 'Fuera de Métrica',
                                                    'Done' => 'Completado',
                                                    default => $statusFinal ?? 'En Proceso'
                                                };
                                                $badgeClass = match($colorFinal) {
                                                    'verde' => 'bg-green-100 text-green-800',
                                                    'amarillo' => 'bg-yellow-100 text-yellow-800',
                                                    'rojo' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                            @endphp
                                            <span class="px-2 py-1 rounded text-xs {{ $badgeClass }}">
                                                {{ $statusDisplay }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">{{ $op->resultado ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $op->dias_transito ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-6 text-center text-slate-500">Sin operaciones recientes</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Verificar que Chart esté disponible
        if (typeof Chart === 'undefined') {
            console.error('Chart.js no se cargó correctamente');
        }
        const stats = @json($stats);
        const total = (stats.en_proceso || 0) + (stats.fuera_metrica || 0) + (stats.done || 0);
        const showEmptyMsg = total === 0;
        const ctx = document.getElementById('statusChart').getContext('2d');
        if (!showEmptyMsg) {
          new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['En Proceso', 'Fuera Métrica', 'Done'],
                datasets: [{
                    label: 'Operaciones',
                    data: [stats.en_proceso, stats.fuera_metrica, stats.done],
                    backgroundColor: ['#facc15','#ef4444','#22c55e']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, precision: 0 } }
            }
          });
        } else {
          ctx.font = '14px system-ui';
          ctx.fillStyle = '#64748b';
          ctx.fillText('Sin datos para mostrar (no hay operaciones aún)', 20, 40);
        }
    </script>

    <!-- Modal Enviar por Correo -->
    <div id="emailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-slate-800">Enviar Reporte por Correo</h2>
                    <button onclick="closeEmailModal()" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <!-- Paso 1: Selección de Cliente -->
                <div id="step1" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Seleccionar Cliente</label>
                        <select id="emailCliente" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Seleccione un cliente --</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button onclick="loadClientOperations()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Continuar
                    </button>
                </div>

                <!-- Paso 2: Vista Previa y Correos -->
                <div id="step2" class="hidden space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-900 mb-2">Cliente: <span id="selectedClientName"></span></h3>
                        <p class="text-sm text-blue-700">Total de operaciones: <span id="totalOperations">0</span></p>
                    </div>

                    <!-- Vista Previa de Operaciones (CSV Completo) -->
                    <div>
                        <h4 class="font-semibold text-slate-700 mb-2">Vista Previa del Reporte CSV</h4>
                        <p class="text-xs text-slate-500 mb-2">Este es el contenido exacto que se incluirá en el archivo CSV adjunto</p>
                        <div class="border border-slate-200 rounded-lg max-h-96 overflow-auto">
                            <table class="min-w-full text-xs">
                                <thead class="bg-slate-100 sticky top-0">
                                    <tr>
                                        <th class="px-2 py-2 text-left border-r">No.</th>
                                        <th class="px-2 py-2 text-left border-r">Ejecutivo</th>
                                        <th class="px-2 py-2 text-left border-r">Operación</th>
                                        <th class="px-2 py-2 text-left border-r">Cliente</th>
                                        <th class="px-2 py-2 text-left border-r">Proveedor/Cliente Final</th>
                                        <th class="px-2 py-2 text-left border-r">Fecha Embarque</th>
                                        <th class="px-2 py-2 text-left border-r">No. Factura</th>
                                        <th class="px-2 py-2 text-left border-r">T. Operación</th>
                                        <th class="px-2 py-2 text-left border-r">Clave</th>
                                        <th class="px-2 py-2 text-left border-r">Referencia Interna</th>
                                        <th class="px-2 py-2 text-left border-r">Aduana</th>
                                        <th class="px-2 py-2 text-left border-r">A.A</th>
                                        <th class="px-2 py-2 text-left border-r">Referencia A.A</th>
                                        <th class="px-2 py-2 text-left border-r">No Ped</th>
                                        <th class="px-2 py-2 text-left border-r">Transporte</th>
                                        <th class="px-2 py-2 text-left border-r">Fecha Arribo Aduana</th>
                                        <th class="px-2 py-2 text-left border-r">Guía/BL</th>
                                        <th class="px-2 py-2 text-left border-r">Status</th>
                                        <th class="px-2 py-2 text-left border-r">Fecha Modulación</th>
                                        <th class="px-2 py-2 text-left border-r">Fecha Arribo Planta</th>
                                        <th class="px-2 py-2 text-left border-r">Resultado</th>
                                        <th class="px-2 py-2 text-left border-r">Target</th>
                                        <th class="px-2 py-2 text-left border-r">Días Tránsito</th>
                                        <th class="px-2 py-2 text-left border-r">Post-Op Completas</th>
                                        <th class="px-2 py-2 text-left border-r">Post-Op Pendientes</th>
                                        <th class="px-2 py-2 text-left">Comentarios</th>
                                    </tr>
                                </thead>
                                <tbody id="previewTable"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Correos -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Correos Destinatarios</label>
                        <div id="emailsList" class="space-y-2 mb-2"></div>
                        <div class="flex gap-2">
                            <input type="email" id="manualEmail" placeholder="Agregar correo manualmente" class="flex-1 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <button onclick="addManualEmail()" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Cuerpo del Mensaje -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Mensaje</label>
                        <textarea id="emailMessage" rows="4" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Escriba el mensaje del correo...">Estimado cliente,

Adjunto encontrará el reporte de operaciones logísticas actualizado.

Saludos cordiales.</textarea>
                    </div>

                    <div class="flex gap-2">
                        <button onclick="backToStep1()" class="flex-1 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">
                            Atrás
                        </button>
                        <button onclick="sendEmail()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-paper-plane mr-2"></i>Enviar Correo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedEmails = [];
        let selectedClient = '';

        function openEmailModal() {
            document.getElementById('emailModal').classList.remove('hidden');
        }

        function closeEmailModal() {
            document.getElementById('emailModal').classList.add('hidden');
            document.getElementById('step1').classList.remove('hidden');
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('emailCliente').value = '';
            selectedEmails = [];
        }

        async function loadClientOperations() {
            const cliente = document.getElementById('emailCliente').value;
            if (!cliente) {
                alert('Por favor seleccione un cliente');
                return;
            }

            try {
                const response = await fetch(`{{ route('logistica.reportes') }}/cliente?cliente=${encodeURIComponent(cliente)}`);
                const data = await response.json();

                if (data.success) {
                    selectedClient = data.cliente;
                    selectedEmails = data.correos || [];

                    document.getElementById('selectedClientName').textContent = data.cliente;
                    document.getElementById('totalOperations').textContent = data.total;

                    // Llenar vista previa con todas las columnas del CSV
                    const previewTable = document.getElementById('previewTable');
                    previewTable.innerHTML = data.operaciones.map(op => `
                        <tr class="border-b hover:bg-slate-50">
                            <td class="px-2 py-2 border-r">${op.no}</td>
                            <td class="px-2 py-2 border-r">${op.ejecutivo}</td>
                            <td class="px-2 py-2 border-r">${op.operacion}</td>
                            <td class="px-2 py-2 border-r">${op.cliente}</td>
                            <td class="px-2 py-2 border-r">${op.proveedor_cliente_final}</td>
                            <td class="px-2 py-2 border-r">${op.fecha_embarque}</td>
                            <td class="px-2 py-2 border-r">${op.no_factura}</td>
                            <td class="px-2 py-2 border-r">${op.tipo_operacion}</td>
                            <td class="px-2 py-2 border-r">${op.clave}</td>
                            <td class="px-2 py-2 border-r">${op.referencia_interna}</td>
                            <td class="px-2 py-2 border-r">${op.aduana}</td>
                            <td class="px-2 py-2 border-r">${op.aa}</td>
                            <td class="px-2 py-2 border-r">${op.referencia_aa}</td>
                            <td class="px-2 py-2 border-r">${op.no_pedimento}</td>
                            <td class="px-2 py-2 border-r">${op.transporte}</td>
                            <td class="px-2 py-2 border-r">${op.fecha_arribo_aduana}</td>
                            <td class="px-2 py-2 border-r">${op.guia_bl}</td>
                            <td class="px-2 py-2 border-r font-semibold">${op.status}</td>
                            <td class="px-2 py-2 border-r">${op.fecha_modulacion}</td>
                            <td class="px-2 py-2 border-r">${op.fecha_arribo_planta}</td>
                            <td class="px-2 py-2 border-r">${op.resultado}</td>
                            <td class="px-2 py-2 border-r">${op.target}</td>
                            <td class="px-2 py-2 border-r">${op.dias_transito}</td>
                            <td class="px-2 py-2 border-r text-xs">${op.post_operaciones_completas}</td>
                            <td class="px-2 py-2 border-r text-xs">${op.post_operaciones_pendientes}</td>
                            <td class="px-2 py-2 text-xs max-w-xs truncate" title="${op.comentarios}">${op.comentarios}</td>
                        </tr>
                    `).join('');

                    // Mostrar correos
                    renderEmailsList();

                    // Cambiar a paso 2
                    document.getElementById('step1').classList.add('hidden');
                    document.getElementById('step2').classList.remove('hidden');
                } else {
                    alert(data.message || 'Error al cargar operaciones');
                }
            } catch (error) {
                alert('Error al cargar los datos del cliente');
                console.error(error);
            }
        }

        function renderEmailsList() {
            const emailsList = document.getElementById('emailsList');
            emailsList.innerHTML = selectedEmails.map((email, index) => `
                <div class="flex items-center gap-2 bg-blue-50 px-3 py-2 rounded">
                    <i class="fas fa-envelope text-blue-600"></i>
                    <span class="flex-1 text-sm">${email}</span>
                    <button onclick="removeEmail(${index})" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `).join('');
        }

        function addManualEmail() {
            const emailInput = document.getElementById('manualEmail');
            const email = emailInput.value.trim();
            
            if (!email) return;
            
            // Validación básica de email
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                alert('Por favor ingrese un correo válido');
                return;
            }

            if (!selectedEmails.includes(email)) {
                selectedEmails.push(email);
                renderEmailsList();
                emailInput.value = '';
            }
        }

        function removeEmail(index) {
            selectedEmails.splice(index, 1);
            renderEmailsList();
        }

        function backToStep1() {
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('step1').classList.remove('hidden');
        }

        function sendEmail() {
            if (selectedEmails.length === 0) {
                alert('Por favor agregue al menos un correo destinatario');
                return;
            }

            const message = document.getElementById('emailMessage').value;
            const subject = `Reporte de Operaciones - ${selectedClient}`;
            
            // Generar URL de descarga del reporte para este cliente
            const reportUrl = `{{ route('logistica.reportes.export') }}?cliente=${encodeURIComponent(selectedClient)}`;
            
            // Construir el cuerpo del correo
            const emailBody = `${message}`;
            
            // Primero descargar el CSV
            const downloadLink = document.createElement('a');
            downloadLink.href = reportUrl;
            downloadLink.download = `Reporte_${selectedClient.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
            
            // Esperar un momento y luego abrir Outlook con el mailto
            setTimeout(() => {
                // Crear mailto link para Outlook
                const mailtoLink = `mailto:${selectedEmails.join(';')}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(emailBody)}`;
                
                // Abrir Outlook
                window.location.href = mailtoLink;
                
                // Mostrar instrucción al usuario
                alert('Se ha descargado el archivo CSV.\n\nPor favor, adjúntelo manualmente al correo de Outlook que se está abriendo.');
                
                // Cerrar modal después de un momento
                setTimeout(() => {
                    closeEmailModal();
                }, 1500);
            }, 500);
        }
    </script>
@endsection
