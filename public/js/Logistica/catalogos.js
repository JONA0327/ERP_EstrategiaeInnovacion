/**
 * Catálogos Maestros - JavaScript
 * Manejo de tabs, modales de edición y eliminación
 */

// Helper para obtener token CSRF
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) {
        console.error('CSRF token no encontrado en la página');
        return null;
    }
    return token.getAttribute('content');
}

// Helper para crear headers con CSRF
function getAuthHeaders() {
    const token = getCsrfToken();
    return token ? {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
    } : {
        'Accept': 'application/json'
    };
}

class CatalogosMaestros {
    constructor() {
        this.activeTab = 'clientes';
        this.currentEditId = null;
        this.currentEditType = null;
        this.init();
    }

    init() {
        this.initTabs();
        this.initModals();
        this.initEventListeners();
        this.initExecutiveAssignment();
        this.showTab('clientes');
    }

    initTabs() {
        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const tabId = button.getAttribute('data-tab');
                this.showTab(tabId);
            });
        });
    }

    showTab(tabId) {
        // Ocultar todos los contenidos de tabs
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            content.classList.add('hidden');
        });

        // Remover clase active de todos los botones
        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(button => {
            button.classList.remove('active');
        });

        // Mostrar el tab seleccionado
        const activeContent = document.getElementById(`${tabId}-content`);
        const activeButton = document.querySelector(`[data-tab="${tabId}"]`);

        if (activeContent && activeButton) {
            activeContent.classList.remove('hidden');
            activeButton.classList.add('active');
            this.activeTab = tabId;
        }
    }

    initModals() {
        // Modal de edición
        const editModal = document.getElementById('editModal');
        const deleteModal = document.getElementById('deleteModal');
        const assignModal = document.getElementById('assignExecutiveModal');

        // Cerrar modales al hacer clic en overlay
        if (editModal) {
            editModal.addEventListener('click', (e) => {
                if (e.target === editModal) {
                    this.closeEditModal();
                }
            });
        }

        if (deleteModal) {
            deleteModal.addEventListener('click', (e) => {
                if (e.target === deleteModal) {
                    this.closeDeleteModal();
                }
            });
        }

        if (assignModal) {
            assignModal.addEventListener('click', (e) => {
                if (e.target === assignModal) {
                    this.closeAssignModal();
                }
            });
        }

        // Cerrar modales con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeEditModal();
                this.closeDeleteModal();
                this.closeAssignModal();
            }
        });
    }

    initEventListeners() {
        // Botones de agregar
        const addButtons = document.querySelectorAll('.btn-add');
        addButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const type = button.getAttribute('data-type');
                this.openAddModal(type);
            });
        });

        // Botones de editar
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-edit')) {
                const id = e.target.getAttribute('data-id');
                const type = e.target.getAttribute('data-type');
                const name = e.target.getAttribute('data-name');
                const ejecutivoId = e.target.getAttribute('data-ejecutivo-id');
                this.openEditModal(id, type, name, ejecutivoId);
            }
        });

        // Botones de eliminar
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-delete')) {
                const id = e.target.getAttribute('data-id');
                const type = e.target.getAttribute('data-type');
                const name = e.target.getAttribute('data-name');
                this.openDeleteModal(id, type, name);
            }
        });

        // Formulario de edición
        const editForm = document.getElementById('editForm');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitEdit();
            });
        }

        // Confirmación de eliminación
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => {
                this.confirmDelete();
            });
        }

        // Botones de cancelar
        const cancelButtons = document.querySelectorAll('.btn-cancel');
        cancelButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.closeEditModal();
                this.closeDeleteModal();
                this.closeAssignModal();
            });
        });

        // Formulario de asignación de ejecutivo
        const assignForm = document.getElementById('assignExecutiveForm');
        if (assignForm) {
            assignForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitExecutiveAssignment();
            });
        }
    }

    initExecutiveAssignment() {
        // Botón de asignar ejecutivo
        const assignBtn = document.getElementById('assignExecutiveBtn');
        if (assignBtn) {
            assignBtn.addEventListener('click', () => {
                this.openAssignModal();
            });
        }

        // Checkbox de seleccionar todos
        const selectAllCheckbox = document.getElementById('selectAllClientes');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                this.selectAllClients(e.target.checked);
            });
        }

        // Checkboxes individuales
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('cliente-checkbox')) {
                this.updateAssignButtonState();
            }
        });
    }

    openAddModal(type) {
        this.currentEditId = null;
        this.currentEditType = type;

        const modal = document.getElementById('editModal');
        const title = document.getElementById('modalTitle');
        const nameInput = document.getElementById('editName');
        const ejecutivoField = document.getElementById('ejecutivoField');
        const ejecutivoSelect = document.getElementById('editEjecutivo');

        title.textContent = `Agregar ${this.getTypeLabel(type)}`;
        nameInput.value = '';
        nameInput.focus();

        // Mostrar campo de ejecutivo solo para clientes
        if (type === 'clientes' && ejecutivoField) {
            ejecutivoField.classList.remove('hidden');
            if (ejecutivoSelect) {
                ejecutivoSelect.value = '';
            }
        } else if (ejecutivoField) {
            ejecutivoField.classList.add('hidden');
        }

        this.showModal(modal);
    }

    openEditModal(id, type, name, ejecutivoId = null) {
        this.currentEditId = id;
        this.currentEditType = type;

        const modal = document.getElementById('editModal');
        const title = document.getElementById('modalTitle');
        const nameInput = document.getElementById('editName');
        const ejecutivoField = document.getElementById('ejecutivoField');
        const ejecutivoSelect = document.getElementById('editEjecutivo');

        title.textContent = `Editar ${this.getTypeLabel(type)}`;
        nameInput.value = name;
        nameInput.focus();
        nameInput.select();

        // Mostrar campo de ejecutivo solo para clientes
        if (type === 'clientes' && ejecutivoField) {
            ejecutivoField.classList.remove('hidden');
            if (ejecutivoSelect && ejecutivoId) {
                ejecutivoSelect.value = ejecutivoId;
            }
        } else if (ejecutivoField) {
            ejecutivoField.classList.add('hidden');
        }

        this.showModal(modal);
    }

    openDeleteModal(id, type, name) {
        this.currentEditId = id;
        this.currentEditType = type;

        const modal = document.getElementById('deleteModal');
        const message = document.getElementById('deleteMessage');

        message.innerHTML = `¿Estás seguro de que deseas eliminar ${this.getTypeLabel(type).toLowerCase()} <strong>"${name}"</strong>?<br><span class="text-sm text-gray-500">Esta acción no se puede deshacer.</span>`;

        this.showModal(modal);
    }

    closeEditModal() {
        const modal = document.getElementById('editModal');
        this.hideModal(modal);
        this.resetForm();
    }

    closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        this.hideModal(modal);
    }

    showModal(modal) {
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';

            setTimeout(() => {
                modal.querySelector('.modal-content').style.transform = 'scale(1)';
            }, 10);
        }
    }

    hideModal(modal) {
        if (modal) {
            modal.classList.remove('show');
            modal.querySelector('.modal-content').style.transform = 'scale(0.95)';

            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }, 300);
        }
    }

    resetForm() {
        const form = document.getElementById('editForm');
        if (form) {
            form.reset();
        }
        this.currentEditId = null;
        this.currentEditType = null;
    }

    async submitEdit() {
        const nameInput = document.getElementById('editName');
        const name = nameInput.value.trim();

        if (!name) {
            this.showAlert('Por favor, ingresa un nombre válido.', 'error');
            return;
        }

        const isEditing = this.currentEditId !== null;
        const url = this.getApiUrl(this.currentEditType, isEditing);
        const method = isEditing ? 'PUT' : 'POST';

        const formData = new FormData();
        formData.append(this.getFieldName(this.currentEditType), name);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        // Agregar ejecutivo asignado si es un cliente
        if (this.currentEditType === 'clientes') {
            const ejecutivoSelect = document.getElementById('editEjecutivo');
            if (ejecutivoSelect) {
                formData.append('ejecutivo_asignado_id', ejecutivoSelect.value || '');
            }
        }

        if (isEditing) {
            formData.append('_method', 'PUT');
        }

        try {
            this.showLoading(true);

            const response = await fetch(url, {
                method: 'POST', // Siempre POST debido a Laravel form method spoofing
                headers: getAuthHeaders(),
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert(data.message, 'success');
                this.closeEditModal();
                this.refreshTable(this.currentEditType);
            } else {
                this.showAlert(data.message || 'Error al procesar la solicitud.', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error de conexión. Por favor, intenta nuevamente.', 'error');
        } finally {
            this.showLoading(false);
        }
    }

    async confirmDelete() {
        const url = this.getApiUrl(this.currentEditType, true);

        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('_method', 'DELETE');

        try {
            this.showLoading(true);

            const response = await fetch(url, {
                method: 'POST',
                headers: getAuthHeaders(),
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert(data.message, 'success');
                this.closeDeleteModal();
                this.refreshTable(this.currentEditType);
            } else {
                this.showAlert(data.message || 'Error al eliminar el registro.', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error de conexión. Por favor, intenta nuevamente.', 'error');
        } finally {
            this.showLoading(false);
        }
    }

    getApiUrl(type, isEditing) {
        const baseUrls = {
            'clientes': '/logistica/clientes',
            'agentes': '/logistica/agentes',
            'transportes': '/logistica/transportes',
            'ejecutivos': '/logistica/ejecutivos'
        };

        let url = baseUrls[type];
        if (isEditing && this.currentEditId) {
            url += `/${this.currentEditId}`;
        }

        return url;
    }

    getFieldName(type) {
        const fieldNames = {
            'clientes': 'cliente',
            'agentes': 'agente_aduanal',
            'transportes': 'transporte',
            'ejecutivos': 'nombre'
        };

        return fieldNames[type];
    }

    getTypeLabel(type) {
        const labels = {
            'clientes': 'Cliente',
            'agentes': 'Agente Aduanal',
            'transportes': 'Transporte',
            'ejecutivos': 'Ejecutivo'
        };

        return labels[type];
    }

    refreshTable(type) {
        // Recargar solo la sección específica
        window.location.reload();
    }

    showLoading(show) {
        const submitBtn = document.getElementById('submitEditBtn');
        const deleteBtn = document.getElementById('confirmDeleteBtn');

        if (submitBtn) {
            submitBtn.disabled = show;
            submitBtn.innerHTML = show ?
                '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Procesando...' :
                'Guardar';
        }

        if (deleteBtn) {
            deleteBtn.disabled = show;
            deleteBtn.innerHTML = show ?
                '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Eliminando...' :
                'Eliminar';
        }
    }

    showAlert(message, type = 'info') {
        // Crear o actualizar el alert
        let alertContainer = document.getElementById('alertContainer');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'alertContainer';
            alertContainer.className = 'fixed top-4 right-4 z-50';
            document.body.appendChild(alertContainer);
        }

        const alertColors = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'info': 'bg-blue-500',
            'warning': 'bg-yellow-500'
        };

        const alert = document.createElement('div');
        alert.className = `${alertColors[type]} text-white px-6 py-3 rounded-lg shadow-lg mb-2 transform translate-x-full transition-transform duration-300`;
        alert.innerHTML = `
            <div class="flex items-center">
                <span>${message}</span>
                <button class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;

        alertContainer.appendChild(alert);

        // Animar entrada
        setTimeout(() => {
            alert.classList.remove('translate-x-full');
        }, 100);

        // Auto remover después de 5 segundos
        setTimeout(() => {
            alert.classList.add('translate-x-full');
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    }

    // Métodos para asignación de ejecutivos
    selectAllClients(checked) {
        const checkboxes = document.querySelectorAll('.cliente-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
        this.updateAssignButtonState();
    }

    updateAssignButtonState() {
        const checkboxes = document.querySelectorAll('.cliente-checkbox');
        const checkedBoxes = document.querySelectorAll('.cliente-checkbox:checked');
        const assignBtn = document.getElementById('assignExecutiveBtn');

        if (assignBtn) {
            assignBtn.disabled = checkedBoxes.length === 0;
            assignBtn.textContent = checkedBoxes.length > 0
                ? `Asignar Ejecutivo (${checkedBoxes.length})`
                : 'Asignar Ejecutivo';
        }

        // Actualizar estado del checkbox principal
        const selectAll = document.getElementById('selectAllClientes');
        if (selectAll) {
            selectAll.checked = checkboxes.length > 0 && checkedBoxes.length === checkboxes.length;
            selectAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < checkboxes.length;
        }
    }

    openAssignModal() {
        const checkedBoxes = document.querySelectorAll('.cliente-checkbox:checked');
        if (checkedBoxes.length === 0) {
            this.showAlert('Por favor, selecciona al menos un cliente.', 'warning');
            return;
        }

        const modal = document.getElementById('assignExecutiveModal');
        const countElement = document.getElementById('selectedClientsCount');
        const ejecutivoSelect = document.getElementById('selectEjecutivo');

        if (countElement) {
            countElement.textContent = checkedBoxes.length;
        }

        if (ejecutivoSelect) {
            ejecutivoSelect.value = '';
        }

        this.showModal(modal);
    }

    closeAssignModal() {
        const modal = document.getElementById('assignExecutiveModal');
        this.hideModal(modal);
    }

    async submitExecutiveAssignment() {
        const checkedBoxes = document.querySelectorAll('.cliente-checkbox:checked');
        const ejecutivoSelect = document.getElementById('selectEjecutivo');

        if (checkedBoxes.length === 0) {
            this.showAlert('No hay clientes seleccionados.', 'error');
            return;
        }

        if (!ejecutivoSelect || !ejecutivoSelect.value) {
            this.showAlert('Por favor, selecciona un ejecutivo.', 'error');
            return;
        }

        const clienteIds = Array.from(checkedBoxes).map(checkbox => checkbox.value);

        const formData = new FormData();
        clienteIds.forEach(id => {
            formData.append('cliente_ids[]', id);
        });
        formData.append('ejecutivo_id', ejecutivoSelect.value);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        try {
            this.showLoading(true);

            const response = await fetch('/logistica/clientes/asignar-ejecutivo', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert(data.message, 'success');
                this.closeAssignModal();
                this.clearSelection();
                this.refreshTable('clientes');
            } else {
                this.showAlert(data.message || 'Error al asignar ejecutivo.', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error de conexión. Por favor, intenta nuevamente.', 'error');
        } finally {
            this.showLoading(false);
        }
    }

    clearSelection() {
        const checkboxes = document.querySelectorAll('.cliente-checkbox');
        const selectAll = document.getElementById('selectAllClientes');

        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });

        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }

        this.updateAssignButtonState();
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.catalogosMaestros = new CatalogosMaestros();
});

// Funciones globales para compatibilidad
function showTab(tabId) {
    if (window.catalogosMaestros) {
        window.catalogosMaestros.showTab(tabId);
    }
}

function openEditModal(id, type, name) {
    if (window.catalogosMaestros) {
        window.catalogosMaestros.openEditModal(id, type, name);
    }
}

function openDeleteModal(id, type, name) {
    if (window.catalogosMaestros) {
        window.catalogosMaestros.openDeleteModal(id, type, name);
    }
}

// ===================================
// FUNCIONES ESPECÍFICAS PARA ADUANAS
// ===================================

// Abrir modal de importación
function openImportAduanasModal() {
    const modal = document.getElementById('importAduanasModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            modal.querySelector('.modal-content').style.transform = 'scale(1)';
        }, 10);
    }
}

// Cerrar modal de importación
function closeImportAduanasModal() {
    const modal = document.getElementById('importAduanasModal');
    if (modal) {
        modal.classList.remove('show');
        modal.querySelector('.modal-content').style.transform = 'scale(0.95)';

        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }, 300);

        // Limpiar formulario
        const form = document.getElementById('importAduanasForm');
        if (form) {
            form.reset();
            document.getElementById('selectedFileName').classList.add('hidden');
        }
    }
}

// Manejar selección de archivo
function initFileHandling() {
    const fileInput = document.getElementById('aduanasFile');
    const fileName = document.getElementById('selectedFileName');

    if (fileInput && fileName) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                fileName.textContent = `Archivo seleccionado: ${file.name}`;
                fileName.classList.remove('hidden');
            } else {
                fileName.classList.add('hidden');
            }
        });
    }
}

// Importar aduanas
async function importAduanas() {
    const form = document.getElementById('importAduanasForm');
    const fileInput = document.getElementById('aduanasFile');
    const importBtn = document.getElementById('importBtn');
    const progressContainer = document.getElementById('importProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    if (!fileInput.files[0]) {
        showAlert('Por favor, selecciona un archivo.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    try {
        // Mostrar progress bar
        if (progressContainer) {
            progressContainer.classList.remove('hidden');
            progressBar.style.width = '20%';
            progressText.textContent = 'Subiendo archivo...';
        }

        // Deshabilitar botón
        if (importBtn) {
            importBtn.disabled = true;
            importBtn.querySelector('.import-text').classList.add('hidden');
            importBtn.querySelector('.loading-text').classList.remove('hidden');
        }

        // Actualizar progreso
        setTimeout(() => {
            if (progressBar) {
                progressBar.style.width = '60%';
                progressText.textContent = 'Procesando contenido del archivo...';
            }
        }, 500);

        const response = await fetch('/logistica/aduanas/import', {
            method: 'POST',
            headers: getAuthHeaders(),
            body: formData
        });

        // Progreso final
        if (progressBar) {
            progressBar.style.width = '100%';
            progressText.textContent = 'Finalizando importación...';
        }

        // Verificar si la respuesta es JSON válida
        const contentType = response.headers.get('content-type');
        let data;
        
        if (response.status === 419) {
            throw new Error('Sesión expirada. Por favor, recarga la página e inténtalo de nuevo.');
        }
        
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            // Si no es JSON, probablemente es una página de error HTML
            const text = await response.text();
            console.error('Respuesta no JSON:', text);
            
            if (text.includes('Page Expired')) {
                throw new Error('Sesión expirada (CSRF). Por favor, recarga la página e inténtalo de nuevo.');
            }
            
            throw new Error('El servidor devolvió una respuesta inválida (no JSON)');
        }

        if (response.ok && data.success) {
            showAlert(`Importación exitosa: ${data.total_imported} aduanas importadas, ${data.total_skipped || 0} omitidas.`, 'success');
            // Cerrar modal inmediatamente después del éxito
            setTimeout(() => {
                closeImportAduanasModal();
                updateAduanasStats(data);
                refreshAduanasTable();
            }, 500);
        } else {
            showAlert(data.message || 'Error en la importación.', 'error');
            // Cerrar modal también cuando hay error
            setTimeout(() => {
                closeImportAduanasModal();
            }, 1500);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error de conexión durante la importación.', 'error');
        // Cerrar modal también en caso de excepción
        setTimeout(() => {
            closeImportAduanasModal();
        }, 1500);
    } finally {
        // Ocultar progress bar
        if (progressContainer) {
            progressContainer.classList.add('hidden');
            progressBar.style.width = '0%';
        }

        // Habilitar botón
        if (importBtn) {
            importBtn.disabled = false;
            importBtn.querySelector('.import-text').classList.remove('hidden');
            importBtn.querySelector('.loading-text').classList.add('hidden');
        }
    }
}

// Eliminar aduana individual
async function eliminarAduana(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta aduana?')) {
        return;
    }

    try {
        const response = await fetch(`/logistica/aduanas/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            showAlert(data.message, 'success');
            refreshAduanasTable();
        } else {
            showAlert(data.message || 'Error al eliminar la aduana.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error de conexión al eliminar la aduana.', 'error');
    }
}

// Limpiar todas las aduanas
async function clearAllAduanas() {
    const totalAduanas = document.getElementById('totalAduanas').textContent;

    if (!confirm(`¿Estás seguro de que deseas eliminar TODAS las ${totalAduanas} aduanas? Esta acción no se puede deshacer.`)) {
        return;
    }

    try {
        const response = await fetch('/logistica/aduanas', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            showAlert(data.message, 'success');
            updateAduanasStats({ total_imported: 0 });
            refreshAduanasTable();
        } else {
            showAlert(data.message || 'Error al limpiar las aduanas.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error de conexión al limpiar las aduanas.', 'error');
    }
}

// Actualizar estadísticas de aduanas
function updateAduanasStats(data) {
    const totalElement = document.getElementById('totalAduanas');
    const ultimaElement = document.getElementById('ultimaImportacion');
    const estadoElement = document.getElementById('estadoImportacion');

    if (totalElement && data.total_imported !== undefined) {
        totalElement.textContent = data.total_imported;
    }

    if (ultimaElement) {
        ultimaElement.textContent = new Date().toLocaleString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    if (estadoElement) {
        estadoElement.textContent = data.success ? 'Actualizado' : 'Error en importación';
    }
}

// Refrescar tabla de aduanas
function refreshAduanasTable() {
    // Recargar la página para mostrar los cambios
    window.location.reload();
}

// Función auxiliar para mostrar alertas
function showAlert(message, type = 'info') {
    if (window.catalogosMaestros) {
        window.catalogosMaestros.showAlert(message, type);
    } else {
        alert(message); // Fallback
    }
}

// Inicializar eventos específicos de aduanas cuando se cargue el DOM
document.addEventListener('DOMContentLoaded', function() {
    // Botón de importar aduanas
    const importAduanasBtn = document.getElementById('importAduanasBtn');
    if (importAduanasBtn) {
        importAduanasBtn.addEventListener('click', openImportAduanasModal);
    }

    // Botón de limpiar aduanas
    const clearAduanasBtn = document.getElementById('clearAduanasBtn');
    if (clearAduanasBtn) {
        clearAduanasBtn.addEventListener('click', clearAllAduanas);
    }

    // Formulario de importación
    const importForm = document.getElementById('importAduanasForm');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            e.preventDefault();
            importAduanas();
        });
    }

    // Cerrar modales con botones cancel
    const cancelButtons = document.querySelectorAll('.btn-cancel');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            closeImportAduanasModal();
        });
    });

    // Inicializar manejo de archivos
    initFileHandling();
});
