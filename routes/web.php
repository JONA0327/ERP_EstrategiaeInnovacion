<?php

use App\Http\Controllers\Sistemas_IT\AdminController;
// use App\Http\Controllers\ArchivoProblemasController; // removed feature
use App\Http\Controllers\Auth\AuthController;
// Removed features: DiscoEnUso, Inventario, Prestamo controllers
use App\Http\Controllers\Sistemas_IT\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Sistemas_IT\TicketController;
use App\Http\Controllers\Sistemas_IT\MaintenanceController;
use App\Http\Controllers\Users\UsersController;
use App\Http\Controllers\RH\ExpedienteController;
use App\Http\Controllers\RH\RelojChecadorImportController; // Nuevo flujo con barra de progreso

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');


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
});

Route::middleware(['auth','area.logistica'])->group(function () {
    Route::get('/logistica', function () { return view('Logistica.index'); })->name('logistica.index');
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
    Route::delete('/ticket/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');
    Route::get('/ticket/{ticket}/can-cancel', [TicketController::class, 'canCancel'])->name('tickets.can-cancel');
    Route::post('/ticket/{ticket}/acknowledge-update', [TicketController::class, 'acknowledgeUpdate'])->name('tickets.acknowledge');
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

// Ayuda pública removida

require __DIR__.'/auth.php';
