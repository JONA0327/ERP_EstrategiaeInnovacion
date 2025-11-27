# Documentación: Sistema de Reportes Word con PhpWord

## Descripción General

Se ha implementado exitosamente un sistema completo para generar reportes en formato Word (.docx) usando la librería PhpWord. El sistema permite generar reportes individuales y múltiples de las operaciones logísticas.

## Componentes Implementados

### 1. Servicio WordDocumentService
**Archivo:** `app/Services/WordDocumentService.php`

**Funciones principales:**
- `crearReporteOperacion()`: Genera reporte detallado de una operación específica
- `crearReporteMultiple()`: Genera reporte con múltiples operaciones
- `guardar()`: Guarda el documento en storage/app/public/reportes/
- `descargar()`: Descarga directamente el archivo al navegador

**Características del reporte:**
- Encabezado corporativo con título del documento
- Sección de información básica (tabla con datos principales)
- Sección de detalles de operación (fechas, agentes, transportes)
- Sección de post-operaciones (si aplica)
- Sección de historial de cambios
- Formato profesional con estilos y colores corporativos

### 2. Controlador con Métodos de Reportes
**Archivo:** `app/Http/Controllers/Logistica/OperacionLogisticaController.php`

**Métodos agregados:**
- `generarReporteWord($id)`: Genera y descarga reporte individual
- `generarReporteMultiple(Request $request)`: Genera reporte con filtros
- `guardarReporteWord($id)`: Guarda reporte en servidor (para uso posterior)

### 3. Rutas de Reportes
**Archivo:** `routes/web.php`

**Rutas agregadas:**
```php
Route::get('/logistica/operaciones/{id}/reporte-word', 'generarReporteWord')
Route::post('/logistica/operaciones/reporte-multiple-word', 'generarReporteMultiple')
Route::get('/logistica/operaciones/{id}/guardar-reporte-word', 'guardarReporteWord')
```

### 4. Interfaz de Usuario
**Archivo:** `resources/views/Logistica/matriz-seguimiento.blade.php`

**Elementos agregados:**
- Botón "Generar Reportes Word" en la barra de herramientas principal
- Botón individual 📄 en cada fila de operación para reporte específico
- Modal completo para generar reportes con filtros
- Opciones de filtrado por cliente, status, fechas
- Botón para reporte completo (todas las operaciones)

### 5. JavaScript para Interacciones
**Archivo:** `public/js/logistica/matriz-seguimiento.js`

**Funciones agregadas:**
- `abrirModalReportes()`: Abre modal de reportes
- `cerrarModalReportes()`: Cierra modal y resetea formulario
- `generarReporteIndividual(id)`: Descarga reporte de operación específica
- `generarReporteTodas()`: Genera reporte de todas las operaciones
- Manejo de formulario con filtros para reportes múltiples
- Indicadores de carga durante generación

## Características Técnicas

### Configuración de PhpWord
- Idioma: Español (es-ES)
- Fuente por defecto: Arial, 11pt
- Márgenes: 2.5cm arriba/abajo, 2.0cm izquierda/derecha
- Formato de salida: Word 2007+ (.docx)

### Contenido de los Reportes

#### Reporte Individual
1. **Encabezado:** "REPORTE DE OPERACIÓN LOGÍSTICA" (centrado, 16pt, negrita)
2. **Información Básica:** Tabla con datos principales de la operación
3. **Detalles:** Tabla con información específica (pedimentos, fechas, agentes)
4. **Post-Operaciones:** Lista de post-operaciones asignadas (si las hay)
5. **Historial:** Últimos 10 cambios en la operación

#### Reporte Múltiple
1. **Encabezado:** "REPORTE DE OPERACIONES LOGÍSTICAS" (personalizable)
2. **Resumen:** Total de operaciones incluidas
3. **Tabla Resumen:** Lista compacta con datos principales de cada operación
4. **Limitación:** Máximo 100 operaciones para evitar archivos muy grandes

### Filtros Disponibles
- **Cliente:** Filtrar por cliente específico
- **Status:** Done, En Proceso, Fuera Métrica
- **Rango de fechas:** Fecha desde y hasta
- **Ejecutivo:** Por ejecutivo asignado (implementable)

### Almacenamiento y Descarga
- **Directorio:** `storage/app/public/reportes/`
- **Enlace público:** `public/storage/reportes/`
- **Nomenclatura:** `reporte_operacion_{numero}_{fecha-hora}.docx`
- **Descarga directa:** Los reportes se descargan inmediatamente al navegador
- **Almacenamiento opcional:** Usar `guardarReporteWord()` para guardar en servidor

## Uso del Sistema

### 1. Reporte Individual
1. En la tabla de operaciones, hacer clic en el botón 📄 de la operación deseada
2. El reporte se genera automáticamente y se descarga
3. El archivo incluye toda la información disponible de esa operación

### 2. Reporte Múltiple con Filtros
1. Hacer clic en "Generar Reportes Word" en la barra de herramientas
2. Seleccionar los filtros deseados en el modal:
   - Cliente específico o todos
   - Status específico o todos
   - Rango de fechas
3. Hacer clic en "Generar Reporte Múltiple"
4. El archivo se descarga con las operaciones filtradas

### 3. Reporte Completo
1. Abrir el modal de reportes
2. Hacer clic en "Generar Reporte Completo"
3. Se incluyen las 100 operaciones más recientes
4. Descarga automática del archivo

## Características de Seguridad y Rendimiento

### Seguridad
- Todas las rutas están protegidas por el middleware de autenticación
- Restricción al área de logística (`area.logistica`)
- Validación de IDs de operaciones
- Token CSRF en formularios

### Rendimiento
- Límite de 100 operaciones en reportes múltiples
- Carga lazy de relaciones (with()) para optimizar consultas
- Generación en memoria para descarga directa
- Almacenamiento opcional para reutilización

### Manejo de Errores
- Try-catch en todos los métodos del controlador
- Logging de errores para debugging
- Mensajes de error amigables al usuario
- Validación de datos antes de procesamiento

## Archivos de Configuración

### Dependencias en composer.json
```json
"phpoffice/phpword": "^1.4"
```

### Estructura de Directorios
```
storage/
└── app/
    └── public/
        └── reportes/           <- Reportes generados
            └── *.docx
public/
└── storage/                   <- Enlace simbólico
    └── reportes/
        └── *.docx
```

## Personalización

### Modificar Estilos
Editar `WordDocumentService.php`:
- Cambiar fuentes, tamaños, colores
- Modificar márgenes y espaciado
- Personalizar encabezados y pie de página

### Agregar Campos
1. Modificar métodos `agregarSeccion*()` en el servicio
2. Actualizar queries en el controlador para incluir nuevos campos
3. Agregar filtros en el modal si es necesario

### Personalizar Contenido
- Modificar títulos y textos en el servicio
- Cambiar ordenamiento de secciones
- Agregar gráficos o imágenes (soportado por PhpWord)

## Troubleshooting

### Problemas Comunes
1. **Error "Class not found"**: Ejecutar `composer dump-autoload`
2. **Archivos no descargan**: Verificar permisos en directorio storage
3. **Error de memoria**: Reducir límite de operaciones en reportes múltiples
4. **Formato incorrecto**: Verificar versión de PhpWord instalada

### Logs
Los errores se registran en `storage/logs/laravel.log` con el prefijo:
- "Error generando reporte Word"
- "Error generando reporte múltiple Word"
- "Error guardando reporte Word"

## Futuras Mejoras Sugeridas

### Funcionalidades
- [ ] Plantillas personalizables por cliente
- [ ] Gráficos y estadísticas en los reportes
- [ ] Programación de reportes automáticos
- [ ] Envío por email de reportes
- [ ] Historial de reportes generados
- [ ] Reportes en otros formatos (PDF, Excel)

### Rendimiento
- [ ] Generación asíncrona para reportes grandes
- [ ] Cache de reportes frecuentes
- [ ] Compresión de archivos
- [ ] Limpieza automática de archivos antiguos

### Interfaz
- [ ] Vista previa de reportes
- [ ] Progreso de generación en tiempo real
- [ ] Búsqueda en reportes generados
- [ ] Favoritos de configuración de filtros

---

**Fecha de implementación:** Noviembre 2025  
**Versión PhpWord:** 1.4.0  
**Estado:** Completamente funcional y listo para producción