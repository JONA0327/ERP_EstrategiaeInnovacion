// Funcionalidad para la página "Crear Ticket"
// JavaScript para manejo de formulario, calendario de mantenimiento, imágenes y validaciones

document.addEventListener('DOMContentLoaded', function() {
    initializeTicketCreate();
});

function initializeTicketCreate() {
    console.log('🎫 Inicializando funcionalidad de Crear Ticket');
    
    const ticketType = getTicketType();
    console.log(`📝 Tipo de ticket: ${ticketType}`);
    
    // Inicializar funcionalidades base
    initializeFormHandling();
    initializeImageUpload();
    initializeProgramSelection();
    
    // Inicializar funcionalidades específicas del tipo
    if (ticketType === 'mantenimiento') {
        initializeMaintenanceScheduling();
    }
}

// ===== UTILIDADES =====
function getTicketType() {
    const mainElement = document.querySelector('[data-ticket-type]');
    return mainElement ? mainElement.getAttribute('data-ticket-type') : 'unknown';
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 
                   type === 'error' ? 'bg-red-500' : 
                   type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
    
    notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full opacity-0`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.classList.remove('translate-x-full', 'opacity-0');
    }, 100);
    
    // Remover después de 4 segundos
    setTimeout(() => {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

// ===== MANEJO DE FORMULARIO =====
function initializeFormHandling() {
    const form = document.querySelector('[data-ticket-create] form');
    if (!form) return;
    
    // Validación antes de envío
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        
        // Mostrar loading en botón de envío
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Creando ticket...
            `;
            
            // Restaurar después de 10 segundos por si hay error
            setTimeout(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }, 10000);
        }
    });
}

function validateForm() {
    const ticketType = getTicketType();
    let isValid = true;
    
    // Validar descripción
    const descripcion = document.getElementById('descripcion_problema');
    if (descripcion && ticketType !== 'mantenimiento') {
        if (!descripcion.value.trim()) {
            showNotification('La descripción del problema es obligatoria', 'error');
            descripcion.focus();
            isValid = false;
        }
    }
    
    // Validar programa para software
    if (ticketType === 'software') {
        const programa = document.getElementById('nombre_programa');
        if (programa && !programa.value) {
            showNotification('Debes seleccionar un programa', 'error');
            programa.focus();
            isValid = false;
        }
    }
    
    // Validar slot de mantenimiento
    if (ticketType === 'mantenimiento') {
        const slotId = document.getElementById('maintenance_slot_id');
        if (slotId && !slotId.value) {
            showNotification('Debes seleccionar una fecha y horario para el mantenimiento', 'error');
            isValid = false;
        }
    }
    
    return isValid;
}

// ===== SELECCIÓN DE PROGRAMA =====
function initializeProgramSelection() {
    const programSelect = document.getElementById('nombre_programa');
    const otroInput = document.getElementById('otro_programa_nombre');
    
    if (!programSelect || !otroInput) return;
    
    // Mostrar/ocultar campo "Otro" basado en selección
    function toggleOtroField() {
        const isOtro = programSelect.value === 'Otro';
        const container = otroInput.closest('div');
        
        if (container) {
            if (isOtro) {
                container.style.display = 'block';
                otroInput.required = true;
                setTimeout(() => otroInput.focus(), 100);
            } else {
                container.style.display = 'none';
                otroInput.required = false;
                otroInput.value = '';
            }
        }
    }
    
    programSelect.addEventListener('change', toggleOtroField);
    
    // Inicializar estado
    toggleOtroField();
}

// ===== MANEJO DE IMÁGENES =====
function initializeImageUpload() {
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const uploadButton = document.querySelector('button[onclick="document.getElementById(\'imageInput\').click()"]');
    
    if (!imageInput || !imagePreview) return;
    
    let selectedFiles = [];
    
    // Manejar selección de archivos
    imageInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        
        // Validar archivos
        const validFiles = files.filter(file => {
            if (file.size > 10 * 1024 * 1024) { // 10MB
                showNotification(`La imagen "${file.name}" es muy grande (máximo 10MB)`, 'warning');
                return false;
            }
            
            if (!file.type.startsWith('image/')) {
                showNotification(`"${file.name}" no es una imagen válida`, 'warning');
                return false;
            }
            
            return true;
        });
        
        // Agregar archivos válidos
        selectedFiles = [...selectedFiles, ...validFiles];
        
        if (selectedFiles.length > 10) {
            selectedFiles = selectedFiles.slice(0, 10);
            showNotification('Máximo 10 imágenes permitidas', 'warning');
        }
        
        updateImagePreview();
        updateFileInput();
    });
    
    function updateImagePreview() {
        if (selectedFiles.length === 0) {
            imagePreview.innerHTML = '<p class="text-sm text-slate-400 text-center py-8">Las imágenes seleccionadas aparecerán aquí</p>';
            return;
        }
        
        imagePreview.innerHTML = '';
        
        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imageContainer = document.createElement('div');
                imageContainer.className = 'relative group';
                
                imageContainer.innerHTML = `
                    <img src="${e.target.result}" alt="Preview ${index + 1}" 
                         class="h-24 w-24 rounded-xl object-cover shadow-md transition group-hover:shadow-lg">
                    <button type="button" 
                            onclick="removeImage(${index})"
                            class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow-lg transition hover:bg-red-600 hover:scale-110">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition rounded-xl flex items-center justify-center">
                        <span class="text-white text-xs font-medium">Ver</span>
                    </div>
                `;
                
                // Expandir imagen al hacer clic
                const img = imageContainer.querySelector('img');
                img.addEventListener('click', () => expandImage(e.target.result, `Imagen ${index + 1}`));
                
                imagePreview.appendChild(imageContainer);
            };
            reader.readAsDataURL(file);
        });
    }
    
    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => {
            if (file) dt.items.add(file);
        });
        imageInput.files = dt.files;
    }
    
    // Función global para remover imagen
    window.removeImage = function(index) {
        selectedFiles.splice(index, 1);
        updateImagePreview();
        updateFileInput();
        
        if (selectedFiles.length === 0) {
            showNotification('Imagen eliminada', 'info');
        }
    };
}

// ===== MODAL DE IMAGEN EXPANDIDA =====
function expandImage(src, alt) {
    let modal = document.getElementById('imageExpandModal');
    
    if (!modal) {
        modal = createImageModal();
    }
    
    const modalImage = modal.querySelector('#expandedImage');
    const modalTitle = modal.querySelector('#expandedImageTitle');
    
    modalImage.src = src;
    modalImage.alt = alt;
    modalTitle.textContent = alt;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function createImageModal() {
    const modal = document.createElement('div');
    modal.id = 'imageExpandModal';
    modal.className = 'fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm p-4';
    
    modal.innerHTML = `
        <div class="relative max-w-[95vw] max-h-[95vh] flex items-center justify-center">
            <button onclick="closeExpandedImage()" 
                    class="absolute -top-4 -right-4 z-10 h-10 w-10 rounded-full bg-white/90 text-gray-800 shadow-lg hover:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                <svg class="h-6 w-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <img id="expandedImage" src="" alt="" 
                 class="max-w-[90vw] max-h-[85vh] object-contain rounded-lg shadow-2xl bg-white">
            <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white p-3 rounded-b-lg">
                <p id="expandedImageTitle" class="text-sm font-medium text-center"></p>
            </div>
        </div>
    `;
    
    // Cerrar con ESC o click fuera
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeExpandedImage();
        }
    });
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeExpandedImage();
        }
    });
    
    document.body.appendChild(modal);
    return modal;
}

window.closeExpandedImage = function() {
    const modal = document.getElementById('imageExpandModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
};

// ===== PROGRAMACIÓN DE MANTENIMIENTO =====
function initializeMaintenanceScheduling() {
    console.log('🔧 Inicializando calendario de mantenimiento');
    
    const scheduling = document.getElementById('maintenanceScheduling');
    if (!scheduling) return;
    
    const availabilityUrl = scheduling.getAttribute('data-availability-url');
    const slotsUrl = scheduling.getAttribute('data-slots-url');
    
    if (!availabilityUrl || !slotsUrl) {
        console.error('URLs de mantenimiento no configuradas');
        return;
    }
    
    let currentDate = new Date();
    let selectedSlotId = null;
    let availabilityData = {};
    
    // Elementos del DOM
    const calendar = document.getElementById('maintenanceCalendar');
    const prevButton = document.getElementById('calendarPrev');
    const nextButton = document.getElementById('calendarNext');
    const currentMonthSpan = document.getElementById('currentMonth');
    const timeSlotsWrapper = document.getElementById('timeSlotsWrapper');
    const timeSlotsList = document.getElementById('timeSlotsList');
    const selectedDateLabel = document.getElementById('selectedDateLabel');
    const selectedSlotLabel = document.getElementById('selectedSlotLabel');
    const noSlotsMessage = document.getElementById('noSlotsMessage');
    const slotIdInput = document.getElementById('maintenance_slot_id');
    const selectedDateInput = document.getElementById('maintenance_selected_date');
    
    // Inicializar
    loadAvailability();
    
    // Event listeners
    if (prevButton) prevButton.addEventListener('click', () => changeMonth(-1));
    if (nextButton) nextButton.addEventListener('click', () => changeMonth(1));
    
    async function loadAvailability() {
        try {
            const response = await fetch(availabilityUrl);
            if (!response.ok) throw new Error('Error al cargar disponibilidad');
            
            availabilityData = await response.json();
            renderCalendar();
        } catch (error) {
            console.error('Error loading availability:', error);
            showNotification('Error al cargar la disponibilidad del calendario', 'error');
        }
    }
    
    function changeMonth(delta) {
        currentDate.setMonth(currentDate.getMonth() + delta);
        renderCalendar();
        hideTimeSlots();
    }
    
    function renderCalendar() {
        if (!calendar || !currentMonthSpan) return;
        
        // Actualizar título del mes
        const monthName = currentDate.toLocaleDateString('es-ES', { 
            month: 'long', 
            year: 'numeric' 
        });
        currentMonthSpan.textContent = monthName.charAt(0).toUpperCase() + monthName.slice(1);
        
        // Limpiar calendario
        calendar.innerHTML = '';
        
        // Días de la semana
        const weekDays = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        weekDays.forEach(day => {
            const dayHeader = document.createElement('div');
            dayHeader.className = 'p-2 text-center text-xs font-semibold text-slate-600 bg-slate-100 rounded-lg';
            dayHeader.textContent = day;
            calendar.appendChild(dayHeader);
        });
        
        // Obtener primer y último día del mes
        const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
        const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay());
        
        // Generar días del calendario
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        for (let i = 0; i < 42; i++) { // 6 semanas x 7 días
            const cellDate = new Date(startDate);
            cellDate.setDate(startDate.getDate() + i);
            
            const dayElement = document.createElement('button');
            dayElement.type = 'button';
            dayElement.className = 'h-12 w-full rounded-lg text-sm font-medium transition-all duration-200 hover:scale-105';
            dayElement.textContent = cellDate.getDate();
            
            const dateKey = cellDate.toISOString().split('T')[0];
            const availability = availabilityData[dateKey];
            const isCurrentMonth = cellDate.getMonth() === currentDate.getMonth();
            const isPast = cellDate < today;
            
            // Estilos basados en disponibilidad
            if (!isCurrentMonth) {
                dayElement.className += ' text-slate-300 cursor-not-allowed bg-slate-50';
                dayElement.disabled = true;
            } else if (isPast) {
                dayElement.className += ' text-slate-400 cursor-not-allowed bg-slate-100';
                dayElement.disabled = true;
            } else if (availability) {
                if (availability.available_slots > 0) {
                    dayElement.className += ' bg-green-100 text-green-800 hover:bg-green-200 border border-green-200';
                } else if (availability.total_slots > 0) {
                    dayElement.className += ' bg-yellow-100 text-yellow-800 cursor-not-allowed border border-yellow-200';
                    dayElement.disabled = true;
                } else {
                    dayElement.className += ' text-slate-400 cursor-not-allowed bg-slate-100';
                    dayElement.disabled = true;
                }
            } else {
                dayElement.className += ' bg-red-100 text-red-800 cursor-not-allowed border border-red-200';
                dayElement.disabled = true;
            }
            
            // Event listener para fechas válidas
            if (!dayElement.disabled) {
                dayElement.addEventListener('click', () => selectDate(dateKey));
            }
            
            calendar.appendChild(dayElement);
        }
    }
    
    async function selectDate(dateKey) {
        try {
            const response = await fetch(`${slotsUrl}?date=${dateKey}`);
            if (!response.ok) throw new Error('Error al cargar horarios');
            
            const slots = await response.json();
            displayTimeSlots(slots, dateKey);
        } catch (error) {
            console.error('Error loading slots:', error);
            showNotification('Error al cargar los horarios disponibles', 'error');
        }
    }
    
    function displayTimeSlots(slots, dateKey) {
        if (!timeSlotsList || !timeSlotsWrapper) return;
        
        const date = new Date(dateKey);
        const dateStr = date.toLocaleDateString('es-ES', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        if (selectedDateLabel) {
            selectedDateLabel.textContent = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
        }
        
        timeSlotsList.innerHTML = '';
        
        if (slots.length === 0) {
            if (noSlotsMessage) {
                noSlotsMessage.classList.remove('hidden');
            }
            timeSlotsWrapper.classList.add('hidden');
            return;
        }
        
        if (noSlotsMessage) {
            noSlotsMessage.classList.add('hidden');
        }
        
        slots.forEach(slot => {
            const slotButton = document.createElement('button');
            slotButton.type = 'button';
            slotButton.className = `p-4 rounded-2xl border-2 transition-all duration-200 text-left hover:scale-105 ${
                slot.available_capacity > 0 
                    ? 'border-green-200 bg-green-50 hover:border-green-300 hover:bg-green-100' 
                    : 'border-gray-200 bg-gray-50 cursor-not-allowed opacity-50'
            }`;
            
            slotButton.innerHTML = `
                <div class="font-semibold text-slate-900">${slot.start_time} - ${slot.end_time}</div>
                <div class="text-sm text-slate-600 mt-1">
                    Disponibles: ${slot.available_capacity}/${slot.capacity}
                </div>
            `;
            
            if (slot.available_capacity > 0) {
                slotButton.addEventListener('click', () => selectTimeSlot(slot, dateKey));
            } else {
                slotButton.disabled = true;
            }
            
            timeSlotsList.appendChild(slotButton);
        });
        
        timeSlotsWrapper.classList.remove('hidden');
    }
    
    function selectTimeSlot(slot, dateKey) {
        selectedSlotId = slot.id;
        
        // Actualizar inputs ocultos
        if (slotIdInput) slotIdInput.value = slot.id;
        if (selectedDateInput) selectedDateInput.value = dateKey;
        
        // Actualizar label de slot seleccionado
        if (selectedSlotLabel) {
            selectedSlotLabel.textContent = `Horario: ${slot.start_time} - ${slot.end_time}`;
        }
        
        // Actualizar estilos de botones
        document.querySelectorAll('#timeSlotsList button').forEach(btn => {
            btn.classList.remove('border-blue-300', 'bg-blue-100', 'ring-2', 'ring-blue-200');
            btn.classList.add('border-green-200', 'bg-green-50');
        });
        
        event.target.closest('button').classList.remove('border-green-200', 'bg-green-50');
        event.target.closest('button').classList.add('border-blue-300', 'bg-blue-100', 'ring-2', 'ring-blue-200');
        
        showNotification(`Horario seleccionado: ${slot.start_time} - ${slot.end_time}`, 'success');
    }
    
    function hideTimeSlots() {
        if (timeSlotsWrapper) timeSlotsWrapper.classList.add('hidden');
        if (selectedSlotLabel) selectedSlotLabel.textContent = '';
        selectedSlotId = null;
        
        if (slotIdInput) slotIdInput.value = '';
        if (selectedDateInput) selectedDateInput.value = '';
    }
}

// Exportar funciones principales
export {
    initializeTicketCreate,
    showNotification,
    closeExpandedImage
};

// Hacer funciones disponibles globalmente para compatibilidad con Blade
window.initializeTicketCreate = initializeTicketCreate;
window.showNotification = showNotification;
window.closeExpandedImage = closeExpandedImage;

console.log('✅ Tickets-create.js cargado correctamente');