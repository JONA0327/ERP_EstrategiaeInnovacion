// === FUNCIONALIDAD DE IMPORTACIÓN DE CLIENTES ===

class ClientesImportManager {
    constructor() {
        this.initClientesImport();
        this.checkExistingClientes();
    }

    initClientesImport() {
        const uploadBtn = document.getElementById('clientes-upload-btn');
        const fileInput = document.getElementById('clientes-file-input');

        if (uploadBtn && fileInput) {
            uploadBtn.addEventListener('click', () => {
                fileInput.click();
            });

            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    this.uploadClientesFile(e.target.files[0]);
                }
            });
        }
    }

    async checkExistingClientes() {
        try {
            const response = await fetch('/logistica/clientes/check', {
                headers: getAuthHeaders()
            });
            
            const data = await response.json();
            
            if (data.success && data.exists) {
                this.hideImportSection();
            }
        } catch (error) {
            console.error('Error al verificar clientes:', error);
        }
    }

    async uploadClientesFile(file) {
        const progressContainer = document.getElementById('clientes-progress-container');
        const progressBar = document.getElementById('clientes-progress-bar');
        const progressText = document.getElementById('clientes-progress-text');
        const progressMessage = document.getElementById('clientes-progress-message');
        const uploadBtn = document.getElementById('clientes-upload-btn');

        try {
            // Mostrar barra de progreso
            progressContainer.classList.remove('hidden');
            uploadBtn.disabled = true;
            
            // Simular progreso de carga
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                
                progressBar.style.width = progress + '%';
                progressText.textContent = Math.round(progress) + '%';
                progressMessage.textContent = 'Procesando archivo...';
            }, 200);

            const formData = new FormData();
            formData.append('clientes_file', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            const response = await fetch('/logistica/clientes/import', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: formData
            });

            const data = await response.json();

            // Completar progreso
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
            progressText.textContent = '100%';

            if (data.success) {
                progressMessage.textContent = 'Importación completada exitosamente';
                
                // Mostrar resultados
                this.showImportResults(data.resultados);
                
                // Ocultar sección de importación después de 3 segundos
                setTimeout(() => {
                    this.hideImportSection();
                    window.location.reload();
                }, 3000);
                
            } else {
                throw new Error(data.message || 'Error en la importación');
            }

        } catch (error) {
            console.error('Error en importación:', error);
            progressMessage.textContent = 'Error: ' + error.message;
            progressBar.classList.add('bg-red-600');
            progressBar.classList.remove('bg-green-600');
        } finally {
            uploadBtn.disabled = false;
            document.getElementById('clientes-file-input').value = '';
        }
    }

    showImportResults(resultados) {
        let mensaje = `✅ Importación completada:\n`;
        mensaje += `• ${resultados.procesados} registros procesados\n`;
        mensaje += `• ${resultados.creados} clientes nuevos creados\n`;
        mensaje += `• ${resultados.actualizados} clientes actualizados\n`;
        
        if (resultados.ejecutivos_no_encontrados.length > 0) {
            mensaje += `• ${resultados.ejecutivos_no_encontrados.length} ejecutivos no encontrados\n`;
        }
        
        if (resultados.errores.length > 0) {
            mensaje += `• ${resultados.errores.length} errores encontrados`;
        }
        
        showAlert(mensaje, 'success');
        
        // Log detallado para el administrador
        if (resultados.ejecutivos_no_encontrados.length > 0) {
            console.log('Ejecutivos no encontrados:', resultados.ejecutivos_no_encontrados);
        }
        if (resultados.errores.length > 0) {
            console.log('Errores de importación:', resultados.errores);
        }
    }

    hideImportSection() {
        const importSection = document.getElementById('clientes-import-section');
        if (importSection) {
            importSection.style.display = 'none';
        }
    }
}

// Inicializar importación de clientes cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    new ClientesImportManager();
});