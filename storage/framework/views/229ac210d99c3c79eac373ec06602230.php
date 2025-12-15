<?php $__env->startSection('title', 'Recursos Humanos - Portal Interno'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/Recursos_Humanos/index.css','resources/js/Recursos_Humanos/index.js']); ?>
    <main class="relative overflow-hidden bg-gradient-to-br from-white via-blue-50 to-blue-100">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-32 -left-20 w-96 h-96 bg-blue-200/40 blur-3xl rounded-full"></div>
            <div class="absolute top-40 -right-24 w-96 h-96 bg-blue-300/30 blur-3xl rounded-full"></div>
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-32 bg-gradient-to-t from-white"></div>
        </div>

        <div class="relative max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-10">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-slate-900">Administración de Recursos Humanos</h1>
                <p class="mx-auto mt-2 max-w-2xl text-sm text-slate-600">Accede a los módulos del área.</p>
            </div>

            <?php
                $cards = [
                    [
                        'title' => 'Expedientes',
                        'description' => 'Administra expedientes y documentación del personal.',
                        'href' => route('rh.expedientes.index'),
                        'cta' => 'Abrir módulo',
                        'status' => 'Disponible',
                        'icon' => 'M3 7.5A2.25 2.25 0 015.25 5.25h4.379c.597 0 1.17.237 1.59.659l1.872 1.872A2.25 2.25 0 0114.34 9.372L9.75 9.375H5.25A2.25 2.25 0 003 11.625v6.375A2.25 2.25 0 005.25 20.25h13.5A2.25 2.25 0 0021 18V9a.75.75 0 00-1.5 0v9a.75.75 0 01-.75.75H5.25a.75.75 0 01-.75-.75V11.625A.75.75 0 015.25 10.875H9.75',
                    ],
                    [
                        'title' => 'Reloj Checador',
                        'description' => 'Control de asistencia y registro de entradas/salidas.',
                        'href' => route('rh.reloj.index'),
                        'cta' => 'Abrir módulo',
                        'status' => 'Disponible',
                        'icon' => 'M12 6v6h4.5m3 0a9 9 0 11-18 0 9 9 0 0118 0z',
                    ],
                    [
                        'title' => 'Evaluación de Desempeño',
                        'description' => 'Gestiona evaluaciones y seguimiento de objetivos.',
                        'href' => route('recursos-humanos.index') . '#evaluaciones',
                        'cta' => 'En construcción',
                        'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h15.75c.621 0 1.125.504 1.125 1.125v4.5A2.25 2.25 0 0118.75 19.875H5.25A2.25 2.25 0 013 17.625v-4.5z M6 11.25V8.25A6 6 0 1118 8.25v3',
                    ],
                ];
            ?>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="relative overflow-hidden rounded-3xl border border-blue-100/80 bg-white/90 backdrop-blur shadow-lg shadow-blue-500/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                        <div class="absolute -top-20 -right-16 w-40 h-40 bg-gradient-to-br from-blue-200/50 to-transparent blur-3xl"></div>
                        <div class="relative p-8">
                            <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-100 to-blue-200 text-blue-600 shadow-inner shadow-white/40 mx-auto mb-6">
                                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($card['icon']); ?>"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-center text-slate-900 mb-3"><?php echo e($card['title']); ?></h3>
                            <p class="text-center text-slate-600 leading-relaxed mb-8"><?php echo e($card['description']); ?></p>
                            <a href="<?php echo e($card['href']); ?>" class="group inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition-all duration-300 hover:from-blue-700 hover:to-blue-800">
                                <svg class="w-5 h-5 mr-2 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <?php echo e($card['cta']); ?>

                            </a>
                            <p class="mt-3 text-center text-[11px] text-slate-400"><?php echo e($card['status'] ?? 'En construcción'); ?></p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SISTEMAS\Downloads\ERP EstrategiaeInnovacion\Sistema_Tickets_E-I\resources\views/Recursos_Humanos/index.blade.php ENDPATH**/ ?>