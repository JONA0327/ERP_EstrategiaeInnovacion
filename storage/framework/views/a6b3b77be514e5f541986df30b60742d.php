<?php $__env->startSection('title', 'Matriz de Seguimiento - Logística'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/Logistica/matriz-seguimiento.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        // Variable global para transportes
        window.transportes = <?php echo json_encode($transportes->groupBy('tipo_operacion'), 15, 512) ?>;
    </script>
    <script src="<?php echo e(asset('js/Logistica/matriz-seguimiento.js')); ?>?v=<?php echo e(md5(time())); ?>"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <main class="relative overflow-hidden bg-gradient-to-br from-white via-blue-50 to-blue-100 min-h-screen">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-32 -left-20 w-96 h-96 bg-blue-200/40 blur-3xl rounded-full"></div>
            <div class="absolute top-40 -right-24 w-96 h-96 bg-blue-300/30 blur-3xl rounded-full"></div>
        </div>

        <div class="relative max-w-full mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <a href="<?php echo e(route('logistica.index')); ?>" class="inline-flex items-center text-blue-600 hover:text-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Regresar
                    </a>
                </div>
                <h1 class="text-3xl font-bold text-slate-900">Matriz de Seguimiento</h1>
                <p class="text-slate-600 mt-2">Control y seguimiento de operaciones logísticas con cálculo automático de días de tránsito</p>
            </div>

            <!-- Controles -->
            <div class="mb-6 bg-white/90 backdrop-blur rounded-2xl border border-blue-100 shadow-lg p-6">
                <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                    <div class="flex flex-wrap gap-3">
                        <button onclick="abrirModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Nueva Operación
                        </button>
                        <button onclick="abrirModalPostOperaciones()" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            Gestionar Post-Operaciones
                        </button>
                        <?php if(isset($esAdmin) && $esAdmin): ?>
                        <button onclick="abrirModalCamposPersonalizados()" class="inline-flex items-center px-4 py-2 bg-slate-600 text-white rounded-xl hover:bg-slate-700 transition-colors shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Configurar Campos
                        </button>
                        <?php endif; ?>
                    </div>
                    <!-- Filtros por Cliente y Ejecutivo -->
                    <div class="flex flex-wrap gap-4 items-center">
                        <!-- Filtro por Cliente -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-slate-600">Cliente:</label>
                            <select id="filtroCliente" class="px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-sm min-w-[200px]" onchange="aplicarFiltros()">
                                <option value="todos" <?php echo e((!isset($filtroCliente) || $filtroCliente === 'todos') ? 'selected' : ''); ?>>Todos los clientes</option>
                                <?php $__currentLoopData = $clientesUnicos ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $clienteUnico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($clienteUnico); ?>" <?php echo e((isset($filtroCliente) && $filtroCliente === $clienteUnico) ? 'selected' : ''); ?>><?php echo e($clienteUnico); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        
                        <?php if($esAdmin): ?>
                        <!-- Filtro por Ejecutivo (solo admin) -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-slate-600">Ejecutivo:</label>
                            <select id="filtroEjecutivo" class="px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-sm min-w-[200px]" onchange="aplicarFiltros()">
                                <option value="todos" <?php echo e((!isset($filtroEjecutivo) || $filtroEjecutivo === 'todos') ? 'selected' : ''); ?>>Todos los ejecutivos</option>
                                <?php $__currentLoopData = $ejecutivosUnicos ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ejecutivoUnico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($ejecutivoUnico); ?>" <?php echo e((isset($filtroEjecutivo) && $filtroEjecutivo === $ejecutivoUnico) ? 'selected' : ''); ?>><?php echo e($ejecutivoUnico); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Botón limpiar filtros -->
                        <button type="button" onclick="limpiarFiltros()" class="px-3 py-2 bg-slate-100 border border-slate-300 rounded-lg hover:bg-slate-200 transition-colors text-sm flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Limpiar
                        </button>
                        
                        <!-- Contador de registros -->
                        <span class="text-sm text-slate-500 ml-4">
                            <span class="font-semibold text-slate-700"><?php echo e(count($operaciones)); ?></span> operaciones
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tabla Principal -->
            <div class="table-container rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="table-header">
                            <tr>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[50px]">No.</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Ejecutivo</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Operación</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Cliente</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Proveedor o Cliente</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Fecha de Embarque</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">No. De Factura</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Tipo de Carga</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Incoterm</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">T. Operación</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[80px]">Clave</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Referencia Interna</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Aduana</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[80px]">A.A</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Referencia A.A</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[80px]">No Ped</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Transporte</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Fecha de Arribo a Aduana</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Guía //BL</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Puerto de Salida</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Status</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Fecha de Modulación</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[150px]">Fecha de Arribo a Planta</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Resultado</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[80px]">Target</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[100px]">Días en Tránsito</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Post-Operaciones</th>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Comentarios</th>
                                <?php
                                    // Determinar qué campos personalizados mostrar según el usuario
                                    $camposVisibles = collect();
                                    if (isset($camposPersonalizados)) {
                                        if (isset($esAdmin) && $esAdmin) {
                                            // Admin ve todos los campos activos
                                            $camposVisibles = $camposPersonalizados;
                                        } elseif (isset($empleadoActual) && $empleadoActual) {
                                            // Usuario normal ve solo campos asignados a él
                                            $camposVisibles = $camposPersonalizados->filter(function($campo) use ($empleadoActual) {
                                                return $campo->ejecutivos->contains('id', $empleadoActual->id);
                                            });
                                        }
                                    }
                                ?>
                                <?php $__currentLoopData = $camposVisibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px] bg-indigo-50" data-campo-id="<?php echo e($campo->id); ?>">
                                    <div class="flex items-center">
                                        <span class="text-indigo-600 mr-1">★</span>
                                        <?php echo e($campo->nombre); ?>

                                    </div>
                                </th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <th class="px-3 py-4 text-left font-semibold text-slate-700 border-r border-slate-200 min-w-[120px]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200" id="operacionesTable">
                            <?php $__empty_1 = true; $__currentLoopData = $operaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $operacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="table-row" data-operacion-id="<?php echo e($operacion->id); ?>">
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->id); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-900 font-medium"><?php echo e($operacion->ejecutivo ?? 'Sin asignar'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->operacion ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->cliente ?? 'Sin cliente'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->proveedor_o_cliente ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->fecha_embarque ? $operacion->fecha_embarque->format('d/m/Y') : '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->no_factura ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->tipo_carga ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->tipo_incoterm ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->tipo_operacion_enum ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->clave ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->referencia_interna ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->aduana ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->agente_aduanal ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->referencia_aa ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->no_pedimento ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->transporte ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->fecha_arribo_aduana ? $operacion->fecha_arribo_aduana->format('d/m/Y') : '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->guia_bl ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->puerto_salida ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200">
                                    <div class="flex flex-col space-y-1">
                                        <!-- Status Manual (prevalece si está en Done) -->
                                        <?php
                                            // Priorizar status_manual si existe y es Done, sino usar status_calculado
                                            $statusFinal = ($operacion->status_manual === 'Done') ? 'Done' : $operacion->status_calculado;
                                            $colorFinal = ($operacion->status_manual === 'Done') ? 'verde' : $operacion->color_status;
                                            $statusDisplay = match($statusFinal) {
                                                'In Process' => 'En Proceso',
                                                'Out of Metric' => 'Fuera de Métrica',
                                                'Done' => 'Completado',
                                                default => $statusFinal ?? 'En Proceso'
                                            };
                                        ?>
                                        <span class="status-badge <?php echo e($colorFinal === 'verde' ? 'status-verde' :
                                            ($colorFinal === 'amarillo' ? 'status-amarillo' :
                                            ($colorFinal === 'rojo' ? 'status-rojo' : 'status-sin-fecha'))); ?> text-xs">
                                            <?php echo e($statusDisplay); ?><?php if($operacion->status_manual === 'Done'): ?> <span class="ml-1">(Manual)</span><?php endif; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->fecha_modulacion ? $operacion->fecha_modulacion->format('d/m/Y') : '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->fecha_arribo_planta ? $operacion->fecha_arribo_planta->format('d/m/Y') : '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->resultado ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600"><?php echo e($operacion->target ?? '-'); ?></td>
                                <td class="px-3 py-4 border-r border-slate-200 text-center">
                                    <?php if($operacion->dias_transito !== null): ?>
                                        <span class="dias-indicator <?php echo e($operacion->color_status === 'verde' ? 'dias-verde' :
                                            ($operacion->color_status === 'amarillo' ? 'dias-amarillo' : 'dias-rojo')); ?>">
                                            <?php echo e(abs($operacion->dias_transito)); ?> días
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-4 border-r border-slate-200 text-center">
                                    <button onclick="verPostOperaciones(<?php echo e($operacion->id); ?>)"
                                            class="action-button btn-view"
                                            title="Ver post-operaciones">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                        </svg>
                                    </button>
                                </td>
                                <td class="px-3 py-4 border-r border-slate-200 text-center">
                                    <button onclick="verComentarios(<?php echo e($operacion->id); ?>)"
                                            class="action-button btn-view"
                                            title="Ver comentarios">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    </button>
                                </td>
                                <?php $__currentLoopData = $camposVisibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $valorCampo = $operacion->valoresCamposPersonalizados->where('campo_personalizado_id', $campo->id)->first();
                                    $valorMostrar = $valorCampo ? $valorCampo->valor : '-';
                                    if ($campo->tipo === 'fecha' && $valorCampo && $valorCampo->valor) {
                                        try {
                                            $valorMostrar = \Carbon\Carbon::parse($valorCampo->valor)->format('d/m/Y');
                                        } catch (\Exception $e) {
                                            $valorMostrar = $valorCampo->valor;
                                        }
                                    }
                                ?>
                                <td class="px-3 py-4 border-r border-slate-200 text-slate-600 bg-indigo-50/30 campo-personalizado-cell" 
                                    data-campo-id="<?php echo e($campo->id); ?>" 
                                    data-operacion-id="<?php echo e($operacion->id); ?>">
                                    <div class="flex items-center justify-between">
                                        <span class="valor-campo"><?php echo e($valorMostrar); ?></span>
                                        <button onclick="editarCampoPersonalizado(<?php echo e($operacion->id); ?>, <?php echo e($campo->id); ?>, '<?php echo e($campo->tipo); ?>', '<?php echo e(addslashes($campo->nombre)); ?>')" 
                                                class="text-indigo-400 hover:text-indigo-600 ml-2" title="Editar">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <td class="px-3 py-4 border-r border-slate-200">
                                    <div class="flex space-x-1">
                                        <button onclick="verHistorial(<?php echo e($operacion->id); ?>)"
                                                class="action-button btn-view"
                                                title="Ver historial">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <button onclick="editarOperacion(<?php echo e($operacion->id); ?>)"
                                                class="action-button btn-edit"
                                                title="Editar operación">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <?php if($operacion->status_manual !== 'Done'): ?>
                                        <button onclick="marcarComoDone(<?php echo e($operacion->id); ?>)"
                                                class="action-button btn-done"
                                                title="Marcar como Done (Manual)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                        <?php endif; ?>

                                        <button onclick="eliminarOperacion(<?php echo e($operacion->id); ?>)"
                                                class="action-button btn-delete"
                                                title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="<?php echo e(25 + count($camposVisibles ?? [])); ?>" class="px-3 py-8 text-center text-slate-500">
                                    <div class="flex flex-col items-center space-y-2">
                                        <i class="fas fa-inbox text-3xl text-slate-400"></i>
                                        <p class="text-sm font-medium">No hay operaciones registradas</p>
                                        <p class="text-xs">Haga clic en "Nueva Operación" para comenzar</p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>



            <!-- Footer/Paginación -->
            <div class="mt-6 bg-white/90 backdrop-blur rounded-2xl border border-blue-100 shadow-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-600">
                        Mostrando operaciones con días de tránsito calculados automáticamente
                    </div>
                    <div class="flex gap-2 text-xs flex-wrap">
                        <span class="status-badge status-verde">✓ Done Manual: Completado por usuario</span>
                        <span class="status-badge status-amarillo">En Proceso: Días ≤ target desde aduana</span>
                        <span class="status-badge status-rojo">Fuera Métrica: Días > target desde aduana</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para Ver Historial -->
    <div id="modalHistorial" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-6xl max-h-[90vh] flex flex-col">
            <!-- Header fijo -->
            <div class="bg-white border-b border-slate-200 p-4 flex justify-between items-center rounded-t-xl">
                <h2 class="text-lg font-semibold text-slate-800">
                    <span class="text-blue-600 mr-2 text-xl">📊</span>
                    Historial de Operación
                </h2>
                <button onclick="cerrarModalHistorial()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">
                    <span>&times;</span>
                </button>
            </div>

            <!-- Contenido del historial con scroll -->
            <div class="flex-1 overflow-y-auto p-4">
                <div id="historialContent">
                    <div class="text-center py-8">
                        <div class="loading-spinner"></div>
                        <p class="text-slate-500 mt-2">Cargando historial...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Añadir Post-Operación -->
    <div id="modalPostOperacion" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
            <!-- Header fijo -->
            <div class="bg-white border-b border-slate-200 p-4 flex justify-between items-center rounded-t-xl">
                <h2 class="text-lg font-semibold text-slate-800">
                    <span class="text-green-600 mr-2 text-xl">➕</span>
                    Añadir Post-Operación
                </h2>
                <button onclick="cerrarModalPostOperacion()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">
                    <span>&times;</span>
                </button>
            </div>

            <!-- Contenido del formulario -->
            <div class="flex-1 overflow-y-auto p-4">
                <form id="formPostOperacion" onsubmit="guardarPostOperacion(event)" class="space-y-4">
                    <?php echo csrf_field(); ?>

                    <!-- Nombre de Post-Operación -->
                    <div>
                        <label for="nombre_post_operacion" class="block text-sm font-medium text-slate-700 mb-1">
                            Nombre de Post-Operación <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre" id="nombre_post_operacion" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ej: Entrega de documentos, Revisión final...">
                    </div>

                    <!-- Operación Relacionada -->
                    <div>
                        <label for="operacion_relacionada" class="block text-sm font-medium text-slate-700 mb-1">
                            Operación Relacionada
                        </label>
                        <select name="operacion_logistica_id" id="operacion_relacionada"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Sin operación específica</option>
                            <?php $__currentLoopData = $operaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $operacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($operacion->id); ?>">
                                    <?php echo e($operacion->operacion ?? 'Operación #' . $operacion->id); ?> - <?php echo e($operacion->cliente ?? 'Sin cliente'); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="descripcion_post_operacion" class="block text-sm font-medium text-slate-700 mb-1">
                            Descripción
                        </label>
                        <textarea name="descripcion" id="descripcion_post_operacion" rows="3"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Descripción detallada de la post-operación..."></textarea>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" onclick="cerrarModalPostOperacion()"
                                class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Guardar Post-Operación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Añadir Nueva Operación -->
    <div id="modalOperacion" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
            <!-- Header fijo -->
            <div class="bg-white border-b border-slate-200 p-4 rounded-t-xl">
                <div class="flex justify-between items-center mb-2">
                    <h2 id="modalTitle" class="text-lg font-semibold text-slate-800">
                        <span class="text-blue-600 mr-2 text-xl">⊕</span>
                        Añadir Nueva Operación
                    </h2>
                    <button onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold" title="Cerrar modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="flex items-center space-x-2 text-xs text-amber-700 bg-amber-50 px-3 py-2 rounded-lg border border-amber-200">
                    <span>🔒</span>
                    <span><strong>Protegido:</strong> Este modal no se cierra al hacer clic fuera para evitar pérdida de datos. Use el botón × para cerrar.</span>
                </div>
            </div>

            <!-- Contenido del formulario con scroll -->
            <div class="flex-1 overflow-y-auto p-6">
                <form id="formOperacion" class="space-y-6">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" id="operacionId" name="operacion_id" value="">
                        <input type="hidden" id="isEditing" name="_method" value="">

                        <!-- PASO 1: Tipo de Operación -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-5 border-l-4 border-blue-500">
                            <div class="flex items-center mb-4">
                                <div class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">1</div>
                                <h3 class="text-lg font-bold text-slate-800">Tipo de Operación</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">¿Qué tipo de operación es? *</label>
                                    <select name="operacion" required class="form-input text-base">
                                        <option value="">Seleccionar...</option>
                                        <option value="IMPORTACION">📦 Importación</option>
                                        <option value="EXPORTACION">🚚 Exportación</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">¿Qué transporte utilizará? *</label>
                                    <select name="tipo_operacion_enum" required onchange="actualizarTransportes(); calcularTargetAutomatico();" class="form-input text-base">
                                        <option value="">Seleccionar...</option>
                                        <option value="Terrestre" data-target="3">🚛 Terrestre (3 días)</option>
                                        <option value="Aerea" data-target="3">✈️ Aérea (3 días)</option>
                                        <option value="Ferrocarril" data-target="3">🚂 Ferrocarril (3 días)</option>
                                        <option value="Maritima" data-target="7">🚢 Marítima (7 días)</option>
                                    </select>
                                    <p class="text-xs text-slate-500 mt-1">💡 El tiempo estimado se calcula automáticamente</p>
                                </div>
                            </div>
                        </div>

                        <!-- PASO 2: Cliente y Responsable -->
                        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl p-5 border-l-4 border-emerald-500">
                            <div class="flex items-center mb-4">
                                <div class="bg-emerald-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">2</div>
                                <h3 class="text-lg font-bold text-slate-800">Cliente y Responsable</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold text-slate-700">Cliente *</label>
                                        <?php if(isset($esAdmin) && $esAdmin): ?>
                                        <button type="button" onclick="mostrarNuevoCliente()"
                                                class="text-xs text-emerald-600 hover:text-emerald-800 font-semibold flex items-center">
                                            <span class="mr-1">+</span> Agregar nuevo
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <select name="cliente" id="clienteSelect" required class="form-input text-base w-full">
                                        <option value="">Selecciona un cliente</option>
                                        <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($cliente->cliente); ?>"><?php echo e($cliente->cliente); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <div id="nuevoClienteForm" class="hidden mt-3 p-3 bg-white border-2 border-emerald-200 rounded-lg shadow-sm">
                                        <input type="text" id="nuevoClienteNombre" placeholder="Nombre del nuevo cliente" class="form-input mb-2">
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="guardarNuevoCliente()"
                                                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 flex items-center">
                                                    ✓ Guardar</button>
                                            <button type="button" onclick="cancelarNuevoCliente()"
                                                    class="px-4 py-2 bg-slate-400 text-white rounded-lg text-sm hover:bg-slate-500">Cancelar</button>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Ejecutivo Responsable *</label>
                                    <?php
                                        $valorEjecutivo = '';
                                        $soloLectura = false;
                                        
                                        if (isset($esAdmin) && isset($empleadoActual)) {
                                            if (!$esAdmin && $empleadoActual) {
                                                $valorEjecutivo = $empleadoActual->nombre;
                                                $soloLectura = true;
                                            }
                                        }
                                    ?>
                                    <?php if(isset($esAdmin) && $esAdmin): ?>
                                        <select name="ejecutivo" id="ejecutivoSelect" required class="form-input text-base w-full">
                                            <option value="">Selecciona un ejecutivo</option>
                                            <?php $__currentLoopData = $empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($empleado->nombre); ?>" <?php echo e($empleado->nombre == $valorEjecutivo ? 'selected' : ''); ?>><?php echo e($empleado->nombre); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="text" name="ejecutivo" required class="form-input text-base" 
                                               placeholder="Nombre del ejecutivo" 
                                               value="<?php echo e($valorEjecutivo); ?>"
                                               readonly>
                                    <?php endif; ?>
                                    <?php if($soloLectura): ?>
                                        <p class="text-xs text-slate-500 mt-1">📌 Tu nombre está asignado automáticamente</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- PASO 3: Información de la Operación -->
                        <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-xl p-5 border-l-4 border-amber-500">
                            <div class="flex items-center mb-4">
                                <div class="bg-amber-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">3</div>
                                <h3 class="text-lg font-bold text-slate-800">Detalles de la Operación</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Proveedor/Cliente Final *</label>
                                    <input type="text" name="proveedor_o_cliente" required class="form-input text-base" placeholder="Nombre del proveedor o cliente">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Número de Factura *</label>
                                    <input type="text" name="no_factura" required class="form-input text-base" placeholder="Ej: FAC-12345">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tipo de Carga</label>
                                    <select name="tipo_carga" class="form-input text-base w-full">
                                        <option value="">Seleccione...</option>
                                        <option value="FCL">FCL (Full Container Load)</option>
                                        <option value="LCL">LCL (Less than Container Load)</option>
                                    </select>
                                    <input type="text" name="tipo_carga_detalle" class="form-input text-base mt-2" placeholder="Cantidad de pallets (opcional)">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tipo de Incoterm</label>
                                    <select name="tipo_incoterm" class="form-input text-base w-full">
                                        <option value="">Seleccione...</option>
                                        <option value="EXW">EXW - Ex Works</option>
                                        <option value="FCA">FCA - Free Carrier</option>
                                        <option value="FAS">FAS - Free Alongside Ship</option>
                                        <option value="FOB">FOB - Free On Board</option>
                                        <option value="CFR">CFR - Cost and Freight</option>
                                        <option value="CIF">CIF - Cost, Insurance and Freight</option>
                                        <option value="CPT">CPT - Carriage Paid To</option>
                                        <option value="CIP">CIP - Carriage and Insurance Paid To</option>
                                        <option value="DAP">DAP - Delivered at Place</option>
                                        <option value="DPU">DPU - Delivered at Place Unloaded</option>
                                        <option value="DDP">DDP - Delivered Duty Paid</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Clave del Pedimento *</label>
                                    <select name="clave" required class="w-full px-4 py-3 text-base border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                        <option value="">Seleccione una clave</option>
                                        <?php $__currentLoopData = $pedimentos ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedimento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($pedimento->clave); ?>"><?php echo e($pedimento->clave); ?> - <?php echo e($pedimento->descripcion); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Referencia Interna *</label>
                                    <input type="text" name="referencia_interna" required class="form-input text-base" placeholder="Referencia para seguimiento">
                                </div>
                            </div>
                        </div>

                        <!-- PASO 4: Fecha y Aduana -->
                        <div class="bg-gradient-to-r from-violet-50 to-purple-50 rounded-xl p-5 border-l-4 border-violet-500">
                            <div class="flex items-center mb-4">
                                <div class="bg-violet-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">4</div>
                                <h3 class="text-lg font-bold text-slate-800">Fecha y Ubicación</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">📅 Fecha de Embarque *</label>
                                    <input type="date" name="fecha_embarque" required class="form-input text-base">
                                    <p class="text-xs text-violet-600 mt-1 font-medium">✓ Esta es la única fecha obligatoria</p>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold text-slate-700">Aduana de Despacho *</label>
                                        <?php if(isset($esAdmin) && $esAdmin): ?>
                                        <button type="button" onclick="mostrarNuevaAduana()"
                                                class="text-xs text-violet-600 hover:text-violet-800 font-semibold flex items-center">
                                            <span class="mr-1">+</span> Agregar nueva
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <select name="aduana" id="aduanaSelect" required class="form-input text-base w-full">
                                        <option value="">Selecciona una aduana</option>
                                        <?php $__currentLoopData = $aduanas ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aduana): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($aduana->aduana); ?><?php echo e($aduana->seccion); ?>" data-denominacion="<?php echo e($aduana->denominacion); ?>"><?php echo e($aduana->aduana); ?><?php echo e($aduana->seccion); ?> - <?php echo e($aduana->denominacion); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <div id="nuevaAduanaForm" class="hidden mt-3 p-3 bg-white border-2 border-violet-200 rounded-lg shadow-sm">
                                        <div class="grid grid-cols-3 gap-2 mb-2">
                                            <input type="text" id="nuevaAduanaCodigo" placeholder="Código" class="form-input text-sm" maxlength="2">
                                            <input type="text" id="nuevaAduanaSeccion" placeholder="Sección" class="form-input text-sm" maxlength="1" value="0">
                                            <input type="text" id="nuevaAduanaDenominacion" placeholder="Nombre" class="form-input text-sm col-span-3">
                                        </div>
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="guardarNuevaAduana()"
                                                    class="px-4 py-2 bg-violet-600 text-white rounded-lg text-sm hover:bg-violet-700 flex items-center">
                                                    ✓ Guardar</button>
                                            <button type="button" onclick="cancelarNuevaAduana()"
                                                    class="px-4 py-2 bg-slate-400 text-white rounded-lg text-sm hover:bg-slate-500">Cancelar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PASO 5: Agente y Transporte -->
                        <div class="bg-gradient-to-r from-sky-50 to-cyan-50 rounded-xl p-5 border-l-4 border-sky-500">
                            <div class="flex items-center mb-4">
                                <div class="bg-sky-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">5</div>
                                <h3 class="text-lg font-bold text-slate-800">Agente Aduanal y Transporte</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold text-slate-700">Agente Aduanal *</label>
                                        <button type="button" onclick="mostrarNuevoAgente()"
                                                class="text-xs text-sky-600 hover:text-sky-800 font-semibold flex items-center">
                                            <span class="mr-1">+</span> Agregar nuevo
                                        </button>
                                    </div>
                                    <select name="agente_aduanal" id="agenteSelect" required class="form-input text-base w-full">
                                        <option value="">Selecciona un agente aduanal</option>
                                        <?php $__currentLoopData = $agentesAduanales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($agente->agente_aduanal); ?>"><?php echo e($agente->agente_aduanal); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <div id="nuevoAgenteForm" class="hidden mt-3 p-3 bg-white border-2 border-sky-200 rounded-lg shadow-sm">
                                        <input type="text" id="nuevoAgenteNombre" placeholder="Nombre del nuevo agente" class="form-input mb-2">
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="guardarNuevoAgente()"
                                                    class="px-4 py-2 bg-sky-600 text-white rounded-lg text-sm hover:bg-sky-700 flex items-center">
                                                    ✓ Guardar</button>
                                            <button type="button" onclick="cancelarNuevoAgente()"
                                                    class="px-4 py-2 bg-slate-400 text-white rounded-lg text-sm hover:bg-slate-500">Cancelar</button>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold text-slate-700">Empresa de Transporte</label>
                                        <button type="button" onclick="mostrarNuevoTransporte()"
                                                class="text-xs text-sky-600 hover:text-sky-800 font-semibold flex items-center">
                                            <span class="mr-1">+</span> Agregar nuevo
                                        </button>
                                    </div>
                                    <select name="transporte" id="transporteSelect" class="form-input text-base w-full">
                                        <option value="">Selecciona una empresa de transporte</option>
                                        <?php $__currentLoopData = $transportes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transporte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($transporte->transporte); ?>" data-tipo="<?php echo e($transporte->tipo_operacion); ?>"><?php echo e($transporte->transporte); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <div id="nuevoTransporteForm" class="hidden mt-3 p-3 bg-white border-2 border-sky-200 rounded-lg shadow-sm">
                                        <input type="text" id="nuevoTransporteNombre" placeholder="Nombre de la empresa" class="form-input mb-2">
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="guardarNuevoTransporte()"
                                                    class="px-4 py-2 bg-sky-600 text-white rounded-lg text-sm hover:bg-sky-700 flex items-center">
                                                    ✓ Guardar</button>
                                            <button type="button" onclick="cancelarNuevoTransporte()"
                                                    class="px-4 py-2 bg-slate-400 text-white rounded-lg text-sm hover:bg-slate-500">Cancelar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PASO 6: Información Adicional (Opcional) -->
                        <div class="bg-gradient-to-r from-slate-50 to-gray-50 rounded-xl p-5 border-l-4 border-slate-400 border-dashed">
                            <div class="flex items-center mb-4">
                                <div class="bg-slate-400 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">6</div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-800">Información Adicional</h3>
                                    <p class="text-xs text-slate-600">Opcional - Se puede completar después</p>
                                </div>
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                                <p class="text-sm text-blue-700 flex items-center">
                                    <span class="mr-2">💡</span>
                                    <strong>Tip:</strong> Puedes guardar ahora y completar estos datos más tarde durante el proceso
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-2">Fecha Arribo a Aduana</label>
                                    <input type="date" name="fecha_arribo_aduana" class="form-input bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-2">Fecha de Modulación</label>
                                    <input type="date" name="fecha_modulacion" class="form-input bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-2">Fecha Arribo a Planta</label>
                                    <input type="date" name="fecha_arribo_planta" class="form-input bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-2">Número de Pedimento</label>
                                    <input type="text" name="no_pedimento" id="no_pedimento" class="form-input bg-white" placeholder="Ej: 25 24 1029 5002294" maxlength="18">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-2">Referencia del Agente</label>
                                    <input type="text" name="referencia_aa" class="form-input bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-2">Guía/BL</label>
                                    <input type="text" name="guia_bl" class="form-input bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-2">Puerto de Salida</label>
                                    <input type="text" name="puerto_salida" class="form-input bg-white" placeholder="Ej: Shanghai, China">
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-slate-600 mb-2">Comentarios</label>
                                <textarea name="comentarios" rows="2" class="form-input w-full bg-white"
                                         placeholder="Agrega cualquier nota o comentario relevante..."></textarea>
                            </div>
                        </div>

                        <!-- PASO 7: Campos Personalizados (dinámico, según ejecutivo) -->
                        <div id="camposPersonalizadosSection" class="hidden bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-5 border-l-4 border-indigo-500">
                            <div class="flex items-center mb-4">
                                <div class="bg-indigo-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">7</div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-800">Campos Personalizados</h3>
                                    <p class="text-xs text-slate-600">Campos adicionales configurados para esta operación</p>
                                </div>
                            </div>
                            <div id="camposPersonalizadosContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Los campos se cargarán dinámicamente según el ejecutivo -->
                                <p class="text-slate-500 text-sm col-span-2">Cargando campos personalizados...</p>
                            </div>
                        </div>

                        <!-- Status Manual (solo visible al editar) -->
                        <div id="statusManualSection" class="hidden bg-gradient-to-r from-rose-50 to-pink-50 rounded-xl p-5 border-l-4 border-rose-500">
                            <div class="flex items-center mb-4">
                                <div class="bg-rose-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">⚙</div>
                                <h3 class="text-lg font-bold text-slate-800">Control de Estado</h3>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Status Manual</label>
                                <select name="status_manual" id="statusManualSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 bg-white">
                                    <option value="In Process">🔄 In Process (En Proceso)</option>
                                    <option value="Done">✅ Done (Completado)</option>
                                    <option value="Out of Metric">🔴 Out of Metric (Fuera de Métrica)</option>
                                </select>
                                <p class="text-xs text-rose-600 mt-2 font-medium">💡 Cambia manualmente el estado si es necesario</p>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex justify-between items-center pt-6 border-t-2 border-slate-200">
                            <button type="button" onclick="cerrarModal()"
                                    class="px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-700 font-semibold hover:bg-slate-50 transition-all">
                                ✕ Cancelar
                            </button>
                            <button type="submit" id="submitButton"
                                    class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-bold hover:from-blue-700 hover:to-indigo-700 shadow-lg hover:shadow-xl transition-all flex items-center">
                                <span class="mr-2">✓</span> <span id="submitButtonText">Guardar Operación</span>
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Post-Operaciones -->
    <div id="modalPostOperaciones" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
            <!-- Header fijo -->
            <div class="bg-white border-b border-slate-200 p-4 flex justify-between items-center rounded-t-xl">
                <h2 class="text-lg font-semibold text-slate-800">
                    <span class="text-purple-600 mr-2 text-xl">📋</span>
                    Post-Operaciones - Operación #<span id="operacionIdPostOp"></span>
                </h2>
                <button onclick="cerrarModalPostOperaciones()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">
                    <span>&times;</span>
                </button>
            </div>

            <!-- Contenido -->
            <div class="flex-1 overflow-y-auto p-4">
                <!-- Información -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                        <div>
                            <h4 class="text-blue-800 font-semibold mb-1">Gestión de Post-Operaciones</h4>
                            <p class="text-blue-700 text-sm">
                                Aquí puede actualizar el estado de las post-operaciones asignadas a esta operación específica.
                                Los cambios se guardan por operación usando el número de pedimento.
                            </p>
                        </div>
                    </div>
                </div>

                <div id="contenidoPostOperaciones">
                    <!-- Se carga dinámicamente -->
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-slate-50 border-t border-slate-200 p-4 flex justify-between items-center rounded-b-xl">
                <button onclick="cerrarModalPostOperaciones()" class="px-4 py-2 text-slate-600 hover:text-slate-800 transition-colors">
                    Cerrar
                </button>
                <button id="guardarCambiosPostOperaciones" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para Ver/Editar Comentarios -->
    <div id="modalComentarios" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col">
            <!-- Header fijo -->
            <div class="bg-white border-b border-slate-200 p-4 rounded-t-xl">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-lg font-semibold text-slate-800">
                        <span class="text-green-600 mr-2 text-xl">📋</span>
                        Observaciones - Operación #<span id="operacionIdComentarios"></span>
                    </h2>
                    <button onclick="cerrarModalComentarios()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold" title="Cerrar modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="flex items-center space-x-2 text-xs text-green-700 bg-green-50 px-3 py-2 rounded-lg border border-green-200">
                    <span>🔒</span>
                    <span><strong>Protegido:</strong> Este modal no se cierra al hacer clic fuera para evitar pérdida de observaciones. Use el botón × para cerrar.</span>
                </div>
            </div>

            <!-- Contenido -->
            <div class="flex-1 overflow-y-auto p-4">
                <!-- Observaciones actuales -->
                <div class="mb-6">
                    <div id="listaComentarios" class="space-y-3">
                        <!-- Se carga dinámicamente -->
                    </div>
                </div>


            </div>
        </div>
    </div>

    <!-- Modal Global para Gestionar Post-Operaciones -->
    <div id="modalGestionPostOp" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
            <!-- Header fijo -->
            <div class="bg-white border-b border-slate-200 p-4 flex justify-between items-center rounded-t-xl">
                <h2 class="text-lg font-semibold text-slate-800">
                    <span class="text-purple-600 mr-2 text-xl">🔧</span>
                    Gestionar Post-Operaciones Globales
                </h2>
                <button onclick="cerrarModalGestionPostOp()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">
                    <span>&times;</span>
                </button>
            </div>

            <!-- Contenido -->
            <div class="flex-1 overflow-y-auto p-4">
                <p class="text-slate-600 mb-4">Desde aquí puede crear post-operaciones estándar que estarán disponibles para todas las operaciones.</p>

                <!-- Lista de post-operaciones globales -->
                <div class="mb-6">
                    <h3 class="text-md font-semibold text-slate-800 mb-3">Post-Operaciones Disponibles</h3>
                    <div id="listaPostOpGlobales" class="space-y-2">
                        <!-- Se carga dinámicamente -->
                    </div>
                </div>

                <!-- Formulario para crear nueva post-operación global -->
                <div class="p-4 border-t border-slate-200">
                    <h3 class="text-md font-semibold text-slate-800 mb-3">Crear Nueva Post-Operación</h3>
                    <form id="formPostOpGlobal">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="nombrePostOpGlobal"
                                       name="nombre"
                                       required
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       placeholder="Ej: Revisión de documentos">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Descripción
                                </label>
                                <textarea id="descripcionPostOpGlobal"
                                         name="descripcion"
                                         rows="3"
                                         class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                         placeholder="Descripción detallada..."></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end mt-4">
                            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Crear Post-Operación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Alerta (Reemplazo de alert) -->
    <div id="modalAlert" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div id="modalAlertIcon" class="flex-shrink-0">
                        <!-- Icon will be inserted here -->
                    </div>
                    <h3 id="modalAlertTitle" class="text-xl font-semibold text-slate-900"></h3>
                </div>
                <p id="modalAlertMessage" class="text-slate-600 mb-6"></p>
                <div class="flex justify-end">
                    <button onclick="cerrarModalAlert()" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium">
                        Aceptar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación (Reemplazo de confirm) -->
    <div id="modalConfirm" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex-shrink-0">
                        <svg class="w-12 h-12 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h3 id="modalConfirmTitle" class="text-xl font-semibold text-slate-900">Confirmar acción</h3>
                </div>
                <p id="modalConfirmMessage" class="text-slate-600 mb-6"></p>
                <div class="flex justify-end gap-3">
                    <button onclick="cerrarModalConfirm(false)" class="px-6 py-2.5 bg-slate-200 text-slate-700 rounded-xl hover:bg-slate-300 transition-colors font-medium">
                        Cancelar
                    </button>
                    <button id="modalConfirmBtn" class="px-6 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Campos Personalizados (Solo Admin) -->
    <?php if(isset($esAdmin) && $esAdmin): ?>
    <div id="modalCamposPersonalizados" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-800">
                    <svg class="w-5 h-5 inline-block mr-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Configurar Campos Personalizados
                </h2>
                <button onclick="cerrarModalCamposPersonalizados()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                <!-- Formulario para crear nuevo campo -->
                <div class="bg-blue-50 rounded-xl p-4 mb-6 border border-blue-100">
                    <h3 class="font-semibold text-slate-700 mb-4">Crear Nuevo Campo</h3>
                    <form id="formNuevoCampo" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del Campo</label>
                                <input type="text" id="campoNombre" required maxlength="100"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Ej: Fecha de Vencimiento">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de Campo</label>
                                <select id="campoTipo" required
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="texto">Texto</option>
                                    <option value="fecha">Fecha</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Mostrar después de</label>
                                <select id="campoMostrarDespuesDe"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">-- Al final --</option>
                                    <option value="ejecutivo">Ejecutivo</option>
                                    <option value="operacion">Operación</option>
                                    <option value="cliente">Cliente</option>
                                    <option value="proveedor">Proveedor o Cliente</option>
                                    <option value="fecha_embarque">Fecha de Embarque</option>
                                    <option value="no_factura">No. De Factura</option>
                                    <option value="tipo_operacion">T. Operación</option>
                                    <option value="clave">Clave</option>
                                    <option value="referencia_interna">Referencia Interna</option>
                                    <option value="aduana">Aduana</option>
                                    <option value="agente_aduanal">A.A</option>
                                    <option value="referencia_aa">Referencia A.A</option>
                                    <option value="no_pedimento">No Ped</option>
                                    <option value="transporte">Transporte</option>
                                    <option value="fecha_arribo_aduana">Fecha de Arribo a Aduana</option>
                                    <option value="guia_bl">Guía //BL</option>
                                    <option value="status">Status</option>
                                    <option value="fecha_modulacion">Fecha de Modulación</option>
                                    <option value="fecha_arribo_planta">Fecha de Arribo a Planta</option>
                                    <option value="resultado">Resultado</option>
                                    <option value="target">Target</option>
                                    <option value="dias_transito">Días en Tránsito</option>
                                    <option value="post_operaciones">Post-Operaciones</option>
                                    <option value="comentarios">Comentarios</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Asignar a Ejecutivos</label>
                            <select id="selectEjecutivosNuevoCampo" multiple
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent min-h-[100px]">
                                <!-- Se llena dinámicamente -->
                            </select>
                            <p class="text-xs text-slate-500 mt-1">Mantén Ctrl para seleccionar múltiples ejecutivos</p>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Crear Campo
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lista de campos existentes -->
                <div>
                    <h3 class="font-semibold text-slate-700 mb-4">Campos Personalizados Existentes</h3>
                    <div id="listaCamposPersonalizados" class="space-y-3">
                        <!-- Se llena dinámicamente -->
                        <p class="text-slate-400 text-sm text-center py-4">Cargando campos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar campo personalizado -->
    <div id="modalEditarCampo" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden z-[60] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-800">Editar Campo Personalizado</h2>
                <button onclick="cerrarModalEditarCampo()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="formEditarCampo" class="p-6 space-y-4">
                <input type="hidden" id="editarCampoId">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del Campo</label>
                        <input type="text" id="editarCampoNombre" required maxlength="100"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de Campo</label>
                        <select id="editarCampoTipo" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="texto">Texto</option>
                            <option value="fecha">Fecha</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Mostrar después de</label>
                        <select id="editarCampoMostrarDespuesDe"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- Al final --</option>
                            <option value="ejecutivo">Ejecutivo</option>
                            <option value="operacion">Operación</option>
                            <option value="cliente">Cliente</option>
                            <option value="proveedor">Proveedor o Cliente</option>
                            <option value="fecha_embarque">Fecha de Embarque</option>
                            <option value="no_factura">No. De Factura</option>
                            <option value="tipo_operacion">T. Operación</option>
                            <option value="clave">Clave</option>
                            <option value="referencia_interna">Referencia Interna</option>
                            <option value="aduana">Aduana</option>
                            <option value="agente_aduanal">A.A</option>
                            <option value="referencia_aa">Referencia A.A</option>
                            <option value="no_pedimento">No Ped</option>
                            <option value="transporte">Transporte</option>
                            <option value="fecha_arribo_aduana">Fecha de Arribo a Aduana</option>
                            <option value="guia_bl">Guía //BL</option>
                            <option value="status">Status</option>
                            <option value="fecha_modulacion">Fecha de Modulación</option>
                            <option value="fecha_arribo_planta">Fecha de Arribo a Planta</option>
                            <option value="resultado">Resultado</option>
                            <option value="target">Target</option>
                            <option value="dias_transito">Días en Tránsito</option>
                            <option value="post_operaciones">Post-Operaciones</option>
                            <option value="comentarios">Comentarios</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
                        <select id="editarCampoActivo"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Asignar a Ejecutivos</label>
                    <select id="selectEjecutivosEditarCampo" multiple
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent min-h-[100px]">
                        <!-- Se llena dinámicamente -->
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Mantén Ctrl para seleccionar múltiples ejecutivos</p>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="cerrarModalEditarCampo()" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SISTEMAS\Downloads\ERP EstrategiaeInnovacion\Sistema_Tickets_E-I\resources\views/Logistica/matriz-seguimiento.blade.php ENDPATH**/ ?>