

<?php $__env->startSection('title', 'Evaluación - ' . $empleado->nombre); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-slate-50 py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
        
        <?php if(session('success')): ?>
            <div class="mb-6 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm animate-fade-in-up">
                <p class="font-bold">¡Éxito!</p><p><?php echo e(session('success')); ?></p>
            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm animate-fade-in-up">
                <p class="font-bold">Error</p><p><?php echo e(session('error')); ?></p>
            </div>
        <?php endif; ?>

        <div class="flex flex-col lg:flex-row gap-8">
            
            
            <div class="w-full lg:w-1/4">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Colaboradores</span>
                        <?php
                            $total = count($empleados);
                            $completados = $empleados->filter(fn($e) => $e->eval_status && $e->eval_status->edit_count >= 1)->count();
                            $porcentaje = $total > 0 ? round(($completados / $total) * 100) : 0;
                        ?>
                        <span class="text-[10px] font-bold px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full"><?php echo e($completados); ?>/<?php echo e($total); ?></span>
                    </div>
                    <div class="w-full bg-slate-100 h-1">
                        <div class="bg-indigo-500 h-1 transition-all duration-500" style="width: <?php echo e($porcentaje); ?>%"></div>
                    </div>
                    <div class="max-h-[70vh] overflow-y-auto custom-scrollbar p-2 space-y-1">
                        <?php if(isset($empleados) && count($empleados) > 0): ?>
                            <?php $__currentLoopData = $empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $isActive = ($empleado->id === $emp->id);
                                    $isFinished = ($emp->eval_status && $emp->eval_status->edit_count >= 1);
                                ?>
                                <a href="<?php echo e(route('rh.evaluacion.show', ['id' => $emp->id, 'periodo' => $periodo])); ?>" class="group flex items-center p-3 rounded-xl transition-all duration-200 border <?php echo e($isActive ? 'bg-indigo-50 border-indigo-200 shadow-sm z-10' : 'hover:bg-slate-50 border-transparent hover:border-slate-100'); ?>">
                                    <div class="relative flex-shrink-0">
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold overflow-hidden border-2 <?php echo e($isActive ? 'border-indigo-200 text-white bg-indigo-600' : ($isFinished ? 'border-emerald-100 text-emerald-700 bg-emerald-50' : 'border-slate-100 text-slate-500 bg-slate-100')); ?>">
                                            <?php echo e(substr($emp->nombre, 0, 1)); ?>

                                        </div>
                                        <div class="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5 shadow-sm">
                                            <?php if($isFinished): ?>
                                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                            <?php elseif($isActive): ?>
                                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            <?php else: ?>
                                                <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="ml-3 overflow-hidden flex-1">
                                        <p class="text-sm font-bold truncate <?php echo e($isActive ? 'text-indigo-900' : 'text-slate-700'); ?>"><?php echo e(explode(' ', $emp->nombre)[0]); ?> <?php echo e(explode(' ', $emp->apellido_paterno)[0]); ?></p>
                                        <p class="text-[10px] truncate <?php echo e($isActive ? 'text-indigo-500 font-medium' : 'text-slate-400'); ?>"><?php echo e($emp->posicion ?? 'Colaborador'); ?></p>
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

                
                <?php if($isMe): ?>
                    <div class="bg-white rounded-3xl shadow-lg border border-slate-200 overflow-hidden text-center py-16 px-8">
                        <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-800 mb-2">Evaluación en Curso</h3>
                        <p class="text-slate-500 max-w-lg mx-auto mb-8">
                            Este módulo es de uso administrativo. Tu evaluación de desempeño está siendo gestionada por tu supervisor y el departamento de Recursos Humanos.
                        </p>
                        
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-sm text-slate-600 font-medium">
                            <?php if(isset($evaluacion) && $evaluacion->edit_count >= 1): ?>
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div> Estado: Finalizada por Supervisor
                            <?php else: ?>
                                <div class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></div> Estado: En Proceso
                            <?php endif; ?>
                        </div>
                        
                        <p class="text-xs text-slate-400 mt-8">
                            Los resultados detallados son confidenciales y serán presentados en tu reunión de retroalimentación presencial.
                        </p>
                    </div>

                
                <?php else: ?>
                    <div class="bg-white rounded-3xl shadow-lg border border-slate-200 overflow-hidden">
                        <div class="bg-slate-900 px-8 py-5 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-white">Formulario de Evaluación</h3>
                                <p class="text-indigo-200 text-xs mt-0.5">Criterios aplicables: <?php echo e($area ?? 'General'); ?></p>
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
                                                <p class="text-sm text-yellow-700 font-bold">Modo Solo Lectura</p>
                                                <p class="text-sm text-yellow-700">Esta evaluación ya ha sido finalizada.</p>
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
                                                            <?php if($criterio->area == 'Recursos Humanos'): ?>
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-purple-100 text-purple-800 mt-1">Soft Skill</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">Peso: <?php echo e($criterio->peso); ?>%</span>
                                                </div>

                                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-4">
                                                    <?php
                                                        $opciones = [
                                                            ['val' => 100, 'label' => 'Muy de acuerdo', 'icon' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                                            ['val' => 75, 'label' => 'De acuerdo', 'icon' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                                            ['val' => 50, 'label' => 'Neutral', 'icon' => 'M10 14H14M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9 10h.01M15 10h.01'],
                                                            ['val' => 25, 'label' => 'En desacuerdo', 'icon' => 'M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                                            ['val' => 0, 'label' => 'Muy en desacuerdo', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z']
                                                        ];
                                                    ?>
                                                    <?php $__currentLoopData = $opciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <label class="cursor-pointer group relative <?php echo e($is_locked ? 'opacity-60 cursor-not-allowed' : ''); ?>">
                                                            <input type="radio" name="calificaciones[<?php echo e($criterio->id); ?>]" value="<?php echo e($op['val']); ?>" class="peer sr-only" x-model="selected" required <?php echo e($is_locked ? 'disabled' : ''); ?>>
                                                            <div class="h-full flex flex-col items-center justify-center p-3 rounded-xl border-2 border-slate-100 bg-white transition-all duration-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:bg-slate-50">
                                                                <div class="mb-1 text-slate-300 peer-checked:text-indigo-600">
                                                                    <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($op['icon']); ?>"></path></svg>
                                                                </div>
                                                                <span class="text-[10px] font-bold text-center text-slate-500 leading-tight block peer-checked:text-indigo-800"><?php echo e($op['label']); ?></span>
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
                                                <?php echo e(isset($evaluacion) ? 'Actualizar y Finalizar' : 'Guardar Resultados'); ?>

                                            </button>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center py-16">
                                        <h3 class="text-lg font-medium text-slate-900">Sin Criterios Asignados</h3>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/Recursos_Humanos/evaluacion/show.blade.php ENDPATH**/ ?>