@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">📄 Gestión de Pedimentos</h1>
                        <p class="text-gray-600 mt-1">Administra el estado de pago de pedimentos asociados a operaciones logísticas</p>
                    </div>
                    <div class="flex space-x-4">
                        <button onclick="marcarPagadosSeleccionados()" id="btnMarcarPagados" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 hidden">
                            ✅ Marcar como Pagados
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="px-6 py-4 bg-gray-50">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</div>
                        <div class="text-sm text-gray-600">Total</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ $stats['pendientes'] }}</div>
                        <div class="text-sm text-gray-600">Pendientes</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $stats['pagados'] }}</div>
                        <div class="text-sm text-gray-600">Pagados</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600">{{ $stats['vencidos'] }}</div>
                        <div class="text-sm text-gray-600">Vencidos</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4">
                <form method="GET" action="{{ route('logistica.pedimentos.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Pedimento</label>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" 
                               placeholder="Número de pedimento..." 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado de Pago</label>
                        <select name="estado_pago" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" {{ request('estado_pago') == 'pendiente' ? 'selected' : '' }}>⏳ Pendiente</option>
                            <option value="pagado" {{ request('estado_pago') == 'pagado' ? 'selected' : '' }}>✅ Pagado</option>
                            <option value="vencido" {{ request('estado_pago') == 'vencido' ? 'selected' : '' }}>❌ Vencido</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                            🔍 Filtrar
                        </button>
                    </div>
                    <div class="flex items-end">
                        <a href="{{ route('logistica.pedimentos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200">
                            🔄 Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Pedimentos -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="rounded border-gray-300">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedimento</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Pago</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operaciones</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($paginatedPedimentos as $pedimento)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($pedimento->estado_pago !== 'pagado')
                                        <input type="checkbox" name="pedimentos[]" value="{{ $pedimento->id }}" onchange="updateSelectAll()" class="pedimento-checkbox rounded border-gray-300">
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $pedimento->clave }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($pedimento->descripcion, 30) }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($pedimento->getColorEstado() == 'green') bg-green-100 text-green-800
                                        @elseif($pedimento->getColorEstado() == 'red') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ $pedimento->getTextoEstado() }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $pedimento->fecha_pago ? $pedimento->fecha_pago->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $pedimento->monto ? '$' . number_format($pedimento->monto, 2) : '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                        {{ $pedimento->operaciones_count ?? 0 }} operación(es)
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button onclick="editarPedimento({{ $pedimento->id }})" class="text-blue-600 hover:text-blue-900">
                                        ✏️ Editar
                                    </button>
                                    <button onclick="verDetalles({{ $pedimento->id }})" class="text-green-600 hover:text-green-900">
                                        👁️ Ver
                                    </button>
                                    <button onclick="eliminarPedimento({{ $pedimento->id }})" class="text-red-600 hover:text-red-900">
                                        🗑️ Eliminar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <div class="text-6xl mb-4">📄</div>
                                        <div class="text-lg font-medium">No hay pedimentos disponibles</div>
                                        <div class="text-sm">Los pedimentos aparecerán aquí cuando se generen operaciones logísticas</div>
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
<div id="modalEditar" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Estado de Pago</h3>
            <form id="formEditar">
                <input type="hidden" id="pedimentoId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado de Pago</label>
                    <select id="estadoPago" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="pendiente">⏳ Pendiente</option>
                        <option value="pagado">✅ Pagado</option>
                        <option value="vencido">❌ Vencido</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Pago</label>
                    <input type="date" id="fechaPago" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monto</label>
                    <input type="number" id="monto" step="0.01" placeholder="0.00" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Vencimiento</label>
                    <input type="date" id="fechaVencimiento" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                    <textarea id="observaciones" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="Observaciones sobre el pago..."></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="cerrarModalEditar()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Detalles -->
<div id="modalDetalles" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Detalles del Pedimento</h3>
            <div id="contenidoDetalles">
                <!-- Contenido se carga dinámicamente -->
            </div>
            <div class="flex justify-end mt-6">
                <button onclick="cerrarModalDetalles()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
let pedimentoActual = null;

// Funciones de selección múltiple
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.pedimento-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectAllButton();
}

function updateSelectAll() {
    const checkboxes = document.querySelectorAll('.pedimento-checkbox');
    const selectAll = document.getElementById('selectAll');
    
    const checkedBoxes = document.querySelectorAll('.pedimento-checkbox:checked');
    selectAll.checked = checkedBoxes.length === checkboxes.length;
    
    updateSelectAllButton();
}

function updateSelectAllButton() {
    const checkedBoxes = document.querySelectorAll('.pedimento-checkbox:checked');
    const btnMarcarPagados = document.getElementById('btnMarcarPagados');
    
    if (checkedBoxes.length > 0) {
        btnMarcarPagados.classList.remove('hidden');
    } else {
        btnMarcarPagados.classList.add('hidden');
    }
}

// Funciones de modales
function editarPedimento(id) {
    fetch(`/logistica/pedimentos/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pedimento = data.pedimento;
                document.getElementById('pedimentoId').value = pedimento.id;
                document.getElementById('estadoPago').value = pedimento.estado_pago;
                document.getElementById('fechaPago').value = pedimento.fecha_pago || '';
                document.getElementById('monto').value = pedimento.monto || '';
                document.getElementById('fechaVencimiento').value = pedimento.fecha_vencimiento ? pedimento.fecha_vencimiento.split('T')[0] : '';
                document.getElementById('observaciones').value = pedimento.observaciones_pago || '';
                
                document.getElementById('modalEditar').classList.remove('hidden');
            } else {
                alert('Error al cargar los datos: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del pedimento');
        });
}

function cerrarModalEditar() {
    document.getElementById('modalEditar').classList.add('hidden');
    document.getElementById('formEditar').reset();
}

function verDetalles(id) {
    fetch(`/logistica/pedimentos/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarDetallesPedimento(data.pedimento, data.operaciones);
            } else {
                alert('Error al cargar los detalles: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los detalles');
        });
}

function mostrarDetallesPedimento(pedimento, operaciones) {
    const contenido = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold mb-3">Información del Pedimento</h4>
                <div class="space-y-2 text-sm">
                    <div><span class="font-medium">Número:</span> ${pedimento.clave}</div>
                    <div><span class="font-medium">Estado:</span> ${getEstadoHtml(pedimento)}</div>
                    <div><span class="font-medium">Fecha de Pago:</span> ${pedimento.fecha_pago ? new Date(pedimento.fecha_pago).toLocaleDateString('es-ES') : 'Sin fecha'}</div>
                    <div><span class="font-medium">Monto:</span> ${pedimento.monto ? '$' + parseFloat(pedimento.monto).toLocaleString('es-ES', {minimumFractionDigits: 2}) : 'Sin monto'}</div>
                    <div><span class="font-medium">Vencimiento:</span> ${pedimento.fecha_vencimiento ? new Date(pedimento.fecha_vencimiento).toLocaleDateString('es-ES') : 'Sin fecha'}</div>
                </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold mb-3">Observaciones</h4>
                <p class="text-sm text-gray-600">${pedimento.observaciones_pago || 'Sin observaciones'}</p>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-semibold mb-3">Operaciones Asociadas (${operaciones.length})</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">ID</th>
                            <th class="text-left py-2">Cliente</th>
                            <th class="text-left py-2">Ejecutivo</th>
                            <th class="text-left py-2">Tipo</th>
                            <th class="text-left py-2">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${operaciones.map(op => `
                            <tr class="border-b">
                                <td class="py-2">#${op.id}</td>
                                <td class="py-2">${op.cliente || '-'}</td>
                                <td class="py-2">${op.ejecutivo?.nombre || '-'}</td>
                                <td class="py-2">${op.tipo_operacion_enum || '-'}</td>
                                <td class="py-2">${new Date(op.created_at).toLocaleDateString('es-ES')}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    document.getElementById('contenidoDetalles').innerHTML = contenido;
    document.getElementById('modalDetalles').classList.remove('hidden');
}

function getEstadoHtml(pedimento) {
    const estados = {
        'pendiente': '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">⏳ Pendiente</span>',
        'pagado': '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">✅ Pagado</span>',
        'vencido': '<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">❌ Vencido</span>'
    };
    return estados[pedimento.estado_pago] || estados['pendiente'];
}

function cerrarModalDetalles() {
    document.getElementById('modalDetalles').classList.add('hidden');
}

// Funciones de acciones
function eliminarPedimento(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este pedimento?')) {
        fetch(`/logistica/pedimentos/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el pedimento');
        });
    }
}

function marcarPagadosSeleccionados() {
    const checkboxes = document.querySelectorAll('.pedimento-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Selecciona al menos un pedimento');
        return;
    }
    
    const fechaPago = prompt('Ingresa la fecha de pago (YYYY-MM-DD):');
    if (!fechaPago) return;
    
    const monto = prompt('Ingresa el monto (opcional):');
    
    const pedimentoIds = Array.from(checkboxes).map(cb => cb.value);
    
    fetch('/logistica/pedimentos/marcar-pagados', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            pedimentos: pedimentoIds,
            fecha_pago: fechaPago,
            monto: monto || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar los pagos');
    });
}

// Event listeners
document.getElementById('formEditar').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const pedimentoId = document.getElementById('pedimentoId').value;
    const formData = {
        estado_pago: document.getElementById('estadoPago').value,
        fecha_pago: document.getElementById('fechaPago').value || null,
        monto: document.getElementById('monto').value || null,
        fecha_vencimiento: document.getElementById('fechaVencimiento').value || null,
        observaciones_pago: document.getElementById('observaciones').value || null
    };
    
    fetch(`/logistica/pedimentos/${pedimentoId}/estado-pago`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cerrarModalEditar();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el pedimento');
    });
});
</script>

@endsection