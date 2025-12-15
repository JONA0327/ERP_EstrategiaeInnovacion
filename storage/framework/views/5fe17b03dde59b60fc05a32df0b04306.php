<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', 'Sistema de Tickets - E&I Tecnología'); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js', 'resources/css/Sistemas_IT/inventario-index.css']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-100">
    <?php echo $__env->make('Sistemas_IT.layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->yieldContent('header'); ?>

    <!-- Page Content -->
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\Users\SISTEMAS\Downloads\ERP EstrategiaeInnovacion\Sistema_Tickets_E-I\resources\views/Sistemas_IT/layouts/master.blade.php ENDPATH**/ ?>