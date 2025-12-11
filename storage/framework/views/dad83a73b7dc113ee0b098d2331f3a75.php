<?php $__env->startSection('title', 'Ver Usuario - ' . $user->name); ?>

<?php $__env->startSection('content'); ?>
<main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">👤 Perfil de Usuario</h1>
            <p class="text-gray-600 mt-2">Información completa e historial de actividad</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
            <a href="<?php echo e(route('admin.users.edit', $user)); ?>" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 inline-flex items-center justify-center text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                ✏️ Editar Usuario
            </a>
            <a href="<?php echo e(route('admin.users')); ?>" 
               class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 inline-flex items-center justify-center text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                ← Volver
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-8">
            <div class="flex items-center">
                <svg class="w-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-green-800 font-medium"><?php echo e(session('success')); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Información del Usuario -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6">
                    <div class="text-center">
                        <!-- Avatar -->
                        <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-white">
                                <?php echo e(strtoupper(substr($user->name, 0, 2))); ?>

                            </span>
                        </div>
                        
                        <!-- Información Básica -->
                        <h2 class="text-xl font-bold text-gray-900"><?php echo e($user->name); ?></h2>
                        <p class="text-gray-600 text-sm"><?php echo e($user->email); ?></p>
                        
                        <!-- Estado y Rol -->
                        <div class="mt-4 space-y-2">
                            <div class="flex justify-center">
                                <?php if($user->role === 'admin'): ?>
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                                        👑 Administrador
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">
                                        👤 Usuario
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex justify-center">
                                <?php if($user->status === 'approved'): ?>
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                        ✅ Aprobado
                                    </span>
                                <?php elseif($user->status === 'pending'): ?>
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">
                                        ⏳ Pendiente
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                                        ❌ Rechazado
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detalles Adicionales -->
                    <div class="mt-6 pt-6 border-t border-gray-200 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">ID de Usuario:</span>
                            <span class="text-sm font-medium text-gray-900">#<?php echo e($user->id); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Registro:</span>
                            <span class="text-sm font-medium text-gray-900"><?php echo e($user->created_at->format('d/m/Y')); ?></span>
                        </div>
                        <?php if($user->approved_at): ?>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Aprobado:</span>
                            <span class="text-sm font-medium text-gray-900"><?php echo e($user->approved_at->format('d/m/Y')); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Rápidas -->
            <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Resumen de Actividad</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total Tickets:</span>
                            <span class="font-semibold text-blue-600"><?php echo e($stats['total_tickets']); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Tickets Cerrados:</span>
                            <span class="font-semibold text-green-600"><?php echo e($stats['tickets_cerrados']); ?></span>
                        </div>
                        <!-- Se eliminaron métricas de préstamos -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Historial de Actividad -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Historial de Tickets -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        🎫 Historial de Tickets (<?php echo e($tickets->count()); ?>)
                    </h3>
                </div>
                
                <?php if($tickets->count() > 0): ?>
                    <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                        <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-sm font-medium text-gray-900">#<?php echo e($ticket->id); ?></span>
                                        <?php if($ticket->estado === 'abierto'): ?>
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                🔴 Abierto
                                            </span>
                                        <?php elseif($ticket->estado === 'en_proceso'): ?>
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                                🟡 En Proceso
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                ✅ Cerrado
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo e(Str::limit($ticket->descripcion, 100)); ?></p>
                                    <div class="flex items-center gap-4 text-xs text-gray-500">
                                        <span>📋 <?php echo e(ucfirst($ticket->tipo_problema)); ?></span>
                                        <span>📅 <?php echo e($ticket->created_at->format('d/m/Y H:i')); ?></span>
                                    </div>
                                </div>
                                <a href="<?php echo e(route('admin.tickets.show', $ticket)); ?>" 
                                   class="ml-4 text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Ver →
                                </a>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="p-6 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p>Sin tickets registrados</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Se eliminó el historial de préstamos -->
            
        </div>
    </div>
</main>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SISTEMAS\Downloads\ERP EstrategiaeInnovacion\Sistema_Tickets_E-I\resources\views\Sistemas_IT/admin/users/show.blade.php ENDPATH**/ ?>