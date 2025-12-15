<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <?php echo e(__('Evaluación de Desempeño')); ?>

            </h2>
            <a href="<?php echo e(route('recursos-humanos.index')); ?>" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al Panel de RH
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <?php $__env->startSection('title', 'Evaluación de Desempeño'); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-indigo-500">
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Selecciona un Área
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Haz clic en un departamento para desplegar su lista de personal.</p>
                    </div>

                    
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-8">
                        <?php
                            $areasEval = ['Logistica', 'Legal', 'Pedimentos', 'Anexo 24', 'Auditoría', 'TI', 'RH'];
                        ?>

                        <?php $__currentLoopData = $areasEval; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button 
                                onclick="cargarEmpleados('<?php echo e($area); ?>')"
                                class="area-btn group relative flex flex-col items-center justify-center p-4 border rounded-xl hover:shadow-md transition-all duration-200 bg-gray-50 hover:bg-indigo-50 hover:border-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                data-area="<?php echo e($area); ?>">
                                
                                <div class="mb-2 p-2 rounded-full bg-white shadow-sm group-hover:scale-110 transition-transform">
                                    
                                    <?php if($area == 'TI'): ?>
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    <?php elseif($area == 'RH'): ?>
                                        <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    <?php elseif($area == 'Logistica' || $area == 'Pedimentos'): ?>
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    <?php else: ?>
                                        <svg class="w-6 h-6 text-gray-600 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    <?php endif; ?>
                                </div>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700 text-center"><?php echo e($area); ?></span>
                                
                                
                                <div class="absolute -bottom-3 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-indigo-500 rounded-full hidden active-indicator"></div>
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    
                    <div id="lista-empleados-container" class="bg-gray-50 rounded-lg p-6 hidden animate-fade-in-down border border-gray-200">
                        <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-2">
                            <h4 class="text-lg font-semibold text-gray-800">
                                Empleados de: <span id="titulo-area-seleccionada" class="text-indigo-600"></span>
                            </h4>
                            <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded" id="contador-empleados">0 empleados</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 rounded-l-lg">Nombre</th>
                                        <th scope="col" class="px-4 py-3">Puesto</th>
                                        <th scope="col" class="px-4 py-3">Estado Evaluación</th>
                                        <th scope="col" class="px-4 py-3 rounded-r-lg text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-body-empleados">
                                    
                                </tbody>
                            </table>
                        </div>
                        
                        
                        <div id="empty-state" class="hidden text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay empleados</h3>
                            <p class="mt-1 text-sm text-gray-500">No se encontraron empleados registrados en esta área.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        // Datos simulados (puedes inyectar datos reales del backend aquí más adelante)
        const empleadosData = {
            'Logistica': [
                { id: 1, nombre: 'Juan Pérez', puesto: 'Coordinador de Tráfico', estado: 'Pendiente' },
                { id: 2, nombre: 'María Lopez', puesto: 'Analista de Rutas', estado: 'Completado' },
                { id: 3, nombre: 'Carlos Ruiz', puesto: 'Auxiliar Logístico', estado: 'En proceso' }
            ],
            'Legal': [
                { id: 4, nombre: 'Lic. Ana Suarez', puesto: 'Abogado Corporativo', estado: 'Pendiente' },
                { id: 5, nombre: 'Roberto Diaz', puesto: 'Asistente Legal', estado: 'Completado' }
            ],
            'Pedimentos': [
                { id: 6, nombre: 'Pedro Gomez', puesto: 'Glosa', estado: 'Pendiente' },
                { id: 7, nombre: 'Lucia Méndez', puesto: 'Capturista', estado: 'Pendiente' }
            ],
            'Anexo 24': [
                { id: 8, nombre: 'Ing. Sofia Torres', puesto: 'Auditor de Anexo', estado: 'En proceso' }
            ],
            'Auditoría': [
                { id: 9, nombre: 'Miguel Angel', puesto: 'Auditor Senior', estado: 'Completado' }
            ],
            'TI': [
                { id: 10, nombre: 'Jona Dev', puesto: 'Full Stack Developer', estado: 'Completado' },
                { id: 11, nombre: 'SysAdmin', puesto: 'Infraestructura', estado: 'Pendiente' }
            ],
            'RH': [
                { id: 12, nombre: 'Gerente RH', puesto: 'Gerencia', estado: 'En proceso' }
            ]
        };

        function cargarEmpleados(area) {
            // UI Update
            document.querySelectorAll('.area-btn').forEach(btn => {
                btn.classList.remove('bg-indigo-50', 'border-indigo-200', 'ring-2', 'ring-indigo-500');
                btn.querySelector('.active-indicator').classList.add('hidden');
                
                if(btn.dataset.area === area) {
                    btn.classList.add('bg-indigo-50', 'border-indigo-200', 'ring-2', 'ring-indigo-500');
                    btn.querySelector('.active-indicator').classList.remove('hidden');
                }
            });

            const container = document.getElementById('lista-empleados-container');
            const titulo = document.getElementById('titulo-area-seleccionada');
            const contador = document.getElementById('contador-empleados');
            const tbody = document.getElementById('tabla-body-empleados');
            const emptyState = document.getElementById('empty-state');

            container.classList.remove('hidden');
            titulo.innerText = area;
            tbody.innerHTML = '';

            const empleados = empleadosData[area] || [];
            contador.innerText = `${empleados.length} empleados`;

            if (empleados.length === 0) {
                emptyState.classList.remove('hidden');
                document.querySelector('table').classList.add('hidden');
            } else {
                emptyState.classList.add('hidden');
                document.querySelector('table').classList.remove('hidden');

                empleados.forEach(emp => {
                    const row = `
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                                    ${emp.nombre.charAt(0)}
                                </div>
                                ${emp.nombre}
                            </td>
                            <td class="px-4 py-3">${emp.puesto}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    ${emp.estado === 'Completado' ? 'bg-green-100 text-green-800' : 
                                      (emp.estado === 'En proceso' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')}">
                                    ${emp.estado}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button class="text-indigo-600 hover:text-indigo-900 text-sm font-medium hover:underline">
                                    Evaluar
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }
        }
    </script>
    <style>
        .animate-fade-in-down {
            animation: fadeInDown 0.3s ease-out;
        }
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <?php $__env->stopPush(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/Recursos_Humanos/evaluacion/index.blade.php ENDPATH**/ ?>