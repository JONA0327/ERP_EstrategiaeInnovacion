

<?php $__env->startSection('title', 'Evaluación de Desempeño'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-slate-50 py-8">
    <div class="container mx-auto px-4 max-w-5xl">
        
        
        <div class="flex items-center justify-between mb-6">
            <a href="<?php echo e(route('rh.evaluacion.index', ['periodo' => $periodo])); ?>" class="flex items-center text-slate-500 hover:text-slate-700 transition font-bold">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Regresar al listado
            </a>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-6 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm">
                <p class="font-bold">¡Éxito!</p><p><?php echo e(session('success')); ?></p>
            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                <p class="font-bold">Error</p><p><?php echo e(session('error')); ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
            
            <div class="bg-slate-900 px-8 py-6 text-white flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center text-2xl font-bold border-2 border-white/20">
                        <?php echo e(substr($empleado->nombre, 0, 1)); ?>

                    </div>
                    <div>
                        <h2 class="text-2xl font-bold"><?php echo e($empleado->nombre); ?> <?php echo e($empleado->apellido_paterno); ?></h2>
                        <p class="text-indigo-300 font-medium"><?php echo e($empleado->posicion ?? 'Puesto no definido'); ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="inline-block px-4 py-1.5 rounded-full bg-indigo-600 border border-indigo-500 text-sm font-bold shadow-sm">
                        <?php echo e($periodo); ?>

                    </div>
                    <p class="text-xs text-slate-400 mt-2 text-center md:text-right">Evaluación Confidencial</p>
                </div>
            </div>

            <div class="p-8">
                <?php 
                    $actionRoute = isset($evaluacion) ? route('rh.evaluacion.update', $evaluacion->id) : route('rh.evaluacion.store'); 
                ?>

                <form method="POST" action="<?php echo e($actionRoute); ?>">
                    <?php echo csrf_field(); ?>
                    <?php if(isset($evaluacion)): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
                    
                    <input type="hidden" name="empleado_id" value="<?php echo e($empleado->id); ?>">
                    <input type="hidden" name="periodo" value="<?php echo e($periodo); ?>">

                    <?php if(isset($is_locked) && $is_locked): ?>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 rounded-r">
                            <div class="flex">
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700 font-bold">Evaluación Finalizada</p>
                                    <p class="text-sm text-yellow-700">Ya has enviado esta evaluación y no se puede editar.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($criterios) && $criterios->isNotEmpty()): ?>
                        <div class="space-y-12"> 
                            
                            
                            <?php $__currentLoopData = $criterios->groupBy('criterio'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                
                                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                                    
                                    <div class="bg-slate-100 px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-2">
                                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                            <?php echo e($categoria); ?>

                                        </h3>
                                        
                                        <div class="flex gap-2">
                                            
                                            <span class="text-xs font-bold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full border border-indigo-200">
                                                Valor Sección: <?php echo e($items->sum('peso')); ?>%
                                            </span>
                                            <span class="text-xs font-semibold bg-white text-slate-500 px-3 py-1 rounded-full border border-slate-200">
                                                <?php echo e($items->count()); ?> Puntos
                                            </span>
                                        </div>
                                    </div>

                                    
                                    <div class="p-6 space-y-8">
                                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $criterio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $valPrevio = $respuestas[$criterio->id] ?? null;
                                                $obsPrevia = $observaciones[$criterio->id] ?? '';
                                            ?>
                                            
                                            <div class="relative pl-4 border-l-4 border-slate-200 hover:border-indigo-400 transition-colors duration-300" x-data="{ selected: <?php echo e($valPrevio ?? 'null'); ?> }">
                                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-2">
                                                    <div class="w-full">
                                                        <p class="text-base font-medium text-slate-700"><?php echo e($criterio->descripcion); ?></p>
                                                    </div>
                                                    
                                                    <span class="shrink-0 text-[10px] font-bold bg-slate-50 text-slate-400 px-2 py-0.5 rounded border border-slate-100">
                                                        <?php echo e($criterio->peso); ?>%
                                                    </span>
                                                </div>

                                                
                                                <div class="grid grid-cols-5 gap-2 mb-3">
                                                    <?php
                                                        $opciones = [
                                                            ['val' => 100, 'label' => 'Excelente'],
                                                            ['val' => 75, 'label' => 'Bueno'],
                                                            ['val' => 50, 'label' => 'Regular'],
                                                            ['val' => 25, 'label' => 'Deficiente'],
                                                            ['val' => 0, 'label' => 'Inaceptable']
                                                        ];
                                                    ?>
                                                    <?php $__currentLoopData = $opciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <label class="cursor-pointer group relative">
                                                            <input type="radio" name="calificaciones[<?php echo e($criterio->id); ?>]" value="<?php echo e($op['val']); ?>" class="peer sr-only" x-model="selected" required <?php echo e($is_locked ? 'disabled' : ''); ?>>
                                                            
                                                            <div class="h-full flex flex-col items-center justify-center py-2 px-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition-all duration-200 
                                                                        peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-checked:shadow-sm">
                                                                <span class="text-sm font-bold"><?php echo e($op['val']); ?></span>
                                                                <span class="hidden md:block text-[9px] uppercase font-bold opacity-60 mt-0.5"><?php echo e($op['label']); ?></span>
                                                            </div>
                                                        </label>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                                
                                                <div class="mt-2">
                                                    <textarea name="observaciones[<?php echo e($criterio->id); ?>]" rows="1"
                                                        class="w-full text-xs rounded-md border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 placeholder-slate-400 resize-none transition-all" 
                                                        placeholder="Nota opcional..." <?php echo e($is_locked ? 'disabled' : ''); ?>><?php echo e($obsPrevia); ?></textarea>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <div class="mt-10 pt-8 border-t border-slate-200">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Comentarios Generales y Feedback</label>
                            <textarea name="comentarios_generales" rows="4" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm" placeholder="Escribe aquí tus conclusiones generales..." <?php echo e($is_locked ? 'disabled' : ''); ?>><?php echo e($evaluacion->comentarios_generales ?? ''); ?></textarea>
                        </div>

                        <?php if(!$is_locked): ?>
                            <div class="mt-10 flex justify-end gap-4">
                                <a href="<?php echo e(route('rh.evaluacion.index')); ?>" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition">Cancelar</a>
                                <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 transform hover:-translate-y-0.5 transition-all">
                                    Enviar Evaluación
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-20 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <h3 class="text-lg font-bold text-slate-600">No hay criterios asignados</h3>
                            <p class="text-slate-400">Este puesto (<?php echo e($area); ?>) no tiene criterios de evaluación definidos en el sistema.</p>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/Recursos_Humanos/evaluacion/show.blade.php ENDPATH**/ ?>