

<?php $__env->startSection('title', 'Evaluación de ' . $empleado->nombre); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8">
    
    <!-- Encabezado y Breadcrumbs -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Evaluación de Desempeño</h1>
            <nav class="flex text-sm font-medium text-gray-500 mt-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li class="inline-flex items-center">
                        <a href="<?php echo e(route('rh.evaluacion.index')); ?>" class="inline-flex items-center hover:text-black transition-colors">
                            <svg class="w-3.5 h-3.5 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                            </svg>
                            Evaluaciones
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>
                            <a href="<?php echo e(route('rh.evaluacion.index', ['area' => $area])); ?>" class="ml-1 hover:text-black transition-colors"><?php echo e($area); ?></a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>
                            <span class="ml-1 text-gray-900 font-semibold"><?php echo e($empleado->nombre); ?></span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <div class="flex-shrink-0">
            <a href="<?php echo e(route('rh.evaluacion.index', ['area' => $area])); ?>" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 text-gray-700 text-sm font-medium transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar: Lista de Empleados del Área -->
        <div class="w-full lg:w-1/4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden sticky top-6">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="font-bold text-gray-700 flex items-center text-sm uppercase tracking-wider">
                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Equipo: <?php echo e($area); ?>

                    </h3>
                </div>
                <div class="max-h-[600px] overflow-y-auto custom-scrollbar">
                    <ul class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <a href="<?php echo e(route('rh.evaluacion.show', $emp->id)); ?>" class="block p-3 hover:bg-gray-50 transition duration-200 group <?php echo e($empleado->id === $emp->id ? 'bg-gray-50 border-l-4 border-gray-400' : 'border-l-4 border-transparent'); ?>">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 relative">
                                            <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-sm overflow-hidden border border-gray-200">
                                                <?php if(isset($emp->foto_path) && $emp->foto_path): ?>
                                                    <img src="<?php echo e(asset('storage/' . $emp->foto_path)); ?>" alt="<?php echo e($emp->nombre); ?>" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <span class="text-gray-400">👤</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-700 truncate group-hover:text-black transition-colors">
                                                <?php echo e($emp->nombre); ?>

                                            </p>
                                            <p class="text-xs text-gray-500 truncate">
                                                <?php echo e($emp->posicion ?? 'Sin puesto'); ?>

                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="w-full lg:w-3/4 space-y-6">
            
            <!-- Tarjeta de Perfil -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                
                <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6">
                    <!-- Foto Grande -->
                    <div class="flex-shrink-0">
                        <div class="w-28 h-28 rounded-full bg-gray-50 p-1 shadow-sm border border-gray-200 overflow-hidden">
                            <div class="w-full h-full rounded-full overflow-hidden flex items-center justify-center bg-white">
                                <?php if(isset($empleado->foto_path) && $empleado->foto_path): ?>
                                    <img src="<?php echo e(asset('storage/' . $empleado->foto_path)); ?>" alt="<?php echo e($empleado->nombre); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="text-4xl text-gray-300">👤</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Datos -->
                    <div class="flex-1 text-center md:text-left">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2"><?php echo e($empleado->nombre); ?> <?php echo e($empleado->apellido_paterno ?? ''); ?></h2>
                        
                        <div class="flex flex-wrap justify-center md:justify-start gap-2 mb-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                <?php echo e($empleado->posicion ?? 'Puesto no asignado'); ?>

                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                <?php echo e($empleado->id_empleado); ?>

                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                <?php echo e($empleado->area); ?>

                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-600 mt-2 font-medium">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <span class="truncate"><?php echo e($empleado->correo ?? 'No registrado'); ?></span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                <span><?php echo e($empleado->telefono ?? 'No registrado'); ?></span>
                            </div>
                            <?php if(isset($empleado->fecha_ingreso)): ?>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span><?php echo e(\Carbon\Carbon::parse($empleado->fecha_ingreso)->format('d/m/Y')); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección del Formulario de Evaluación -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Formulario de Evaluación</h3>
                        <p class="text-xs text-gray-500 mt-0.5 font-medium">Complete los siguientes campos para evaluar el desempeño.</p>
                    </div>
                    <div class="bg-white border border-gray-300 text-gray-600 text-xs font-semibold px-2 py-0.5 rounded uppercase tracking-wide">
                        Periodo 2025-Q1
                    </div>
                </div>
                
                <!-- Aquí iría tu formulario real -->
                <div class="p-8">
                    <div class="py-10 text-center text-gray-600 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 transition-colors hover:bg-gray-100">
                        <div class="mb-3">
                            <span class="inline-block p-3 bg-white rounded-full shadow-sm border border-gray-200">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </span>
                        </div>
                        <h4 class="text-base font-bold text-gray-900 mb-1">Formulario de Evaluación</h4>
                        <p class="text-sm text-gray-500 font-medium max-w-sm mx-auto mb-5">El formulario interactivo se cargará en esta sección.</p>
                        
                        <button class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold rounded-md shadow-sm transition-all duration-200">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Iniciar Evaluación
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/Recursos_Humanos/evaluacion/show.blade.php ENDPATH**/ ?>