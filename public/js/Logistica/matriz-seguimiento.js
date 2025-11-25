// JavaScript para Matriz de Seguimiento

// Variables globales
let transportes = window.transportes || {};
let operacionActualId = null;



// Funciones del modal principal
window.abrirModal = function() {
    document.getElementById('modalOperacion').classList.remove('hidden');
};

window.cerrarModal = function() {
    document.getElementById('modalOperacion').classList.add('hidden');
    const form = document.getElementById('formOperacion');
    if (form) form.reset();
    
    // Cerrar todos los formularios de nuevos registros
    cancelarNuevoCliente();
    cancelarNuevoAgente();
    cancelarNuevoTransporte();
};

document.addEventListener('DOMContentLoaded', function() {

// Funciones para nuevo cliente
window.mostrarNuevoCliente = function() {
    const form = document.getElementById('nuevoClienteForm');
    if (form) form.classList.remove('hidden');
};

window.cancelarNuevoCliente = function() {
    const form = document.getElementById('nuevoClienteForm');
    const input = document.getElementById('nuevoClienteNombre');
    if (form) form.classList.add('hidden');
    if (input) input.value = '';
};

window.guardarNuevoCliente = function() {
    const nombreInput = document.getElementById('nuevoClienteNombre');
    if (!nombreInput) {
        alert('Error: No se encontró el campo de nombre del cliente');
        return;
    }
    
    const nombre = nombreInput.value.trim();
    if (!nombre) {
        alert('Por favor, ingrese el nombre del cliente');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('Error: Token CSRF no encontrado');
        return;
    }

    fetch('/logistica/clientes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
        },
        body: JSON.stringify({ cliente: nombre })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Actualizar el campo del formulario con el nuevo nombre
            const clienteInput = document.querySelector('input[name="cliente"]');
            if (clienteInput) {
                clienteInput.value = nombre;
            }
            
            // Agregar al datalist si existe
            const datalist = document.getElementById('clientesList');
            if (datalist) {
                const option = document.createElement('option');
                option.value = nombre;
                datalist.appendChild(option);
            } else {
                console.warn('Datalist clientesList not found - verificando DOM...');
                // Intentar encontrar el datalist por su atributo
                const allDatalist = document.querySelectorAll('datalist');

            }
            
            cancelarNuevoCliente();
            alert('✅ Cliente guardado exitosamente y agregado al formulario');
        } else {
            alert('Error al guardar el cliente: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        alert('Error de conexión: ' + error.message);
    });
};

window.guardarNuevoCliente = function() {
        const nombre = document.getElementById('nuevoClienteNombre').value.trim();
        if (!nombre) {
            alert('Por favor, ingrese el nombre del cliente');
            return;
        }

        fetch('/logistica/clientes', {
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
                // Agregar al datalist
                const datalist = document.getElementById('clientesList');
                if (datalist) {
                    const option = document.createElement('option');
                    option.value = data.cliente.cliente;
                    datalist.appendChild(option);
                }
                
                // Establecer valor en el input
                const input = document.querySelector('input[name="cliente"]');
                if (input) {
                    input.value = data.cliente.cliente;
                }
                
                cancelarNuevoCliente();
            } else {
                alert('Error al guardar el cliente: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
        });
    };

// Funciones para nuevo agente
window.mostrarNuevoAgente = function() {
    const form = document.getElementById('nuevoAgenteForm');
    if (form) form.classList.remove('hidden');
};

window.cancelarNuevoAgente = function() {
    const form = document.getElementById('nuevoAgenteForm');
    const input = document.getElementById('nuevoAgenteNombre');
    if (form) form.classList.add('hidden');
    if (input) input.value = '';
};

window.guardarNuevoAgente = function() {
    const nombreInput = document.getElementById('nuevoAgenteNombre');
    if (!nombreInput) {
        alert('Error: No se encontró el campo de nombre del agente');
        return;
    }
    
    const nombre = nombreInput.value.trim();
    if (!nombre) {
        alert('Por favor, ingrese el nombre del agente aduanal');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('Error: Token CSRF no encontrado');
        return;
    }

    fetch('/logistica/agentes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
        },
        body: JSON.stringify({ agente_aduanal: nombre })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Actualizar el campo del formulario con el nuevo nombre
            const agenteInput = document.querySelector('input[name="agente_aduanal"]');
            if (agenteInput) {
                agenteInput.value = nombre;
            }
            
            // Agregar al datalist si existe
            const datalist = document.getElementById('agentesList');
            if (datalist) {
                const option = document.createElement('option');
                option.value = nombre;
                datalist.appendChild(option);
            } else {
                console.warn('Datalist agentesList not found - verificando DOM...');
                // Intentar encontrar el datalist por su atributo
                const allDatalist = document.querySelectorAll('datalist');

            }
            
            cancelarNuevoAgente();
            alert('✅ Agente aduanal guardado exitosamente y agregado al formulario');
        } else {
            alert('Error al guardar el agente aduanal: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        alert('Error de conexión: ' + error.message);
    });
};

    window.guardarNuevoAgente = function() {
        const nombre = document.getElementById('nuevoAgenteNombre').value.trim();
        if (!nombre) {
            alert('Por favor, ingrese el nombre del agente aduanal');
            return;
        }

        fetch('/logistica/agentes', {
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
                // Agregar al datalist
                const datalist = document.getElementById('agentesList');
                if (datalist) {
                    const option = document.createElement('option');
                    option.value = data.agente.agente_aduanal;
                    datalist.appendChild(option);
                }
                
                // Establecer valor en el input
                const input = document.querySelector('input[name="agente_aduanal"]');
                if (input) {
                    input.value = data.agente.agente_aduanal;
                }
                
                cancelarNuevoAgente();
            } else {
                alert('Error al guardar el agente aduanal: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
        });
    };

// Función para actualizar transportes y target
window.actualizarTransportes = function() {
    // Calcular target automáticamente cuando cambia el tipo de operación
    calcularTargetAutomatico();
    
    // Filtrar transportes en el datalist según el tipo de operación
    const tipoOperacion = document.querySelector('select[name="tipo_operacion_enum"]').value;
    const datalist = document.getElementById('transportesList');
    
    if (datalist && tipoOperacion) {
        // Ocultar/mostrar opciones según el tipo de operación
        const options = datalist.querySelectorAll('option');
        options.forEach(option => {
            const optionTipo = option.getAttribute('data-tipo');
            if (optionTipo === tipoOperacion) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    }
};window.mostrarNuevoTransporte = function() {
    const select = document.querySelector('select[name="tipo_operacion_enum"]');
    const form = document.getElementById('nuevoTransporteForm');
    
    if (!select || !select.value) {
        alert('Por favor, seleccione primero el tipo de operación');
        return;
    }
    if (form) form.classList.remove('hidden');
};

window.cancelarNuevoTransporte = function() {
    const form = document.getElementById('nuevoTransporteForm');
    const input = document.getElementById('nuevoTransporteNombre');
    if (form) form.classList.add('hidden');
    if (input) input.value = '';
};

window.guardarNuevoTransporte = function() {
    const nombreInput = document.getElementById('nuevoTransporteNombre');
    if (!nombreInput) {
        alert('Error: No se encontró el campo de nombre del transporte');
        return;
    }
    
    const nombre = nombreInput.value.trim();
    const tipoOperacionSelect = document.querySelector('select[name="tipo_operacion_enum"]');
    const tipoOperacion = tipoOperacionSelect ? tipoOperacionSelect.value : '';
    
    if (!nombre) {
        alert('Por favor, ingrese el nombre del transporte');
        return;
    }
    
    if (!tipoOperacion) {
        alert('Por favor, seleccione el tipo de operación primero');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('Error: Token CSRF no encontrado');
        return;
    }

    fetch('/logistica/transportes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
        },
        body: JSON.stringify({ 
            transporte: nombre,
            tipo_operacion: tipoOperacion
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Actualizar el campo del formulario con el nuevo nombre
            const transporteInput = document.querySelector('input[name="transporte"]');
            if (transporteInput) {
                transporteInput.value = nombre;
            }
            
            // Agregar al datalist si existe
            const datalist = document.getElementById('transportesList');
            if (datalist) {
                const option = document.createElement('option');
                option.value = nombre;
                option.setAttribute('data-tipo', tipoOperacion);
                datalist.appendChild(option);
            } else {
                console.warn('Datalist transportesList not found - verificando DOM...');
                // Intentar encontrar el datalist por su atributo
                const allDatalist = document.querySelectorAll('datalist');

            }
            
            cancelarNuevoTransporte();
            alert('✅ Transporte guardado exitosamente y agregado al formulario');
        } else {
            alert('Error al guardar el transporte: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        alert('Error de conexión: ' + error.message);
    });
};

    window.guardarNuevoTransporte = function() {
        const nombre = document.getElementById('nuevoTransporteNombre').value.trim();
        const tipoOperacion = document.querySelector('select[name="tipo_operacion_enum"]').value;
        
        if (!nombre) {
            alert('Por favor, ingrese el nombre del transporte');
            return;
        }
        
        if (!tipoOperacion) {
            alert('Por favor, seleccione el tipo de operación primero');
            return;
        }

        fetch('/logistica/transportes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                transporte: nombre,
                tipo_operacion: tipoOperacion
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Agregar al datalist con el tipo de operación
                const datalist = document.getElementById('transportesList');
                if (datalist) {
                    const option = document.createElement('option');
                    option.value = data.transporte.transporte;
                    option.setAttribute('data-tipo', tipoOperacion);
                    datalist.appendChild(option);
                }
                
                // Establecer valor en el input
                const input = document.querySelector('input[name="transporte"]');
                if (input) {
                    input.value = data.transporte.transporte;
                }
                
                if (!transportes[tipoOperacion]) {
                    transportes[tipoOperacion] = [];
                }
                transportes[tipoOperacion].push(data.transporte);
                
                cancelarNuevoTransporte();
            } else {
                alert('Error al guardar el transporte: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
        });
    };

// Cálculos automáticos
window.calcularResultado = function() {
    const fechaArribo = document.querySelector('input[name="fecha_arribo_aduana"]').value;
    const fechaModulacion = document.querySelector('input[name="fecha_modulacion"]').value;
    
    if (fechaArribo && fechaModulacion) {
        const arribo = new Date(fechaArribo);
        const modulacion = new Date(fechaModulacion);
        const diferencia = Math.abs((modulacion - arribo) / (1000 * 60 * 60 * 24));
        
        document.querySelector('input[name="resultado"]').value = Math.round(diferencia);
    }
};

window.calcularTargetAutomatico = function() {
    const tipoOperacionSelect = document.querySelector('select[name="tipo_operacion_enum"]');
    const targetInput = document.querySelector('input[name="target"]');
    
    if (tipoOperacionSelect && targetInput) {
        const selectedOption = tipoOperacionSelect.options[tipoOperacionSelect.selectedIndex];
        const targetValue = selectedOption ? selectedOption.getAttribute('data-target') : '';
        
        if (targetValue) {
            targetInput.value = targetValue;
        }
    }
};window.calcularDiasTransito = function() {
        const fechaEmbarque = document.querySelector('input[name="fecha_embarque"]').value;
        const fechaArribo = document.querySelector('input[name="fecha_arribo_planta"]').value;
        
        if (fechaEmbarque && fechaArribo) {
            const embarque = new Date(fechaEmbarque);
            const arribo = new Date(fechaArribo);
            const diferencia = Math.abs((arribo - embarque) / (1000 * 60 * 60 * 24));
            
            document.querySelector('input[name="dias_transito"]').value = Math.round(diferencia);
        }
    };

// Funciones del historial
window.verHistorial = function(operacionId) {
        operacionActualId = operacionId;
        document.getElementById('modalHistorial').classList.remove('hidden');
        
        fetch(`/logistica/operaciones/${operacionId}/historial`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarHistorial(data.historial, data.operacion, data.operaciones_relacionadas || []);
                } else {
                    document.getElementById('historialContent').innerHTML = `
                        <div class="text-center py-8 text-red-500">
                            <p>Error al cargar el historial</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('historialContent').innerHTML = `
                    <div class="text-center py-8 text-red-500">
                        <p>Error de conexión</p>
                    </div>
                `;
            });
    };

window.cerrarModalHistorial = function() {
    const modal = document.getElementById('modalHistorial');
    if (modal) modal.classList.add('hidden');
    operacionActualId = null;
};

    function mostrarHistorial(historial, operacion, operacionesRelacionadas = []) {
        const content = document.getElementById('historialContent');
        
        const historialHtml = `
            <div class="space-y-6">
                <!-- Información de la Operación -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-slate-800 mb-3">Información de la Operación</h3>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                        <div>
                            <span class="text-slate-600">Operación:</span>
                            <p class="font-medium">${operacion.operacion || '-'}</p>
                        </div>
                        <div>
                            <span class="text-slate-600">Cliente:</span>
                            <p class="font-medium">${operacion.cliente || 'Sin cliente'}</p>
                        </div>
                        <div>
                            <span class="text-slate-600">No Pedimento:</span>
                            <p class="font-medium">${operacion.no_pedimento || 'Sin No Ped'}</p>
                        </div>
                        <div>
                            <span class="text-slate-600">Status:</span>
                            <p class="font-medium">${operacion.status || '-'}</p>
                        </div>
                        <div>
                            <span class="text-slate-600">ID:</span>
                            <p class="font-medium">#${operacion.id}</p>
                        </div>
                    </div>
                </div>

                <!-- Operaciones Relacionadas del Cliente -->
                ${operacionesRelacionadas.length > 0 ? `
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-slate-800 mb-3">
                            Otras Operaciones del Cliente "${operacion.cliente}"
                        </h3>
                        <div class="grid gap-2">
                            ${operacionesRelacionadas.map(opRel => `
                                <div class="flex justify-between items-center bg-white p-3 rounded border">
                                    <div class="flex-1">
                                        <span class="font-medium">Operación #${opRel.id}</span>
                                        <span class="text-sm text-slate-600 ml-2">${opRel.operacion}</span>
                                        ${opRel.no_pedimento ? `<span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded ml-2">${opRel.no_pedimento}</span>` : ''}
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium">${opRel.status}</div>
                                        <div class="text-xs text-slate-500">${opRel.fecha_creacion}</div>
                                        <div class="text-xs text-slate-600">${opRel.historial_count} registros</div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}

                <!-- Historial de Estados -->
                <div class="bg-white rounded-lg border p-4">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Historial de Estados</h3>
                    ${historial.length > 0 ? `
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-slate-700">Fecha Registro</th>
                                        <th class="px-3 py-2 text-left font-medium text-slate-700">Fecha Arribo Aduana</th>
                                        <th class="px-3 py-2 text-left font-medium text-slate-700">Días Transcurridos</th>
                                        <th class="px-3 py-2 text-left font-medium text-slate-700">Target Días</th>
                                        <th class="px-3 py-2 text-left font-medium text-slate-700">Color Status</th>
                                        <th class="px-3 py-2 text-left font-medium text-slate-700">Status Operación</th>
                                        <th class="px-3 py-2 text-left font-medium text-slate-700">Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    ${historial.map(registro => `
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-3 py-2">${registro.fecha_registro || '-'}</td>
                                            <td class="px-3 py-2">${registro.fecha_arribo_aduana || '-'}</td>
                                            <td class="px-3 py-2 text-center">${registro.dias_transcurridos || '0'}</td>
                                            <td class="px-3 py-2 text-center">${registro.target_dias || '0'}</td>
                                            <td class="px-3 py-2">
                                                <span class="status-badge ${getColorStatusClass(registro.color_status)}">
                                                    ${getColorStatusText(registro.color_status)}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2">${registro.operacion_status || '-'}</td>
                                            <td class="px-3 py-2">${registro.observaciones || '-'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : `
                        <div class="text-center py-8 text-slate-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium">No hay historial registrado</p>
                            <p class="text-sm">Los cambios de estado se mostrarán aquí</p>
                        </div>
                    `}
                </div>
            </div>
        `;
        
        content.innerHTML = historialHtml;
    }

    function getColorStatusClass(status) {
        switch(status) {
            case 'verde': return 'status-verde';
            case 'amarillo': return 'status-amarillo';
            case 'rojo': return 'status-rojo';
            case 'sin_fecha': return 'status-sin-fecha';
            default: return 'status-sin-fecha';
        }
    }

    function getColorStatusText(status) {
        switch(status) {
            case 'verde': return 'Verde';
            case 'amarillo': return 'Amarillo';
            case 'rojo': return 'Rojo';
            case 'sin_fecha': return 'Sin Fecha';
            default: return status || 'Desconocido';
        }
    }

// Función para editar operación (solo marcar como Done)
window.editarOperacion = function(operacionId) {
        if (confirm('¿Desea marcar esta operación como COMPLETADA (Done)?')) {
            fetch(`/logistica/operaciones/${operacionId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    status_calculado: 'Done'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Operación marcada como completada exitosamente');
                    window.location.reload();
                } else {
                    alert('Error al actualizar el status: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
        }
    };

// Función para eliminar operación
window.eliminarOperacion = function(operacionId) {
        if (confirm('¿Está seguro de que desea eliminar esta operación? Esta acción no se puede deshacer.')) {
            fetch(`/logistica/operaciones/${operacionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Operación eliminada exitosamente');
                    window.location.reload();
                } else {
                    alert('Error al eliminar la operación: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
        }
    };

    // Event listeners para fechas y cálculos automáticos
    document.addEventListener('change', function(e) {
        if (e.target.name === 'fecha_arribo_aduana' || e.target.name === 'fecha_modulacion') {
            calcularResultado();
        }
        if (e.target.name === 'fecha_embarque' || e.target.name === 'fecha_arribo_planta') {
            calcularDiasTransito();
        }
        if (e.target.name === 'tipo_operacion_enum') {
            actualizarTransportes();
        }
    });

    // Manejar envío del formulario
    document.getElementById('formOperacion').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('/logistica/operaciones', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Operación guardada exitosamente');
                cerrarModal();
                window.location.reload();
            } else {
                alert('Error al guardar la operación: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            alert('Error al guardar la operación: ' + error.message);
        });
    });

    // Event listeners para cerrar modales
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModal();
            cerrarModalHistorial();
        }
    });

    document.getElementById('modalOperacion').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModal();
        }
    });

    document.getElementById('modalHistorial').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalHistorial();
        }
    });
});

// Función global para recalcular todos los status automáticamente
function recalcularTodosLosStatus() {
    if (confirm('¿Desea recalcular automáticamente todos los status de las operaciones?')) {
        fetch('/logistica/operaciones/recalcular-status', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Status recalculados exitosamente');
                window.location.reload();
            } else {
                alert('Error al recalcular: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
        });
    }
}

// Función para marcar operación como Done
window.marcarComoDone = function(operacionId) {
    if (confirm('¿Está seguro de marcar esta operación como completada?')) {
        fetch(`/logistica/operaciones/${operacionId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: 'Done'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar la página para mostrar los cambios
                window.location.reload();
            } else {
                alert('Error al actualizar el status: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
        });
    }
}

// Funciones para Post-Operaciones
window.marcarPostOpComoDone = function(postOpId) {
    if (confirm('¿Está seguro de marcar esta post-operación como completada?')) {
        fetch(`/logistica/post-operaciones/${postOpId}/done`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar la tabla de post-operaciones
                cargarPostOperaciones();
            } else {
                alert('Error al marcar como completada: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
        });
    }
}

window.eliminarPostOperacion = function(postOpId) {
    if (confirm('¿Está seguro de eliminar esta post-operación?')) {
        fetch(`/logistica/post-operaciones/${postOpId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar la tabla de post-operaciones
                cargarPostOperaciones();
            } else {
                alert('Error al eliminar: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
        });
    }
}

window.abrirModalPostOperacion = function() {
    // Limpiar el formulario
    document.getElementById('formPostOperacion').reset();
    
    // Mostrar el modal
    document.getElementById('modalPostOperacion').classList.remove('hidden');
}

window.cerrarModalPostOperacion = function() {
    document.getElementById('modalPostOperacion').classList.add('hidden');
}

// Función para cargar las post-operaciones
function cargarPostOperaciones() {
    fetch('/logistica/post-operaciones')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarTablaPostOperaciones(data.postOperaciones);
            }
        })
        .catch(error => console.error('Error:', error));
}

function actualizarTablaPostOperaciones(postOperaciones) {
    const tbody = document.querySelector('#tablaPostOperaciones tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (postOperaciones.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-3 py-4 text-center text-slate-500">
                    No hay post-operaciones registradas
                </td>
            </tr>
        `;
        return;
    }
    
    postOperaciones.forEach(postOp => {
        const statusBadge = postOp.status === 'Completado' 
            ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completado</span>'
            : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pendiente</span>';
        
        const acciones = postOp.status === 'Completado' 
            ? `<button onclick="eliminarPostOperacion(${postOp.id})" class="text-red-600 hover:text-red-800">Eliminar</button>`
            : `
                <button onclick="marcarPostOpComoDone(${postOp.id})" class="text-green-600 hover:text-green-800 mr-2">Completar</button>
                <button onclick="eliminarPostOperacion(${postOp.id})" class="text-red-600 hover:text-red-800">Eliminar</button>
            `;
        
        tbody.innerHTML += `
            <tr>
                <td class="px-3 py-4 border-r border-slate-200">${postOp.id}</td>
                <td class="px-3 py-4 border-r border-slate-200">${postOp.operacion_relacionada || '-'}</td>
                <td class="px-3 py-4 border-r border-slate-200">${postOp.nombre}</td>
                <td class="px-3 py-4 border-r border-slate-200">${statusBadge}</td>
                <td class="px-3 py-4 border-r border-slate-200">${postOp.fecha_creacion}</td>
                <td class="px-3 py-4">${acciones}</td>
            </tr>
        `;
    });
}

// Función para guardar post-operación
window.guardarPostOperacion = function(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    fetch('/logistica/post-operaciones', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cerrarModalPostOperacion();
            cargarPostOperaciones();
            alert('Post-operación guardada exitosamente');
        } else {
            alert('Error al guardar: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión');
    });
}

// ============================================
// FUNCIONES PARA POST-OPERACIONES POR OPERACIÓN
// ============================================

// Variables globales
let operacionActualPostOp = null;
let operacionActualComentarios = null;

// Función para ver post-operaciones de una operación específica
window.verPostOperaciones = function(operacionId) {
    operacionActualPostOp = operacionId;
    document.getElementById('operacionIdPostOp').textContent = operacionId;
    document.getElementById('modalPostOperaciones').classList.remove('hidden');
    
    cargarPostOperacionesPorOperacion(operacionId);
};

window.cerrarModalPostOperaciones = function() {
    document.getElementById('modalPostOperaciones').classList.add('hidden');
    operacionActualPostOp = null;
};

function cargarPostOperacionesPorOperacion(operacionId) {
    fetch(`/logistica/operaciones/${operacionId}/post-operaciones`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarPostOperacionesOperacion(data.postOperaciones);
            } else {
                document.getElementById('contenidoPostOperaciones').innerHTML = `
                    <div class="text-center py-8 text-red-500">
                        <p>Error al cargar post-operaciones</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('contenidoPostOperaciones').innerHTML = `
                <div class="text-center py-8 text-red-500">
                    <p>Error de conexión</p>
                </div>
            `;
        });
}

function mostrarPostOperacionesOperacion(postOperaciones) {
    const contenedor = document.getElementById('contenidoPostOperaciones');
    
    if (postOperaciones.length === 0) {
        contenedor.innerHTML = `
            <div class="text-center py-8 text-slate-500">
                <svg class="w-12 h-12 mx-auto mb-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <p class="text-lg font-medium">No hay post-operaciones disponibles</p>
                <p class="text-sm">No se han creado post-operaciones globales. Use el botón "Post-Operaciones Globales" para crear plantillas.</p>
            </div>
        `;
        return;
    }
    
    contenedor.innerHTML = `
        <div class="space-y-4">
            ${postOperaciones.map(postOp => {
                const statusColor = 
                    postOp.status === 'Completado' ? 'bg-green-50 border-green-200' :
                    postOp.status === 'No Aplica' ? 'bg-gray-50 border-gray-200' : 
                    'bg-yellow-50 border-yellow-200';
                
                const isPlantilla = postOp.es_plantilla;
                const postOpId = postOp.id_asignacion || postOp.id_global;
                    
                return `
                <div class="border rounded-lg p-4 ${statusColor} ${isPlantilla ? 'border-dashed' : ''}">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h4 class="font-semibold text-slate-800">${postOp.nombre}</h4>
                                ${isPlantilla ? '<span class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded">Plantilla</span>' : '<span class="text-xs bg-green-100 text-green-600 px-2 py-1 rounded">Asignada</span>'}
                            </div>
                            ${postOp.descripcion ? `<p class="text-sm text-slate-600 mb-3">${postOp.descripcion}</p>` : ''}
                            <p class="text-xs text-slate-500">Creado: ${postOp.fecha_creacion}</p>
                        </div>
                        <div class="ml-4 min-w-0">
                            <p class="text-xs text-slate-500 mb-2">Estado para esta operación:</p>
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" 
                                           name="status_${postOpId}" 
                                           value="Pendiente" 
                                           ${postOp.status === 'Pendiente' ? 'checked' : ''}
                                           onchange="cambiarEstadoPostOp('${postOpId}', 'Pendiente', ${isPlantilla ? `'${postOp.id_global}'` : 'null'}, '${postOp.nombre}')"
                                           class="mr-2 text-orange-500">
                                    <span class="text-sm text-orange-600">Pendiente</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" 
                                           name="status_${postOpId}" 
                                           value="Completado" 
                                           ${postOp.status === 'Completado' ? 'checked' : ''}
                                           onchange="cambiarEstadoPostOp('${postOpId}', 'Completado', ${isPlantilla ? `'${postOp.id_global}'` : 'null'}, '${postOp.nombre}')"
                                           class="mr-2 text-green-500">
                                    <span class="text-sm text-green-600">Completado</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" 
                                           name="status_${postOpId}" 
                                           value="No Aplica" 
                                           ${postOp.status === 'No Aplica' ? 'checked' : ''}
                                           onchange="cambiarEstadoPostOp('${postOpId}', 'No Aplica', ${isPlantilla ? `'${postOp.id_global}'` : 'null'}, '${postOp.nombre}')"
                                           class="mr-2 text-gray-500">
                                    <span class="text-sm text-gray-600">No Aplica</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    ${postOp.fecha_completado ? `
                        <div class="mt-3 pt-3 border-t border-slate-200">
                            <p class="text-xs text-slate-500">
                                <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                Completado el: ${postOp.fecha_completado}
                            </p>
                        </div>
                    ` : ''}
                </div>
                `;
            }).join('')}
        </div>
    `;
}

// Variable para almacenar cambios pendientes
let cambiosPendientesPostOps = {};

// Función para manejar cambios de estado de post-operaciones
window.cambiarEstadoPostOp = function(postOpId, nuevoEstado, idGlobal = null, nombre = null) {
    // Almacenar el cambio pendiente con información adicional
    cambiosPendientesPostOps[postOpId] = {
        estado: nuevoEstado,
        id_global: idGlobal, // Para plantillas que necesitan crearse como específicas
        nombre: nombre,
        es_plantilla: idGlobal !== null
    };
    
    // Actualizar visualmente (cambiar color de fondo)
    const postOpElement = document.querySelector(`input[name="status_${postOpId}"]:checked`).closest('.border');
    if (postOpElement) {
        // Remover clases de color anteriores y bordes dashed
        postOpElement.classList.remove('bg-green-50', 'border-green-200', 'bg-gray-50', 'border-gray-200', 'bg-yellow-50', 'border-yellow-200', 'border-dashed');
        
        // Agregar nuevas clases según el estado
        if (nuevoEstado === 'Completado') {
            postOpElement.classList.add('bg-green-50', 'border-green-200');
        } else if (nuevoEstado === 'No Aplica') {
            postOpElement.classList.add('bg-gray-50', 'border-gray-200');
        } else {
            postOpElement.classList.add('bg-yellow-50', 'border-yellow-200');
        }
        
        // Si era plantilla y ahora tiene estado específico, quitar el estilo de plantilla
        if (idGlobal && nuevoEstado !== 'Pendiente') {
            const badge = postOpElement.querySelector('.bg-blue-100');
            if (badge) {
                badge.className = 'text-xs bg-green-100 text-green-600 px-2 py-1 rounded';
                badge.textContent = 'Asignada';
            }
        }
    }
};

// Función para guardar todos los cambios pendientes
function guardarCambiosPostOperaciones() {
    const operacionId = operacionActualPostOp;
    
    if (Object.keys(cambiosPendientesPostOps).length === 0) {
        alert('No hay cambios pendientes para guardar');
        return;
    }
    
    const btn = document.getElementById('guardarCambiosPostOperaciones');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
    
    fetch(`/logistica/operaciones/${operacionId}/post-operaciones/actualizar-estados`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            cambios: cambiosPendientesPostOps,
            no_pedimento: obtenerNoPedimento(operacionId)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cambios guardados exitosamente');
            cambiosPendientesPostOps = {}; // Limpiar cambios pendientes
            cargarPostOperacionesPorOperacion(operacionId);
            // Actualizar tabla principal si es necesario
            // window.location.reload();
        } else {
            alert('Error al guardar: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al guardar');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save mr-2"></i>Guardar Cambios';
    });
}

// Función auxiliar para obtener número de pedimento
function obtenerNoPedimento(operacionId) {
    // Buscar en la tabla el número de pedimento usando data-operacion-id
    const fila = document.querySelector(`tr[data-operacion-id="${operacionId}"]`);
    if (fila) {
        const celdaPedimento = fila.querySelector('td:nth-child(14)'); // Columna "No Ped" 
        const pedimento = celdaPedimento ? celdaPedimento.textContent.trim() : null;
        return pedimento && pedimento !== '-' ? pedimento : null;
    }
    return null;
}

// Event listener para el botón de guardar cambios
document.addEventListener('DOMContentLoaded', function() {
    const btnGuardar = document.getElementById('guardarCambiosPostOperaciones');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', guardarCambiosPostOperaciones);
    }
});

// ============================================
// FUNCIONES PARA COMENTARIOS
// ============================================

// Función para ver comentarios de una operación
window.verComentarios = function(operacionId) {
    operacionActualComentarios = operacionId;
    document.getElementById('operacionIdComentarios').textContent = operacionId;
    document.getElementById('modalComentarios').classList.remove('hidden');
    
    cargarComentariosPorOperacion(operacionId);
};

window.cerrarModalComentarios = function() {
    document.getElementById('modalComentarios').classList.add('hidden');
    operacionActualComentarios = null;
    cancelarEdicionComentario();
};

function cargarComentariosPorOperacion(operacionId) {
    fetch(`/logistica/operaciones/${operacionId}/comentarios`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarComentarios(data.comentarios);
            } else {
                document.getElementById('listaComentarios').innerHTML = `
                    <div class="text-center py-4 text-red-500">
                        <p>Error al cargar comentarios</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('listaComentarios').innerHTML = `
                <div class="text-center py-4 text-red-500">
                    <p>Error de conexión</p>
                </div>
            `;
        });
}

function mostrarComentarios(comentarios) {
    const contenedor = document.getElementById('listaComentarios');
    
    if (comentarios.length === 0) {
        contenedor.innerHTML = `
            <div class="text-center py-8 text-slate-500">
                <svg class="w-12 h-12 mx-auto mb-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <p class="text-lg font-medium">No hay comentarios</p>
                <p class="text-sm">Añada el primer comentario para esta operación</p>
            </div>
        `;
        return;
    }
    
    contenedor.innerHTML = comentarios.map(comentario => `
        <div class="border border-slate-200 rounded-lg p-4 bg-slate-50">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <p class="text-slate-800">${comentario.texto}</p>
                    <div class="flex justify-between items-center mt-2">
                        <p class="text-xs text-slate-500">
                            ${comentario.autor} - ${comentario.fecha}
                        </p>
                        <button onclick="editarComentario(${comentario.id}, '${comentario.texto.replace(/'/g, "\\'")}', '${comentario.autor}')" 
                                class="text-blue-600 hover:text-blue-800 text-xs">
                            Editar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// Función para editar comentario
window.editarComentario = function(comentarioId, texto, autor) {
    document.getElementById('comentarioId').value = comentarioId;
    document.getElementById('textoComentario').value = texto;
    document.getElementById('tituloComentario').textContent = 'Editar Comentario';
    document.getElementById('textoBotonComentario').textContent = 'Actualizar Comentario';
    document.getElementById('btnCancelarComentario').classList.remove('hidden');
};

window.cancelarEdicionComentario = function() {
    document.getElementById('comentarioId').value = '';
    document.getElementById('textoComentario').value = '';
    document.getElementById('tituloComentario').textContent = 'Añadir Comentario';
    document.getElementById('textoBotonComentario').textContent = 'Guardar Comentario';
    document.getElementById('btnCancelarComentario').classList.add('hidden');
};

// Manejar formulario de comentarios
document.getElementById('formComentario').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!operacionActualComentarios) {
        alert('Error: No se ha seleccionado una operación');
        return;
    }
    
    const comentarioId = document.getElementById('comentarioId').value;
    const texto = document.getElementById('textoComentario').value;
    const isEdit = comentarioId !== '';
    
    const formData = new FormData();
    formData.append('comentario', texto);
    formData.append('operacion_logistica_id', operacionActualComentarios);
    
    const url = isEdit 
        ? `/logistica/comentarios/${comentarioId}` 
        : '/logistica/comentarios';
    
    const method = isEdit ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cancelarEdicionComentario();
            cargarComentariosPorOperacion(operacionActualComentarios);
        } else {
            alert('Error al guardar: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión');
    });
});

// ============================================
// FUNCIONES PARA GESTIÓN GLOBAL DE POST-OPERACIONES
// ============================================

// Función para abrir el modal de gestión global (el botón que está arriba)
window.abrirModalPostOperaciones = function() {
    document.getElementById('modalGestionPostOp').classList.remove('hidden');
    cargarPostOperacionesGlobales();
};

window.cerrarModalGestionPostOp = function() {
    document.getElementById('modalGestionPostOp').classList.add('hidden');
};

function cargarPostOperacionesGlobales() {
    fetch('/logistica/post-operaciones-globales')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarPostOperacionesGlobales(data.postOperaciones);
            }
        })
        .catch(error => console.error('Error:', error));
}

function mostrarPostOperacionesGlobales(postOperaciones) {
    const contenedor = document.getElementById('listaPostOpGlobales');
    
    if (postOperaciones.length === 0) {
        contenedor.innerHTML = `
            <div class="text-center py-4 text-slate-500">
                <p>No hay post-operaciones definidas</p>
            </div>
        `;
        return;
    }
    
    contenedor.innerHTML = postOperaciones.map(postOp => `
        <div class="flex justify-between items-center p-3 border border-slate-200 rounded-lg">
            <div>
                <h4 class="font-medium text-slate-800">${postOp.nombre}</h4>
                ${postOp.descripcion ? `<p class="text-sm text-slate-600">${postOp.descripcion}</p>` : ''}
            </div>
            <button onclick="eliminarPostOpGlobal(${postOp.id})" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `).join('');
}

window.eliminarPostOpGlobal = function(id) {
    if (confirm('¿Está seguro de eliminar esta post-operación?')) {
        fetch(`/logistica/post-operaciones-globales/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cargarPostOperacionesGlobales();
            }
        })
        .catch(error => console.error('Error:', error));
    }
};

// Manejar formulario de post-operación global
document.getElementById('formPostOpGlobal').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('nombre', document.getElementById('nombrePostOpGlobal').value);
    formData.append('descripcion', document.getElementById('descripcionPostOpGlobal').value);
    
    fetch('/logistica/post-operaciones-globales', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('formPostOpGlobal').reset();
            cargarPostOperacionesGlobales();
        } else {
            alert('Error al guardar: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión');
    });
});

// Remover la carga automática de post-operaciones al iniciar
// document.addEventListener('DOMContentLoaded', function() {
//     cargarPostOperaciones();
// });
