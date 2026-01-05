<?php

use App\Http\Controllers\Sistemas_IT\AdminController;
// use App\Http\Controllers\ArchivoProblemasController; // removed feature
use App\Http\Controllers\Auth\AuthController;
// Removed features: DiscoEnUso, Inventario, Prestamo controllers
use App\Http\Controllers\Sistemas_IT\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Sistemas_IT\TicketController;
use App\Http\Controllers\Sistemas_IT\MaintenanceController;
use App\Http\Controllers\JerarquiaController;
use App\Http\Controllers\Users\UsersController;
use App\Http\Controllers\RH\ExpedienteController;
use App\Http\Controllers\RH\RelojChecadorImportController; // Nuevo flujo con barra de progreso
use App\Http\Controllers\Logistica\OperacionLogisticaController;
use App\Http\Controllers\Logistica\PedimentoController;
use App\Http\Controllers\Logistica\LogisticaCorreoCCController;
use App\Http\Controllers\EvaluacionController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Ruta pública para consulta de operaciones logísticas (sin autenticación)
Route::get('/logistica/consulta-publica', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'consultaPublica'])->name('logistica.consulta-publica');
Route::get('/logistica/consulta-publica/buscar', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'buscarOperacionPublica'])->name('logistica.consulta-publica.buscar');

// Áreas adicionales (requieren autenticación)
// Áreas bajo autenticación y control de área
Route::middleware(['auth','area.rh'])->group(function () {
    Route::get('/recursos-humanos', function () { return view('Recursos_Humanos.index'); })->name('recursos-humanos.index');
    // Unificamos la vista principal del reloj checador para usar nueva importación con progreso
    Route::get('/recursos-humanos/reloj-checador', [RelojChecadorImportController::class, 'index'])->name('rh.reloj.index');
    Route::post('/recursos-humanos/reloj-checador/iniciar', [RelojChecadorImportController::class, 'start'])->name('rh.reloj.import.start');
    Route::get('/recursos-humanos/reloj-checador/progreso/{key}', [RelojChecadorImportController::class, 'progress'])->name('rh.reloj.import.progress');
    Route::prefix('recursos-humanos/expedientes')->name('rh.expedientes.')->group(function () {
        Route::get('/', [ExpedienteController::class, 'index'])->name('index');
        Route::post('/refresh', [ExpedienteController::class, 'refresh'])->name('refresh');
        Route::get('/{empleado}', [ExpedienteController::class, 'show'])->name('show');
        Route::get('/{empleado}/editar', [ExpedienteController::class, 'edit'])->name('edit');
        Route::put('/{empleado}', [ExpedienteController::class, 'update'])->name('update');
        Route::delete('/{empleado}', [ExpedienteController::class, 'destroy'])->name('destroy');
    });
    Route::get('/recursos-humanos/evaluacion', [EvaluacionController::class, 'index'])->name('rh.evaluacion.index');
    Route::get('/recursos-humanos/evaluacion/{id}', [EvaluacionController::class, 'show'])->name('rh.evaluacion.show');

    Route::post('/recursos-humanos/evaluacion', [EvaluacionController::class, 'store'])->name('rh.evaluacion.store');
    Route::put('/recursos-humanos/evaluacion/{id}', [EvaluacionController::class, 'update'])->name('rh.evaluacion.update');

    Route::get('/jerarquia', [JerarquiaController::class, 'index'])->name('rh.jerarquia.index');
    Route::patch('/jerarquia/{id}', [JerarquiaController::class, 'update'])->name('rh.jerarquia.update');
    });

Route::middleware(['auth','area.logistica'])->group(function () {
    Route::get('/logistica', function () { return view('Logistica.index'); })->name('logistica.index');
    Route::get('/logistica/matriz-seguimiento', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'index'])->name('logistica.matriz-seguimiento');
    // Reportes: página y exportación CSV
    Route::get('/logistica/reportes', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'reportes'])->name('logistica.reportes');
    Route::get('/logistica/reportes/export', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'exportCSV'])->name('logistica.reportes.export');
    Route::get('/logistica/reportes/pedimentos/export', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'exportPedimentos'])->name('logistica.reportes.pedimentos.export');
    Route::get('/logistica/reportes/resumen/export', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'exportResumenEjecutivo'])->name('logistica.reportes.resumen.export');
    Route::get('/logistica/reportes/cliente', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'getOperacionesPorCliente'])->name('logistica.reportes.cliente');
    Route::get('/logistica/catalogos', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'catalogos'])->name('logistica.catalogos');
    Route::get('/test-routes', function () { return view('test_routes'); });
    Route::get('/logistica/operaciones/create', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'create'])->name('logistica.operaciones.create');
    Route::post('/logistica/operaciones', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'store'])->name('logistica.operaciones.store');
    Route::put('/logistica/operaciones/{id}', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'update'])->name('logistica.operaciones.update');
    Route::get('/logistica/transportes-por-tipo', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'getTransportesPorTipo'])->name('logistica.transportes-por-tipo');

    // Rutas para CRUD de clientes
    Route::post('/logistica/clientes', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'storeCliente'])->name('logistica.clientes.store');
    // Ruta específica para eliminar todos los clientes (DEBE ir antes que la ruta con {id})
    Route::delete('/logistica/clientes/all', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'deleteAllClientes'])->middleware(['auth', 'admin']);
    Route::put('/logistica/clientes/{id}', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'updateCliente'])->name('logistica.clientes.update');
    Route::delete('/logistica/clientes/{id}', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'destroyCliente'])->name('logistica.clientes.destroy');

    // Rutas para CRUD de agentes
    Route::post('/logistica/agentes', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'storeAgente'])->name('logistica.agentes.store');
    Route::put('/logistica/agentes/{id}', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'updateAgente'])->name('logistica.agentes.update');
    Route::delete('/logistica/agentes/{id}', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'destroyAgente'])->name('logistica.agentes.destroy');

    // Rutas para CRUD de transportes
    Route::post('/logistica/transportes', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'storeTransporte'])->name('logistica.transportes.store');
    Route::put('/logistica/transportes/{id}', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'updateTransporte'])->name('logistica.transportes.update');
    Route::delete('/logistica/transportes/{id}', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'destroyTransporte'])->name('logistica.transportes.destroy');

    // Rutas para catálogo de Incoterms
    Route::get('/logistica/incoterms', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'getIncoterms']);

    // Rutas para importación de Excel (oculta - solo admin)
    Route::get('/logistica/importar-excel', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'vistaImportarExcel'])->name('logistica.importar-excel');
    Route::post('/logistica/importar-excel', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'importarExcel'])->name('logistica.importar-excel.procesar');
    Route::get('/logistica/mapeo-columnas', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'obtenerMapeoColumnas'])->name('logistica.mapeo-columnas');

    // Rutas para asignación de clientes a ejecutivos
    Route::post('/logistica/clientes/asignar-ejecutivo', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'asignarClientesEjecutivo'])->name('logistica.clientes.asignar-ejecutivo');
    Route::get('/logistica/clientes/por-ejecutivo', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'getClientesPorEjecutivo'])->name('logistica.clientes.por-ejecutivo');

    // Rutas para historial y eliminación de operaciones
    Route::get('/logistica/operaciones/{id}/historial', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'obtenerHistorial']);
    Route::put('/logistica/operaciones/{id}/status', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'updateStatus']);
    Route::delete('/logistica/operaciones/{id}', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'destroy']);

    // Rutas para Post-Operaciones por Operación
    Route::get('/logistica/operaciones/{id}/post-operaciones', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'getPostoperacionesByoperacion']);
    Route::post('/logistica/post-operaciones', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'storePostOperacion']);
    Route::put('/logistica/post-operaciones/{id}/estado', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'updatePostOperacionEstado']);
    Route::put('/logistica/operaciones/{id}/post-operaciones/actualizar-estados', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'actualizarEstadosPostOperaciones']);
    Route::delete('/logistica/post-operaciones/{id}', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'destroyPostOperacion']);

    // Rutas para Pedimentos
    Route::get('/logistica/pedimentos', [PedimentoController::class, 'index'])->name('logistica.pedimentos.index');
    Route::post('/logistica/pedimentos/marcar-pagados', [PedimentoController::class, 'marcarPagados'])->name('logistica.pedimentos.marcar-pagados');
    Route::post('/logistica/pedimentos/actualizar', [PedimentoController::class, 'actualizarPedimento'])->name('logistica.pedimentos.actualizar');
    Route::get('/logistica/pedimentos/check', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'checkPedimentos']);
    Route::get('/logistica/pedimentos/claves', [\App\Http\Controllers\Logistica\ReporteController::class, 'getClaves'])->name('logistica.pedimentos.claves');
    Route::get('/logistica/pedimentos/categorias', [\App\Http\Controllers\Logistica\PedimentoImportController::class, 'getCategorias']);
    Route::get('/logistica/pedimentos/subcategorias', [\App\Http\Controllers\Logistica\PedimentoImportController::class, 'getSubcategorias']);
    Route::post('/logistica/pedimentos/import', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'importPedimentos'])->name('logistica.pedimentos.import.legacy');
    Route::get('/logistica/pedimentos/clave/{clave}', [PedimentoController::class, 'getPedimentosPorClave'])->name('logistica.pedimentos.por-clave');
    // Rutas con parámetro {id} deben ir al final
    Route::get('/logistica/pedimentos/{id}', [PedimentoController::class, 'show'])->name('logistica.pedimentos.show');
    Route::put('/logistica/pedimentos/{id}/estado-pago', [PedimentoController::class, 'updateEstadoPago'])->name('logistica.pedimentos.update-estado');
    Route::delete('/logistica/pedimentos/{id}', [PedimentoController::class, 'destroy'])->name('logistica.pedimentos.destroy');
    
    Route::get('/api/monedas', [PedimentoController::class, 'getMonedas'])->name('api.monedas');
    
    // Rutas para reportes separados
    Route::get('/reportes/pedimentos', [\App\Http\Controllers\Logistica\ReporteController::class, 'index'])->name('reportes.pedimentos.index');
    Route::get('/reportes/matriz/excel', [\App\Http\Controllers\Logistica\ReporteController::class, 'generarExcelMatriz'])->name('reportes.matriz.excel');
    Route::get('/reportes/pedimentos/excel', [\App\Http\Controllers\Logistica\ReporteController::class, 'generarExcelPedimentos'])->name('reportes.pedimentos.excel');
    Route::get('/logistica/clientes', [\App\Http\Controllers\Logistica\ReporteController::class, 'getClientes'])->name('logistica.clientes');

    // Rutas para Post-Operaciones Globales
    Route::get('/logistica/post-operaciones-globales', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'indexPostOperacionesGlobales']);
    Route::post('/logistica/post-operaciones-globales', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'storePostOperacionGlobal']);
    Route::delete('/logistica/post-operaciones-globales/{id}', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'destroyPostOperacionGlobal']);

    // Ruta para recalcular status
    Route::post('/logistica/operaciones/recalcular-status', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'recalcularStatus']);

    // Rutas para Comentarios
    Route::get('/logistica/operaciones/{id}/comentarios', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'getComentariosByOperacion']);
    Route::get('/logistica/operaciones/{id}/comentarios-historial', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'obtenerHistorialComentarios']);
    Route::post('/logistica/comentarios', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'storeComentario']);
    Route::put('/logistica/comentarios/{id}', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'updateComentario']);
    // Nuevas rutas para observaciones del historial
    Route::get('/logistica/operaciones/{id}/observaciones-historial', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'obtenerHistorialObservaciones']);
    Route::put('/logistica/operaciones/{id}/observaciones', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'updateObservacionesHistorial']);

    // Rutas para Aduanas
    Route::get('/logistica/aduanas', [\App\Http\Controllers\Logistica\AduanaImportController::class, 'index']);
    Route::post('/logistica/aduanas', [\App\Http\Controllers\Logistica\AduanaImportController::class, 'store']);
    Route::put('/logistica/aduanas/{id}', [\App\Http\Controllers\Logistica\AduanaImportController::class, 'update']);
    Route::post('/logistica/aduanas/import', [\App\Http\Controllers\Logistica\AduanaImportController::class, 'import']);
    Route::delete('/logistica/aduanas/{id}', [\App\Http\Controllers\Logistica\AduanaImportController::class, 'destroy']);
    Route::delete('/logistica/aduanas', [\App\Http\Controllers\Logistica\AduanaImportController::class, 'clear']);

    // Rutas para verificar existencia de datos
    Route::get('/logistica/aduanas/check', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'checkAduanas']);

    // Rutas para búsqueda de empleados (solo admin)
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/logistica/empleados/search', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'searchEmployees']);
        Route::post('/logistica/empleados/add-ejecutivo', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'addEjecutivo']);
        
        // Rutas para Campos Personalizados (solo admin)
        Route::get('/logistica/campos-personalizados', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'index']);
        Route::get('/logistica/campos-personalizados/tipos', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'tipos']);
        Route::get('/logistica/campos-personalizados/ejecutivos', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'ejecutivos']);
        Route::post('/logistica/campos-personalizados', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'store']);
        Route::put('/logistica/campos-personalizados/{id}', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'update']);
        Route::delete('/logistica/campos-personalizados/{id}', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'destroy']);
        
        // Rutas para Configuración de Columnas por Ejecutivo (solo admin)
        Route::get('/logistica/columnas-config', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'getColumnasConfig']);
        Route::post('/logistica/columnas-config', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'guardarColumnasConfig']);
        Route::get('/logistica/columnas-config/ejecutivo/{empleadoId}', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'getColumnasEjecutivo']);
        Route::post('/logistica/columnas-config/idioma', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'guardarIdiomaEjecutivo']);
        Route::post('/logistica/columnas-config/orden', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'guardarOrdenColumnas']);
        Route::get('/logistica/columnas-config/ordenadas/{empleadoId}', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'getColumnasOrdenadas']);
    });

    // Rutas para Campos Personalizados (acceso general para obtener valores)
    Route::get('/logistica/campos-personalizados/ejecutivo/{ejecutivoId}', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'camposPorEjecutivo']);
    Route::get('/logistica/campos-personalizados/operacion/{operacionId}/valores', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'valoresPorOperacion']);
    Route::post('/logistica/campos-personalizados/valor', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'guardarValor']);
    Route::get('/logistica/campos-adicionales', [\App\Http\Controllers\Logistica\CampoPersonalizadoController::class, 'camposAdicionales']);

    // Rutas para Reportes Word
    Route::get('/logistica/operaciones/{id}/reporte-word', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'generarReporteWord'])->name('logistica.operaciones.reporte-word');
    Route::post('/logistica/operaciones/reporte-multiple-word', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'generarReporteMultiple'])->name('logistica.operaciones.reporte-multiple-word');
    Route::get('/logistica/operaciones/{id}/guardar-reporte-word', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'guardarReporteWord'])->name('logistica.operaciones.guardar-reporte-word');

    // Ruta para envío de reportes por correo con CC
    Route::post('/logistica/reportes/enviar-correo', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'enviarReporte'])->name('logistica.reportes.enviar-correo');

    // Ruta para exportar Excel profesional
    Route::get('/logistica/reportes/export-excel', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'exportExcelProfesional'])->name('logistica.reportes.export-excel');

    // Ruta para limpiar archivos temporales
    Route::delete('/logistica/reportes/limpiar-temporales', [\App\Http\Controllers\Logistica\OperacionLogisticaController::class, 'limpiarArchivosTemporales'])->name('logistica.reportes.limpiar-temporales');

    // Rutas para Catálogo de Correos CC
    Route::prefix('logistica/correos-cc')->name('logistica.correos-cc.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Logistica\LogisticaCorreoCCController::class, 'index'])->name('index');
        Route::get('/api', [\App\Http\Controllers\Logistica\LogisticaCorreoCCController::class, 'api'])->name('api');
        Route::get('/crear', [\App\Http\Controllers\Logistica\LogisticaCorreoCCController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Logistica\LogisticaCorreoCCController::class, 'store'])->name('store');
        Route::get('/{correoCC}/editar', [\App\Http\Controllers\Logistica\LogisticaCorreoCCController::class, 'edit'])->name('edit');
        Route::put('/{correoCC}', [\App\Http\Controllers\Logistica\LogisticaCorreoCCController::class, 'update'])->name('update');
        Route::delete('/{correoCC}', [\App\Http\Controllers\Logistica\LogisticaCorreoCCController::class, 'destroy'])->name('destroy');
        Route::patch('/{correoCC}/toggle-activo', [\App\Http\Controllers\Logistica\LogisticaCorreoCCController::class, 'toggleActivo'])->name('toggle-activo');

        // API endpoints para obtener correos CC
        Route::get('/api', [\App\Http\Controllers\Logistica\LogisticaCorreoCCController::class, 'api'])->name('api');
        Route::get('/api/por-tipo', [\App\Http\Controllers\Logistica\LogisticaCorreoCCController::class, 'apiPorTipo'])->name('api.por-tipo');
        Route::get('/api/todos-activos', [\App\Http\Controllers\Logistica\LogisticaCorreoCCController::class, 'apiTodosActivos'])->name('api.todos-activos');
    });
});

// Rutas de autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Rutas protegidas de tickets (requieren autenticación)
Route::middleware('auth')->group(function () {
    Route::get('/ticket/create/{tipo}', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/ticket', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/mis-tickets', [TicketController::class, 'misTickets'])->name('tickets.mis-tickets');
    Route::delete('/ticket/{id}', [TicketController::class, 'destroy'])->name('tickets.destroy');
    Route::get('/ticket/{id}/can-cancel', [TicketController::class, 'canCancel'])->name('tickets.can-cancel');
    Route::post('/ticket/{id}/acknowledge-update', [TicketController::class, 'acknowledgeUpdate'])->name('tickets.acknowledge');
    Route::post('/tickets/acknowledge-all', [TicketController::class, 'acknowledgeAllUpdates'])->name('tickets.acknowledge-all');

    Route::get('/maintenance/availability', [MaintenanceController::class, 'availability'])->name('maintenance.availability');
    Route::get('/maintenance/slots', [MaintenanceController::class, 'slots'])->name('maintenance.slots');

});

// Archivo de problemas: rutas eliminadas

// Rutas de administración
Route::middleware(['auth', 'verified', 'sistemas_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::post('/tickets/{ticket}/change-maintenance-date', [TicketController::class, 'changeMaintenanceDate'])->name('tickets.change-maintenance-date');
    Route::get('/maintenance-slots/available', [TicketController::class, 'getAvailableMaintenanceSlots'])->name('maintenance-slots.available');
    // Maintenance management (routes added to resolve missing references)
    Route::get('/maintenance', [MaintenanceController::class, 'adminIndex'])->name('maintenance.index');
    // Alias: legacy link target to computers index redirects to maintenance index for now
    Route::get('/maintenance/computers', function () {
        return redirect()->route('admin.maintenance.index');
    })->name('maintenance.computers.index');
    Route::get('/maintenance/computers/{computerProfile}', [MaintenanceController::class, 'showComputer'])->name('maintenance.computers.show');
    Route::post('/maintenance/computers', [MaintenanceController::class, 'storeComputer'])->name('maintenance.computers.store');
    // Slots management
    Route::post('/maintenance/slots', [MaintenanceController::class, 'store'])->name('maintenance.slots.store');
    Route::post('/maintenance/slots/bulk', [MaintenanceController::class, 'storeBulk'])->name('maintenance.slots.store-bulk');
    Route::put('/maintenance/slots/{slot}', [MaintenanceController::class, 'updateSlot'])->name('maintenance.slots.update');
    Route::delete('/maintenance/slots/{slot}', [MaintenanceController::class, 'destroySlot'])->name('maintenance.slots.destroy');
    Route::delete('/maintenance/slots/destroy-past', [MaintenanceController::class, 'destroyPastSlots'])->name('maintenance.slots.destroy-past');
    // Inventory removed from admin panel

    
    // Se mantienen solo tickets y usuarios en el panel admin

    // Gestión de usuarios (separado del dominio Sistemas)
    Route::get('/users', [UsersController::class, 'index'])->name('users');
    Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');
    Route::post('/users/{user}/approve', [UsersController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/reject', [UsersController::class, 'reject'])->name('users.reject');
    Route::get('/users/{user}', [UsersController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
    Route::delete('/users/{user}/rejection', [UsersController::class, 'destroyRejected'])->name('users.rejections.destroy');
    Route::delete('/blocked-emails/{blockedEmail}', [UsersController::class, 'destroyBlockedEmail'])->name('blocked-emails.destroy');

    // Rutas de ayuda en admin eliminadas
});

// API Routes for Notifications (Admin only)
Route::middleware(['auth', 'admin'])->prefix('api')->group(function () {
    Route::get('/notifications/count', [NotificationController::class, 'getUnreadCount']);
    Route::get('/notifications/unread', [NotificationController::class, 'getUnreadTickets']);
    Route::post('/notifications/{ticket}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/stats', [NotificationController::class, 'getStats']);
});

// Rutas de inventario, préstamos y discos en uso eliminadas

// Visualización de inventario y préstamos eliminadas

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// En routes/web.php

Route::middleware(['auth', \App\Http\Middleware\AreaRHMiddleware::class])->group(function () {
    
    // Rutas del Reloj Checador (Unificadas con prefijo 'rh.')
    Route::prefix('recursos-humanos/reloj')->name('rh.reloj.')->group(function () {
        
        // Vista principal
        Route::get('/', [RelojChecadorImportController::class, 'index'])
            ->name('index'); // Genera: rh.reloj.index

        // Procesos de Carga e Importación
        Route::post('/start', [RelojChecadorImportController::class, 'start'])
            ->name('start'); // Genera: rh.reloj.start
            
        Route::get('/progress/{key}', [RelojChecadorImportController::class, 'progress'])
            ->name('progress'); // Genera: rh.reloj.progress

        // Gestión de Datos
        Route::delete('/clear', [RelojChecadorImportController::class, 'clear'])
            ->name('clear'); // Genera: rh.reloj.clear  <-- ESTA ES LA QUE FALTABA

        Route::put('/update/{id}', [RelojChecadorImportController::class, 'update'])
            ->name('update'); // Genera: rh.reloj.update

        Route::post('/store', [RelojChecadorImportController::class, 'store'])
            ->name('store'); // Genera: rh.reloj.store
    });

});

// Ayuda pública removida

require __DIR__.'/auth.php';
