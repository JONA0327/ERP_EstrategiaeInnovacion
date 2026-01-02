

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Editar Capacitación</h2>
        <a href="<?php echo e(route('rh.capacitacion.manage')); ?>" class="text-indigo-600 hover:text-indigo-800">&larr; Volver</a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="<?php echo e(route('rh.capacitacion.update', $video->id)); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Título</label>
                <input type="text" name="titulo" value="<?php echo e($video->titulo); ?>" class="w-full border rounded px-3 py-2 text-gray-700 focus:outline-none focus:border-indigo-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Descripción</label>
                <textarea name="descripcion" rows="4" class="w-full border rounded px-3 py-2 text-gray-700"><?php echo e($video->descripcion); ?></textarea>
            </div>

            
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
                <label class="block text-yellow-800 font-bold mb-2">Reemplazar Video (Opcional)</label>
                <p class="text-sm text-yellow-600 mb-2">Sube un archivo solo si quieres cambiar el video actual.</p>
                <input type="file" name="video" accept="video/*" class="w-full text-sm">
            </div>

            
            <div class="mb-6 border-t pt-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Documentos Complementarios</h3>

                
                <?php if($video->adjuntos->count() > 0): ?>
                    <div class="mb-4 space-y-2">
                        <?php $__currentLoopData = $video->adjuntos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adjunto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex justify-between items-center bg-gray-50 p-2 rounded border">
                                <span class="text-sm text-gray-600 flex items-center">
                                    📄 <?php echo e($adjunto->titulo); ?>

                                </span>
                                
                                <button type="button" onclick="confirmDeleteAdjunto('<?php echo e(route('rh.capacitacion.destroyAdjunto', $adjunto->id)); ?>')" class="text-red-500 hover:text-red-700 text-xs font-bold uppercase">
                                    Eliminar
                                </button>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

                
                <label class="block text-gray-700 font-bold mb-2">Agregar Documentos (PDF, Word, Excel)</label>
                <input type="file" name="adjuntos[]" multiple class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700">
                <p class="text-xs text-gray-500 mt-1">Puedes seleccionar varios archivos a la vez.</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white font-bold py-2 px-6 rounded hover:bg-indigo-700 transition">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>


<form id="delete-adjunto-form" action="" method="POST" class="hidden">
    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
</form>
<script>
    function confirmDeleteAdjunto(url) {
        if(confirm('¿Seguro que quieres eliminar este documento?')) {
            document.getElementById('delete-adjunto-form').action = url;
            document.getElementById('delete-adjunto-form').submit();
        }
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/Recursos_Humanos/capacitacion/edit.blade.php ENDPATH**/ ?>