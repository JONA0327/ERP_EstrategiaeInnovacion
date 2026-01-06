<?php $__env->startSection('title','Editar Expediente'); ?>
<?php $__env->startSection('content'); ?>
<main class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-bold text-slate-900">Editar Expediente</h1>
        <a href="<?php echo e(route('rh.expedientes.index')); ?>" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">← Volver</a>
    </div>

    <form method="POST" action="<?php echo e(route('rh.expedientes.update',$empleado)); ?>" class="space-y-6 rounded-3xl border border-blue-100 bg-white/90 backdrop-blur p-6 shadow">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="text-xs font-semibold text-slate-600">Nombre</label>
                <input type="text" value="<?php echo e($empleado->nombre); ?>" disabled class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm" />
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600">Correo</label>
                <input type="text" value="<?php echo e($empleado->correo); ?>" disabled class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm" />
            </div>
            <div>
                <label for="id_empleado" class="text-xs font-semibold text-slate-600">ID Empleado</label>
                <input type="text" id="id_empleado" name="id_empleado" value="<?php echo e(old('id_empleado',$empleado->id_empleado)); ?>" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:ring-0" />
                <?php $__errorArgs = ['id_empleado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label for="area" class="text-xs font-semibold text-slate-600">Área</label>
                <input type="text" id="area" name="area" value="<?php echo e(old('area',$empleado->area)); ?>" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:ring-0" />
                <?php $__errorArgs = ['area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>
        <div class="text-right">
            <button class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-2 text-sm font-semibold text-white shadow hover:from-blue-700 hover:to-blue-800">Guardar cambios</button>
        </div>
    </form>
</main>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/Recursos_Humanos/expedientes/edit.blade.php ENDPATH**/ ?>