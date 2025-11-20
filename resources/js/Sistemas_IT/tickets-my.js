// Funcionalidad para la página "Mis Tickets"
// JavaScript para manejo de modales, cancelación de tickets e imágenes

document.addEventListener('DOMContentLoaded', function() {
    initializeMyTickets();
});

function initializeMyTickets() {
    console.log('🎫 Inicializando funcionalidad de Mis Tickets');
    
    // Inicializar funcionalidad de imágenes
    initializeImageModal();
    
    // Inicializar funcionalidad de cancelación de tickets
    initializeCancelTicketModal();
    
    // Inicializar notificaciones de updates
    initializeUpdateNotifications();
}

// ===== MODAL DE IMÁGENES =====
function initializeImageModal() {
    // Modal de imagen expandida
    let imageModal = document.getElementById('imageModal');
    
    // Crear modal si no existe
    if (!imageModal) {
        imageModal = createImageModal();
    }
    
    // Evento para cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && imageModal && !imageModal.classList.contains('hidden')) {
            closeImageModal();
        }
    });
    
    // Evento para cerrar modal clickeando fuera
    if (imageModal) {
        imageModal.addEventListener('click', function(e) {
            if (e.target === imageModal) {
                closeImageModal();
            }
        });
    }
}

function createImageModal() {
    const modal = document.createElement('div');
    modal.id = 'imageModal';
    modal.className = 'fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm p-4';
    modal.innerHTML = `
        <div class="relative max-w-[95vw] max-h-[95vh] flex items-center justify-center">
            <button onclick="closeImageModal()" 
                    class="absolute -top-4 -right-4 z-10 h-10 w-10 rounded-full bg-white/90 text-gray-800 shadow-lg hover:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                <svg class="h-6 w-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <img id="modalImage" src="" alt="" 
                 class="max-w-[90vw] max-h-[85vh] object-contain rounded-lg shadow-2xl bg-white">
            <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white p-3 rounded-b-lg">
                <p id="modalImageName" class="text-sm font-medium text-center"></p>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    return modal;
}

// Función global para expandir imagen (llamada desde onclick en Blade)
window.expandImage = function(img) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const modalImageName = document.getElementById('modalImageName');
    
    if (modal && modalImage) {
        modalImage.src = img.src;
        modalImage.alt = img.alt;
        
        if (modalImageName) {
            modalImageName.textContent = img.alt || 'Imagen del ticket';
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
};

window.closeImageModal = function() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
};

// ===== CANCELACIÓN DE TICKETS =====
function initializeCancelTicketModal() {
    const cancelModal = document.getElementById('cancelModal');
    const cancelModalClose = document.getElementById('cancelModalClose');
    const cancelModalConfirm = document.getElementById('cancelModalConfirm');
    const cancelModalMessage = document.getElementById('cancelModalMessage');
    
    let currentTicketId = null;
    let currentTicketFolio = null;
    
    // Manejar botones de cancelar ticket
    document.addEventListener('click', function(e) {
        const cancelButton = e.target.closest('[data-cancel-ticket]');
        if (cancelButton) {
            e.preventDefault();
            
            // Obtener datos del ticket
            currentTicketId = cancelButton.dataset.ticketId;
            currentTicketFolio = cancelButton.dataset.ticketFolio;
            
            if (cancelModalMessage) {
                cancelModalMessage.textContent = `¿Estás seguro que deseas cancelar el ticket ${currentTicketFolio}? Esta acción no se puede deshacer.`;
            }
            
            if (cancelModal) {
                cancelModal.classList.remove('hidden');
                cancelModal.classList.add('flex');
            }
        }
    });
    
    // Cerrar modal
    if (cancelModalClose) {
        cancelModalClose.addEventListener('click', closeCancelModal);
    }
    
    // Confirmar cancelación
    if (cancelModalConfirm) {
        cancelModalConfirm.addEventListener('click', function() {
            if (currentTicketId) {
                cancelTicket(currentTicketId);
            }
        });
    }
    
    // Cerrar con ESC o click fuera
    if (cancelModal) {
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !cancelModal.classList.contains('hidden')) {
                closeCancelModal();
            }
        });
        
        cancelModal.addEventListener('click', function(e) {
            if (e.target === cancelModal) {
                closeCancelModal();
            }
        });
    }
    
    function closeCancelModal() {
        if (cancelModal) {
            cancelModal.classList.add('hidden');
            cancelModal.classList.remove('flex');
        }
        currentTicketId = null;
        currentTicketFolio = null;
    }
    
    async function cancelTicket(ticketId) {
        try {
            // Mostrar loading
            if (cancelModalConfirm) {
                cancelModalConfirm.disabled = true;
                cancelModalConfirm.textContent = 'Cancelando...';
            }
            
            const response = await fetch(`/ticket/${ticketId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
            
            if (response.ok) {
                // Cerrar modal
                closeCancelModal();
                
                // Mostrar mensaje de éxito
                showNotification('Ticket cancelado exitosamente', 'success');
                
                // Recargar página después de un breve delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error('Error al cancelar el ticket');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al cancelar el ticket. Inténtalo de nuevo.', 'error');
        } finally {
            // Restaurar botón
            if (cancelModalConfirm) {
                cancelModalConfirm.disabled = false;
                cancelModalConfirm.textContent = 'Aceptar';
            }
        }
    }
}

// ===== NOTIFICACIONES DE UPDATES =====
function initializeUpdateNotifications() {
    // Manejar botones de "Marcar como revisado"
    document.addEventListener('click', function(e) {
        const acknowledgeButton = e.target.closest('[data-acknowledge-update]');
        if (acknowledgeButton) {
            e.preventDefault();
            
            const ticketId = acknowledgeButton.dataset.ticketId;
            if (ticketId) {
                acknowledgeUpdate(ticketId, acknowledgeButton);
            }
        }
    });
    
    // Manejar botón de "Marcar todos como revisados"
    const acknowledgeAllButton = document.querySelector('[data-acknowledge-all]');
    if (acknowledgeAllButton) {
        acknowledgeAllButton.addEventListener('click', function(e) {
            e.preventDefault();
            acknowledgeAllUpdates(this);
        });
    }
    
    async function acknowledgeUpdate(ticketId, button) {
        try {
            // Mostrar loading
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Marcando...';
            
            const response = await fetch(`/ticket/${ticketId}/acknowledge-update`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
            
            if (response.ok) {
                // Remover indicador de actualización
                const ticketCard = button.closest('[data-ticket-card]');
                if (ticketCard) {
                    const updateIndicator = ticketCard.querySelector('[data-update-indicator]');
                    if (updateIndicator) {
                        updateIndicator.remove();
                    }
                }
                
                button.remove();
                showNotification('Actualización marcada como revisada', 'success');
                
                // Actualizar contador de notificaciones si existe
                updateNotificationCount();
            } else {
                throw new Error('Error al marcar como revisado');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al marcar como revisado', 'error');
            
            // Restaurar botón
            button.disabled = false;
            button.textContent = originalText;
        }
    }
    
    async function acknowledgeAllUpdates(button) {
        try {
            // Mostrar loading
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Marcando todos...';
            
            const response = await fetch('/tickets/acknowledge-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
            
            if (response.ok) {
                showNotification('Todas las actualizaciones marcadas como revisadas', 'success');
                
                // Recargar página para reflejar cambios
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error('Error al marcar todos como revisados');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al marcar todos como revisados', 'error');
            
            // Restaurar botón
            button.disabled = false;
            button.textContent = originalText;
        }
    }
    
    function updateNotificationCount() {
        // Contar tickets con updates pendientes
        const updateIndicators = document.querySelectorAll('[data-update-indicator]');
        const count = updateIndicators.length;
        
        // Actualizar badge en navegación si existe
        const notificationBadge = document.querySelector('[data-notification-count]');
        if (notificationBadge) {
            if (count > 0) {
                notificationBadge.textContent = count;
                notificationBadge.classList.remove('hidden');
            } else {
                notificationBadge.classList.add('hidden');
            }
        }
    }
}

// ===== UTILIDADES =====
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 
                   type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    
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

// Exportar funciones principales
export {
    initializeMyTickets,
    expandImage,
    closeImageModal,
    showNotification
};

// Hacer funciones disponibles globalmente para compatibilidad con Blade
window.initializeMyTickets = initializeMyTickets;
window.expandImage = expandImage;
window.closeImageModal = closeImageModal;
window.showNotification = showNotification;

console.log('✅ Tickets-my.js cargado correctamente');