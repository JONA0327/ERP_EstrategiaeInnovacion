<?php $__env->startSection('title','Expedientes - Recursos Humanos'); ?>
<?php $__env->startSection('content'); ?>
<main class="relative max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-10">
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">Expedientes
                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-600 border border-blue-100"><?php echo e($empleados->total()); ?> registros</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Gestión centralizada de empleados corporativos.</p>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="<?php echo e(route('rh.expedientes.refresh')); ?>" onsubmit="return confirm('¿Sincronizar nuevos usuarios?')" class="inline-block">
                <?php echo csrf_field(); ?>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2 text-sm font-semibold text-white shadow hover:from-blue-700 hover:to-blue-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5 13a7 7 0 0114 0 7 7 0 01-14 0z"/></svg>
                    Refrescar
                </button>
            </form>
        </div>
    </div>

    <form method="GET" class="mb-5">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Buscar nombre, correo o área..." class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 pr-10 text-sm focus:border-blue-400 focus:ring-0 shadow-sm" />
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M9.5 17A7.5 7.5 0 109.5 2a7.5 7.5 0 000 15z"/></svg>
            </div>
            <button class="rounded-2xl border border-blue-200 bg-blue-50 px-6 py-2.5 text-sm font-semibold text-blue-700 shadow-sm hover:bg-blue-100">Buscar</button>
        </div>
    </form>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-lg shadow-slate-200/40">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-2 text-left text-[11px] font-semibold tracking-wide text-slate-600">NOMBRE</th>
                    <th class="px-5 py-2 text-left text-[11px] font-semibold tracking-wide text-slate-600">CORREO</th>
                    <th class="px-5 py-2 text-left text-[11px] font-semibold tracking-wide text-slate-600">ÁREA</th>
                    <th class="px-5 py-2 text-right text-[11px] font-semibold tracking-wide text-slate-600">ACCIONES</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php $__empty_1 = true; $__currentLoopData = $empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="group hover:bg-blue-50/50 transition-colors">
                        <td class="px-5 py-2 text-sm font-medium text-slate-800"><?php echo e($empleado->nombre); ?></td>
                        <td class="px-5 py-2 text-xs text-slate-600"><?php echo e($empleado->correo); ?></td>
                        <td class="px-5 py-2 text-xs">
                            <?php $areaLabel = $empleado->area; ?>
                            <?php if($areaLabel): ?>
                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-medium
                                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'bg-blue-50 border-blue-200 text-blue-700'=> str_starts_with($areaLabel,'S'),
                                        'bg-green-50 border-green-200 text-green-700'=> str_starts_with($areaLabel,'R'),
                                        'bg-purple-50 border-purple-200 text-purple-700'=> str_starts_with($areaLabel,'L'),
                                        'bg-orange-50 border-orange-200 text-orange-700'=> str_starts_with($areaLabel,'C'),
                                        ]); ?>""><?php echo e($areaLabel); ?></span>
                            <?php else: ?>
                                <span class="text-slate-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-2 text-xs text-right space-x-1">
                            <a href="<?php echo e(route('rh.expedientes.show',$empleado)); ?>" class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2 py-1 text-blue-600 hover:bg-blue-100 shadow-sm">
                                <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'eye','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'eye','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>Ver
                            </a>
                            <a href="<?php echo e(route('rh.expedientes.edit',$empleado)); ?>" class="inline-flex items-center gap-1 rounded-lg bg-yellow-50 px-2 py-1 text-yellow-700 hover:bg-yellow-100 shadow-sm">
                                <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'pencil-square','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pencil-square','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>Editar
                            </a>
                            <form action="<?php echo e(route('rh.expedientes.destroy',$empleado)); ?>" method="POST" class="inline" onsubmit="return confirm('¿Eliminar expediente?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-2 py-1 text-red-600 hover:bg-red-100 shadow-sm">
                                    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'trash','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>Borrar
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">No hay expedientes registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-6"><?php echo e($empleados->links()); ?></div>
</main>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/Recursos_Humanos/expedientes/index.blade.php ENDPATH**/ ?>