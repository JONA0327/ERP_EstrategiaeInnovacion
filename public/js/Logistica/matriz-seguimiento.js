// JavaScript para Matriz de Seguimiento

// Variables globales
let transportes = window.transportes || {};
let operacionActualId = null;

// ========================================
// SISTEMA DE MODALES REUTILIZABLES
// ========================================

/**
 * Muestra un modal de alerta (reemplazo de alert())
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo: 'success', 'error', 'warning', 'info'
 * @param {string} title - Título opcional
 */
window.mostrarAlerta = function(message, type = 'info', title = '') {
    const modal = document.getElementById('modalAlert');
    const iconContainer = document.getElementById('modalAlertIcon');
    const titleElement = document.getElementById('modalAlertTitle');
    const messageElement = document.getElementById('modalAlertMessage');
    
    // Definir iconos y colores según el tipo
    const types = {
        success: {
            icon: `<svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>`,
            title: title || 'Éxito'
        },
        error: {
            icon: `<svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>`,
            title: title || 'Error'
        },
        warning: {
            icon: `<svg class="w-12 h-12 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>`,
            title: title || 'Advertencia'
        },
        info: {
            icon: `<svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>`,
            title: title || 'Información'
        }
    };
    
    const config = types[type] || types.info;
    iconContainer.innerHTML = config.icon;
    titleElement.textContent = config.title;
    messageElement.textContent = message;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
};

/**
 * Cierra el modal de alerta
 */
window.cerrarModalAlert = function() {
    const modal = document.getElementById('modalAlert');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
};

/**
 * Muestra un modal de confirmación (reemplazo de confirm())
 * @param {string} message - Mensaje a mostrar
 * @param {function} onConfirm - Callback cuando se confirma
 * @param {string} title - Título opcional
 * @param {string} confirmText - Texto del botón de confirmar
 */
window.mostrarConfirmacion = function(message, onConfirm, title = 'Confirmar acción', confirmText = 'Confirmar') {
    const modal = document.getElementById('modalConfirm');
    const titleElement = document.getElementById('modalConfirmTitle');
    const messageElement = document.getElementById('modalConfirmMessage');
    const confirmBtn = document.getElementById('modalConfirmBtn');
    
    titleElement.textContent = title;
    messageElement.textContent = message;
    confirmBtn.textContent = confirmText;
    
    // Remover listeners anteriores
    const newBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
    
    // Agregar nuevo listener
    document.getElementById('modalConfirmBtn').addEventListener('click', function() {
        cerrarModalConfirm(true);
        if (typeof onConfirm === 'function') {
            onConfirm();
        }
    });
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
};

/**
 * Cierra el modal de confirmación
 */
window.cerrarModalConfirm = function(confirmed) {
    const modal = document.getElementById('modalConfirm');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
};

// Event listener para cerrar modales con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModalAlert();
        cerrarModalConfirm(false);
    }
});

// ========================================
// FIN SISTEMA DE MODALES
// ========================================


// Funciones del modal principal
window.abrirModal = function() {
    // Resetear el formulario para nueva operación
    const form = document.getElementById('formOperacion');
    if (form) form.reset();
    
    document.getElementById('operacionId').value = '';
    document.getElementById('isEditing').value = '';
    document.getElementById('modalTitle').innerHTML = '<span class="text-blue-600 mr-2 text-xl">⊕</span>Añadir Nueva Operación';
    document.getElementById('submitButtonText').textContent = 'Guardar Operación';
    document.getElementById('statusManualSection').classList.add('hidden');
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

// Funciones utilitarias para conversión a mayúsculas
function convertirAMayusculas(input) {
    const valor = input.value;
    const inicio = input.selectionStart;
    const fin = input.selectionEnd;
    input.value = valor.toUpperCase();
    input.setSelectionRange(inicio, fin);
}

// Función para aplicar conversión automática a mayúsculas a un campo
function aplicarMayusculasAutomaticas(elementId) {
    const elemento = document.getElementById(elementId);
    if (elemento) {
        elemento.addEventListener('input', function() {
            convertirAMayusculas(this);
        });
    }
}

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
        mostrarAlerta('No se encontró el campo de nombre del cliente', 'error');
        return;
    }
    
    const nombre = nombreInput.value.trim().toUpperCase();
    if (!nombre) {
        mostrarAlerta('Por favor, ingrese el nombre del cliente', 'warning');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        mostrarAlerta('Token CSRF no encontrado', 'error');
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
            // Agregar el nuevo cliente al select (el servidor retorna el nombre en mayúsculas)
            const clienteSelect = document.getElementById('clienteSelect');
            if (clienteSelect && data.cliente) {
                const option = document.createElement('option');
                option.value = data.cliente.cliente;
                option.textContent = data.cliente.cliente;
                clienteSelect.appendChild(option);
                // Seleccionar el nuevo cliente
                clienteSelect.value = data.cliente.cliente;
            }
            
            cancelarNuevoCliente();
            mostrarAlerta('Cliente guardado exitosamente y agregado al formulario', 'success');
        } else {
            mostrarAlerta('Error al guardar el cliente: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        mostrarAlerta('Error de conexión: ' + error.message, 'error');
    });
};

// Función duplicada eliminada - usar la versión principal más arriba

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
        mostrarAlerta('No se encontró el campo de nombre del agente', 'error');
        return;
    }
    
    const nombre = nombreInput.value.trim().toUpperCase();
    if (!nombre) {
        mostrarAlerta('Por favor, ingrese el nombre del agente aduanal', 'warning');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        mostrarAlerta('Token CSRF no encontrado', 'error');
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
            // Agregar el nuevo agente al select
            const agenteSelect = document.getElementById('agenteSelect');
            if (agenteSelect) {
                const option = document.createElement('option');
                option.value = nombre;
                option.textContent = nombre;
                agenteSelect.appendChild(option);
                // Seleccionar el nuevo agente
                agenteSelect.value = nombre;
            }
            
            cancelarNuevoAgente();
            mostrarAlerta('Agente aduanal guardado exitosamente y agregado al formulario', 'success');
        } else {
            mostrarAlerta('Error al guardar el agente aduanal: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        mostrarAlerta('Error de conexión: ' + error.message, 'error');
    });
};

    window.guardarNuevoAgente = function() {
        const nombre = document.getElementById('nuevoAgenteNombre').value.trim();
        if (!nombre) {
            mostrarAlerta('Por favor, ingrese el nombre del agente aduanal', 'warning');
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
                mostrarAlerta('Error al guardar el agente aduanal: ' + (data.message || 'Error desconocido'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error de conexión', 'error');
        });
    };

// Función para actualizar transportes y target
window.actualizarTransportes = function() {
    // Calcular target automáticamente cuando cambia el tipo de operación
    calcularTargetAutomatico();
    
    // Filtrar transportes en el select según el tipo de operación
    const tipoOperacion = document.querySelector('select[name="tipo_operacion_enum"]').value;
    const transporteSelect = document.getElementById('transporteSelect');
    
    if (transporteSelect && tipoOperacion) {
        // Guardar el valor actual seleccionado
        const valorActual = transporteSelect.value;
        
        // Ocultar/mostrar opciones según el tipo de operación
        const options = transporteSelect.querySelectorAll('option');
        let tieneOpcionesValidas = false;
        
        options.forEach(option => {
            const optionTipo = option.getAttribute('data-tipo');
            if (option.value === '' || optionTipo === tipoOperacion) {
                option.style.display = '';
                if (option.value !== '') tieneOpcionesValidas = true;
            } else {
                option.style.display = 'none';
            }
        });
        
        // Si el valor actual no es válido para el nuevo tipo, resetear
        const opcionActual = transporteSelect.querySelector(`option[value="${valorActual}"]`);
        if (opcionActual && opcionActual.getAttribute('data-tipo') !== tipoOperacion && valorActual !== '') {
            transporteSelect.value = '';
        }
    }
};window.mostrarNuevoTransporte = function() {
    const select = document.querySelector('select[name="tipo_operacion_enum"]');
    const form = document.getElementById('nuevoTransporteForm');
    
    if (!select || !select.value) {
        mostrarAlerta('Por favor, seleccione primero el tipo de operación', 'warning');
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
        mostrarAlerta('No se encontró el campo de nombre del transporte', 'error');
        return;
    }
    
    const nombre = nombreInput.value.trim().toUpperCase();
    const tipoOperacionSelect = document.querySelector('select[name="tipo_operacion_enum"]');
    const tipoOperacion = tipoOperacionSelect ? tipoOperacionSelect.value : '';
    
    if (!nombre) {
        mostrarAlerta('Por favor, ingrese el nombre del transporte', 'warning');
        return;
    }
    
    if (!tipoOperacion) {
        mostrarAlerta('Por favor, seleccione el tipo de operación primero', 'warning');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        mostrarAlerta('Token CSRF no encontrado', 'error');
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
            mostrarAlerta('Transporte guardado exitosamente y agregado al formulario', 'success');
        } else {
            mostrarAlerta('Error al guardar el transporte: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        mostrarAlerta('Error de conexión: ' + error.message, 'error');
    });
};

    window.guardarNuevoTransporte = function() {
        const nombre = document.getElementById('nuevoTransporteNombre').value.trim().toUpperCase();
        const tipoOperacion = document.querySelector('select[name="tipo_operacion_enum"]').value;
        
        if (!nombre) {
            mostrarAlerta('Por favor, ingrese el nombre del transporte', 'warning');
            return;
        }
        
        if (!tipoOperacion) {
            mostrarAlerta('Por favor, seleccione el tipo de operación primero', 'warning');
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
                // Agregar el nuevo transporte al select
                const transporteSelect = document.getElementById('transporteSelect');
                if (transporteSelect) {
                    const option = document.createElement('option');
                    option.value = data.transporte.transporte;
                    option.textContent = data.transporte.transporte;
                    option.setAttribute('data-tipo', tipoOperacion);
                    transporteSelect.appendChild(option);
                    // Seleccionar el nuevo transporte
                    transporteSelect.value = data.transporte.transporte;
                }
                
                if (!transportes[tipoOperacion]) {
                    transportes[tipoOperacion] = [];
                }
                transportes[tipoOperacion].push(data.transporte);
                
                cancelarNuevoTransporte();
            } else {
                mostrarAlerta('Error al guardar el transporte: ' + (data.message || 'Error desconocido'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error de conexión', 'error');
        });
    };

    // Inicializar conversión automática a mayúsculas para campos específicos
    const camposConMayusculas = [
        'nuevoClienteNombre',
        'nuevoAgenteNombre', 
        'nuevoTransporteNombre',
        'nuevaAduanaDenominacion',
        'no_pedimento'
    ];

    camposConMayusculas.forEach(function(campoId) {
        aplicarMayusculasAutomaticas(campoId);
    });

    // También aplicar a campos de texto que deben ser en mayúsculas por su name
    const camposPorName = [
        'proveedor_o_cliente',
        'no_factura', 
        'referencia_interna',
        'aduana',
        'agente_aduanal',
        'transporte'
    ];

    camposPorName.forEach(function(campoName) {
        const elemento = document.querySelector(`input[name="${campoName}"]`);
        if (elemento) {
            elemento.addEventListener('input', function() {
                convertirAMayusculas(this);
            });
        }
    });

// Cálculos automáticos
window.calcularResultado = function() {
    const fechaArriboInput = document.querySelector('input[name="fecha_arribo_aduana"]');
    const fechaModulacionInput = document.querySelector('input[name="fecha_modulacion"]');
    const resultadoInput = document.querySelector('input[name="resultado"]');
    
    if (fechaArriboInput && fechaModulacionInput && resultadoInput) {
        const fechaArribo = fechaArriboInput.value;
        const fechaModulacion = fechaModulacionInput.value;
        
        if (fechaArribo && fechaModulacion) {
            const arribo = new Date(fechaArribo);
            const modulacion = new Date(fechaModulacion);
            const diferencia = Math.abs((modulacion - arribo) / (1000 * 60 * 60 * 24));
            
            resultadoInput.value = Math.round(diferencia);
        }
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
        const fechaEmbarqueInput = document.querySelector('input[name="fecha_embarque"]');
        const fechaArriboInput = document.querySelector('input[name="fecha_arribo_planta"]');
        const diasTransitoInput = document.querySelector('input[name="dias_transito"]');
        
        if (fechaEmbarqueInput && fechaArriboInput && diasTransitoInput) {
            const fechaEmbarque = fechaEmbarqueInput.value;
            const fechaArribo = fechaArriboInput.value;
            
            if (fechaEmbarque && fechaArribo) {
                const embarque = new Date(fechaEmbarque);
                const arribo = new Date(fechaArribo);
                const diferencia = Math.abs((arribo - embarque) / (1000 * 60 * 60 * 24));
                
                diasTransitoInput.value = Math.round(diferencia);
            }
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
                            <p class="font-medium">${operacion.status_actual || operacion.status_manual || operacion.status_calculado || '-'}</p>
                        </div>
                        <div>
                            <span class="text-slate-600">ID:</span>
                            <p class="font-medium">#${operacion.id}</p>
                        </div>
                    </div>
                </div>

                <!-- Observaciones del Ejecutivo -->
                ${operacion.comentarios ? `
                <div class="bg-amber-50 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-slate-800 mb-3">
                        <i class="fas fa-user-tie text-amber-600 mr-2"></i>Observaciones del Ejecutivo
                    </h3>
                    <div class="bg-white rounded-lg p-3 border border-amber-200">
                        <p class="text-slate-700 whitespace-pre-wrap">${(operacion.comentarios || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/\n/g, '<br>')}</p>
                    </div>
                </div>
                ` : ''}

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

                <!-- Observaciones del Sistema -->
                <div class="bg-white rounded-lg border p-4">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">
                        <i class="fas fa-cogs text-blue-600 mr-2"></i>Observaciones del Sistema
                    </h3>
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

// Función para editar operación - Cargar datos en el modal
window.editarOperacion = function(operacionId) {
    // Obtener los datos de la operación
    fetch(`/logistica/operaciones/${operacionId}/historial`)
        .then(response => response.json())
        .then(data => {
            console.log('Datos recibidos:', data); // Debug
            
            if (data.success && data.operacion) {
                const op = data.operacion;
                console.log('Operación:', op); // Debug
                
                // Configurar el modal para edición
                document.getElementById('operacionId').value = op.id || '';
                document.getElementById('isEditing').value = 'PUT';
                document.getElementById('modalTitle').innerHTML = '<span class="text-amber-600 mr-2 text-xl">✏️</span>Editar Operación #' + op.id;
                document.getElementById('submitButtonText').textContent = 'Actualizar Operación';
                document.getElementById('statusManualSection').classList.remove('hidden');
                
                // Llenar todos los campos del formulario
                const form = document.getElementById('formOperacion');
                if (form) {
                    // Función helper para llenar campos de forma segura
                    const setFieldValue = (selector, value) => {
                        const field = form.querySelector(selector);
                        if (field) {
                            field.value = value || '';
                        } else {
                            console.warn('Campo no encontrado:', selector);
                        }
                    };
                    
                    // Tipo de operación
                    setFieldValue('[name="operacion"]', op.operacion);
                    setFieldValue('[name="tipo_operacion_enum"]', op.tipo_operacion_enum);
                    
                    // Cliente y ejecutivo
                    setFieldValue('[name="cliente"]', op.cliente);
                    setFieldValue('[name="ejecutivo"]', op.ejecutivo);
                    
                    // Detalles de operación
                    setFieldValue('[name="proveedor_o_cliente"]', op.proveedor_o_cliente);
                    setFieldValue('[name="no_factura"]', op.no_factura);
                    setFieldValue('[name="clave"]', op.clave);
                    setFieldValue('[name="referencia_interna"]', op.referencia_interna);
                    
                    // Fecha y aduana
                    setFieldValue('[name="fecha_embarque"]', op.fecha_embarque);
                    setFieldValue('[name="aduana"]', op.aduana);
                    
                    // Agente y transporte
                    setFieldValue('[name="agente_aduanal"]', op.agente_aduanal);
                    setFieldValue('[name="transporte"]', op.transporte);
                    
                    // Información adicional
                    setFieldValue('[name="fecha_arribo_aduana"]', op.fecha_arribo_aduana);
                    setFieldValue('[name="fecha_modulacion"]', op.fecha_modulacion);
                    setFieldValue('[name="fecha_arribo_planta"]', op.fecha_arribo_planta);
                    setFieldValue('[name="no_pedimento"]', op.no_pedimento);
                    setFieldValue('[name="referencia_aa"]', op.referencia_aa);
                    setFieldValue('[name="guia_bl"]', op.guia_bl);
                    setFieldValue('[name="comentarios"]', op.comentarios);
                    
                    // Status manual - asegurar que el select existe
                    const statusSelect = document.getElementById('statusManualSelect');
                    if (statusSelect) {
                        statusSelect.value = op.status_manual || 'In Process';
                        console.log('Status manual configurado:', statusSelect.value);
                    } else {
                        console.error('Select de status manual no encontrado');
                    }
                }
                
                // Actualizar transportes según el tipo
                if (typeof actualizarTransportes === 'function') {
                    actualizarTransportes();
                }
                
                // Abrir el modal
                document.getElementById('modalOperacion').classList.remove('hidden');
            } else {
                console.error('Respuesta inválida:', data);
                mostrarAlerta('Error al cargar los datos de la operación', 'error');
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            mostrarAlerta('Error de conexión al cargar la operación: ' + error.message, 'error');
        });
};

// Función para eliminar operación
window.eliminarOperacion = function(operacionId) {
        mostrarConfirmacion('¿Está seguro de que desea eliminar esta operación? Esta acción no se puede deshacer.', function() {
            fetch(`/logistica/operaciones/${operacionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Operación eliminada exitosamente', 'success');
                    window.location.reload();
                } else {
                    mostrarAlerta('Error al eliminar la operación: ' + (data.message || 'Error desconocido'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error de conexión', 'error');
            });
        }, '¿Eliminar operación?', 'Eliminar');
    };

    // Event listeners para fechas y cálculos automáticos
    document.addEventListener('change', function(e) {
        // NOTA: resultado y dias_transito se calculan automáticamente en el backend
        // No necesitan cálculo en el frontend ya que no tienen campos en el formulario
        // if (e.target.name === 'fecha_arribo_aduana' || e.target.name === 'fecha_modulacion') {
        //     calcularResultado();
        // }
        // if (e.target.name === 'fecha_embarque' || e.target.name === 'fecha_arribo_planta') {
        //     calcularDiasTransito();
        // }
        if (e.target.name === 'tipo_operacion_enum') {
            actualizarTransportes();
        }
    });

    // Manejar envío del formulario (crear o actualizar)
    document.getElementById('formOperacion').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        const operacionId = document.getElementById('operacionId').value;
        const isEditing = document.getElementById('isEditing').value === 'PUT';
        
        // Mostrar indicador de carga
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 inline-block mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> ' + (isEditing ? 'Actualizando...' : 'Guardando...');
        
        // Determinar URL y método
        const url = isEditing ? `/logistica/operaciones/${operacionId}` : '/logistica/operaciones';
        const method = isEditing ? 'PUT' : 'POST';
        
        // Si es edición, agregar el método PUT al FormData
        if (isEditing) {
            formData.append('_method', 'PUT');
        }
        
        fetch(url, {
            method: 'POST', // Siempre POST, Laravel detecta PUT por _method
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
                submitBtn.innerHTML = '<svg class="h-5 w-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> ¡' + (isEditing ? 'Actualizado!' : 'Guardado!');
                setTimeout(() => {
                    cerrarModal();
                    window.location.reload();
                }, 800);
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                mostrarAlerta('Error al ' + (isEditing ? 'actualizar' : 'guardar') + ' la operación: ' + (data.message || 'Error desconocido'), 'error');
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            mostrarAlerta('Error al ' + (isEditing ? 'actualizar' : 'guardar') + ' la operación: ' + error.message, 'error');
        });
    });

    // Event listeners para cerrar modales
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModal();
            cerrarModalHistorial();
        }
    });

    // MODAL DE OPERACIÓN: NO se cierra al hacer clic fuera para evitar pérdida de datos
    // Solo se puede cerrar con el botón X o después de guardar
    document.getElementById('modalOperacion').addEventListener('click', function(e) {
        // Comentado para evitar cierre accidental y pérdida de trabajo
        // if (e.target === this) {
        //     cerrarModal();
        // }
    });

    // MODAL DE COMENTARIOS/OBSERVACIONES: También protegido contra cierre accidental
    document.getElementById('modalComentarios').addEventListener('click', function(e) {
        // Comentado para evitar cierre accidental y pérdida de trabajo al editar observaciones
        // if (e.target === this) {
        //     cerrarModalComentarios();
        // }
    });

    document.getElementById('modalHistorial').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalHistorial();
        }
    });
});

// NOTA: El recálculo de status ahora se ejecuta automáticamente al cargar la página
// La función manual ha sido eliminada ya que no es necesaria

// Función para marcar operación como Done
window.marcarComoDone = function(operacionId) {
    mostrarConfirmacion('¿Está seguro de marcar esta operación como completada?', function() {
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
                mostrarAlerta('Error al actualizar el status: ' + (data.message || 'Error desconocido'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error de conexión', 'error');
        });
    }, '¿Marcar como completada?', 'Marcar');
}

// Funciones para Post-Operaciones
window.marcarPostOpComoDone = function(postOpId) {
    mostrarConfirmacion('¿Está seguro de marcar esta post-operación como completada?', function() {
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
                mostrarAlerta('Error al marcar como completada: ' + (data.message || 'Error desconocido'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error de conexión', 'error');
        });
    }, '¿Marcar como completada?', 'Marcar');
}

window.eliminarPostOperacion = function(postOpId) {
    mostrarConfirmacion('¿Está seguro de eliminar esta post-operación?', function() {
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
                mostrarAlerta('Error al eliminar: ' + (data.message || 'Error desconocido'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error de conexión', 'error');
        });
    }, '¿Eliminar post-operación?', 'Eliminar');
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
            mostrarAlerta('Post-operación guardada exitosamente', 'success');
        } else {
            mostrarAlerta('Error al guardar: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión', 'error');
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
            if (data.success && data.postOperaciones && Array.isArray(data.postOperaciones)) {
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
    
    // Validar que postOperaciones sea un array válido
    if (!postOperaciones || !Array.isArray(postOperaciones)) {
        contenedor.innerHTML = `
            <div class="text-center py-8 text-red-500">
                <p>Error: Datos de post-operaciones no válidos</p>
            </div>
        `;
        return;
    }
    
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
        mostrarAlerta('No hay cambios pendientes para guardar', 'info');
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
            mostrarAlerta('Cambios guardados exitosamente', 'success');
            cambiosPendientesPostOps = {}; // Limpiar cambios pendientes
            cargarPostOperacionesPorOperacion(operacionId);
            // Actualizar tabla principal si es necesario
            // window.location.reload();
        } else {
            mostrarAlerta('Error al guardar: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al guardar', 'error');
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
    // Resetear cualquier estado de edición de comentarios
    const textareas = document.querySelectorAll('#modalComentarios textarea');
    textareas.forEach(textarea => textarea.style.display = 'none');
    const spans = document.querySelectorAll('#modalComentarios .comentario-texto');
    spans.forEach(span => span.style.display = 'block');
};

function cargarComentariosPorOperacion(operacionId) {
    // Cargar historial de observaciones (agregar timestamp para evitar caché)
    const timestamp = new Date().getTime();
    fetch(`/logistica/operaciones/${operacionId}/observaciones-historial?v=${timestamp}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarHistorialObservaciones(data.observaciones, data.operacion);
            } else {
                document.getElementById('listaComentarios').innerHTML = `
                    <div class="text-center py-4 text-red-500">
                        <p>Error al cargar observaciones</p>
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

function mostrarHistorialObservaciones(observaciones, operacion) {
    const contenedor = document.getElementById('listaComentarios');
    
    // Mostrar información de la operación
    const infoOperacion = `
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <h4 class="font-semibold text-blue-900">Operación: ${operacion.operacion}</h4>
            <p class="text-sm text-blue-700">Cliente: ${operacion.cliente}</p>
            ${operacion.no_pedimento ? `<p class="text-sm text-blue-700">Pedimento: ${operacion.no_pedimento}</p>` : ''}
            <p class="text-sm text-blue-700">Status actual: ${operacion.status_actual}</p>
        </div>
    `;
    
    // Mostrar observación actual editable
    const observacionActual = operacion.observacion_actual || '';
    const formularioEdicion = `
        <div class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-lg">
            <h4 class="font-semibold text-orange-900 mb-3">👤 Observaciones del Ejecutivo</h4>
            <form id="formEditarObservaciones" class="space-y-3">
                <textarea id="observacionesActuales" rows="4" 
                    class="w-full p-3 border border-orange-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500" 
                    placeholder="Escriba aquí sus observaciones como ejecutivo...">${observacionActual}</textarea>
                <div class="flex space-x-2">
                    <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 focus:ring-2 focus:ring-orange-500 transition-colors">
                        💾 Agregar al Historial
                    </button>
                    <button type="button" onclick="cancelarEdicionObservaciones()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        ❌ Cancelar
                    </button>
                </div>
            </form>
        </div>
    `;
    
    // Mostrar historial completo de observaciones (ordenado del más reciente al más antiguo)
    let historialHTML = '';
    if (observaciones && observaciones.length > 0) {
        // Ordenar del más reciente al más antiguo
        const observacionesOrdenadas = [...observaciones].reverse();
        
        historialHTML = `
            <div class="mb-4">
                <h5 class="font-medium text-gray-700 mb-3">📋 Historial Completo de Observaciones</h5>
                <div class="space-y-2">
                    ${observacionesOrdenadas.map((obs, index) => {
                        const esReciente = index === 0;
                        const colorBorde = esReciente ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50';
                        const indicadorReciente = esReciente ? '<span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-2"></span>' : '';
                        
                        return `
                            <div class="border ${colorBorde} rounded-lg p-3 text-sm">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex items-center space-x-2">
                                        ${indicadorReciente}
                                        <span class="font-medium text-gray-700">${obs.usuario}</span>
                                        ${obs.status ? `<span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded">${obs.status}</span>` : ''}
                                        ${esReciente ? '<span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded font-medium">MÁS RECIENTE</span>' : ''}
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500">${obs.fecha_formateada}</div>
                                        <div class="text-xs text-gray-400">${obs.tiempo_relativo}</div>
                                    </div>
                                </div>
                                <p class="text-gray-800 leading-relaxed">${obs.observaciones}</p>
                            </div>
                        `;
                    }).join('')}
                </div>
                <div class="mt-3 p-2 bg-blue-50 rounded text-xs text-blue-700">
                    💡 <strong>Nota:</strong> Cada vez que agregue una observación, se creará un nuevo registro en el historial preservando todas las observaciones anteriores.
                </div>
            </div>
        `;
    } else {
        historialHTML = `
            <div class="mb-4 text-center py-6 text-gray-500">
                <div class="text-4xl mb-2">📝</div>
                <p>No hay observaciones previas en el historial</p>
                <p class="text-xs mt-1">Su primera observación aparecerá aquí</p>
            </div>
        `;
    }
    
    contenedor.innerHTML = infoOperacion + formularioEdicion + historialHTML;
    
    // Agregar event listener al formulario
    document.getElementById('formEditarObservaciones').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarObservaciones();
    });
}

// Funciones para manejar observaciones
window.guardarObservaciones = function() {
    if (!operacionActualComentarios) {
        mostrarAlerta('No se ha seleccionado una operación', 'error');
        return;
    }
    
    const observacionesTexto = document.getElementById('observacionesActuales').value.trim();
    
    if (!observacionesTexto) {
        mostrarAlerta('Las observaciones no pueden estar vacías', 'warning');
        return;
    }
    
    // Deshabilitar el botón mientras se procesa
    const submitButton = document.querySelector('#formEditarObservaciones button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '⏳ Guardando...';
    
    fetch(`/logistica/operaciones/${operacionActualComentarios}/observaciones`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            observaciones: observacionesTexto
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Nueva observación agregada al historial exitosamente', 'success');
            // Recargar las observaciones para mostrar la nueva entrada
            cargarComentariosPorOperacion(operacionActualComentarios);
            // Recargar la tabla para mostrar cambios
            if (typeof actualizarStatusOperacion === 'function') {
                actualizarStatusOperacion(operacionActualComentarios);
            }
        } else {
            mostrarAlerta('Error al guardar observaciones: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al guardar observaciones', 'error');
    })
    .finally(() => {
        // Restaurar el botón
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
};

window.cancelarEdicionObservaciones = function() {
    // Recargar las observaciones originales
    cargarComentariosPorOperacion(operacionActualComentarios);
};



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
    mostrarConfirmacion('¿Está seguro de eliminar esta post-operación?', function() {
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
    }, '¿Eliminar post-operación?', 'Eliminar');
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
            mostrarAlerta('Error al guardar: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión', 'error');
    });
});

// Remover la carga automática de post-operaciones al iniciar
// document.addEventListener('DOMContentLoaded', function() {
//     cargarPostOperaciones();
// });

// ========================================
// FUNCIONES PARA REPORTES WORD (ELIMINADAS)
// ========================================
// Las funciones de generación de reportes Word han sido eliminadas
// ya que esta funcionalidad no se utilizará

// =======================================
// FUNCIONES PARA NUEVA ADUANA EN MATRIZ
// =======================================

// Mostrar formulario de nueva aduana
window.mostrarNuevaAduana = function() {
    const form = document.getElementById('nuevaAduanaForm');
    if (form) form.classList.remove('hidden');
};

// Cancelar nueva aduana
window.cancelarNuevaAduana = function() {
    const form = document.getElementById('nuevaAduanaForm');
    const inputs = ['nuevaAduanaCodigo', 'nuevaAduanaSeccion', 'nuevaAduanaDenominacion'];
    
    if (form) form.classList.add('hidden');
    
    inputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) input.value = '';
    });
    
    // Restaurar valor por defecto
    const seccionInput = document.getElementById('nuevaAduanaSeccion');
    if (seccionInput) seccionInput.value = '0';
};

// Guardar nueva aduana
window.guardarNuevaAduana = function() {
    const codigo = document.getElementById('nuevaAduanaCodigo').value.trim();
    const seccion = document.getElementById('nuevaAduanaSeccion').value.trim() || '0';
    const denominacion = document.getElementById('nuevaAduanaDenominacion').value.trim();

    // Validaciones
    if (!codigo || codigo.length !== 2 || !/^\d{2}$/.test(codigo)) {
        mostrarAlerta('El código debe ser de 2 dígitos (01-99)', 'warning');
        return;
    }

    if (seccion.length !== 1 || !/^\d{1}$/.test(seccion)) {
        mostrarAlerta('La sección debe ser de 1 dígito (0-9)', 'warning');
        return;
    }

    if (!denominacion) {
        mostrarAlerta('La denominación es obligatoria', 'warning');
        return;
    }

    // Crear FormData
    const formData = new FormData();
    formData.append('aduana', codigo);
    formData.append('seccion', seccion);
    formData.append('denominacion', denominacion);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    // Enviar petición
    fetch('/logistica/aduanas', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Agregar la nueva aduana al select
            const aduanaSelect = document.getElementById('aduanaSelect');
            if (aduanaSelect) {
                const option = document.createElement('option');
                const valorCompleto = `${codigo}${seccion}`;
                const textoCompleto = `${codigo}${seccion} - ${denominacion}`;
                option.value = valorCompleto;
                option.textContent = textoCompleto;
                option.setAttribute('data-denominacion', denominacion);
                aduanaSelect.appendChild(option);
                // Seleccionar la nueva aduana
                aduanaSelect.value = valorCompleto;
            }

            // Limpiar y ocultar formulario
            cancelarNuevaAduana();
            mostrarAlerta('Aduana creada exitosamente', 'success');
        } else {
            mostrarAlerta(data.message || 'Error al crear la aduana', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al crear la aduana', 'error');
    });
};

// ========================================
// FORMATEO AUTOMÁTICO DE NÚMERO DE PEDIMENTO
// ========================================

/**
 * Formatea el número de pedimento con la estructura: XX XX XXXX XXXXXXX
 * Ejemplo: 25 24 1029 5002294
 */
document.addEventListener('DOMContentLoaded', function() {
    const pedimentoInput = document.getElementById('no_pedimento');
    
    if (pedimentoInput) {
        pedimentoInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, ''); // Quitar espacios
            let formatted = '';
            
            // Solo permitir números
            value = value.replace(/\D/g, '');
            
            // Aplicar formato: XX XX XXXX XXXXXXX
            if (value.length > 0) {
                formatted = value.substring(0, 2); // Primeros 2 dígitos
            }
            if (value.length > 2) {
                formatted += ' ' + value.substring(2, 4); // Siguientes 2 dígitos
            }
            if (value.length > 4) {
                formatted += ' ' + value.substring(4, 8); // Siguientes 4 dígitos
            }
            if (value.length > 8) {
                formatted += ' ' + value.substring(8, 15); // Últimos 7 dígitos
            }
            
            e.target.value = formatted;
        });
    }
});
