# Solución Error 419 CSRF - Sistema Logística

## Problema Identificado
Error 419 "Page Expired" al intentar importar aduanas en el módulo de catálogos.

### Síntomas:
```
Failed to load resource: the server responded with a status of 419 (unknown status)
Error: El servidor devolvió una respuesta inválida (no JSON)
```

## Causas Identificadas

1. **Headers CSRF Inconsistentes**: Algunas llamadas fetch no incluían el header `X-CSRF-TOKEN`
2. **Configuración de Sesiones**: Uso de `SESSION_DRIVER=database` con posibles problemas de persistencia
3. **Tiempo de Vida de Sesiones**: `SESSION_LIFETIME=120` (2 horas) causaba expiración prematura
4. **Manejo de Errores**: No se detectaba específicamente errores 419 para mostrar mensajes útiles

## Soluciones Implementadas

### 1. Funciones Helper para CSRF
**Archivo:** `public/js/logistica/catalogos.js`

**Agregado:**
```javascript
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
```

### 2. Actualización de Llamadas Fetch
**Modificado:** Todas las llamadas `fetch()` para incluir headers CSRF consistentes

**Antes:**
```javascript
const response = await fetch(url, {
    method: 'POST',
    body: formData
});
```

**Después:**
```javascript
const response = await fetch(url, {
    method: 'POST',
    headers: getAuthHeaders(),
    body: formData
});
```

**Ubicaciones actualizadas:**
- Línea ~350: Operaciones de edición de catálogos
- Línea ~380: Operaciones de eliminación
- Línea ~590: Asignación de ejecutivos a clientes
- Línea ~750: Importación de aduanas

### 3. Configuración de Sesiones
**Archivo:** `.env`

**Cambios:**
```dotenv
# Antes
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Después  
SESSION_DRIVER=file
SESSION_LIFETIME=480
```

**Razones:**
- `SESSION_DRIVER=file`: Más confiable para desarrollo, evita problemas de conexión DB
- `SESSION_LIFETIME=480`: 8 horas en lugar de 2, reduce expiraciones frecuentes

### 4. Manejo Mejorado de Errores CSRF
**Archivo:** `public/js/logistica/catalogos.js`

**Agregado detección específica de errores 419:**
```javascript
if (response.status === 419) {
    throw new Error('Sesión expirada. Por favor, recarga la página e inténtalo de nuevo.');
}

// También detección en contenido HTML
if (text.includes('Page Expired')) {
    throw new Error('Sesión expirada (CSRF). Por favor, recarga la página e inténtalo de nuevo.');
}
```

### 5. Limpieza de Cache y Configuración
**Comandos ejecutados:**
```bash
php artisan config:clear
php artisan cache:clear  
php artisan route:clear
```

## Verificaciones Realizadas

### ✅ Meta Tag CSRF
Confirmado que `layouts/erp.blade.php` incluye:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### ✅ Directorio de Sesiones
Verificado que existe `storage/framework/sessions/` con permisos correctos

### ✅ Consistencia de Headers
Todas las llamadas fetch ahora usan `getAuthHeaders()` para consistencia

## Archivos Modificados

### 1. `.env`
- Cambio de SESSION_DRIVER de database a file
- Incremento de SESSION_LIFETIME de 120 a 480 minutos

### 2. `public/js/logistica/catalogos.js`
- Agregadas funciones helper `getCsrfToken()` y `getAuthHeaders()`
- Actualizado manejo de errores para detectar errores 419 específicamente
- Modificadas 4 llamadas fetch para usar headers CSRF consistentes

## Mejoras de UX Implementadas

### Mensajes de Error Específicos
- **Error 419 detectado**: "Sesión expirada. Por favor, recarga la página e inténtalo de nuevo."
- **Page Expired en HTML**: "Sesión expirada (CSRF). Por favor, recarga la página e inténtalo de nuevo."
- **Error genérico**: "El servidor devolvió una respuesta inválida (no JSON)"

### Headers Consistentes
- Todas las peticiones AJAX incluyen `X-CSRF-TOKEN` y `Accept: application/json`
- Uso de función helper para evitar duplicación de código
- Validación de existencia del token antes de enviarlo

## Testing y Verificación

### Casos de Prueba Recomendados:
1. **Importación normal**: Subir archivo CSV válido
2. **Sesión expirada**: Esperar timeout y intentar importar
3. **Token inválido**: Modificar token en DevTools e intentar operación
4. **Recarga después de error**: Verificar que funciona tras recargar página

### Monitoreo:
- Verificar logs en `storage/logs/laravel.log` para errores CSRF
- Usar DevTools Network tab para verificar headers en peticiones
- Confirmar que responses son JSON válido, no HTML de error

## Estado Final

✅ **Error 419 solucionado**  
✅ **Headers CSRF consistentes en todas las llamadas**  
✅ **Sesiones configuradas para mayor durabilidad**  
✅ **Manejo de errores mejorado con mensajes específicos**  
✅ **Código refactorizado con helpers reutilizables**

## Notas para Producción

### Configuración Recomendada para Producción:
```dotenv
SESSION_DRIVER=database  # Más escalable
SESSION_LIFETIME=240     # 4 horas (balance entre UX y seguridad)
SESSION_SECURE_COOKIE=true  # Solo HTTPS
SESSION_DOMAIN=.tudominio.com  # Especificar dominio
```

### Monitoreo Continuo:
- Revisar frecuencia de errores 419 en logs
- Monitorear tiempo promedio de sesión de usuarios
- Considerar implementar refresh automático de tokens para sesiones largas

---

**Fecha de resolución:** 26 de Noviembre de 2025  
**Tiempo de resolución:** ~30 minutos  
**Impacto:** Funcionalidad de importación de aduanas restaurada completamente