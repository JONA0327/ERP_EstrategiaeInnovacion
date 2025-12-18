<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', 'Sistemas IT'); ?> | Estrategia e Innovación</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    
    <?php echo $__env->yieldPushContent('styles'); ?> 
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-600">
    
    <div class="min-h-screen flex flex-col">
        <?php echo $__env->make('Sistemas_IT.layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main class="flex-grow">
            <div class="py-8">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </div>
        </main>

        <footer class="bg-white border-t border-slate-200 mt-auto py-6">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <p class="text-xs text-slate-400">
                    &copy; <?php echo e(date('Y')); ?> Estrategia e Innovación. Departamento de Sistemas.
                </p>
            </div>
        </footer>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/Sistemas_IT/layouts/master.blade.php ENDPATH**/ ?>