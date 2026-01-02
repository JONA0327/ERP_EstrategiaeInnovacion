

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto py-8 px-4">
    <div class="mb-4">
        <a href="<?php echo e(route('capacitacion.index')); ?>" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
            &larr; Volver a Capacitaciones
        </a>
    </div>

    <div class="bg-black rounded-xl overflow-hidden shadow-2xl">
        <video controls class="w-full aspect-video" controlsList="nodownload">
            <source src="<?php echo e(asset('storage/' . $video->archivo_path)); ?>" type="video/mp4">
            Tu navegador no soporta la reproducción de video.
        </video>
    </div>

    <div class="mt-6 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <h1 class="text-2xl font-bold text-gray-900"><?php echo e($video->titulo); ?></h1>
        <div class="mt-2 text-sm text-gray-500">
            Publicado el <?php echo e($video->created_at->format('d/m/Y')); ?>

        </div>
        <hr class="my-4 border-gray-100">
        <div class="prose max-w-none text-gray-700">
            <?php echo e($video->descripcion); ?>

        </div>
        <?php if($video->adjuntos->isNotEmpty()): ?>
            <div class="mt-8 border-t pt-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Material de Apoyo y Descargas</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php $__currentLoopData = $video->adjuntos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adjunto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(asset('storage/' . $adjunto->archivo_path)); ?>" target="_blank" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 border border-gray-200 transition group">
                            <span class="text-2xl mr-3 group-hover:scale-110 transition">📄</span>
                            <div>
                                <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-600"><?php echo e($adjunto->titulo); ?></p>
                                <p class="text-xs text-gray-500">Clic para descargar</p>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/Recursos_Humanos/capacitacion/show.blade.php ENDPATH**/ ?>