<?php $__env->startSection('title', 'Mi Perfil'); ?>

<?php $__env->startSection('content'); ?>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 px-2">
                <div>
                    <h2 class="font-bold text-3xl text-slate-900 leading-tight tracking-tight">
                        <?php echo e(__('Mi Perfil')); ?>

                    </h2>
                    <p class="text-sm text-slate-500 mt-1">Administra la información de tu cuenta y la seguridad.</p>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow-sm border border-slate-200 rounded-3xl relative overflow-hidden">
                <div class="absolute top-0 right-0 p-6 opacity-10 pointer-events-none">
                    <svg class="w-24 h-24 text-indigo-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path></svg>
                </div>
                <div class="max-w-xl relative z-10">
                    <?php echo $__env->make('Sistemas_IT.profile.partials.update-profile-information-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow-sm border border-slate-200 rounded-3xl relative overflow-hidden">
                <div class="absolute top-0 right-0 p-6 opacity-10 pointer-events-none">
                    <svg class="w-24 h-24 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"></path></svg>
                </div>
                <div class="max-w-xl relative z-10">
                    <?php echo $__env->make('Sistemas_IT.profile.partials.update-password-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow-sm border border-red-100 rounded-3xl relative overflow-hidden">
                <div class="absolute top-0 right-0 p-6 opacity-5 pointer-events-none">
                    <svg class="w-24 h-24 text-red-600" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"></path></svg>
                </div>
                <div class="max-w-xl relative z-10">
                    <?php echo $__env->make('Sistemas_IT.profile.partials.delete-user-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('Sistemas_IT.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views\Sistemas_IT/profile/edit.blade.php ENDPATH**/ ?>