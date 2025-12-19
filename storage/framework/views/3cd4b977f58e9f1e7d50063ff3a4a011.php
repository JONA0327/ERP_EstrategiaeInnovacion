

<?php $__env->startSection('title', 'Evaluación - ' . $empleado->nombre); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-slate-50 py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
        
        <?php if(session('error')): ?>
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                <p class="font-bold">Error</p><p><?php echo e(session('error')); ?></p>
            </div>
        <?php endif; ?>

        <div class="flex flex-col lg:flex-row gap-8">
            
            <div class="w-full lg:w-1/4">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
                    <div class="p-4 bg-slate-50 border-b border-slate-200">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Equipo: <?php echo e($area ?? 'General'); ?></span>
                    </div>
                    <div class="max-h-[70vh] overflow-y-auto custom-scrollbar p-2 space-y-1">
                        <?php if(isset($empleados) && count($empleados) > 0): ?>
                            <?php $__currentLoopData = $empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(route('rh.evaluacion.show', ['id' => $emp->id, 'periodo' => $periodo])); ?>" class="flex items-center p-3 rounded-xl transition-all duration-200 <?php echo e($empleado->id === $emp->id ? 'bg-indigo-50 border border-indigo-100 shadow-sm' : 'hover:bg-slate-50 border border-transparent'); ?>">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold overflow-hidden <?php echo e($empleado->id === $emp->id ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-600'); ?>">
                                            <?php echo e(substr($emp->nombre, 0, 1)); ?>

                                        </div>
                                    </div>
                                    <div class="ml-3 overflow-hidden">
                                        <p class="text-sm font-semibold truncate <?php echo e($empleado->id === $emp->id ? 'text-indigo-900' : 'text-slate-700'); ?>"><?php echo e($emp->nombre); ?></p>
                                        <p class="text-[10px] truncate <?php echo e($empleado->id === $emp->id ? 'text-indigo-600' : 'text-slate-500'); ?>"><?php echo e($emp->posicion ?? 'N/A'); ?></p>
                                    </div>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="w-full lg:w-3/4 space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 md:p-8 relative overflow-hidden">
                    <div class="relative z-10 flex flex-col md:flex-row gap-6 items-center md:items-start text-center md:text-left">
                        <div class="w-24 h-24 rounded-full bg-white p-1 shadow-lg ring-4 ring-indigo-50">
                            <div class="w-full h-full rounded-full bg-slate-100 flex items-center justify-center overflow-hidden">
                                <?php if(isset($empleado->foto_path) && $empleado->foto_path): ?>
                                    <img src="<?php echo e(asset('storage/' . $empleado->foto_path)); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="text-3xl font-bold text-slate-400"><?php echo e(substr($empleado->nombre, 0, 1)); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold text-slate-900"><?php echo e($empleado->nombre); ?> <?php echo e($empleado->apellido_paterno); ?></h2>
                            <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-2">
                                <span class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold border border-indigo-200"><?php echo e($empleado->posicion ?? 'Puesto no asignado'); ?></span>
                                <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold border border-blue-200 flex items-center gap-1"><?php echo e($periodo); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-lg border border-slate-200 overflow-hidden">
                    <div class="bg-slate-900 px-8 py-5 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-white">Formulario de Evaluación</h3>
                            <p class="text-indigo-200 text-xs mt-0.5">Criterios para: <?php echo e($area ?? 'General'); ?></p>
                        </div>
                    </div>

                    <div class="p-8">
                        <?php $actionRoute = isset($evaluacion) ? route('rh.evaluacion.update', $evaluacion->id) : route('rh.evaluacion.store'); ?>

                        <form method="POST" action="<?php echo e($actionRoute); ?>">
                            <?php echo csrf_field(); ?>
                            <?php if(isset($evaluacion)): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
                            <input type="hidden" name="empleado_id" value="<?php echo e($empleado->id); ?>">
                            <input type="hidden" name="periodo" value="<?php echo e($periodo); ?>">

                            <?php if(isset($is_locked) && $is_locked): ?>
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 rounded-r">
                                    <div class="flex">
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700 font-bold">Modo Lectura</p>
                                            <p class="text-sm text-yellow-700">
                                                <?php if(!$isWindowOpen): ?> El periodo de evaluación está cerrado. <?php else: ?> Esta evaluación ya ha sido finalizada. <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if(isset($criterios) && $criterios->isNotEmpty()): ?>
                                <div class="space-y-12">
                                    <?php $__currentLoopData = $criterios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $criterio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $valPrevio = $respuestas[$criterio->id] ?? null;
                                            $obsPrevia = $observaciones[$criterio->id] ?? '';
                                        ?>
                                        <div class="relative" x-data="{ selected: <?php echo e($valPrevio ?? 'null'); ?> }">
                                            <div class="flex items-start justify-between mb-4">
                                                <div class="flex gap-4">
                                                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm border border-slate-200"><?php echo e($loop->iteration); ?></span>
                                                    <div>
                                                        <h4 class="text-base font-bold text-slate-800 leading-tight"><?php echo e($criterio->criterio); ?></h4>
                                                        <p class="text-sm text-slate-500 mt-1 leading-relaxed"><?php echo e($criterio->descripcion); ?></p>
                                                    </div>
                                                </div>
                                                <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">Peso: <?php echo e($criterio->peso); ?>%</span>
                                            </div>

                                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-4">
                                                <?php
                                                    $opciones = [
                                                        ['val' => 100, 'label' => 'Muy de acuerdo', 'icon' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'container_classes' => 'hover:border-emerald-200 hover:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50', 'icon_classes' => 'group-hover:text-emerald-400 peer-checked:text-emerald-600', 'label_classes' => 'group-hover:text-emerald-700 peer-checked:text-emerald-800', 'check_color' => 'text-emerald-600'],
                                                        ['val' => 75, 'label' => 'De acuerdo', 'icon' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'container_classes' => 'hover:border-green-200 hover:bg-green-50 peer-checked:border-green-500 peer-checked:bg-green-50', 'icon_classes' => 'group-hover:text-green-400 peer-checked:text-green-600', 'label_classes' => 'group-hover:text-green-700 peer-checked:text-green-800', 'check_color' => 'text-green-600'],
                                                        ['val' => 50, 'label' => 'Neutral', 'icon' => 'M10 14H14M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9 10h.01M15 10h.01', 'container_classes' => 'hover:border-yellow-200 hover:bg-yellow-50 peer-checked:border-yellow-500 peer-checked:bg-yellow-50', 'icon_classes' => 'group-hover:text-yellow-400 peer-checked:text-yellow-600', 'label_classes' => 'group-hover:text-yellow-700 peer-checked:text-yellow-800', 'check_color' => 'text-yellow-600'],
                                                        ['val' => 25, 'label' => 'En desacuerdo', 'icon' => 'M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'container_classes' => 'hover:border-orange-200 hover:bg-orange-50 peer-checked:border-orange-500 peer-checked:bg-orange-50', 'icon_classes' => 'group-hover:text-orange-400 peer-checked:text-orange-600', 'label_classes' => 'group-hover:text-orange-700 peer-checked:text-orange-800', 'check_color' => 'text-orange-600'],
                                                        ['val' => 0, 'label' => 'Muy en desacuerdo', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', 'container_classes' => 'hover:border-red-200 hover:bg-red-50 peer-checked:border-red-500 peer-checked:bg-red-50', 'icon_classes' => 'group-hover:text-red-400 peer-checked:text-red-600', 'label_classes' => 'group-hover:text-red-700 peer-checked:text-red-800', 'check_color' => 'text-red-600']
                                                    ];
                                                ?>
                                                <?php $__currentLoopData = $opciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <label class="cursor-pointer group relative <?php echo e($is_locked ? 'opacity-60 cursor-not-allowed' : ''); ?>">
                                                        <input type="radio" name="calificaciones[<?php echo e($criterio->id); ?>]" value="<?php echo e($op['val']); ?>" class="peer sr-only" x-model="selected" required <?php echo e($is_locked ? 'disabled' : ''); ?>>
                                                        <div class="h-full flex flex-col items-center justify-center p-3 rounded-xl border-2 border-slate-100 bg-white transition-all duration-200 peer-checked:shadow-md <?php echo e($op['container_classes']); ?>">
                                                            <div class="mb-1 text-slate-300 transition-colors <?php echo e($op['icon_classes']); ?>">
                                                                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($op['icon']); ?>"></path></svg>
                                                            </div>
                                                            <span class="text-[10px] font-bold text-center text-slate-500 leading-tight block <?php echo e($op['label_classes']); ?>"><?php echo e($op['label']); ?></span>
                                                        </div>
                                                        <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity <?php echo e($op['check_color']); ?>">
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                        </div>
                                                    </label>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                            <div class="mt-3 pl-12">
                                                <input type="text" name="observaciones[<?php echo e($criterio->id); ?>]" value="<?php echo e($obsPrevia); ?>" class="w-full text-sm border-0 border-b border-slate-200 focus:border-indigo-500 focus:ring-0 bg-transparent placeholder-slate-400 transition-colors disabled:text-slate-400" placeholder="Añadir comentario (opcional)..." <?php echo e($is_locked ? 'disabled' : ''); ?>>
                                            </div>
                                        </div>
                                        <?php if(!$loop->last): ?> <hr class="border-slate-100"> <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>

                                <div class="mt-8 pt-6 border-t border-slate-200">
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Comentarios Generales</label>
                                    <textarea name="comentarios_generales" rows="4" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm disabled:bg-slate-50" <?php echo e($is_locked ? 'disabled' : ''); ?>><?php echo e($evaluacion->comentarios_generales ?? ''); ?></textarea>
                                </div>

                                <?php if(!$is_locked): ?>
                                    <div class="mt-12 flex justify-end items-center gap-4 pt-6 border-t border-slate-200">
                                        <a href="<?php echo e(route('rh.evaluacion.index')); ?>" class="px-6 py-3 text-sm font-bold text-slate-600 hover:text-slate-800 transition">Cancelar</a>
                                        <button type="submit" class="inline-flex items-center px-8 py-3 bg-indigo-600 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shadow-lg shadow-indigo-200 transform hover:-translate-y-0.5">
                                            <?php echo e(isset($evaluacion) ? 'Actualizar Evaluación' : 'Guardar Resultados'); ?>

                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-12 flex justify-end pt-6 border-t border-slate-200">
                                        <a href="<?php echo e(route('rh.evaluacion.index')); ?>" class="px-6 py-3 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition">Volver al listado</a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-16">
                                    <h3 class="text-lg font-medium text-slate-900">Sin Criterios</h3>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/Recursos_Humanos/evaluacion/show.blade.php ENDPATH**/ ?>