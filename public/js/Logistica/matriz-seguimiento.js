// JavaScript para Matriz de Seguimiento

// Variables globales
let transportes = window.transportes || {};
let operacionActualId = null;

// ========================================
// SISTEMA DE REORDENAMIENTO DE COLUMNAS EN TABLA
// ========================================

/**
 * Aplica el orden de columnas guardado al cargar la página
 * Lee el orden desde window.columnasOrdenadasConfig (desde el servidor/BD)
 */
window.aplicarOrdenColumnasGuardado = function() {
    // Obtener orden guardado desde el servidor (pasado por PHP)
    let ordenGuardado = window.columnasOrdenadasConfig || [];
    
    console.log('Orden de columnas desde BD:', ordenGuardado);
    
    if (!ordenGuardado.length) {
        console.log('No hay orden de columnas guardado en BD para aplicar');
        return;
    }
    
    // Verificar si hay un orden personalizado (orden diferente al por defecto)
    const tieneOrdenPersonalizado = ordenGuardado.some((col, idx) => col.orden !== idx);
    if (!tieneOrdenPersonalizado) {
        console.log('Las columnas están en orden por defecto, no se necesita reordenar');
        return;
    }
    
    // Filtrar solo las columnas que tienen un orden definido
    const columnasConOrden = ordenGuardado.filter(col => typeof col.orden === 'number');
    if (!columnasConOrden.length) {
        console.log('No hay columnas con orden definido');
        return;
    }
    
    // Ordenar por el campo 'orden'
    columnasConOrden.sort((a, b) => a.orden - b.orden);
    console.log('Columnas ordenadas:', columnasConOrden.map(c => c.columna));
    
    const tabla = document.getElementById('tablaMatriz');
    if (!tabla) {
        console.log('Tabla no encontrada');
        return;
    }
    
    const thead = tabla.querySelector('thead tr');
    const tbody = tabla.querySelector('tbody');
    if (!thead) return;
    
    const headers = Array.from(thead.querySelectorAll('th'));
    const rows = tbody ? Array.from(tbody.querySelectorAll('tr')) : [];
    
    console.log('Headers encontrados:', headers.length);
    console.log('Headers con data-columna:', headers.map(h => h.dataset.columna || h.dataset.campoId || h.textContent.substring(0, 20)));
    
    // Crear mapa de columna -> índice actual
    const mapaIndices = {};
    headers.forEach((th, index) => {
        let columna = th.dataset.columna;
        if (!columna && th.dataset.campoId) {
            columna = `campo_${th.dataset.campoId}`;
        }
        if (columna) {
            mapaIndices[columna] = index;
        }
    });
    
    console.log('Mapa de índices:', mapaIndices);
    
    // Crear el nuevo orden de índices
    const nuevoOrden = [];
    columnasConOrden.forEach(col => {
        const columnaKey = col.columna;
        if (mapaIndices.hasOwnProperty(columnaKey)) {
            nuevoOrden.push(mapaIndices[columnaKey]);
        }
    });
    
    // Agregar columnas que no están en el orden guardado (al final)
    headers.forEach((th, index) => {
        if (!nuevoOrden.includes(index)) {
            nuevoOrden.push(index);
        }
    });
    
    console.log('Nuevo orden de índices:', nuevoOrden);
    
    // Si el orden es igual al actual, no hacer nada
    const ordenActual = headers.map((_, i) => i);
    if (JSON.stringify(nuevoOrden) === JSON.stringify(ordenActual)) {
        console.log('El orden de columnas ya está aplicado');
        return;
    }
    
    // Reordenar headers
    const fragment = document.createDocumentFragment();
    nuevoOrden.forEach(idx => {
        if (headers[idx]) {
            fragment.appendChild(headers[idx].cloneNode(true));
        }
    });
    thead.innerHTML = '';
    thead.appendChild(fragment);
    
    // Reordenar celdas en cada fila
    rows.forEach(row => {
        const cells = Array.from(row.querySelectorAll('td'));
        const rowFragment = document.createDocumentFragment();
        nuevoOrden.forEach(idx => {
            if (cells[idx]) {
                rowFragment.appendChild(cells[idx].cloneNode(true));
            }
        });
        row.innerHTML = '';
        row.appendChild(rowFragment);
    });
    
    console.log('Orden de columnas aplicado exitosamente desde BD');
    
    // Reinicializar drag & drop después de reordenar
    setTimeout(() => {
        inicializarDragDropColumnas();
    }, 100);
};

// Mantener la función anterior por compatibilidad
window.reordenarColumnasTabla = window.aplicarOrdenColumnasGuardado;

// ========================================
// SCROLL SINCRONIZADO SUPERIOR E INFERIOR
// ========================================

/**
 * Inicializa el scroll sincronizado entre el scroll superior y el de la tabla
 */
window.inicializarScrollSincronizado = function() {
    const scrollSuperior = document.getElementById('scrollSuperior');
    const scrollInferior = document.getElementById('scrollInferior');
    const scrollSuperiorInner = document.getElementById('scrollSuperiorInner');
    const tabla = document.getElementById('tablaMatriz');
    
    if (!scrollSuperior || !scrollInferior || !scrollSuperiorInner || !tabla) {
        return;
    }
    
    // Establecer el ancho del div interno igual al de la tabla
    const actualizarAnchoScroll = function() {
        scrollSuperiorInner.style.width = tabla.scrollWidth + 'px';
    };
    
    actualizarAnchoScroll();
    
    // Sincronizar scrolls
    let sincronizando = false;
    
    scrollSuperior.addEventListener('scroll', function() {
        if (!sincronizando) {
            sincronizando = true;
            scrollInferior.scrollLeft = scrollSuperior.scrollLeft;
            sincronizando = false;
        }
    });
    
    scrollInferior.addEventListener('scroll', function() {
        if (!sincronizando) {
            sincronizando = true;
            scrollSuperior.scrollLeft = scrollInferior.scrollLeft;
            sincronizando = false;
        }
    });
    
    // Actualizar cuando cambie el tamaño de la ventana
    window.addEventListener('resize', actualizarAnchoScroll);
    
    // Actualizar después de que se carguen los datos (por si hay cambios)
    setTimeout(actualizarAnchoScroll, 500);
    setTimeout(actualizarAnchoScroll, 1500);
};

// Ejecutar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    inicializarScrollSincronizado();
});

// Ejecutar reordenamiento al cargar - HABILITADO
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un momento para que la tabla esté completamente renderizada
    setTimeout(function() {
        aplicarOrdenColumnasGuardado();
    }, 200);
});

// ========================================
// SISTEMA DE DRAG & DROP PARA COLUMNAS DE LA TABLA
// ========================================

/**
 * Variables globales para el sistema de drag & drop de columnas
 */
let columnaDragIndex = null;
let columnaDragElement = null;
let guardarOrdenTimeout = null;

/**
 * Obtiene el empleado_id del usuario actual para guardar configuración
 */
function obtenerEmpleadoIdActual() {
    // Primero intentar la variable global definida en el blade
    if (window.empleadoIdActual) {
        return window.empleadoIdActual;
    }
    
    // Intentar obtener del dataset del body
    const bodyDataset = document.body.dataset;
    if (bodyDataset.empleadoId) {
        return bodyDataset.empleadoId;
    }
    
    // Intentar obtener de un elemento oculto
    const hiddenInput = document.getElementById('empleadoIdActual');
    if (hiddenInput && hiddenInput.value) {
        return hiddenInput.value;
    }
    
    // Intentar obtener del select de filtro ejecutivo si hay uno seleccionado
    const selectEjecutivo = document.getElementById('filtroEjecutivo');
    if (selectEjecutivo && selectEjecutivo.value && selectEjecutivo.value !== 'todos') {
        return selectEjecutivo.value;
    }
    
    return null;
}

/**
 * Inicializa el sistema de drag & drop para las columnas de la tabla
 */
window.inicializarDragDropColumnas = function() {
    const tabla = document.getElementById('tablaMatriz');
    if (!tabla) return;
    
    const thead = tabla.querySelector('thead tr');
    if (!thead) return;
    
    const headers = thead.querySelectorAll('th');
    
    headers.forEach((th, index) => {
        // Asignar índice de columna como atributo
        th.dataset.columnIndex = index;
        
        // Obtener identificador de columna para guardar
        if (!th.dataset.columna) {
            // Intentar inferir de clases o contenido
            const texto = th.textContent.trim().replace(/[★\s]+/g, ' ').trim();
            th.dataset.columnaOriginal = texto;
        }
        
        // Hacer el header draggable
        th.draggable = true;
        th.style.cursor = 'grab';
        
        // Agregar estilos para indicar que es arrastrable
        th.classList.add('draggable-column');
        
        // Eventos de drag
        th.addEventListener('dragstart', handleColumnDragStart);
        th.addEventListener('dragover', handleColumnDragOver);
        th.addEventListener('dragenter', handleColumnDragEnter);
        th.addEventListener('dragleave', handleColumnDragLeave);
        th.addEventListener('drop', handleColumnDrop);
        th.addEventListener('dragend', handleColumnDragEnd);
    });
    
    console.log('Sistema de drag & drop de columnas inicializado');
};

/**
 * Manejador del inicio del arrastre de una columna
 */
function handleColumnDragStart(e) {
    columnaDragIndex = parseInt(this.dataset.columnIndex);
    columnaDragElement = this;
    
    // Efecto visual
    this.style.opacity = '0.5';
    this.style.cursor = 'grabbing';
    
    // Información para el drop
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', columnaDragIndex);
    
    // Agregar clase para estilo visual
    this.classList.add('dragging');
}

/**
 * Manejador cuando se arrastra sobre una columna
 */
function handleColumnDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    return false;
}

/**
 * Manejador cuando se entra a una columna durante el arrastre
 */
function handleColumnDragEnter(e) {
    this.classList.add('drag-over');
}

/**
 * Manejador cuando se sale de una columna durante el arrastre
 */
function handleColumnDragLeave(e) {
    this.classList.remove('drag-over');
}

/**
 * Manejador cuando se suelta una columna
 */
function handleColumnDrop(e) {
    e.stopPropagation();
    e.preventDefault();
    
    const targetIndex = parseInt(this.dataset.columnIndex);
    
    if (columnaDragIndex !== null && columnaDragIndex !== targetIndex) {
        // Reordenar columnas en la tabla
        reordenarColumnasEnTabla(columnaDragIndex, targetIndex);
        
        // Programar guardado automático (con debounce de 1 segundo)
        clearTimeout(guardarOrdenTimeout);
        guardarOrdenTimeout = setTimeout(() => {
            guardarOrdenColumnasAutomatico();
        }, 1000);
    }
    
    this.classList.remove('drag-over');
    return false;
}

/**
 * Manejador cuando termina el arrastre
 */
function handleColumnDragEnd(e) {
    // Restaurar estilos
    this.style.opacity = '';
    this.style.cursor = 'grab';
    this.classList.remove('dragging');
    
    // Limpiar clases de drag-over en todos los headers
    const headers = document.querySelectorAll('#tablaMatriz thead th');
    headers.forEach(th => {
        th.classList.remove('drag-over');
    });
    
    columnaDragIndex = null;
    columnaDragElement = null;
}

/**
 * Reordena las columnas en la tabla (headers y celdas)
 */
function reordenarColumnasEnTabla(fromIndex, toIndex) {
    const tabla = document.getElementById('tablaMatriz');
    if (!tabla) return;
    
    // Reordenar headers
    const thead = tabla.querySelector('thead tr');
    if (thead) {
        const headers = Array.from(thead.querySelectorAll('th'));
        const movedHeader = headers[fromIndex];
        
        if (fromIndex < toIndex) {
            // Mover hacia la derecha
            thead.insertBefore(movedHeader, headers[toIndex].nextSibling);
        } else {
            // Mover hacia la izquierda
            thead.insertBefore(movedHeader, headers[toIndex]);
        }
    }
    
    // Reordenar celdas en todas las filas del tbody
    const tbody = tabla.querySelector('tbody');
    if (tbody) {
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(row => {
            const cells = Array.from(row.querySelectorAll('td'));
            if (cells.length > Math.max(fromIndex, toIndex)) {
                const movedCell = cells[fromIndex];
                
                if (fromIndex < toIndex) {
                    row.insertBefore(movedCell, cells[toIndex].nextSibling);
                } else {
                    row.insertBefore(movedCell, cells[toIndex]);
                }
            }
        });
    }
    
    // Actualizar índices de columnas
    actualizarIndicesColumnas();
    
    // Mostrar notificación de cambio
    mostrarNotificacionOrden();
}

/**
 * Actualiza los atributos data-column-index después de reordenar
 */
function actualizarIndicesColumnas() {
    const tabla = document.getElementById('tablaMatriz');
    if (!tabla) return;
    
    const headers = tabla.querySelectorAll('thead th');
    headers.forEach((th, index) => {
        th.dataset.columnIndex = index;
    });
}

/**
 * Muestra una notificación temporal de que se guardará el orden
 */
function mostrarNotificacionOrden() {
    // Remover notificación existente si hay
    const existente = document.getElementById('notificacionOrden');
    if (existente) {
        existente.remove();
    }
    
    const notificacion = document.createElement('div');
    notificacion.id = 'notificacionOrden';
    notificacion.className = 'fixed bottom-4 right-4 bg-blue-600 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 z-50 transition-opacity duration-300';
    notificacion.innerHTML = `
        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Guardando orden de columnas...</span>
    `;
    document.body.appendChild(notificacion);
}

/**
 * Guarda automáticamente el orden de las columnas en la base de datos
 */
function guardarOrdenColumnasAutomatico() {
    const empleadoId = obtenerEmpleadoIdActual();
    
    // Recopilar orden actual de columnas
    const tabla = document.getElementById('tablaMatriz');
    if (!tabla) return;
    
    const headers = tabla.querySelectorAll('thead th');
    const ordenColumnas = [];
    
    headers.forEach((th, index) => {
        let columna = th.dataset.columna;
        const campoId = th.dataset.campoId;
        
        if (campoId) {
            columna = `campo_${campoId}`;
        } else if (!columna) {
            // Usar el texto del header como identificador de respaldo
            columna = th.textContent.trim().replace(/[★\s]+/g, '_').substring(0, 30) || `columna_${index}`;
        }
        
        ordenColumnas.push({
            columna: columna,
            orden: index,
            visible: true
        });
    });
    
    console.log('Guardando orden de columnas en BD:', ordenColumnas);
    console.log('Empleado ID:', empleadoId);
    
    // Si no hay empleado específico, mostrar error
    if (!empleadoId) {
        console.error('No hay empleado_id, no se puede guardar en BD');
        mostrarNotificacionError('No se puede guardar: usuario no identificado');
        return;
    }
    
    // Guardar en el servidor (base de datos)
    fetch('/logistica/columnas-config/orden', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            empleado_id: parseInt(empleadoId),
            orden_columnas: ordenColumnas
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Respuesta del servidor:', data);
        if (data.success) {
            mostrarNotificacionExito();
        } else {
            console.error('Error del servidor:', data);
            mostrarNotificacionError(data.mensaje || 'Error al guardar');
        }
    })
    .catch(error => {
        console.error('Error guardando orden:', error);
        mostrarNotificacionError('Error de conexión: ' + error.message);
    });
}

/**
 * Muestra notificación de error
 */
function mostrarNotificacionError(mensaje) {
    const notificacion = document.getElementById('notificacionOrden');
    if (notificacion) {
        notificacion.className = 'fixed bottom-4 right-4 bg-red-600 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 z-50 transition-opacity duration-300';
        notificacion.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span>${mensaje}</span>
        `;
        
        // Desvanecer y remover después de 4 segundos
        setTimeout(() => {
            notificacion.style.opacity = '0';
            setTimeout(() => {
                notificacion.remove();
            }, 300);
        }, 4000);
    }
}

/**
 * Muestra notificación de éxito al guardar
 */
function mostrarNotificacionExito() {
    const notificacion = document.getElementById('notificacionOrden');
    if (notificacion) {
        notificacion.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 z-50 transition-opacity duration-300';
        notificacion.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>Orden guardado correctamente</span>
        `;
        
        // Desvanecer y remover después de 2 segundos
        setTimeout(() => {
            notificacion.style.opacity = '0';
            setTimeout(() => {
                notificacion.remove();
            }, 300);
        }, 2000);
    }
}

// Inicializar drag & drop al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    inicializarDragDropColumnas();
});

// ========================================
// SISTEMA DE FILTROS
// ========================================

/**
 * Aplica los filtros seleccionados y recarga la página
 */
window.aplicarFiltros = function() {
    const cliente = document.getElementById('filtroCliente')?.value || 'todos';
    const ejecutivoSelect = document.getElementById('filtroEjecutivo');
    const ejecutivo = ejecutivoSelect ? ejecutivoSelect.value : 'todos';
    
    const params = new URLSearchParams();
    if (cliente && cliente !== 'todos') {
        params.set('cliente', cliente);
    }
    if (ejecutivo && ejecutivo !== 'todos') {
        params.set('ejecutivo', ejecutivo);
    }
    
    const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.location.href = url;
};

/**
 * Filtra por cliente específico (usado por las pestañas)
 * @param {string} cliente - Nombre del cliente o 'todos'
 */
window.filtrarPorCliente = function(cliente) {
    const select = document.getElementById('filtroCliente');
    if (select) {
        select.value = cliente;
    }
    aplicarFiltros();
};

/**
 * Limpia todos los filtros
 */
window.limpiarFiltros = function() {
    window.location.href = window.location.pathname;
};

// ========================================
// SISTEMA DE CONFIGURACIÓN DE COLUMNAS
// ========================================

let ejecutivoSeleccionadoColumnas = null;
let columnasPredeterminadasConfig = {};
let columnasOpcionalesConfig = {};

/**
 * Cambiar entre pestañas de configuración
 */
window.cambiarTabConfig = function(tab) {
    const tabColumnas = document.getElementById('tabColumnas');
    const tabCampos = document.getElementById('tabCampos');
    const panelColumnas = document.getElementById('panelColumnas');
    const panelCampos = document.getElementById('panelCampos');
    
    if (!tabColumnas || !tabCampos || !panelColumnas || !panelCampos) return;
    
    if (tab === 'columnas') {
        tabColumnas.classList.add('text-blue-600', 'border-blue-600', 'bg-blue-50');
        tabColumnas.classList.remove('text-slate-500', 'border-transparent');
        tabCampos.classList.remove('text-blue-600', 'border-blue-600', 'bg-blue-50');
        tabCampos.classList.add('text-slate-500', 'border-transparent');
        panelColumnas.classList.remove('hidden');
        panelCampos.classList.add('hidden');
        cargarEjecutivosParaColumnas();
    } else {
        tabCampos.classList.add('text-blue-600', 'border-blue-600', 'bg-blue-50');
        tabCampos.classList.remove('text-slate-500', 'border-transparent');
        tabColumnas.classList.remove('text-blue-600', 'border-blue-600', 'bg-blue-50');
        tabColumnas.classList.add('text-slate-500', 'border-transparent');
        panelCampos.classList.remove('hidden');
        panelColumnas.classList.add('hidden');
        cargarCamposPersonalizados();
        cargarEjecutivosParaCampos();
        cargarOpcionesPosicionCampo(); // Cargar opciones dinámicas
    }
};

/**
 * Cargar opciones dinámicas para el select "Mostrar después de"
 */
window.cargarOpcionesPosicionCampo = function() {
    const select = document.getElementById('posicionNuevoCampo');
    if (!select) return;
    
    // Obtener el idioma seleccionado
    const idioma = document.querySelector('input[name="idiomaColumnas"]:checked')?.value || 'es';
    
    // Obtener configuración de columnas
    fetch('/logistica/columnas-config')
        .then(response => response.json())
        .then(data => {
            const columnasPred = idioma === 'en' ? (data.columnas_predeterminadas_en || {}) : (data.columnas_predeterminadas_es || {});
            const columnasOpc = idioma === 'en' ? (data.columnas_opcionales_en || {}) : (data.columnas_opcionales_es || {});
            
            let html = '<option value="">-- ' + (idioma === 'en' ? 'At the end of the table' : 'Al final de la tabla') + ' --</option>';
            
            // Agregar grupo de columnas predeterminadas
            html += '<optgroup label="📋 ' + (idioma === 'en' ? 'Default Columns' : 'Columnas Predeterminadas') + '">';
            Object.entries(columnasPred).forEach(([key, nombre]) => {
                html += `<option value="${key}">● ${nombre}</option>`;
            });
            html += '</optgroup>';
            
            // Agregar grupo de columnas opcionales
            html += '<optgroup label="🔧 ' + (idioma === 'en' ? 'Optional Columns' : 'Columnas Opcionales') + '">';
            Object.entries(columnasOpc).forEach(([key, nombre]) => {
                html += `<option value="${key}">◆ ${nombre}</option>`;
            });
            html += '</optgroup>';
            
            // Cargar campos personalizados existentes
            return fetch('/logistica/campos-personalizados')
                .then(res => res.json())
                .then(campos => {
                    if (campos && campos.length > 0) {
                        html += '<optgroup label="★ ' + (idioma === 'en' ? 'Custom Fields' : 'Campos Personalizados') + '">';
                        campos.forEach(campo => {
                            html += `<option value="campo_${campo.id}">★ ${campo.nombre}</option>`;
                        });
                        html += '</optgroup>';
                    }
                    
                    select.innerHTML = html;
                });
        })
        .catch(error => {
            console.error('Error cargando opciones de posición:', error);
            // Fallback con opciones básicas
            select.innerHTML = `
                <option value="">-- ${idioma === 'en' ? 'At the end of the table' : 'Al final de la tabla'} --</option>
                <option value="comentarios">${idioma === 'en' ? 'Comments' : 'Comentarios'}</option>
                <option value="post_operaciones">${idioma === 'en' ? 'Post-Operations' : 'Post-Operaciones'}</option>
            `;
        });
};

/**
 * Cargar lista de ejecutivos para el select de columnas
 */
window.cargarEjecutivosParaColumnas = function() {
    fetch('/logistica/columnas-config')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('selectEjecutivoColumnas');
            if (!select) return;
            
            // Guardar configuraciones
            columnasPredeterminadasConfig = {
                es: data.columnas_predeterminadas_es || {},
                en: data.columnas_predeterminadas_en || {}
            };
            columnasOpcionalesConfig = {
                es: data.columnas_opcionales_es || {},
                en: data.columnas_opcionales_en || {}
            };
            
            select.innerHTML = '<option value="">-- Seleccione un ejecutivo --</option>';
            (data.ejecutivos || []).forEach(ej => {
                select.innerHTML += `<option value="${ej.id}">${ej.nombre}</option>`;
            });
        })
        .catch(error => {
            console.error('Error cargando ejecutivos:', error);
        });
};

/**
 * Cambiar idioma de los nombres de columnas dinámicamente
 */
window.cambiarIdiomaColumnas = function() {
    const idiomaSeleccionado = document.querySelector('input[name="idiomaColumnas"]:checked')?.value || 'es';
    
    // Guardar el estado actual de los checkboxes
    const predeterminadasMarcadas = [];
    const opcionalesMarcadas = [];
    
    document.querySelectorAll('.columna-predeterminada:checked').forEach(cb => {
        predeterminadasMarcadas.push(cb.dataset.columna);
    });
    document.querySelectorAll('.columna-opcional:checked').forEach(cb => {
        opcionalesMarcadas.push(cb.dataset.columna);
    });
    
    // Regenerar HTML con el nuevo idioma
    const gridPredeterminadas = document.getElementById('columnasPredeterminadasGrid');
    if (gridPredeterminadas) {
        gridPredeterminadas.innerHTML = generarColumnasPredeterminadasHTML(idiomaSeleccionado);
        // Restaurar estado de checkboxes
        document.querySelectorAll('.columna-predeterminada').forEach(cb => {
            cb.checked = predeterminadasMarcadas.includes(cb.dataset.columna);
        });
    }
    
    const gridOpcionales = document.getElementById('columnasOpcionalesGrid');
    if (gridOpcionales) {
        gridOpcionales.innerHTML = generarColumnasOpcionalesHTML(idiomaSeleccionado);
        // Restaurar estado de checkboxes
        document.querySelectorAll('.columna-opcional').forEach(cb => {
            cb.checked = opcionalesMarcadas.includes(cb.dataset.columna);
        });
    }
    
    // Actualizar también la lista de columnas ordenables con el nuevo idioma
    actualizarIdiomaColumnasOrdenables(idiomaSeleccionado);
    
    // Actualizar el dropdown de "Mostrar después de" con el nuevo idioma
    cargarOpcionesPosicionCampo();
};

/**
 * Actualizar los nombres de las columnas ordenables según el idioma
 */
window.actualizarIdiomaColumnasOrdenables = function(idioma) {
    const container = document.getElementById('columnasOrdenList');
    if (!container) return;
    
    const items = container.querySelectorAll('.columna-ordenable');
    if (items.length === 0) return;
    
    const columnasPred = columnasPredeterminadasConfig[idioma] || columnasPredeterminadasConfig['es'] || {};
    const columnasOpc = columnasOpcionalesConfig[idioma] || columnasOpcionalesConfig['es'] || {};
    
    items.forEach(item => {
        const columna = item.dataset.columna;
        const nombreSpan = item.querySelector('.nombre-columna');
        if (!nombreSpan) return;
        
        // Buscar el nombre en predeterminadas
        if (columnasPred[columna]) {
            nombreSpan.textContent = columnasPred[columna];
        }
        // Buscar en opcionales
        else if (columnasOpc[columna]) {
            nombreSpan.textContent = columnasOpc[columna];
        }
        // Los campos personalizados mantienen su nombre original
    });
};

/**
 * Generar HTML para columnas predeterminadas
 */
function generarColumnasPredeterminadasHTML(idioma = 'es') {
    const columnas = columnasPredeterminadasConfig[idioma] || {};
    let html = '';
    
    Object.entries(columnas).forEach(([key, nombre]) => {
        html += `
            <div class="flex items-center px-3 py-2 bg-white rounded border border-slate-200 hover:border-green-400 transition-colors">
                <input type="checkbox" data-columna="${key}" class="columna-predeterminada mr-2 w-4 h-4 text-green-600 rounded focus:ring-green-500" checked>
                <span class="text-sm text-slate-600">${nombre}</span>
            </div>
        `;
    });
    
    return html;
}

/**
 * Generar HTML para columnas opcionales
 */
function generarColumnasOpcionalesHTML(idioma = 'es') {
    const columnas = columnasOpcionalesConfig[idioma] || {};
    let html = '';
    
    const descripcionesEs = {
        'tipo_carga': 'FCL / LCL',
        'tipo_incoterm': 'EXW, FOB, CIF, etc.',
        'puerto_salida': 'Puerto de origen',
        'in_charge': 'Persona responsable',
        'proveedor': 'Nombre del proveedor',
        'tipo_previo': 'SI/NO + Responsable',
        'fecha_etd': 'Fecha salida estimada',
        'fecha_zarpe': 'Fecha real de zarpe',
        'pedimento_en_carpeta': 'Control expediente',
        'referencia_cliente': 'Ref. secundaria',
        'mail_subject': 'Subject del email'
    };
    
    const descripcionesEn = {
        'tipo_carga': 'FCL / LCL',
        'tipo_incoterm': 'EXW, FOB, CIF, etc.',
        'puerto_salida': 'Port of origin',
        'in_charge': 'Person in charge',
        'proveedor': 'Supplier name',
        'tipo_previo': 'YES/NO + Responsible',
        'fecha_etd': 'Estimated departure',
        'fecha_zarpe': 'Actual departure date',
        'pedimento_en_carpeta': 'Folder control',
        'referencia_cliente': 'Secondary ref.',
        'mail_subject': 'Email subject'
    };
    
    const descripciones = idioma === 'en' ? descripcionesEn : descripcionesEs;
    
    Object.entries(columnas).forEach(([key, nombre]) => {
        html += `
            <label class="flex items-center px-4 py-3 bg-white rounded-lg border-2 border-purple-200 cursor-pointer hover:border-purple-400 transition-colors">
                <input type="checkbox" data-columna="${key}" class="columna-opcional mr-3 w-5 h-5 text-purple-600 rounded focus:ring-purple-500">
                <div>
                    <span class="font-medium text-slate-700">${nombre}</span>
                    <p class="text-xs text-slate-500">${descripciones[key] || ''}</p>
                </div>
            </label>
        `;
    });
    
    return html;
}

/**
 * Cargar configuración de columnas para el ejecutivo seleccionado
 */
window.cargarColumnasEjecutivo = function() {
    const select = document.getElementById('selectEjecutivoColumnas');
    const container = document.getElementById('configuracionColumnasContainer');
    const empleadoId = select.value;
    
    if (!empleadoId) {
        container.classList.add('hidden');
        return;
    }
    
    ejecutivoSeleccionadoColumnas = empleadoId;
    container.classList.remove('hidden');
    
    // Cargar configuración actual
    fetch(`/logistica/columnas-config/ejecutivo/${empleadoId}`)
        .then(response => response.json())
        .then(data => {
            const columnasVisibles = data.columnas_visibles || [];
            const columnasPredeterminadasOcultas = data.columnas_predeterminadas_ocultas || [];
            const idioma = data.idioma || 'es';
            
            // Establecer idioma
            if (idioma === 'en') {
                document.getElementById('idiomaEn').checked = true;
                document.getElementById('idiomaEs').checked = false;
            } else {
                document.getElementById('idiomaEs').checked = true;
                document.getElementById('idiomaEn').checked = false;
            }
            
            // Generar HTML de columnas predeterminadas
            const gridPredeterminadas = document.getElementById('columnasPredeterminadasGrid');
            if (gridPredeterminadas) {
                gridPredeterminadas.innerHTML = generarColumnasPredeterminadasHTML(idioma);
                
                // Desmarcar las que están ocultas
                columnasPredeterminadasOcultas.forEach(col => {
                    const checkbox = gridPredeterminadas.querySelector(`input[data-columna="${col}"]`);
                    if (checkbox) checkbox.checked = false;
                });
            }
            
            // Generar HTML de columnas opcionales
            const gridOpcionales = document.getElementById('columnasOpcionalesGrid');
            if (gridOpcionales) {
                gridOpcionales.innerHTML = generarColumnasOpcionalesHTML(idioma);
                
                // Marcar las que están visibles
                columnasVisibles.forEach(col => {
                    const checkbox = gridOpcionales.querySelector(`input[data-columna="${col}"]`);
                    if (checkbox) checkbox.checked = true;
                });
            }
            
            // Cargar lista de columnas ordenables
            cargarColumnasOrdenables(empleadoId);
        })
        .catch(error => {
            console.error('Error cargando configuración:', error);
        });
};

/**
 * Cargar la lista de columnas ordenables para drag & drop
 */
window.cargarColumnasOrdenables = function(empleadoId) {
    const container = document.getElementById('columnasOrdenList');
    if (container) {
        container.innerHTML = `
            <div class="text-center py-4 text-gray-500">
                <svg class="animate-spin h-6 w-6 mx-auto mb-2 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Cargando columnas...
            </div>
        `;
    }
    
    fetch(`/logistica/columnas-config/ordenadas/${empleadoId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Columnas ordenadas recibidas:', data);
            if (data.success && data.columnas && data.columnas.length > 0) {
                renderizarColumnasOrdenables(data.columnas, data.idioma);
            } else {
                // Si no hay configuración, mostrar orden por defecto
                console.log('Sin configuración guardada, usando valores por defecto');
                renderizarColumnasOrdenablesDefault();
            }
        })
        .catch(error => {
            console.error('Error cargando columnas ordenables:', error);
            renderizarColumnasOrdenablesDefault();
        });
};

/**
 * Renderizar la lista de columnas ordenables con drag & drop
 */
window.renderizarColumnasOrdenables = function(columnas, idioma = 'es') {
    const container = document.getElementById('columnasOrdenList');
    if (!container) {
        console.error('No se encontró el contenedor columnasOrdenList');
        return;
    }
    
    console.log('Renderizando', columnas.length, 'columnas ordenables');
    
    let html = '';
    columnas.forEach((col, index) => {
        let visibleClass = col.visible ? 'bg-white border-blue-300' : 'bg-gray-100 border-gray-300 opacity-60';
        let tipoIcon = '';
        let tipoBadge = '';
        
        if (col.personalizado) {
            tipoIcon = '<span class="text-indigo-500 text-xs mr-2">★</span>';
            tipoBadge = '<span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded ml-2">Personalizado</span>';
            visibleClass = col.visible ? 'bg-indigo-50 border-indigo-300' : 'bg-gray-100 border-gray-300 opacity-60';
        } else if (col.predeterminada) {
            tipoIcon = '<span class="text-green-500 text-xs mr-2">●</span>';
            tipoBadge = '';
        } else if (col.opcional) {
            tipoIcon = '<span class="text-purple-500 text-xs mr-2">◆</span>';
            tipoBadge = '<span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded ml-2">Opcional</span>';
        }
        
        html += `
            <div class="columna-ordenable flex items-center p-3 rounded-lg border-2 ${visibleClass} transition-all hover:shadow-md"
                 data-columna="${col.columna}"
                 data-orden="${index}"
                 data-visible="${col.visible ? '1' : '0'}"
                 data-tipo="${col.personalizado ? 'personalizado' : (col.predeterminada ? 'predeterminada' : 'opcional')}">
                
                <!-- Botones para mover -->
                <div class="flex flex-col mr-2">
                    <button type="button" onclick="moverColumnaArriba(this)" class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-100 rounded transition-colors" title="Mover arriba">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                        </svg>
                    </button>
                    <button type="button" onclick="moverColumnaAbajo(this)" class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-100 rounded transition-colors" title="Mover abajo">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
                
                <span class="orden-numero bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold mr-3">${index + 1}</span>
                ${tipoIcon}
                <span class="nombre-columna flex-1 font-medium text-slate-700" data-columna="${col.columna}">${col.nombre}</span>
                ${tipoBadge}
                <label class="flex items-center ml-3 cursor-pointer">
                    <input type="checkbox" class="columna-visible-check w-4 h-4 text-blue-600 rounded" 
                           data-columna="${col.columna}" 
                           ${col.visible ? 'checked' : ''} 
                           onchange="toggleVisibilidadColumna(this)">
                    <span class="ml-1 text-xs text-gray-500">Visible</span>
                </label>
            </div>
        `;
    });
    
    container.innerHTML = html;
    console.log('Columnas renderizadas con botones de ordenamiento');
};

/**
 * Mover columna hacia arriba
 */
window.moverColumnaArriba = function(btn) {
    const item = btn.closest('.columna-ordenable');
    const container = document.getElementById('columnasOrdenList');
    const prevItem = item.previousElementSibling;
    
    if (prevItem && prevItem.classList.contains('columna-ordenable')) {
        container.insertBefore(item, prevItem);
        actualizarNumerosOrden();
    }
};

/**
 * Mover columna hacia abajo
 */
window.moverColumnaAbajo = function(btn) {
    const item = btn.closest('.columna-ordenable');
    const container = document.getElementById('columnasOrdenList');
    const nextItem = item.nextElementSibling;
    
    if (nextItem && nextItem.classList.contains('columna-ordenable')) {
        container.insertBefore(nextItem, item);
        actualizarNumerosOrden();
    }
};

/**
 * Renderizar columnas ordenables con valores por defecto
 */
window.renderizarColumnasOrdenablesDefault = function() {
    const idioma = document.querySelector('input[name="idiomaColumnas"]:checked')?.value || 'es';
    
    // Obtener columnas predeterminadas y opcionales desde el config cargado
    const columnasPred = columnasPredeterminadasConfig[idioma] || columnasPredeterminadasConfig['es'] || {};
    const columnasOpc = columnasOpcionalesConfig[idioma] || columnasOpcionalesConfig['es'] || {};
    
    const columnas = [];
    let orden = 0;
    
    // Primero predeterminadas
    Object.entries(columnasPred).forEach(([key, nombre]) => {
        columnas.push({
            columna: key,
            nombre: nombre,
            visible: true,
            orden: orden++,
            predeterminada: true
        });
    });
    
    // Luego opcionales
    Object.entries(columnasOpc).forEach(([key, nombre]) => {
        columnas.push({
            columna: key,
            nombre: nombre,
            visible: false,
            orden: orden++,
            predeterminada: false
        });
    });
    
    renderizarColumnasOrdenables(columnas, idioma);
};

// Variable global para el elemento arrastrado
let elementoArrastrado = null;

/**
 * Inicializar el sistema de drag and drop
 */
window.inicializarDragAndDrop = function() {
    const container = document.getElementById('columnasOrdenList');
    if (!container) {
        console.error('No se encontró el contenedor para drag & drop');
        return;
    }
    
    // Remover listeners anteriores clonando y reemplazando el contenedor
    const items = container.querySelectorAll('.columna-ordenable');
    
    console.log('Inicializando drag & drop para', items.length, 'items');
    
    items.forEach(item => {
        // Hacer el elemento arrastrable
        item.setAttribute('draggable', 'true');
        
        // Drag Start
        item.ondragstart = function(e) {
            elementoArrastrado = this;
            this.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
            console.log('Arrastrando:', this.dataset.columna);
        };
        
        // Drag End
        item.ondragend = function(e) {
            this.style.opacity = '1';
            elementoArrastrado = null;
            
            // Limpiar indicadores visuales
            items.forEach(el => {
                el.style.borderTop = '';
                el.style.borderBottom = '';
                el.classList.remove('drag-over-top', 'drag-over-bottom');
            });
            
            actualizarNumerosOrden();
            console.log('Soltar completado');
        };
        
        // Drag Over
        item.ondragover = function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            if (!elementoArrastrado || elementoArrastrado === this) return;
            
            const rect = this.getBoundingClientRect();
            const midY = rect.top + rect.height / 2;
            
            // Limpiar estilos de este elemento
            this.style.borderTop = '';
            this.style.borderBottom = '';
            
            if (e.clientY < midY) {
                this.style.borderTop = '3px solid #3b82f6';
            } else {
                this.style.borderBottom = '3px solid #3b82f6';
            }
        };
        
        // Drag Leave
        item.ondragleave = function(e) {
            this.style.borderTop = '';
            this.style.borderBottom = '';
        };
        
        // Drop
        item.ondrop = function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            this.style.borderTop = '';
            this.style.borderBottom = '';
            
            if (elementoArrastrado && elementoArrastrado !== this) {
                const rect = this.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;
                
                if (e.clientY < midY) {
                    container.insertBefore(elementoArrastrado, this);
                } else {
                    container.insertBefore(elementoArrastrado, this.nextSibling);
                }
                
                console.log('Movido:', elementoArrastrado.dataset.columna, 'cerca de:', this.dataset.columna);
            }
        };
    });
    
    // Permitir soltar al final del contenedor
    container.ondragover = function(e) {
        e.preventDefault();
    };
    
    container.ondrop = function(e) {
        e.preventDefault();
    };
    
    console.log('Drag & drop listo');
};

/**
 * Actualizar los números de orden después de reordenar
 */
window.actualizarNumerosOrden = function() {
    const items = document.querySelectorAll('.columna-ordenable');
    items.forEach((item, index) => {
        const numeroSpan = item.querySelector('.orden-numero');
        if (numeroSpan) {
            numeroSpan.textContent = index + 1;
        }
        item.dataset.orden = index;
    });
};

/**
 * Toggle visibilidad de una columna en la lista ordenable
 */
window.toggleVisibilidadColumna = function(checkbox) {
    const item = checkbox.closest('.columna-ordenable');
    if (!item) return;
    
    const visible = checkbox.checked;
    item.dataset.visible = visible ? '1' : '0';
    
    // Actualizar estilos visuales
    if (visible) {
        item.classList.remove('bg-gray-100', 'border-gray-300', 'opacity-60');
        const tipo = item.dataset.tipo;
        if (tipo === 'personalizado') {
            item.classList.add('bg-indigo-50', 'border-indigo-300');
        } else {
            item.classList.add('bg-white', 'border-blue-300');
        }
    } else {
        item.classList.remove('bg-white', 'border-blue-300', 'bg-indigo-50', 'border-indigo-300');
        item.classList.add('bg-gray-100', 'border-gray-300', 'opacity-60');
    }
};

/**
 * Resetear configuración de columnas a predeterminados
 */
window.resetearConfiguracionColumnas = function() {
    if (!ejecutivoSeleccionadoColumnas) {
        mostrarAlerta('Por favor seleccione un ejecutivo', 'warning');
        return;
    }
    
    if (!confirm('¿Está seguro de resetear la configuración? Se mostrarán todas las columnas predeterminadas y se ocultarán las adicionales.')) {
        return;
    }
    
    // Marcar todas las predeterminadas
    document.querySelectorAll('.columna-predeterminada').forEach(checkbox => {
        checkbox.checked = true;
    });
    
    // Desmarcar todas las opcionales
    document.querySelectorAll('.columna-opcional').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Resetear idioma a español
    const idiomaEs = document.getElementById('idiomaEs');
    const idiomaEn = document.getElementById('idiomaEn');
    if (idiomaEs) idiomaEs.checked = true;
    if (idiomaEn) idiomaEn.checked = false;
    
    // Guardar automáticamente
    guardarConfiguracionColumnas();
};

/**
 * Guardar configuración de columnas para el ejecutivo
 */
window.guardarConfiguracionColumnas = function() {
    if (!ejecutivoSeleccionadoColumnas) {
        mostrarAlerta('Por favor seleccione un ejecutivo', 'warning');
        return;
    }
    
    // Obtener idioma seleccionado
    const idioma = document.querySelector('input[name="idiomaColumnas"]:checked')?.value || 'es';
    
    // Obtener columnas predeterminadas VISIBLES (las que SÍ están marcadas)
    const columnasPredeterminadasVisibles = [];
    document.querySelectorAll('#columnasPredeterminadasGrid .columna-predeterminada').forEach(checkbox => {
        if (checkbox.checked) {
            columnasPredeterminadasVisibles.push(checkbox.dataset.columna);
        }
    });
    
    // Obtener columnas opcionales VISIBLES (las que SÍ están marcadas)
    const columnasOpcionalesVisibles = [];
    document.querySelectorAll('#columnasOpcionalesGrid .columna-opcional').forEach(checkbox => {
        if (checkbox.checked) {
            columnasOpcionalesVisibles.push(checkbox.dataset.columna);
        }
    });
    
    console.log('Guardando configuración:', {
        empleado_id: ejecutivoSeleccionadoColumnas,
        columnas_predeterminadas: columnasPredeterminadasVisibles,
        columnas_opcionales: columnasOpcionalesVisibles,
        idioma: idioma
    });
    
    // Usar la ruta existente POST /logistica/columnas-config
    fetch('/logistica/columnas-config', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            empleado_id: ejecutivoSeleccionadoColumnas,
            columnas_predeterminadas: columnasPredeterminadasVisibles,
            columnas_opcionales: columnasOpcionalesVisibles,
            idioma: idioma
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Configuración de columnas guardada exitosamente. Recarga la página para ver los cambios.', 'success');
        } else {
            mostrarAlerta('Error al guardar la configuración: ' + (data.message || data.mensaje || ''), 'error');
        }
    })
    .catch(error => {
        console.error('Error guardando configuración:', error);
        mostrarAlerta('Error al guardar la configuración', 'error');
    });
};

/**
 * Previsualizar la configuración de columnas para el ejecutivo seleccionado
 * Abre una nueva pestaña mostrando la vista como la vería ese ejecutivo
 */
window.previsualizarConfiguracion = function() {
    if (!ejecutivoSeleccionadoColumnas) {
        mostrarAlerta('Por favor seleccione un ejecutivo para previsualizar su configuración', 'warning');
        return;
    }
    
    // Obtener el nombre del ejecutivo seleccionado
    const selectEjecutivo = document.getElementById('selectEjecutivoColumnas');
    const nombreEjecutivo = selectEjecutivo.options[selectEjecutivo.selectedIndex]?.text || 'Ejecutivo';
    
    // Abrir nueva pestaña con parámetro de previsualización
    const url = `/logistica/matriz-seguimiento?preview_as=${ejecutivoSeleccionadoColumnas}`;
    window.open(url, '_blank');
};

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
    
    // Cargar campos personalizados para el modal
    cargarCamposParaModal();
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
                    
                    // Columnas opcionales (si existen en la sección 6 o en sección 7)
                    setFieldValue('[name="tipo_carga"]', op.tipo_carga);
                    setFieldValue('[name="tipo_incoterm"]', op.tipo_incoterm);
                    setFieldValue('[name="puerto_salida"]', op.puerto_salida);
                    
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
                
                // Cargar campos personalizados y columnas opcionales, luego cargar valores
                cargarCamposParaModal().then(() => {
                    // Cargar valores de campos personalizados
                    cargarValoresCamposOperacion(op.id);
                    // Cargar valores de columnas opcionales en la sección 7
                    cargarValoresColumnasOpcionales(op);
                });
                
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
                // Guardar campos personalizados
                const opId = data.operacion_id || operacionId;
                if (opId) {
                    guardarValoresCamposPersonalizados(opId).then(() => {
                        submitBtn.innerHTML = '<svg class="h-5 w-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> ¡' + (isEditing ? 'Actualizado!' : 'Guardado!');
                        setTimeout(() => {
                            cerrarModal();
                            window.location.reload();
                        }, 800);
                    });
                } else {
                    submitBtn.innerHTML = '<svg class="h-5 w-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> ¡' + (isEditing ? 'Actualizado!' : 'Guardado!');
                    setTimeout(() => {
                        cerrarModal();
                        window.location.reload();
                    }, 800);
                }
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
                <p class="text-lg font-medium">No hay post-operaciones asignadas</p>
                <p class="text-sm">Esta operación no tiene post-operaciones asignadas.</p>
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
    if (!contenedor) return;
    
    if (!postOperaciones || postOperaciones.length === 0) {
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

// ========================================
// CAMPOS PERSONALIZADOS DE MATRIZ
// ========================================

let camposPersonalizadosData = [];
let ejecutivosData = [];

/**
 * Abre el modal de configuración de columnas
 */
window.abrirModalCamposPersonalizados = function() {
    const modal = document.getElementById('modalCamposPersonalizados');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        cargarEjecutivosParaColumnas();
    }
};

/**
 * Cierra el modal de configuración de campos personalizados
 */
window.cerrarModalCamposPersonalizados = function() {
    const modal = document.getElementById('modalCamposPersonalizados');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
};

/**
 * Carga la lista de campos personalizados existentes
 */
async function cargarCamposPersonalizados() {
    try {
        const response = await fetch('/logistica/campos-personalizados');
        camposPersonalizadosData = await response.json();
        renderizarCamposPersonalizados();
    } catch (error) {
        console.error('Error al cargar campos:', error);
        mostrarAlerta('Error al cargar los campos personalizados', 'error');
    }
}

/**
 * Carga la lista de ejecutivos para asignación
 */
async function cargarEjecutivosParaCampos() {
    try {
        const response = await fetch('/logistica/campos-personalizados/ejecutivos');
        ejecutivosData = await response.json();
        renderizarEjecutivosNuevoCampo();
    } catch (error) {
        console.error('Error al cargar ejecutivos:', error);
    }
}

/**
 * Renderiza la lista de ejecutivos en el formulario de nuevo campo
 */
function renderizarEjecutivosNuevoCampo() {
    const select = document.getElementById('selectEjecutivosNuevoCampo');
    if (!select) return;

    if (ejecutivosData.length === 0) {
        select.innerHTML = '<option value="" disabled>No hay ejecutivos disponibles</option>';
        return;
    }

    select.innerHTML = ejecutivosData.map(ej => 
        `<option value="${ej.id}">${ej.nombre}</option>`
    ).join('');
}

// ═══════════════════════════════════════════════════════════════
// Variables globales para campos personalizados
// ═══════════════════════════════════════════════════════════════
let opcionesActuales = [];

/**
 * Iconos para tipos de campo
 */
const tipoIconos = {
    'texto': '📝',
    'descripcion': '📄',
    'numero': '🔢',
    'decimal': '💲',
    'moneda': '💰',
    'fecha': '📅',
    'booleano': '✅',
    'selector': '📋',
    'multiple': '☑️',
    'email': '📧',
    'telefono': '📞',
    'url': '🔗'
};

/**
 * Mostrar/ocultar opciones según el tipo de campo seleccionado
 */
window.mostrarOpcionesTipo = function() {
    const tipo = document.getElementById('tipoNuevoCampo')?.value || 'texto';
    
    // Ocultar todos los contenedores de configuración
    document.getElementById('opcionesSelectorContainer')?.classList.add('hidden');
    document.getElementById('configDecimalContainer')?.classList.add('hidden');
    document.getElementById('configMonedaContainer')?.classList.add('hidden');
    document.getElementById('configNumeroContainer')?.classList.add('hidden');
    
    // Mostrar según el tipo
    switch(tipo) {
        case 'selector':
            document.getElementById('opcionesSelectorContainer')?.classList.remove('hidden');
            // Pre-seleccionar "Solo uno" para selector
            document.getElementById('seleccionUnica').checked = true;
            break;
        case 'multiple':
            document.getElementById('opcionesSelectorContainer')?.classList.remove('hidden');
            // Pre-seleccionar "Varios" para multiple
            document.getElementById('seleccionMultiple').checked = true;
            break;
        case 'decimal':
            document.getElementById('configDecimalContainer')?.classList.remove('hidden');
            break;
        case 'moneda':
            document.getElementById('configDecimalContainer')?.classList.remove('hidden');
            document.getElementById('configMonedaContainer')?.classList.remove('hidden');
            break;
        case 'numero':
            document.getElementById('configNumeroContainer')?.classList.remove('hidden');
            break;
    }
};

/**
 * Agregar una opción al selector/múltiple
 */
window.agregarOpcion = function() {
    const input = document.getElementById('nuevaOpcionInput');
    const opcion = input?.value?.trim();
    
    if (!opcion) {
        mostrarAlerta('Escribe una opción primero', 'warning');
        return;
    }
    
    if (opcionesActuales.includes(opcion)) {
        mostrarAlerta('Esta opción ya existe', 'warning');
        return;
    }
    
    opcionesActuales.push(opcion);
    input.value = '';
    renderizarOpciones();
};

/**
 * Eliminar una opción
 */
window.eliminarOpcion = function(index) {
    opcionesActuales.splice(index, 1);
    renderizarOpciones();
};

/**
 * Renderizar las opciones actuales
 */
function renderizarOpciones() {
    const container = document.getElementById('listaOpciones');
    if (!container) return;
    
    if (opcionesActuales.length === 0) {
        container.innerHTML = '<span class="text-sm text-gray-400 italic">Las opciones aparecerán aquí...</span>';
        return;
    }
    
    container.innerHTML = opcionesActuales.map((opcion, index) => `
        <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
            ${opcion}
            <button type="button" onclick="eliminarOpcion(${index})" class="ml-2 text-green-600 hover:text-red-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </span>
    `).join('');
}

/**
 * Crear un nuevo campo personalizado
 */
window.crearCampoPersonalizado = async function() {
    const nombre = document.getElementById('nombreNuevoCampo')?.value?.trim();
    let tipo = document.getElementById('tipoNuevoCampo')?.value || 'texto';
    const mostrar_despues_de = document.getElementById('posicionNuevoCampo')?.value || null;
    const requerido = document.getElementById('campoRequerido')?.checked || false;
    const selectEjecutivos = document.getElementById('selectEjecutivosNuevoCampo');
    const ejecutivos = selectEjecutivos ? Array.from(selectEjecutivos.selectedOptions).map(opt => parseInt(opt.value)) : [];

    if (!nombre) {
        mostrarAlerta('Por favor ingresa un nombre para el campo', 'warning');
        return;
    }

    // Si es selector o multiple, verificar la opción de selección
    if (tipo === 'selector' || tipo === 'multiple') {
        const tipoSeleccion = document.querySelector('input[name="tipoSeleccion"]:checked')?.value;
        // Ajustar el tipo según la selección del usuario
        tipo = tipoSeleccion === 'multiple' ? 'multiple' : 'selector';
    }

    // Validar opciones para selector/multiple
    if ((tipo === 'selector' || tipo === 'multiple') && opcionesActuales.length === 0) {
        mostrarAlerta('Debes agregar al menos una opción para este tipo de campo', 'warning');
        return;
    }

    // Construir objeto de datos
    const datos = { 
        nombre, 
        tipo, 
        mostrar_despues_de, 
        ejecutivos,
        requerido
    };

    // Agregar opciones si aplica
    if (tipo === 'selector' || tipo === 'multiple') {
        datos.opciones = opcionesActuales;
    }

    // Agregar configuración según el tipo
    const configuracion = {};
    
    if (tipo === 'decimal' || tipo === 'moneda') {
        configuracion.decimales = parseInt(document.getElementById('decimalesInput')?.value) || 2;
    }
    
    if (tipo === 'moneda') {
        configuracion.moneda = document.getElementById('monedaSelect')?.value || 'MXN';
    }
    
    if (tipo === 'numero') {
        const min = document.getElementById('minNumeroInput')?.value;
        const max = document.getElementById('maxNumeroInput')?.value;
        if (min !== '') configuracion.min = parseInt(min);
        if (max !== '') configuracion.max = parseInt(max);
    }
    
    if (Object.keys(configuracion).length > 0) {
        datos.configuracion = configuracion;
    }

    try {
        const response = await fetch('/logistica/campos-personalizados', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(datos)
        });

        const data = await response.json();
        if (data.success) {
            mostrarAlerta('Campo creado exitosamente', 'success');
            // Limpiar formulario
            document.getElementById('nombreNuevoCampo').value = '';
            document.getElementById('tipoNuevoCampo').value = 'texto';
            document.getElementById('posicionNuevoCampo').value = '';
            document.getElementById('campoRequerido').checked = false;
            document.getElementById('decimalesInput').value = '2';
            document.getElementById('minNumeroInput').value = '';
            document.getElementById('maxNumeroInput').value = '';
            opcionesActuales = [];
            renderizarOpciones();
            mostrarOpcionesTipo();
            if (selectEjecutivos) {
                Array.from(selectEjecutivos.options).forEach(opt => opt.selected = false);
            }
            // Recargar lista
            cargarCamposPersonalizados();
        } else {
            mostrarAlerta(data.mensaje || 'Error al crear el campo', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al crear el campo', 'error');
    }
};

/**
 * Eliminar un campo personalizado
 */
window.eliminarCampoPersonalizado = async function(campoId, nombreCampo) {
    if (!confirm(`¿Está seguro de eliminar el campo "${nombreCampo}"? Esta acción no se puede deshacer.`)) {
        return;
    }

    try {
        const response = await fetch(`/logistica/campos-personalizados/${campoId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();
        if (data.success) {
            mostrarAlerta('Campo eliminado exitosamente', 'success');
            cargarCamposPersonalizados();
        } else {
            mostrarAlerta(data.mensaje || 'Error al eliminar el campo', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al eliminar el campo', 'error');
    }
};

/**
 * Renderiza la lista de campos personalizados existentes
 */
function renderizarCamposPersonalizados() {
    const container = document.getElementById('listaCamposPersonalizados');
    if (!container) return;

    if (camposPersonalizadosData.length === 0) {
        container.innerHTML = '<p class="text-slate-400 text-sm text-center py-4">No hay campos personalizados creados</p>';
        return;
    }

    // Mapa de columnas para mostrar nombre legible
    const columnasNombres = {
        'ejecutivo': 'Ejecutivo',
        'operacion': 'Operación',
        'cliente': 'Cliente',
        'proveedor': 'Proveedor o Cliente',
        'fecha_embarque': 'Fecha de Embarque',
        'no_factura': 'No. De Factura',
        'tipo_operacion': 'T. Operación',
        'clave': 'Clave',
        'referencia_interna': 'Referencia Interna',
        'aduana': 'Aduana',
        'agente_aduanal': 'A.A',
        'referencia_aa': 'Referencia A.A',
        'no_pedimento': 'No Ped',
        'transporte': 'Transporte',
        'fecha_arribo_aduana': 'Fecha de Arribo a Aduana',
        'guia_bl': 'Guía //BL',
        'status': 'Status',
        'fecha_modulacion': 'Fecha de Modulación',
        'fecha_arribo_planta': 'Fecha de Arribo a Planta',
        'resultado': 'Resultado',
        'target': 'Target',
        'dias_transito': 'Días en Tránsito',
        'post_operaciones': 'Post-Operaciones',
        'comentarios': 'Comentarios'
    };

    container.innerHTML = camposPersonalizadosData.map(campo => {
        const ejecutivosNombres = campo.ejecutivos?.map(e => e.nombre).join(', ') || 'Sin asignar';
        const tipoIcono = tipoIconos[campo.tipo] || '📝';
        const tipoNombres = {
            'texto': 'Texto corto',
            'descripcion': 'Descripción',
            'numero': 'Número',
            'decimal': 'Decimal',
            'moneda': 'Moneda',
            'fecha': 'Fecha',
            'booleano': 'Sí/No',
            'selector': 'Selector',
            'multiple': 'Múltiple',
            'email': 'Email',
            'telefono': 'Teléfono',
            'url': 'URL'
        };
        const tipoLabel = `${tipoIcono} ${tipoNombres[campo.tipo] || campo.tipo}`;
        const estadoClass = campo.activo ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500';
        const estadoLabel = campo.activo ? 'Activo' : 'Inactivo';
        const posicionLabel = campo.mostrar_despues_de ? `Después de: ${columnasNombres[campo.mostrar_despues_de] || campo.mostrar_despues_de}` : 'Al final';
        
        // Info adicional según tipo
        let infoAdicional = '';
        if ((campo.tipo === 'selector' || campo.tipo === 'multiple') && campo.opciones) {
            const opciones = Array.isArray(campo.opciones) ? campo.opciones : JSON.parse(campo.opciones || '[]');
            infoAdicional = `<br><span class="text-xs text-gray-500">Opciones: ${opciones.join(', ')}</span>`;
        }
        if (campo.tipo === 'moneda' && campo.configuracion) {
            const config = typeof campo.configuracion === 'string' ? JSON.parse(campo.configuracion) : campo.configuracion;
            infoAdicional = `<br><span class="text-xs text-gray-500">Moneda: ${config.moneda || 'MXN'}</span>`;
        }
        const requeridoBadge = campo.requerido ? '<span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">Requerido</span>' : '';

        return `
            <div class="bg-white border border-slate-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            <h4 class="font-medium text-slate-800">${campo.nombre}</h4>
                            <span class="text-xs px-2 py-0.5 rounded-full ${estadoClass}">${estadoLabel}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">${tipoLabel}</span>
                            ${requeridoBadge}
                            <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">${posicionLabel}</span>
                        </div>
                        <p class="text-sm text-slate-500">
                            <strong>Ejecutivos:</strong> ${ejecutivosNombres}${infoAdicional}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="editarCampoPersonalizado(${campo.id})" 
                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button onclick="eliminarCampoPersonalizado(${campo.id}, '${campo.nombre}')" 
                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Maneja el envío del formulario de nuevo campo
 */
document.addEventListener('DOMContentLoaded', function() {
    const formNuevoCampo = document.getElementById('formNuevoCampo');
    if (formNuevoCampo) {
        formNuevoCampo.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const nombre = document.getElementById('campoNombre').value.trim();
            const tipo = document.getElementById('campoTipo').value;
            const mostrar_despues_de = document.getElementById('campoMostrarDespuesDe').value;
            const selectEjecutivos = document.getElementById('selectEjecutivosNuevoCampo');
            const ejecutivos = Array.from(selectEjecutivos.selectedOptions).map(opt => parseInt(opt.value));

            if (!nombre) {
                mostrarAlerta('Por favor ingresa un nombre para el campo', 'warning');
                return;
            }

            try {
                const response = await fetch('/logistica/campos-personalizados', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ nombre, tipo, mostrar_despues_de, ejecutivos })
                });

                const data = await response.json();
                if (data.success) {
                    mostrarAlerta('Campo creado exitosamente', 'success');
                    formNuevoCampo.reset();
                    cargarCamposPersonalizados();
                } else {
                    mostrarAlerta(data.mensaje || 'Error al crear el campo', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarAlerta('Error de conexión al crear el campo', 'error');
            }
        });
    }
});

/**
 * Abre el modal para editar un campo personalizado
 */
window.editarCampoPersonalizado = function(campoId) {
    const campo = camposPersonalizadosData.find(c => c.id === campoId);
    if (!campo) return;

    document.getElementById('editarCampoId').value = campo.id;
    document.getElementById('editarCampoNombre').value = campo.nombre;
    document.getElementById('editarCampoTipo').value = campo.tipo;
    document.getElementById('editarCampoActivo').value = campo.activo ? '1' : '0';
    document.getElementById('editarCampoMostrarDespuesDe').value = campo.mostrar_despues_de || '';

    // Renderizar ejecutivos en el select con los seleccionados marcados
    const select = document.getElementById('selectEjecutivosEditarCampo');
    const ejecutivosSeleccionados = campo.ejecutivos?.map(e => e.id) || [];
    
    select.innerHTML = ejecutivosData.map(ej => 
        `<option value="${ej.id}" ${ejecutivosSeleccionados.includes(ej.id) ? 'selected' : ''}>${ej.nombre}</option>`
    ).join('');

    const modal = document.getElementById('modalEditarCampo');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
};

/**
 * Cierra el modal de edición de campo
 */
window.cerrarModalEditarCampo = function() {
    const modal = document.getElementById('modalEditarCampo');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
};

/**
 * Maneja el envío del formulario de edición
 */
document.addEventListener('DOMContentLoaded', function() {
    const formEditarCampo = document.getElementById('formEditarCampo');
    if (formEditarCampo) {
        formEditarCampo.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const id = document.getElementById('editarCampoId').value;
            const nombre = document.getElementById('editarCampoNombre').value.trim();
            const tipo = document.getElementById('editarCampoTipo').value;
            const activo = document.getElementById('editarCampoActivo').value === '1';
            const mostrar_despues_de = document.getElementById('editarCampoMostrarDespuesDe').value;
            const selectEjecutivos = document.getElementById('selectEjecutivosEditarCampo');
            const ejecutivos = Array.from(selectEjecutivos.selectedOptions).map(opt => parseInt(opt.value));

            try {
                const response = await fetch(`/logistica/campos-personalizados/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ nombre, tipo, activo, mostrar_despues_de, ejecutivos })
                });

                const data = await response.json();
                if (data.success) {
                    mostrarAlerta('Campo actualizado exitosamente', 'success');
                    cerrarModalEditarCampo();
                    cargarCamposPersonalizados();
                } else {
                    mostrarAlerta(data.mensaje || 'Error al actualizar el campo', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarAlerta('Error de conexión al actualizar el campo', 'error');
            }
        });
    }
});

/**
 * Elimina un campo personalizado
 */
window.eliminarCampoPersonalizado = function(campoId, nombre) {
    mostrarConfirmacion(
        `¿Estás seguro de eliminar el campo "${nombre}"? Esta acción eliminará también todos los valores guardados.`,
        async function() {
            try {
                const response = await fetch(`/logistica/campos-personalizados/${campoId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                if (data.success) {
                    mostrarAlerta('Campo eliminado exitosamente', 'success');
                    cargarCamposPersonalizados();
                } else {
                    mostrarAlerta(data.mensaje || 'Error al eliminar el campo', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarAlerta('Error de conexión al eliminar el campo', 'error');
            }
        },
        'Eliminar Campo',
        'Eliminar'
    );
};

// ============================================
// FUNCIONES PARA CAMPOS PERSONALIZADOS EN OPERACIONES
// ============================================

// Variable para almacenar campos del ejecutivo actual
let camposDelEjecutivo = [];
let columnasOpcionalesDelEjecutivo = [];

/**
 * Cargar campos adicionales para el modal de operación
 * Incluye campos personalizados y columnas opcionales del ejecutivo
 */
async function cargarCamposParaModal(ejecutivoNombre = null) {
    const container = document.getElementById('camposPersonalizadosContainer');
    const section = document.getElementById('camposPersonalizadosSection');
    const nombreEjecutivoSpan = document.getElementById('nombreEjecutivoCampos');
    const columnasContainer = document.getElementById('columnasOpcionalesContainer');
    const columnasSubsection = document.getElementById('columnasOpcionalesSubsection');
    const camposSubsection = document.getElementById('camposPersonalizadosSubsection');
    const sinCamposDiv = document.getElementById('sinCamposAdicionales');
    
    if (!container || !section) return;
    
    try {
        // Obtener campos adicionales del ejecutivo actual
        const response = await fetch('/logistica/campos-adicionales');
        const data = await response.json();
        
        // Actualizar nombre del ejecutivo
        if (nombreEjecutivoSpan && data.ejecutivo_nombre) {
            nombreEjecutivoSpan.textContent = data.ejecutivo_nombre;
        }
        
        // Si no tiene campos adicionales, ocultar la sección
        if (!data.tiene_campos_adicionales) {
            section.classList.add('hidden');
            camposDelEjecutivo = [];
            columnasOpcionalesDelEjecutivo = [];
            return;
        }
        
        // Mostrar la sección principal
        section.classList.remove('hidden');
        
        // Procesar columnas opcionales
        columnasOpcionalesDelEjecutivo = data.columnas_opcionales || [];
        if (columnasOpcionalesDelEjecutivo.length > 0 && columnasContainer && columnasSubsection) {
            columnasSubsection.classList.remove('hidden');
            columnasContainer.innerHTML = columnasOpcionalesDelEjecutivo.map(col => {
                return `
                    <div class="columna-opcional-input">
                        <label class="block text-sm font-medium text-slate-600 mb-2">
                            <span class="text-indigo-500 mr-1">📊</span>${col.nombre}
                        </label>
                        ${generarInputColumnaOpcional(col)}
                    </div>
                `;
            }).join('');
        } else if (columnasSubsection) {
            columnasSubsection.classList.add('hidden');
        }
        
        // Procesar campos personalizados
        const camposActivos = (data.campos_personalizados || []).filter(c => c.activo);
        camposDelEjecutivo = camposActivos;
        
        if (camposActivos.length > 0 && camposSubsection) {
            camposSubsection.classList.remove('hidden');
            container.innerHTML = camposActivos.map(campo => {
                return `
                    <div class="campo-personalizado-input">
                        <label class="block text-sm font-medium text-slate-600 mb-2">
                            <span class="text-indigo-600 mr-1">★</span>${campo.nombre}
                            ${campo.requerido ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        ${generarInputCampoPersonalizado(campo)}
                    </div>
                `;
            }).join('');
        } else if (camposSubsection) {
            camposSubsection.classList.add('hidden');
        }
        
        // Si no hay ni campos ni columnas opcionales
        if (camposActivos.length === 0 && columnasOpcionalesDelEjecutivo.length === 0 && sinCamposDiv) {
            sinCamposDiv.classList.remove('hidden');
        } else if (sinCamposDiv) {
            sinCamposDiv.classList.add('hidden');
        }
        
    } catch (error) {
        console.error('Error al cargar campos adicionales:', error);
        section.classList.add('hidden');
    }
}

// Variable global para almacenar los incoterms cargados
let incotermsDisponibles = [];

/**
 * Cargar catálogo de incoterms desde el servidor
 */
async function cargarIncoterms() {
    try {
        const response = await fetch('/logistica/incoterms');
        if (response.ok) {
            const data = await response.json();
            if (data.success && data.incoterms) {
                incotermsDisponibles = data.incoterms;
            }
        }
    } catch (error) {
        console.error('Error al cargar incoterms:', error);
        // Fallback con valores por defecto si falla la carga
        incotermsDisponibles = [
            { codigo: 'EXW', nombre: 'EXW - En Fábrica', grupo: 'E' },
            { codigo: 'FCA', nombre: 'FCA - Franco Transportista', grupo: 'F' },
            { codigo: 'FAS', nombre: 'FAS - Franco al Costado del Buque', grupo: 'F' },
            { codigo: 'FOB', nombre: 'FOB - Franco a Bordo', grupo: 'F' },
            { codigo: 'CFR', nombre: 'CFR - Coste y Flete', grupo: 'C' },
            { codigo: 'CIF', nombre: 'CIF - Coste, Seguro y Flete', grupo: 'C' },
            { codigo: 'CPT', nombre: 'CPT - Transporte Pagado Hasta', grupo: 'C' },
            { codigo: 'CIP', nombre: 'CIP - Transporte y Seguro Pagados Hasta', grupo: 'C' },
            { codigo: 'DAP', nombre: 'DAP - Entregada en Lugar', grupo: 'D' },
            { codigo: 'DPU', nombre: 'DPU - Entregada y Descargada', grupo: 'D' },
            { codigo: 'DDP', nombre: 'DDP - Entregada Derechos Pagados', grupo: 'D' }
        ];
    }
}

// Cargar incoterms al inicio
document.addEventListener('DOMContentLoaded', function() {
    cargarIncoterms();
});

/**
 * Genera el HTML del input según el tipo de columna opcional
 */
function generarInputColumnaOpcional(columna, valorActual = '') {
    const baseClass = 'form-input bg-white w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500';
    const clave = columna.clave;
    
    // Configuración específica para cada columna opcional
    switch(clave) {
        case 'tipo_carga':
            return `<select name="${clave}" class="${baseClass}">
                        <option value="">-- Seleccionar --</option>
                        <option value="FCL" ${valorActual === 'FCL' ? 'selected' : ''}>FCL (Full Container Load)</option>
                        <option value="LCL" ${valorActual === 'LCL' ? 'selected' : ''}>LCL (Less than Container Load)</option>
                    </select>`;
        
        case 'tipo_incoterm':
            let opcionesIncoterm = '<option value="">-- Seleccionar Incoterm --</option>';
            incotermsDisponibles.forEach(inc => {
                const selected = valorActual === inc.codigo ? 'selected' : '';
                opcionesIncoterm += `<option value="${inc.codigo}" ${selected}>${inc.nombre}</option>`;
            });
            return `<select name="${clave}" class="${baseClass}">${opcionesIncoterm}</select>`;
        
        case 'puerto_salida':
            return `<input type="text" name="${clave}" class="${baseClass}" 
                    placeholder="Ej: Shanghai, China" value="${valorActual}">`;
        
        case 'in_charge':
            return `<input type="text" name="${clave}" class="${baseClass}" 
                    placeholder="Nombre del responsable" value="${valorActual}">`;
        
        case 'proveedor':
            return `<input type="text" name="${clave}" class="${baseClass}" 
                    placeholder="Nombre del proveedor" value="${valorActual}">`;
        
        case 'tipo_previo':
            return `<select name="${clave}" class="${baseClass}">
                        <option value="">-- Seleccionar --</option>
                        <option value="Normal" ${valorActual === 'Normal' ? 'selected' : ''}>Normal</option>
                        <option value="Previo" ${valorActual === 'Previo' ? 'selected' : ''}>Previo</option>
                        <option value="Reconocimiento" ${valorActual === 'Reconocimiento' ? 'selected' : ''}>Reconocimiento</option>
                    </select>`;
        
        case 'fecha_etd':
            return `<input type="date" name="${clave}" class="${baseClass}" value="${valorActual}">`;
        
        case 'fecha_zarpe':
            return `<input type="date" name="${clave}" class="${baseClass}" value="${valorActual}">`;
        
        case 'pedimento_en_carpeta':
            return `<div class="flex gap-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="${clave}" value="1" class="mr-2 text-green-600" ${valorActual === '1' || valorActual === true ? 'checked' : ''}>
                            <span class="text-green-600">✓ Sí</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="${clave}" value="0" class="mr-2 text-red-600" ${valorActual === '0' || valorActual === false || !valorActual ? 'checked' : ''}>
                            <span class="text-red-600">✗ No</span>
                        </label>
                    </div>`;
        
        case 'referencia_cliente':
            return `<input type="text" name="${clave}" class="${baseClass}" 
                    placeholder="Referencia del cliente" value="${valorActual}">`;
        
        case 'mail_subject':
            return `<input type="text" name="${clave}" class="${baseClass}" 
                    placeholder="Asunto del correo" value="${valorActual}">`;
        
        default:
            return `<input type="text" name="${clave}" class="${baseClass}" 
                    placeholder="Ingrese ${columna.nombre}" value="${valorActual}">`;
    }
}

/**
 * Genera el HTML del input según el tipo de campo personalizado
 */
function generarInputCampoPersonalizado(campo, valorActual = '') {
    const baseClass = 'form-input bg-white w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500';
    const nombre = `campo_personalizado_${campo.id}`;
    const opciones = campo.opciones || [];
    const config = campo.configuracion || {};
    
    switch(campo.tipo) {
        case 'texto':
            return `<input type="text" name="${nombre}" data-campo-id="${campo.id}" 
                    class="${baseClass}" placeholder="Ingrese ${campo.nombre}" value="${valorActual}">`;
        
        case 'descripcion':
            return `<textarea name="${nombre}" data-campo-id="${campo.id}" 
                    class="${baseClass} min-h-[80px]" placeholder="Ingrese ${campo.nombre}">${valorActual}</textarea>`;
        
        case 'numero':
            const minNum = config.min !== undefined ? `min="${config.min}"` : '';
            const maxNum = config.max !== undefined ? `max="${config.max}"` : '';
            return `<input type="number" name="${nombre}" data-campo-id="${campo.id}" 
                    class="${baseClass}" ${minNum} ${maxNum} step="1" placeholder="0" value="${valorActual}">`;
        
        case 'decimal':
            const decimales = config.decimales || 2;
            return `<input type="number" name="${nombre}" data-campo-id="${campo.id}" 
                    class="${baseClass}" step="${Math.pow(10, -decimales).toFixed(decimales)}" placeholder="0.00" value="${valorActual}">`;
        
        case 'moneda':
            const moneda = config.moneda || 'MXN';
            const decMoneda = config.decimales || 2;
            return `<div class="flex">
                        <span class="inline-flex items-center px-3 bg-slate-100 border border-r-0 border-slate-300 rounded-l-lg text-slate-600 text-sm">${moneda}</span>
                        <input type="number" name="${nombre}" data-campo-id="${campo.id}" 
                        class="${baseClass} rounded-l-none" step="${Math.pow(10, -decMoneda).toFixed(decMoneda)}" placeholder="0.00" value="${valorActual}">
                    </div>`;
        
        case 'fecha':
            return `<input type="date" name="${nombre}" data-campo-id="${campo.id}" 
                    class="${baseClass}" value="${valorActual}">`;
        
        case 'booleano':
            const checkedSi = valorActual === '1' || valorActual === 'si' || valorActual === true ? 'checked' : '';
            const checkedNo = valorActual === '0' || valorActual === 'no' || valorActual === false ? 'checked' : '';
            return `<div class="flex gap-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="${nombre}" data-campo-id="${campo.id}" value="1" class="mr-2 text-green-600" ${checkedSi}>
                            <span class="text-green-600">✓ Sí</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="${nombre}" data-campo-id="${campo.id}" value="0" class="mr-2 text-red-600" ${checkedNo}>
                            <span class="text-red-600">✗ No</span>
                        </label>
                    </div>`;
        
        case 'selector':
            let selectOptions = '<option value="">-- Seleccionar --</option>';
            opciones.forEach(opt => {
                const selected = valorActual === opt ? 'selected' : '';
                selectOptions += `<option value="${opt}" ${selected}>${opt}</option>`;
            });
            return `<select name="${nombre}" data-campo-id="${campo.id}" class="${baseClass}">${selectOptions}</select>`;
        
        case 'multiple':
            const valoresSeleccionados = valorActual ? (Array.isArray(valorActual) ? valorActual : JSON.parse(valorActual || '[]')) : [];
            let checkboxes = '<div class="flex flex-wrap gap-3">';
            opciones.forEach((opt, idx) => {
                const checked = valoresSeleccionados.includes(opt) ? 'checked' : '';
                checkboxes += `
                    <label class="inline-flex items-center cursor-pointer bg-slate-50 px-3 py-2 rounded-lg border hover:bg-indigo-50 transition-colors">
                        <input type="checkbox" name="${nombre}[]" data-campo-id="${campo.id}" value="${opt}" class="mr-2 text-indigo-600 rounded" ${checked}>
                        <span class="text-sm">${opt}</span>
                    </label>`;
            });
            checkboxes += '</div>';
            return checkboxes;
        
        case 'email':
            return `<input type="email" name="${nombre}" data-campo-id="${campo.id}" 
                    class="${baseClass}" placeholder="correo@ejemplo.com" value="${valorActual}">`;
        
        case 'telefono':
            return `<input type="tel" name="${nombre}" data-campo-id="${campo.id}" 
                    class="${baseClass}" placeholder="+52 123 456 7890" value="${valorActual}">`;
        
        case 'url':
            return `<input type="url" name="${nombre}" data-campo-id="${campo.id}" 
                    class="${baseClass}" placeholder="https://ejemplo.com" value="${valorActual}">`;
        
        default:
            return `<input type="text" name="${nombre}" data-campo-id="${campo.id}" 
                    class="${baseClass}" placeholder="Ingrese ${campo.nombre}" value="${valorActual}">`;
    }
}

/**
 * Cargar valores de campos personalizados para una operación al editar
 */
async function cargarValoresCamposOperacion(operacionId) {
    try {
        const response = await fetch(`/logistica/campos-personalizados/operacion/${operacionId}/valores`);
        const valores = await response.json();
        
        // Llenar los inputs con los valores según el tipo
        Object.keys(valores).forEach(campoId => {
            const valorData = valores[campoId];
            if (!valorData) return;
            
            const valor = valorData.valor || '';
            
            // Input, select, textarea normales
            const input = document.querySelector(`[name="campo_personalizado_${campoId}"]`);
            if (input) {
                if (input.tagName === 'SELECT') {
                    input.value = valor;
                } else if (input.tagName === 'TEXTAREA') {
                    input.value = valor;
                } else {
                    input.value = valor;
                }
                return;
            }
            
            // Radio buttons (booleano)
            const radios = document.querySelectorAll(`input[name="campo_personalizado_${campoId}"]`);
            if (radios.length > 0) {
                radios.forEach(radio => {
                    if (radio.value === valor || (valor === '1' && radio.value === '1') || (valor === '0' && radio.value === '0')) {
                        radio.checked = true;
                    }
                });
                return;
            }
            
            // Checkboxes (múltiple)
            const checkboxes = document.querySelectorAll(`input[name="campo_personalizado_${campoId}[]"]`);
            if (checkboxes.length > 0) {
                let valoresArray = [];
                try {
                    valoresArray = Array.isArray(valor) ? valor : JSON.parse(valor || '[]');
                } catch(e) {
                    valoresArray = valor ? [valor] : [];
                }
                checkboxes.forEach(cb => {
                    cb.checked = valoresArray.includes(cb.value);
                });
            }
        });
    } catch (error) {
        console.error('Error al cargar valores de campos:', error);
    }
}

/**
 * Cargar valores de columnas opcionales para una operación al editar (sección 7)
 */
function cargarValoresColumnasOpcionales(operacion) {
    if (!operacion || !columnasOpcionalesDelEjecutivo) return;
    
    const container = document.getElementById('columnasOpcionalesContainer');
    if (!container) return;
    
    // Lista de columnas opcionales a verificar
    const columnasOpcionales = ['tipo_carga', 'tipo_incoterm', 'puerto_salida', 'in_charge', 
                               'proveedor', 'tipo_previo', 'fecha_etd', 'fecha_zarpe', 
                               'pedimento_en_carpeta', 'referencia_cliente', 'mail_subject'];
    
    columnasOpcionales.forEach(clave => {
        const valor = operacion[clave];
        if (valor === undefined) return;
        
        // Buscar el input en el contenedor de sección 7
        const input = container.querySelector(`[name="${clave}"]`);
        if (input) {
            if (input.tagName === 'SELECT') {
                input.value = valor || '';
            } else if (input.type === 'radio') {
                // Para radio buttons (pedimento_en_carpeta)
                const radios = container.querySelectorAll(`[name="${clave}"]`);
                radios.forEach(radio => {
                    const valorStr = String(valor);
                    if (radio.value === valorStr || 
                        (valorStr === 'true' && radio.value === '1') || 
                        (valorStr === 'false' && radio.value === '0') ||
                        (valorStr === '1' && radio.value === '1') ||
                        (valorStr === '0' && radio.value === '0')) {
                        radio.checked = true;
                    }
                });
            } else if (input.type === 'date' && valor) {
                // Formatear fecha si es necesario
                input.value = valor.split('T')[0]; // Por si viene con timestamp
            } else {
                input.value = valor || '';
            }
        }
    });
}

/**
 * Guardar valores de campos personalizados después de guardar la operación
 */
async function guardarValoresCamposPersonalizados(operacionId) {
    // Recopilar todos los valores de campos personalizados
    const camposGuardar = {};
    
    // Inputs, selects, textareas
    const elementos = document.querySelectorAll('[name^="campo_personalizado_"]:not([name$="[]"])');
    elementos.forEach(el => {
        const campoId = el.dataset.campoId;
        if (!campoId) return;
        
        if (el.type === 'radio') {
            if (el.checked) {
                camposGuardar[campoId] = el.value;
            }
        } else {
            camposGuardar[campoId] = el.value;
        }
    });
    
    // Checkboxes (múltiple)
    const checkboxes = document.querySelectorAll('input[name^="campo_personalizado_"][name$="[]"]');
    const checkboxGroups = {};
    checkboxes.forEach(cb => {
        const campoId = cb.dataset.campoId;
        if (!campoId) return;
        
        if (!checkboxGroups[campoId]) {
            checkboxGroups[campoId] = [];
        }
        if (cb.checked) {
            checkboxGroups[campoId].push(cb.value);
        }
    });
    
    // Agregar grupos de checkboxes
    Object.keys(checkboxGroups).forEach(campoId => {
        camposGuardar[campoId] = JSON.stringify(checkboxGroups[campoId]);
    });
    
    // Guardar cada campo
    for (const [campoId, valor] of Object.entries(camposGuardar)) {
        try {
            await fetch('/logistica/campos-personalizados/valor', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    operacion_id: operacionId,
                    campo_id: campoId,
                    valor: valor
                })
            });
        } catch (error) {
            console.error('Error al guardar campo personalizado:', error);
        }
    }
}

/**
 * Editar un campo personalizado directamente desde la tabla
 */
window.editarCampoPersonalizado = async function(operacionId, campoId, tipo, nombre) {
    // Obtener datos del campo completo
    let campoData = null;
    try {
        const response = await fetch('/logistica/campos-personalizados');
        const campos = await response.json();
        campoData = campos.find(c => c.id == campoId);
    } catch (e) {
        console.error('Error cargando campo:', e);
    }
    
    if (!campoData) {
        campoData = { id: campoId, tipo: tipo, nombre: nombre, opciones: [], configuracion: {} };
    }
    
    // Obtener valor actual
    let valorActual = '';
    try {
        const valResponse = await fetch(`/logistica/campos-personalizados/operacion/${operacionId}/valores`);
        const valores = await valResponse.json();
        if (valores[campoId]) {
            valorActual = valores[campoId].valor || '';
        }
    } catch (e) {
        console.error('Error cargando valor:', e);
    }
    
    // Generar el input correcto según el tipo
    const inputHtml = generarInputCampoPersonalizadoModal(campoData, valorActual);
    
    const modalHtml = `
        <div id="modalEditarValorCampo" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-slate-800">
                        <span class="text-indigo-600 mr-2">★</span>Editar ${nombre}
                    </h3>
                    <button onclick="cerrarModalEditarValorCampo()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-600 mb-2">${nombre}</label>
                    ${inputHtml}
                </div>
                <div class="flex justify-end space-x-3">
                    <button onclick="cerrarModalEditarValorCampo()" 
                            class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50">
                        Cancelar
                    </button>
                    <button onclick="guardarValorCampoInline(${operacionId}, ${campoId}, '${tipo}')" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
};

/**
 * Genera el HTML del input para el modal de edición inline
 */
function generarInputCampoPersonalizadoModal(campo, valorActual = '') {
    const baseClass = 'form-input bg-white w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500';
    const opciones = campo.opciones || [];
    const config = campo.configuracion || {};
    
    switch(campo.tipo) {
        case 'texto':
            return `<input type="text" id="valorCampoEditar" class="${baseClass}" placeholder="Ingrese ${campo.nombre}" value="${valorActual}">`;
        
        case 'descripcion':
            return `<textarea id="valorCampoEditar" class="${baseClass} min-h-[100px]" placeholder="Ingrese ${campo.nombre}">${valorActual}</textarea>`;
        
        case 'numero':
            const minNum = config.min !== undefined ? `min="${config.min}"` : '';
            const maxNum = config.max !== undefined ? `max="${config.max}"` : '';
            return `<input type="number" id="valorCampoEditar" class="${baseClass}" ${minNum} ${maxNum} step="1" value="${valorActual}">`;
        
        case 'decimal':
            const decimales = config.decimales || 2;
            return `<input type="number" id="valorCampoEditar" class="${baseClass}" step="${Math.pow(10, -decimales).toFixed(decimales)}" value="${valorActual}">`;
        
        case 'moneda':
            const moneda = config.moneda || 'MXN';
            const decMoneda = config.decimales || 2;
            return `<div class="flex">
                        <span class="inline-flex items-center px-3 bg-slate-100 border border-r-0 border-slate-300 rounded-l-lg text-slate-600">${moneda}</span>
                        <input type="number" id="valorCampoEditar" class="${baseClass} rounded-l-none" step="${Math.pow(10, -decMoneda).toFixed(decMoneda)}" value="${valorActual}">
                    </div>`;
        
        case 'fecha':
            return `<input type="date" id="valorCampoEditar" class="${baseClass}" value="${valorActual}">`;
        
        case 'booleano':
            const checkedSi = valorActual === '1' || valorActual === 'si' ? 'checked' : '';
            const checkedNo = valorActual === '0' || valorActual === 'no' ? 'checked' : '';
            return `<div class="flex gap-6 py-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="valorCampoEditarRadio" value="1" class="mr-2 w-5 h-5 text-green-600" ${checkedSi}>
                            <span class="text-lg text-green-600 font-medium">✓ Sí</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="valorCampoEditarRadio" value="0" class="mr-2 w-5 h-5 text-red-600" ${checkedNo}>
                            <span class="text-lg text-red-600 font-medium">✗ No</span>
                        </label>
                    </div>`;
        
        case 'selector':
            let selectOptions = '<option value="">-- Seleccionar --</option>';
            opciones.forEach(opt => {
                const selected = valorActual === opt ? 'selected' : '';
                selectOptions += `<option value="${opt}" ${selected}>${opt}</option>`;
            });
            return `<select id="valorCampoEditar" class="${baseClass}">${selectOptions}</select>`;
        
        case 'multiple':
            let valoresSeleccionados = [];
            try {
                valoresSeleccionados = valorActual ? (Array.isArray(valorActual) ? valorActual : JSON.parse(valorActual || '[]')) : [];
            } catch(e) {
                valoresSeleccionados = valorActual ? [valorActual] : [];
            }
            let checkboxes = '<div class="flex flex-wrap gap-3" id="valorCampoEditarMultiple">';
            opciones.forEach((opt, idx) => {
                const checked = valoresSeleccionados.includes(opt) ? 'checked' : '';
                checkboxes += `
                    <label class="inline-flex items-center cursor-pointer bg-slate-50 px-4 py-2 rounded-lg border hover:bg-indigo-50 transition-colors">
                        <input type="checkbox" name="valorCampoEditarCb" value="${opt}" class="mr-2 w-4 h-4 text-indigo-600 rounded" ${checked}>
                        <span>${opt}</span>
                    </label>`;
            });
            checkboxes += '</div>';
            return checkboxes;
        
        case 'email':
            return `<input type="email" id="valorCampoEditar" class="${baseClass}" placeholder="correo@ejemplo.com" value="${valorActual}">`;
        
        case 'telefono':
            return `<input type="tel" id="valorCampoEditar" class="${baseClass}" placeholder="+52 123 456 7890" value="${valorActual}">`;
        
        case 'url':
            return `<input type="url" id="valorCampoEditar" class="${baseClass}" placeholder="https://ejemplo.com" value="${valorActual}">`;
        
        default:
            return `<input type="text" id="valorCampoEditar" class="${baseClass}" value="${valorActual}">`;
    }
}

window.cerrarModalEditarValorCampo = function() {
    const modal = document.getElementById('modalEditarValorCampo');
    if (modal) modal.remove();
};

window.guardarValorCampoInline = async function(operacionId, campoId, tipo) {
    let valor = '';
    
    // Obtener valor según el tipo
    if (tipo === 'booleano') {
        const radioChecked = document.querySelector('input[name="valorCampoEditarRadio"]:checked');
        valor = radioChecked ? radioChecked.value : '';
    } else if (tipo === 'multiple') {
        const checkboxes = document.querySelectorAll('input[name="valorCampoEditarCb"]:checked');
        const valores = Array.from(checkboxes).map(cb => cb.value);
        valor = JSON.stringify(valores);
    } else {
        const input = document.getElementById('valorCampoEditar');
        valor = input ? input.value : '';
    }
    
    try {
        const response = await fetch('/logistica/campos-personalizados/valor', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                operacion_id: operacionId,
                campo_id: campoId,
                valor: valor
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Actualizar el valor en la celda de la tabla
            const celda = document.querySelector(`td[data-campo-id="${campoId}"][data-operacion-id="${operacionId}"] .valor-campo`);
            if (celda) {
                let valorMostrar = formatearValorParaMostrar(valor, tipo);
                celda.textContent = valorMostrar;
            }
            
            cerrarModalEditarValorCampo();
            mostrarAlerta('Valor guardado exitosamente', 'success');
        } else {
            mostrarAlerta(data.mensaje || 'Error al guardar el valor', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al guardar el valor', 'error');
    }
};

/**
 * Formatea un valor para mostrarlo en la tabla
 */
function formatearValorParaMostrar(valor, tipo) {
    if (!valor || valor === '' || valor === '[]') return '-';
    
    switch(tipo) {
        case 'fecha':
            if (valor.match(/^\d{4}-\d{2}-\d{2}$/)) {
                const [year, month, day] = valor.split('-');
                return `${day}/${month}/${year}`;
            }
            return valor;
        
        case 'booleano':
            return valor === '1' ? '✓ Sí' : '✗ No';
        
        case 'multiple':
            try {
                const arr = JSON.parse(valor);
                return arr.join(', ');
            } catch(e) {
                return valor;
            }
        
        case 'moneda':
            return `$ ${parseFloat(valor).toLocaleString('es-MX', {minimumFractionDigits: 2})}`;
        
        default:
            return valor;
    }
}

// Exponer funciones globalmente
window.cargarCamposParaModal = cargarCamposParaModal;
window.cargarValoresCamposOperacion = cargarValoresCamposOperacion;
window.guardarValoresCamposPersonalizados = guardarValoresCamposPersonalizados;