<button <?php echo e($attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center justify-center px-5 py-3 bg-red-600 border border-transparent rounded-xl font-semibold text-sm text-white shadow-sm transition-all duration-200 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed'
])); ?>>
    <?php echo e($slot); ?>

</button>
<?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views\Sistemas_IT/components/danger-button.blade.php ENDPATH**/ ?>