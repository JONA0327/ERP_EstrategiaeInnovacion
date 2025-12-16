

<?php $__env->startSection('title', 'Gestión de Jerarquía - Sistemas IT'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8">
    
    <!-- Encabezado con Navegación -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 flex-1 w-full">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Jerarquía Organizacional</h1>
            </div>
            <p class="text-gray-500 mt-1">Asigna supervisores a los empleados para configurar los flujos de evaluación.</p>
            
            <div class="mt-6 pt-6 border-t border-gray-100">
                <!-- Buscador y Filtros integrados en la tarjeta -->
                <form action="<?php echo e(route('rh.jerarquia.index')); ?>" method="GET" class="flex flex-col sm:flex-row gap-3 w-full">
                    <select name="area" class="rounded-md border-gray-300 text-sm focus:ring-gray-500 focus:border-gray-500 bg-gray-50" onchange="this.form.submit()">
                        <option value="Todos">Todas las áreas</option>
                        <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($area); ?>" <?php echo e(request('area') == $area ? 'selected' : ''); ?>><?php echo e($area); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    
                    <div class="relative flex-1">
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Buscar empleado por nombre, ID o puesto..." class="rounded-md border-gray-300 pl-10 text-sm w-full focus:ring-gray-500 focus:border-gray-500 bg-gray-50">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-medium hover:bg-gray-700 transition shadow-sm">
                        Buscar
                    </button>
                    
                    <?php if(request('area') || request('search')): ?>
                        <a href="<?php echo e(route('rh.jerarquia.index')); ?>" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50 transition text-center">
                            Limpiar
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Mensajes de Feedback -->
    <?php if(session('success')): ?>
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded-r-md shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            <p class="font-medium"><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r-md shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
            <p class="font-medium"><?php echo e(session('error')); ?></p>
        </div>
    <?php endif; ?>

    <!-- Tabla de Asignación -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Empleado</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Puesto / Área</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Supervisor Asignado</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <?php if($empleado->foto_path): ?>
                                            <img class="h-10 w-10 rounded-full object-cover border border-gray-200" src="<?php echo e(asset('storage/' . $empleado->foto_path)); ?>" alt="">
                                        <?php else: ?>
                                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold border border-gray-200">
                                                <?php echo e(substr($empleado->nombre, 0, 1)); ?>

                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900"><?php echo e($empleado->nombre); ?></div>
                                        <div class="text-xs text-gray-500 font-mono"><?php echo e($empleado->id_empleado); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-medium"><?php echo e($empleado->posicion); ?></div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mt-1 border border-gray-200">
                                    <?php echo e($empleado->area); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form action="<?php echo e(route('rh.jerarquia.update', $empleado->id)); ?>" method="POST" class="flex items-center gap-3">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    
                                    <div class="relative">
                                        <select name="supervisor_id" class="block w-64 rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm py-1.5 pl-3 pr-8 truncate">
                                            <option value="">-- Sin Supervisor --</option>
                                            <?php $__currentLoopData = $posiblesSupervisores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supervisor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if($supervisor->id !== $empleado->id): ?>
                                                    <option value="<?php echo e($supervisor->id); ?>" <?php echo e($empleado->supervisor_id == $supervisor->id ? 'selected' : ''); ?>>
                                                        <?php echo e($supervisor->nombre); ?>

                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    
                                    
                                    <button type="submit" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold rounded shadow-sm transition-colors flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Guardar
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <?php if($empleado->supervisor): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                                        Asignado
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></span>
                                        Pendiente
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <p class="text-base font-medium text-gray-900">No se encontraron empleados</p>
                                    <p class="text-sm text-gray-500">Intenta con otros filtros de búsqueda.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <?php echo e($empleados->appends(request()->query())->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/Recursos_Humanos/jerarquia/index.blade.php ENDPATH**/ ?>