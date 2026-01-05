<?php $__env->startSection('title','Reportes - Logística'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/Logistica/matriz-seguimiento.css')); ?>">
    <link href="<?php echo e(asset('css/Logistica/export-styles.css')); ?>" rel="stylesheet">
<?php $__env->stopPush(); ?>


<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<?php $__env->startSection('content'); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center mb-2">
                    <a href="<?php echo e(route('logistica.index')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-800 shadow-sm transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Regresar
                    </a>
                </div>
                <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">
                    <?php echo e(__('Reportes de Operaciones')); ?>

                </h2>
                <p class="text-xs text-slate-500 mt-1">Análisis y seguimiento de operaciones logísticas en tiempo real.</p>
            </div>
            <div class="flex gap-3">
                <button onclick="openEmailModal()" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Enviar Correo
                </button>

                <button onclick="openExportModal()" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:border-emerald-900 focus:ring ring-emerald-300 disabled:opacity-25 transition shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Generar Reporte
                </button>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-2">
                <nav class="flex space-x-1 overflow-x-auto custom-scrollbar pb-2 md:pb-0" aria-label="Tabs">
                    <button type="button" data-tab-target="seguimiento"
                            class="tab-button whitespace-nowrap px-5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 ease-in-out flex-shrink-0 text-slate-500 hover:text-slate-700 hover:bg-slate-50">
                        Reporte de Seguimiento
                    </button>

                    <button type="button" data-tab-target="pedimentos"
                            class="tab-button whitespace-nowrap px-5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 ease-in-out flex-shrink-0 text-slate-500 hover:text-slate-700 hover:bg-slate-50">
                        Reporte de Pedimentos
                    </button>

                    <button type="button" data-tab-target="resumen"
                            class="tab-button whitespace-nowrap px-5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 ease-in-out flex-shrink-0 text-slate-500 hover:text-slate-700 hover:bg-slate-50">
                        Resumen Ejecutivo
                    </button>
                </nav>
            </div>

            <!-- Panel: Seguimiento de operaciones -->
            <div data-tab-panel="seguimiento" class="space-y-8">

                <!-- Filtros -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-slate-800">Filtros de Búsqueda</h3>
                        <span class="text-xs text-slate-500 bg-slate-50 px-2 py-1 rounded-md">Personaliza tu reporte</span>
                    </div>
                    <form method="GET" action="<?php echo e(route('logistica.reportes')); ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Período -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Período</label>
                            <select name="periodo" class="w-full text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-green-500 focus:border-green-500 text-slate-700 font-semibold cursor-pointer">
                            <option value="" <?php echo e(request('periodo') === null || request('periodo') === '' ? 'selected' : ''); ?>>-- Todos --</option>
                            <option value="semanal" <?php echo e(request('periodo') === 'semanal' ? 'selected' : ''); ?>>Última Semana</option>
                            <option value="mensual" <?php echo e(request('periodo') === 'mensual' ? 'selected' : ''); ?>>Último Mes</option>
                            <option value="anual" <?php echo e(request('periodo') === 'anual' ? 'selected' : ''); ?>>Último Año</option>
                        </select>
                    </div>

                        <!-- Mes y Año -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Mes/Año</label>
                            <div class="flex gap-2">
                                <select name="mes" class="w-1/2 text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-green-500 focus:border-green-500 text-slate-700 font-semibold cursor-pointer">
                                    <option value="" <?php echo e(request('mes') === null || request('mes') === '' ? 'selected' : ''); ?>>-- Mes --</option>
                                    <?php for($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo e($m); ?>" <?php echo e(request('mes') == $m ? 'selected' : ''); ?>><?php echo e(\Carbon\Carbon::create(null, $m)->format('M')); ?></option>
                                    <?php endfor; ?>
                                </select>
                                <select name="anio" class="w-1/2 text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-green-500 focus:border-green-500 text-slate-700 font-semibold cursor-pointer">
                                <option value="" <?php echo e(request('anio') === null || request('anio') === '' ? 'selected' : ''); ?>>-- Año --</option>
                                <?php for($y = now()->year; $y >= now()->year - 5; $y--): ?>
                                    <option value="<?php echo e($y); ?>" <?php echo e(request('anio') == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                        <!-- Cliente -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Cliente</label>
                            <select name="cliente" class="w-full text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-green-500 focus:border-green-500 text-slate-700 font-semibold cursor-pointer">
                            <option value="" <?php echo e(request('cliente') === null || request('cliente') === '' ? 'selected' : ''); ?>>-- Todos los Clientes --</option>
                            <?php if(isset($clientes) && is_array($clientes)): ?>
                                <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(is_string($c)): ?>
                                        <option value="<?php echo e($c); ?>" <?php echo e(request('cliente') === $c ? 'selected' : ''); ?>><?php echo e($c); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </select>
                    </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Status</label>
                            <select name="status" class="w-full text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-green-500 focus:border-green-500 text-slate-700 font-semibold cursor-pointer">
                            <option value="" <?php echo e(request('status') === null || request('status') === '' ? 'selected' : ''); ?>>-- Todos los Status --</option>
                            <option value="In Process" <?php echo e(request('status') === 'In Process' ? 'selected' : ''); ?>>En Proceso</option>
                            <option value="Out of Metric" <?php echo e(request('status') === 'Out of Metric' ? 'selected' : ''); ?>>Fuera de Métrica</option>
                            <option value="Done" <?php echo e(request('status') === 'Done' ? 'selected' : ''); ?>>Completado</option>
                        </select>
                    </div>

                        <!-- Fecha Desde -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Desde</label>
                            <input type="date" name="fecha_desde" value="<?php echo e(request('fecha_desde')); ?>" class="w-full text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-green-500 focus:border-green-500 text-slate-700 font-semibold">
                        </div>

                        <!-- Fecha Hasta -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Hasta</label>
                            <input type="date" name="fecha_hasta" value="<?php echo e(request('fecha_hasta')); ?>" class="w-full text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-green-500 focus:border-green-500 text-slate-700 font-semibold">
                        </div>

                        <!-- Botones -->
                        <div class="flex items-end gap-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition shadow-sm">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                Filtrar
                            </button>
                            <a href="<?php echo e(route('logistica.reportes')); ?>" class="inline-flex items-center px-4 py-2 bg-slate-100 border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-200 active:bg-slate-300 focus:outline-none focus:border-slate-300 focus:ring ring-slate-200 disabled:opacity-25 transition shadow-sm">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Limpiar
                            </a>
                        </div>
                    </form>
                </div>

                
                <?php
                    $totalOps = $statsTemporales['total_operaciones'] ?? 0;
                    $enTiempo = $statsTemporales['en_tiempo'] ?? 0;
                    $completadoTiempo = $statsTemporales['completado_tiempo'] ?? 0;
                    $enRiesgo = $statsTemporales['en_riesgo'] ?? 0;
                    $conRetraso = $statsTemporales['con_retraso'] ?? 0;
                    $completadoRetraso = $statsTemporales['completado_retraso'] ?? 0;
                    $operacionesExitosas = $enTiempo + $completadoTiempo;
                    $eficienciaGeneral = $totalOps > 0 ? round(($operacionesExitosas / $totalOps) * 100, 1) : 0;
                    $promedioEjecucion = round($statsTemporales['promedio_dias'] ?? 0, 1);
                ?>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500 relative overflow-hidden group hover:shadow-md transition">
                        <div class="relative z-10">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Eficiencia</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo e($eficienciaGeneral); ?><span class="text-lg text-gray-400 font-normal">%</span></p>
                            <p class="text-xs text-green-600 font-medium mt-1">Operaciones exitosas</p>
                        </div>
                        <div class="absolute right-0 top-0 h-full w-24 bg-gradient-to-l from-green-50 to-transparent opacity-50 group-hover:opacity-100 transition"></div>
                        <div class="absolute -right-2 -bottom-4 text-green-100 opacity-50">
                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500 relative overflow-hidden group hover:shadow-md transition">
                        <div class="relative z-10">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Operaciones</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo e($totalOps); ?></p>
                            <p class="text-xs text-blue-600 font-medium mt-1">En seguimiento</p>
                        </div>
                        <div class="absolute -right-4 -bottom-4 text-blue-100 opacity-50">
                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path d="M20.924 7.625a1.523 1.523 0 00-1.238-1.044l-5.051-.734-2.259-4.577a1.534 1.534 0 00-2.752 0L7.365 5.847l-5.051.734A1.535 1.535 0 001.08 7.625l3.66 3.566-.863 5.031a1.532 1.532 0 002.226 1.616L11 15.033l4.897 2.805a1.532 1.532 0 002.226-1.616l-.863-5.031 3.66-3.566z"></path></svg>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500 relative overflow-hidden group hover:shadow-md transition">
                        <div class="relative z-10">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">En Riesgo</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo e($enRiesgo + $conRetraso); ?></p>
                            <p class="text-xs text-amber-600 font-medium mt-1">Requieren atención</p>
                        </div>
                        <div class="absolute -right-4 -bottom-4 text-amber-100 opacity-50">
                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500 relative overflow-hidden group hover:shadow-md transition">
                        <div class="relative z-10">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Promedio Días</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo e($promedioEjecucion); ?></p>
                            <p class="text-xs text-red-600 font-medium mt-1">Tiempo ejecución</p>
                        </div>
                        <div class="absolute -right-4 -bottom-4 text-red-100 opacity-50">
                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Gráfico - Lado Izquierdo -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group hover:shadow-md transition">
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-sm font-medium text-slate-500 uppercase tracking-wider">Resumen por Status</h2>
                                <div class="relative">
                                    <select id="chartTypeSelector" class="text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-green-500 focus:border-green-500 text-slate-700 font-semibold cursor-pointer appearance-none pr-8" onchange="changeChartType()">
                                        <option value="bar">Barras</option>
                                        <option value="line">📈 Líneas</option>
                                        <option value="doughnut">🍩 Volumen</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="relative">
                                <canvas id="statusChart" style="max-height: 350px"></canvas>
                                <div id="chartLoader" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 hidden">
                                    <div class="flex items-center space-x-2 text-blue-600">
                                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-sm font-medium">Cargando gráfico...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Operaciones - Lado Derecho -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group hover:shadow-md transition">
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-sm font-medium text-slate-500 uppercase tracking-wider">Últimas Operaciones</h2>
                                <span class="text-xs text-slate-500 bg-slate-50 px-2 py-1 rounded-md">Vista Resumida</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table id="operacionesTable" class="min-w-full text-sm">
                                    <thead>
                                        <tr class="bg-slate-50">
                                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">ID</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Ejecutivo</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Cliente</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tipo</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Resultado</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Días</th>
                                        </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $operaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="border-b hover:bg-slate-50" data-operacion-id="<?php echo e($op->id ?? ''); ?>">
                                        <td class="px-4 py-3 text-sm font-medium text-slate-900"><?php echo e($op->id ?? '-'); ?></td>
                                        <td class="px-4 py-3 text-sm text-slate-700"><?php echo e(is_string($op->ejecutivo) ? $op->ejecutivo : '-'); ?></td>
                                        <td class="px-4 py-3 text-sm text-slate-700"><?php echo e(is_string($op->cliente) ? $op->cliente : '-'); ?></td>
                                        <td class="px-4 py-3 text-sm text-slate-700"><?php echo e(is_string($op->tipo_operacion_enum) ? $op->tipo_operacion_enum : '-'); ?></td>
                                        <td class="px-4 py-3">
                                            <?php
                                                $statusFinal = ($op->status_manual === 'Done') ? 'Done' : $op->status_calculado;
                                                $colorFinal = ($op->status_manual === 'Done') ? 'verde' : $op->color_status;
                                                $statusDisplay = match($statusFinal) {
                                                    'In Process' => 'En Proceso',
                                                    'Out of Metric' => 'Fuera de Métrica',
                                                    'Done' => 'Completado',
                                                    default => $statusFinal ?? 'En Proceso'
                                                };
                                                $badgeClass = match($colorFinal) {
                                                    'verde' => 'bg-green-100 text-green-800',
                                                    'amarillo' => 'bg-yellow-100 text-yellow-800',
                                                    'rojo' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                            ?>
                                            <span class="px-2 py-1 rounded text-xs <?php echo e($badgeClass); ?>">
                                                <?php echo e($statusDisplay); ?>

                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-700"><?php echo e($op->resultado ?? '-'); ?></td>
                                        <td class="px-4 py-3 text-sm text-slate-700"><?php echo e($op->dias_transito ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-slate-500 text-sm">Sin operaciones recientes</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

            </div>

            <!-- Panel: Reporte de Pedimentos -->
            <div data-tab-panel="pedimentos" class="space-y-8 hidden">
                <!-- Filtros de Pedimentos -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            Filtros de Pedimentos
                        </h3>
                        <span class="text-xs text-slate-500 bg-slate-50 px-2 py-1 rounded-md">Reportes de Pedimentos</span>
                    </div>
                    
                    <form method="GET" action="<?php echo e(route('logistica.reportes')); ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Estado de Pago -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Estado de Pago</label>
                            <select name="estado_pago" class="w-full text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-blue-500 focus:border-blue-500 text-slate-700 font-semibold cursor-pointer">
                                <option value="" <?php echo e(request('estado_pago') === null ? 'selected' : ''); ?>>Todos</option>
                                <option value="pagado" <?php echo e(request('estado_pago') === 'pagado' ? 'selected' : ''); ?>>Pagados</option>
                                <option value="pendiente" <?php echo e(request('estado_pago') === 'pendiente' ? 'selected' : ''); ?>>Pendientes</option>
                            </select>
                        </div>

                        <!-- Tipo de Operación -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tipo de Operación</label>
                            <select name="tipo_operacion" class="w-full text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-blue-500 focus:border-blue-500 text-slate-700 font-semibold cursor-pointer">
                                <option value="" <?php echo e(request('tipo_operacion') === null ? 'selected' : ''); ?>>Todas</option>
                                <option value="importacion" <?php echo e(request('tipo_operacion') === 'importacion' ? 'selected' : ''); ?>>Importación</option>
                                <option value="exportacion" <?php echo e(request('tipo_operacion') === 'exportacion' ? 'selected' : ''); ?>>Exportación</option>
                            </select>
                        </div>

                        <!-- Moneda -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Moneda</label>
                            <select name="moneda" class="w-full text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-blue-500 focus:border-blue-500 text-slate-700 font-semibold cursor-pointer">
                                <option value="" <?php echo e(request('moneda') === null ? 'selected' : ''); ?>>Todas</option>
                                <option value="MXN" <?php echo e(request('moneda') === 'MXN' ? 'selected' : ''); ?>>MXN - Peso Mexicano</option>
                                <option value="USD" <?php echo e(request('moneda') === 'USD' ? 'selected' : ''); ?>>USD - Dólar Americano</option>
                                <option value="EUR" <?php echo e(request('moneda') === 'EUR' ? 'selected' : ''); ?>>EUR - Euro</option>
                            </select>
                        </div>

                        <!-- Clave de Pedimento -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Clave de Pedimento</label>
                            <select name="clave_pedimento" class="w-full text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-blue-500 focus:border-blue-500 text-slate-700 font-semibold cursor-pointer">
                                <option value="" <?php echo e(request('clave_pedimento') === null ? 'selected' : ''); ?>>Todas las Claves</option>
                                <option value="A1" <?php echo e(request('clave_pedimento') === 'A1' ? 'selected' : ''); ?>>A1 - Importación para Consumo</option>
                                <option value="A3" <?php echo e(request('clave_pedimento') === 'A3' ? 'selected' : ''); ?>>A3 - Importación Temporal</option>
                                <option value="A4" <?php echo e(request('clave_pedimento') === 'A4' ? 'selected' : ''); ?>>A4 - Importación Temporal Maquila</option>
                                <option value="B1" <?php echo e(request('clave_pedimento') === 'B1' ? 'selected' : ''); ?>>B1 - Exportación Definitiva</option>
                                <option value="B2" <?php echo e(request('clave_pedimento') === 'B2' ? 'selected' : ''); ?>>B2 - Exportación Temporal</option>
                            </select>
                        </div>

                        <!-- Fecha de Embarque (Desde) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Fecha Embarque (Desde)</label>
                            <input type="date" name="fecha_embarque_desde" value="<?php echo e(request('fecha_embarque_desde')); ?>" 
                                   class="w-full text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-blue-500 focus:border-blue-500 text-slate-700">
                        </div>

                        <!-- Fecha de Embarque (Hasta) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Fecha Embarque (Hasta)</label>
                            <input type="date" name="fecha_embarque_hasta" value="<?php echo e(request('fecha_embarque_hasta')); ?>" 
                                   class="w-full text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-blue-500 focus:border-blue-500 text-slate-700">
                        </div>

                        <!-- Cliente -->
                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Cliente</label>
                            <input type="text" name="cliente" value="<?php echo e(request('cliente')); ?>" placeholder="Buscar por nombre de cliente..."
                                   class="w-full text-sm border-slate-300 bg-slate-50 rounded-md focus:ring-blue-500 focus:border-blue-500 text-slate-700">
                        </div>

                        <!-- Botones de Acción -->
                        <div class="md:col-span-2 lg:col-span-3 flex gap-3 justify-end pt-4 border-t border-slate-200">
                            <button type="button" onclick="limpiarFiltrosPedimentos()" class="inline-flex items-center px-4 py-2 bg-slate-100 border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-500 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Limpiar Filtros
                            </button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Generar Reporte
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Resultados del Reporte de Pedimentos -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            Resultados del Reporte
                        </h3>
                        <p class="text-sm text-slate-600 mt-1">Visualización de pedimentos según filtros aplicados</p>
                    </div>

                    <div class="p-6">
                        <!-- Resumen de Totales -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-slate-800">0</div>
                                    <div class="text-sm text-slate-600">Total Pedimentos</div>
                                </div>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-800">$0</div>
                                    <div class="text-sm text-green-600">Total Pagado</div>
                                </div>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-yellow-800">$0</div>
                                    <div class="text-sm text-yellow-600">Total Pendiente</div>
                                </div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-800">0</div>
                                    <div class="text-sm text-blue-600">Promedio Días</div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Pedimentos -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Pedimento</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Cliente</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Clave</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Monto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Estado Pago</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Fecha Embarque</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <!-- Mensaje cuando no hay datos -->
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <p class="text-lg font-medium text-slate-900 mb-1">No hay pedimentos para mostrar</p>
                                                <p class="text-sm text-slate-500">Aplica filtros para generar el reporte de pedimentos</p>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- Aquí se cargarían los datos reales de pedimentos -->
                                    
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="mt-6 flex items-center justify-between border-t border-slate-200 pt-6">
                            <div class="text-sm text-slate-700">
                                Mostrando <span class="font-medium">0</span> resultados
                            </div>
                            <div class="flex gap-2">
                                <button disabled class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-slate-300 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span class="sr-only">Anterior</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                                <button disabled class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-slate-300 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span class="sr-only">Siguiente</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel: Resumen ejecutivo y análisis temporal -->
            <div data-tab-panel="resumen" class="space-y-8 hidden">
                <!-- Header del Resumen Ejecutivo -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                Resumen Ejecutivo
                            </h2>
                            <p class="text-slate-600">Análisis de rendimiento y eficiencia operacional</p>
                        </div>
                        <div class="text-xs text-slate-500 bg-slate-50 px-3 py-1 rounded-full border border-slate-200">
                            <?php echo e(now()->format('d/m/Y H:i')); ?>

                        </div>
                    </div>
                </div>

                <!-- Resumen Ejecutivo -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Rendimiento General -->
                        <div class="bg-slate-50 rounded-lg p-6 border border-slate-200">
                            <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center border border-green-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                Rendimiento General
                            </h3>
                            
                            <?php
                                $totalOps = $statsTemporales['total_operaciones'];
                                $enTiempo = $statsTemporales['en_tiempo'] ?? 0;
                                $completadoTiempo = $statsTemporales['completado_tiempo'] ?? 0;
                                $enRiesgo = $statsTemporales['en_riesgo'] ?? 0;
                                $conRetraso = $statsTemporales['con_retraso'] ?? 0;
                                $completadoRetraso = $statsTemporales['completado_retraso'] ?? 0;
                                $operacionesExitosas = $enTiempo + $completadoTiempo;
                                $eficienciaGeneral = $totalOps > 0 ? round(($operacionesExitosas / $totalOps) * 100, 1) : 0;
                                $promedioEjecucion = round($statsTemporales['promedio_dias'] ?? 0, 1);
                                $targetPromedio = round($statsTemporales['promedio_target'] ?? 3, 1);
                            ?>
                            
                            <!-- Métrica Principal -->
                            <div class="bg-white rounded-lg p-4 mb-4 border border-slate-200">
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-slate-800 mb-2"><?php echo e($eficienciaGeneral); ?>%</div>
                                    <div class="text-sm text-slate-600 mb-3">Eficiencia General</div>
                                    <?php if($eficienciaGeneral >= 80): ?>
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            ✓ Excelente
                                        </div>
                                    <?php elseif($eficienciaGeneral >= 60): ?>
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                            ⚠ Regular
                                        </div>
                                    <?php else: ?>
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                            ⚠ Requiere Atención
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Desglose de Eficiencia -->
                            <div class="bg-white rounded-lg p-4 border border-slate-200">
                                <h4 class="font-medium text-slate-700 mb-3 text-sm">Desglose de Operaciones</h4>
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between items-center">
                                        <span class="text-green-600">✓ Exitosas:</span>
                                        <span class="font-semibold text-slate-800"><?php echo e($operacionesExitosas); ?> de <?php echo e($totalOps); ?></span>
                                    </div>
                                    <div class="ml-4 space-y-1 text-xs text-slate-600">
                                        <div class="flex justify-between">
                                            <span>• Activas en Tiempo:</span>
                                            <span><?php echo e($enTiempo); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>• Finalizadas a Tiempo:</span>
                                            <span><?php echo e($completadoTiempo); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                                        <span class="text-red-600">⚠ Problemáticas:</span>
                                        <span class="font-semibold text-slate-800"><?php echo e($totalOps - $operacionesExitosas); ?></span>
                                    </div>
                                    <div class="ml-4 space-y-1 text-xs text-slate-600">
                                        <div class="flex justify-between">
                                            <span>• En Riesgo:</span>
                                            <span><?php echo e($enRiesgo); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>• Con Retraso:</span>
                                            <span><?php echo e($conRetraso); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>• Finalizadas con Retraso:</span>
                                            <span><?php echo e($completadoRetraso); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 text-sm text-slate-600">
                                <p><span class="font-medium">Tiempo Promedio:</span> <?php echo e($promedioEjecucion); ?> días</p>
                                <p><span class="font-medium">Target Promedio:</span> <?php echo e($targetPromedio); ?> días</p>
                            </div>
                        </div>

                        <!-- Distribución Visual -->
                        <div class="bg-slate-50 rounded-lg p-6 border border-slate-200">
                            <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                    </svg>
                                </div>
                                Distribución de Operaciones
                            </h3>

                            <!-- Operaciones Activas -->
                            <div class="bg-white rounded-lg p-4 mb-4 border border-slate-200">
                                <h4 class="font-medium text-slate-700 mb-3 text-sm flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Operaciones Activas
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between items-center p-2 bg-green-50 rounded border border-green-200">
                                        <span class="text-green-700">En Tiempo</span>
                                        <span class="font-semibold text-green-800"><?php echo e($enTiempo); ?> (<?php echo e($totalOps > 0 ? round(($enTiempo / $totalOps) * 100, 1) : 0); ?>%)</span>
                                    </div>
                                    <div class="flex justify-between items-center p-2 bg-yellow-50 rounded border border-yellow-200">
                                        <span class="text-yellow-700">En Riesgo</span>
                                        <span class="font-semibold text-yellow-800"><?php echo e($enRiesgo); ?> (<?php echo e($totalOps > 0 ? round(($enRiesgo / $totalOps) * 100, 1) : 0); ?>%)</span>
                                    </div>
                                    <div class="flex justify-between items-center p-2 bg-red-50 rounded border border-red-200">
                                        <span class="text-red-700">Con Retraso</span>
                                        <span class="font-semibold text-red-800"><?php echo e($conRetraso); ?> (<?php echo e($totalOps > 0 ? round(($conRetraso / $totalOps) * 100, 1) : 0); ?>%)</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Operaciones Finalizadas -->
                            <div class="bg-white rounded-lg p-4 border border-slate-200">
                                <h4 class="font-medium text-slate-700 mb-3 text-sm flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Operaciones Finalizadas
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between items-center p-2 bg-emerald-50 rounded border border-emerald-200">
                                        <span class="text-emerald-700">A Tiempo</span>
                                        <span class="font-semibold text-emerald-800"><?php echo e($completadoTiempo); ?> (<?php echo e($totalOps > 0 ? round(($completadoTiempo / $totalOps) * 100, 1) : 0); ?>%)</span>
                                    </div>
                                    <div class="flex justify-between items-center p-2 bg-orange-50 rounded border border-orange-200">
                                        <span class="text-orange-700">Con Retraso</span>
                                        <span class="font-semibold text-orange-800"><?php echo e($completadoRetraso); ?> (<?php echo e($totalOps > 0 ? round(($completadoRetraso / $totalOps) * 100, 1) : 0); ?>%)</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Alertas -->
                            <?php if($conRetraso > 0 || $enRiesgo > 0): ?>
                            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                <h4 class="font-medium text-amber-800 mb-2 text-sm">⚠ Alertas</h4>
                                <div class="space-y-1 text-sm text-amber-700">
                                    <?php if($conRetraso > 0): ?>
                                        <p>• <?php echo e($conRetraso); ?> operaciones con retraso activo</p>
                                    <?php endif; ?>
                                    <?php if($enRiesgo > 0): ?>
                                        <p>• <?php echo e($enRiesgo); ?> operaciones en riesgo de retraso</p>
                                    <?php endif; ?>
                                    <?php if($eficienciaGeneral < 70): ?>
                                        <p>• Considerar revisión de procesos operativos</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                <p class="text-sm text-green-700 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Todas las operaciones están en tiempo
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <!-- Análisis Temporal - Nuevo Gráfico -->
            <div class="bg-white rounded-xl shadow p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-700">Análisis Temporal de Operaciones</h2>
                        <p class="text-sm text-slate-500 mt-1">
                            <?php if($esAdmin ?? false): ?>
                                Mostrando datos de todos los ejecutivos
                            <?php else: ?>
                                Mostrando solo tus operaciones asignadas
                            <?php endif; ?>
                            (<?php echo e(isset($statsTemporales['total_operaciones']) ? $statsTemporales['total_operaciones'] : 0); ?> operaciones)
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- Selector de vista del gráfico temporal -->
                        <div class="relative">
                            <select id="temporalChartType" class="px-4 py-2 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg text-sm font-medium text-green-700 focus:ring-2 focus:ring-green-500 focus:border-green-500 hover:bg-gradient-to-r hover:from-green-100 hover:to-emerald-100 transition-all cursor-pointer appearance-none pr-8" onchange="changeTemporalChart()">
                                <option value="scatter">Dispersión (Días vs Target)</option>
                                <option value="bar">Categorías de Rendimiento</option>
                                <option value="radar">🕸️ Análisis por Cliente</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        <!-- Botón para vista completa -->
                        <button onclick="openFullScreenChart()" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-500 text-white rounded-lg hover:from-purple-600 hover:to-indigo-600 transition-all flex items-center space-x-2 text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                            </svg>
                            <span>Ver Grande</span>
                        </button>
                        <!-- Botones de descarga -->
                        <div class="relative">
                            <button onclick="toggleExportMenu()" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white rounded-lg hover:from-blue-600 hover:to-cyan-600 transition-all flex items-center space-x-2 text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                </svg>
                                <span>Exportar</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <!-- Menú de exportación -->
                            <div id="exportMenu" class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-lg z-10 hidden">
                                <div class="py-1">
                                    <button onclick="exportChart('png')" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 flex items-center space-x-2">
                                        <span>🖼️</span><span>Imagen PNG</span>
                                    </button>
                                    <button onclick="exportChart('jpg')" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 flex items-center space-x-2">
                                        <span>📷</span><span>Imagen JPG</span>
                                    </button>
                                    <button onclick="exportData('csv')" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 flex items-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>Datos CSV</span>
                                    </button>
                                    <button onclick="exportData('excel')" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 flex items-center space-x-2">
                                        <span>📈</span><span>Excel con Gráficos</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Métricas rápidas -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-green-600"><?php echo e(isset($statsTemporales['en_tiempo']) ? $statsTemporales['en_tiempo'] : 0); ?></div>
                        <div class="text-xs text-green-700">En Tiempo</div>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-600"><?php echo e(isset($statsTemporales['en_riesgo']) && is_scalar($statsTemporales['en_riesgo']) ? $statsTemporales['en_riesgo'] : 0); ?></div>
                        <div class="text-xs text-yellow-700">En Riesgo</div>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-red-600"><?php echo e(isset($statsTemporales['con_retraso']) && is_scalar($statsTemporales['con_retraso']) ? $statsTemporales['con_retraso'] : 0); ?></div>
                        <div class="text-xs text-red-700">Con Retraso</div>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-blue-600"><?php echo e(isset($statsTemporales['completado_tiempo']) && is_scalar($statsTemporales['completado_tiempo']) ? $statsTemporales['completado_tiempo'] : 0); ?></div>
                        <div class="text-xs text-blue-700">Completado a Tiempo</div>
                    </div>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-purple-600"><?php echo e(isset($statsTemporales['completado_retraso']) && is_scalar($statsTemporales['completado_retraso']) ? $statsTemporales['completado_retraso'] : 0); ?></div>
                        <div class="text-xs text-purple-700">Completado con Retraso</div>
                    </div>
                </div>

                <!-- Canvas del gráfico temporal -->
                <div class="relative">
                    <canvas id="temporalChart" style="max-height: 400px"></canvas>
                    <div id="temporalLoader" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 hidden">
                        <div class="flex items-center space-x-2 text-green-600">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-medium">Analizando datos temporales...</span>
                        </div>
                    </div>
                </div>
            </div>
            </div> <!-- Cierra panel resumen -->

        <!-- Modal para vista de pantalla completa -->
        <div id="fullScreenModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeFullScreenChart()"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Análisis Temporal - Vista Completa
                                </h3>
                                <div class="mt-4">
                </div>
                <div class="p-6">
                    <canvas id="fullScreenChart" style="max-height: 70vh"></canvas>
                </div>
            </div>
        </div>


        </main>

    <script>
        // Verificar que Chart esté disponible
        if (typeof Chart === 'undefined') {
            console.error('Chart.js no se cargó correctamente');
        }

        const tabButtons = document.querySelectorAll('[data-tab-target]');
        const tabPanels = document.querySelectorAll('[data-tab-panel]');

        function activateTab(tabId) {
            console.log('Activating tab:', tabId);
            console.log('Found tab buttons:', tabButtons.length);
            console.log('Found tab panels:', tabPanels.length);
            
            // Actualizar estilos de botones
            tabButtons.forEach(button => {
                const isActive = button.dataset.tabTarget === tabId;
                if (isActive) {
                    button.classList.add('bg-blue-600', 'text-white', 'shadow-lg');
                    button.classList.remove('text-slate-500', 'hover:text-slate-700', 'hover:bg-slate-50');
                } else {
                    button.classList.remove('bg-blue-600', 'text-white', 'shadow-lg');
                    button.classList.add('text-slate-500', 'hover:text-slate-700', 'hover:bg-slate-50');
                }
            });

            // Actualizar visibilidad de paneles
            tabPanels.forEach(panel => {
                const panelId = panel.dataset.tabPanel;
                const shouldShow = panelId === tabId;
                
                console.log(`Panel ${panelId}: shouldShow = ${shouldShow}`);
                
                if (shouldShow) {
                    panel.classList.remove('hidden');
                    console.log('Showing panel:', panelId);
                    panel.style.display = 'block';
                    // Forzar reflow para asegurar que el elemento se renderice
                    panel.offsetHeight;
                } else {
                    panel.classList.add('hidden');
                    panel.style.display = 'none';
                }
            });

            // Scroll suave al inicio
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        tabButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const targetTab = button.dataset.tabTarget;
                console.log('Tab clicked:', targetTab);
                activateTab(targetTab);
                

            });
        });

        // Activar pestaña por defecto según parámetro URL o seguimiento
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'seguimiento';
        
        console.log('URL params:', window.location.search);
        console.log('Active tab will be:', activeTab);
        
        // Ejecutar después de que el DOM esté completamente cargado
        setTimeout(() => {
            activateTab(activeTab);
        }, 100);
        


        const stats = <?php echo json_encode($stats, 15, 512) ?>;
        const total = (stats.en_proceso || 0) + (stats.fuera_metrica || 0) + (stats.done || 0);
        const showEmptyMsg = total === 0;
        const ctx = document.getElementById('statusChart').getContext('2d');

        let currentChart = null;

        // Datos base para todos los gráficos
        const chartData = {
            labels: ['En Proceso', 'Fuera Métrica', 'Completado'],
            datasets: [{
                label: 'Operaciones',
                data: [stats.en_proceso || 0, stats.fuera_metrica || 0, stats.done || 0],
                backgroundColor: [
                    'rgba(250, 204, 21, 0.8)',  // Amarillo para En Proceso
                    'rgba(239, 68, 68, 0.8)',   // Rojo para Fuera Métrica
                    'rgba(34, 197, 94, 0.8)'    // Verde para Completado
                ],
                borderColor: [
                    'rgba(250, 204, 21, 1)',
                    'rgba(239, 68, 68, 1)',
                    'rgba(34, 197, 94, 1)'
                ],
                borderWidth: 2
            }]
        };

        // Configuraciones para cada tipo de gráfico
        const chartConfigs = {
            bar: {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Distribución de Operaciones por Status'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            },
            line: {
                type: 'line',
                data: {
                    ...chartData,
                    datasets: [{
                        ...chartData.datasets[0],
                        fill: true,
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        pointBackgroundColor: [
                            'rgba(250, 204, 21, 1)',
                            'rgba(239, 68, 68, 1)',
                            'rgba(34, 197, 94, 1)'
                        ],
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 8,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Comportamiento de Operaciones por Status'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            },
            doughnut: {
                type: 'doughnut',
                data: chartData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Volumen de Operaciones por Status'
                        }
                    },
                    cutout: '50%'
                }
            }
        };

        // Función para cambiar el tipo de gráfico con animación
        function changeChartType() {
            const selectedType = document.getElementById('chartTypeSelector').value;
            const loader = document.getElementById('chartLoader');

            // Mostrar loader
            loader.classList.remove('hidden');

            // Pequeño delay para mostrar la animación de carga
            setTimeout(() => {
                createChart(selectedType);
                loader.classList.add('hidden');
            }, 300);
        }

        // Función para crear el gráfico
        function createChart(type = 'bar') {
            if (currentChart) {
                currentChart.destroy();
            }

            if (!showEmptyMsg) {
                // Agregar animaciones personalizadas según el tipo
                const config = { ...chartConfigs[type] };

                // Animaciones específicas por tipo de gráfico
                config.options.animation = {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                };

                if (type === 'line') {
                    config.options.animation.delay = (context) => {
                        return context.type === 'data' && context.mode === 'default' ? context.dataIndex * 100 : 0;
                    };
                } else if (type === 'doughnut') {
                    config.options.animation = {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1200
                    };
                }

                currentChart = new Chart(ctx, config);
            } else {
                // Mostrar mensaje cuando no hay datos
                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                ctx.font = '14px system-ui';
                ctx.fillStyle = '#64748b';
                ctx.textAlign = 'center';
                ctx.fillText('Sin datos para mostrar', ctx.canvas.width / 2, ctx.canvas.height / 2);
                ctx.fillText('(no hay operaciones aún)', ctx.canvas.width / 2, ctx.canvas.height / 2 + 20);
            }
        }

        // Inicializar el gráfico con tipo barras por defecto
        createChart('bar');

        // Exponer función globalmente para el selector
        window.changeChartType = changeChartType;

        // ==================== SEGUNDO GRÁFICO: ANÁLISIS TEMPORAL ====================

        const comportamientoTemporal = <?php echo json_encode($comportamientoTemporal ?? [], 15, 512) ?>;
        const statsTemporales = <?php echo json_encode($statsTemporales ?? [], 15, 512) ?>;
        const temporalCtx = document.getElementById('temporalChart').getContext('2d');
        let currentTemporalChart = null;
        let fullScreenTemporalChart = null;

        // Datos procesados para el gráfico temporal
        const temporalData = {
            scatter: {
                datasets: [{
                    label: 'En Tiempo',
                    data: comportamientoTemporal.filter(op => op.categoria === 'En Tiempo').map(op => ({
                        x: op.dias_transcurridos,
                        y: op.target,
                        cliente: op.cliente,
                        id: op.id
                    })),
                    backgroundColor: 'rgba(34, 197, 94, 0.6)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    pointRadius: 8
                }, {
                    label: 'En Riesgo',
                    data: comportamientoTemporal.filter(op => op.categoria === 'En Riesgo').map(op => ({
                        x: op.dias_transcurridos,
                        y: op.target,
                        cliente: op.cliente,
                        id: op.id
                    })),
                    backgroundColor: 'rgba(251, 191, 36, 0.6)',
                    borderColor: 'rgba(251, 191, 36, 1)',
                    pointRadius: 8
                }, {
                    label: 'Con Retraso',
                    data: comportamientoTemporal.filter(op => op.categoria === 'Con Retraso').map(op => ({
                        x: op.dias_transcurridos,
                        y: op.target,
                        cliente: op.cliente,
                        id: op.id
                    })),
                    backgroundColor: 'rgba(239, 68, 68, 0.6)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    pointRadius: 8
                }, {
                    label: 'Completado a Tiempo',
                    data: comportamientoTemporal.filter(op => op.categoria === 'Completado a Tiempo').map(op => ({
                        x: op.dias_transcurridos,
                        y: op.target,
                        cliente: op.cliente,
                        id: op.id
                    })),
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    pointRadius: 6
                }, {
                    label: 'Completado con Retraso',
                    data: comportamientoTemporal.filter(op => op.categoria === 'Completado con Retraso').map(op => ({
                        x: op.dias_transcurridos,
                        y: op.target,
                        cliente: op.cliente,
                        id: op.id
                    })),
                    backgroundColor: 'rgba(156, 163, 175, 0.6)',
                    borderColor: 'rgba(75, 85, 99, 1)',
                    pointRadius: 6
                }]
            },
            bar: {
                labels: ['En Tiempo', 'En Riesgo', 'Con Retraso', 'Completado a Tiempo', 'Completado con Retraso'],
                datasets: [{
                    label: 'Operaciones',
                    data: [
                        statsTemporales.en_tiempo,
                        statsTemporales.en_riesgo,
                        statsTemporales.con_retraso,
                        statsTemporales.completado_tiempo,
                        statsTemporales.completado_retraso
                    ],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(156, 163, 175, 0.8)'
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(251, 191, 36, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(75, 85, 99, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            radar: (() => {
                // Agrupar por cliente y calcular métricas
                const clienteStats = {};
                comportamientoTemporal.forEach(op => {
                    if (!clienteStats[op.cliente]) {
                        clienteStats[op.cliente] = {
                            total: 0,
                            en_tiempo: 0,
                            retrasos: 0,
                            completadas: 0,
                            promedio_dias: 0,
                            suma_dias: 0
                        };
                    }
                    const stats = clienteStats[op.cliente];
                    stats.total++;
                    stats.suma_dias += op.dias_transcurridos;
                    if (op.categoria.includes('En Tiempo')) stats.en_tiempo++;
                    if (op.categoria.includes('Retraso')) stats.retrasos++;
                    if (op.categoria.includes('Completado')) stats.completadas++;
                });

                // Calcular promedios y tomar top 5 clientes
                const topClientes = Object.keys(clienteStats)
                    .map(cliente => ({
                        nombre: cliente,
                        ...clienteStats[cliente],
                        promedio_dias: clienteStats[cliente].suma_dias / clienteStats[cliente].total,
                        eficiencia: (clienteStats[cliente].en_tiempo / clienteStats[cliente].total) * 100
                    }))
                    .sort((a, b) => b.total - a.total)
                    .slice(0, 5);

                return {
                    labels: ['Total Operaciones', 'Eficiencia (%)', 'Operaciones a Tiempo', 'Completadas', 'Promedio Días'],
                    datasets: topClientes.map((cliente, index) => ({
                        label: cliente.nombre,
                        data: [
                            cliente.total,
                            cliente.eficiencia,
                            cliente.en_tiempo,
                            cliente.completadas,
                            Math.min(cliente.promedio_dias, 10) // Normalizar a escala de 10
                        ],
                        backgroundColor: `hsla(${index * 72}, 70%, 50%, 0.2)`,
                        borderColor: `hsla(${index * 72}, 70%, 50%, 1)`,
                        pointBackgroundColor: `hsla(${index * 72}, 70%, 50%, 1)`,
                        borderWidth: 2
                    }))
                };
            })()
        };

        // Configuraciones para cada tipo de gráfico temporal
        const temporalChartConfigs = {
            scatter: {
                type: 'scatter',
                data: temporalData.scatter,
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Días Transcurridos vs Target por Operación'
                        },
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const point = context.raw;
                                    return `${context.dataset.label}: Cliente: ${point.cliente}, Días: ${point.x}, Target: ${point.y}, ID: ${point.id}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Días Transcurridos'
                            },
                            beginAtZero: true
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Target (Días)'
                            },
                            beginAtZero: true
                        }
                    },
                    animation: {
                        duration: 1500,
                        easing: 'easeInOutQuart'
                    }
                }
            },
            bar: {
                type: 'bar',
                data: temporalData.bar,
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Distribución por Categorías de Rendimiento'
                        },
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    },
                    animation: {
                        duration: 1200,
                        delay: (context) => context.dataIndex * 100
                    }
                }
            },
            radar: {
                type: 'radar',
                data: temporalData.radar,
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Análisis Comparativo por Cliente (Top 5)'
                        }
                    },
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 100
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeInOutElastic'
                    }
                }
            }
        };

        // Función para cambiar el tipo de gráfico temporal
        function changeTemporalChart() {
            const selectedType = document.getElementById('temporalChartType').value;
            const loader = document.getElementById('temporalLoader');

            loader.classList.remove('hidden');

            setTimeout(() => {
                createTemporalChart(selectedType);
                loader.classList.add('hidden');
            }, 400);
        }

        // Función para crear el gráfico temporal
        function createTemporalChart(type = 'scatter') {
            if (currentTemporalChart) {
                currentTemporalChart.destroy();
            }

            if (comportamientoTemporal.length > 0) {
                currentTemporalChart = new Chart(temporalCtx, temporalChartConfigs[type]);
            } else {
                temporalCtx.clearRect(0, 0, temporalCtx.canvas.width, temporalCtx.canvas.height);
                temporalCtx.font = '16px system-ui';
                temporalCtx.fillStyle = '#64748b';
                temporalCtx.textAlign = 'center';
                temporalCtx.fillText('Sin datos temporales para mostrar', temporalCtx.canvas.width / 2, temporalCtx.canvas.height / 2);
            }
        }

        // Funciones del modal de pantalla completa
        function openFullScreenChart() {
            const modal = document.getElementById('fullScreenModal');
            modal.classList.remove('hidden');

            // Crear gráfico en pantalla completa
            setTimeout(() => {
                const fullCtx = document.getElementById('fullScreenChart').getContext('2d');
                const currentType = document.getElementById('temporalChartType').value;
                const config = { ...temporalChartConfigs[currentType] };
                config.options.responsive = true;
                config.options.maintainAspectRatio = false;

                if (fullScreenTemporalChart) {
                    fullScreenTemporalChart.destroy();
                }

                fullScreenTemporalChart = new Chart(fullCtx, config);
            }, 100);
        }

        function closeFullScreenChart() {
            document.getElementById('fullScreenModal').classList.add('hidden');
            if (fullScreenTemporalChart) {
                fullScreenTemporalChart.destroy();
                fullScreenTemporalChart = null;
            }
        }

        // Funciones de exportación
        function toggleExportMenu() {
            const menu = document.getElementById('exportMenu');
            menu.classList.toggle('hidden');
        }

        function exportChart(format) {
            const chart = fullScreenTemporalChart || currentTemporalChart;
            if (!chart) return;

            const url = chart.toBase64Image('image/' + format, 1.0);
            const link = document.createElement('a');
            link.download = `analisis-temporal-${new Date().toISOString().split('T')[0]}.${format}`;
            link.href = url;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            toggleExportMenu();
        }

        function exportData(format) {
            if (format === 'csv') {
                exportCSV();
            } else if (format === 'excel') {
                exportExcel();
            }
            toggleExportMenu();
        }

        function exportCSV() {
            const csvContent = [
                ['ID', 'Cliente', 'Ejecutivo', 'Días Transcurridos', 'Target', 'Retraso', 'Status', 'Categoría', '% Progreso'],
                ...comportamientoTemporal.map(op => [
                    op.id,
                    op.cliente,
                    op.ejecutivo,
                    op.dias_transcurridos,
                    op.target,
                    op.retraso,
                    op.status,
                    op.categoria,
                    op.porcentaje_progreso.toFixed(1) + '%'
                ])
            ].map(row => row.map(cell => `"${cell}"`).join(',')).join('\\n');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `analisis-temporal-${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
        }

        function exportExcel() {
            // Para una implementación completa de Excel, se necesitaría una librería como SheetJS
            // Por ahora, exportamos como CSV con extensión .xls
            exportCSV();
        }

        // Cerrar menú de exportación al hacer clic fuera
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('exportMenu');
            const button = event.target.closest('button');
            if (!button || !button.onclick || button.onclick.toString().indexOf('toggleExportMenu') === -1) {
                menu.classList.add('hidden');
            }
        });

        // Inicializar el gráfico temporal con tipo scatter por defecto
        createTemporalChart('scatter');

        // Exponer funciones globalmente
        window.changeTemporalChart = changeTemporalChart;
        window.openFullScreenChart = openFullScreenChart;
        window.closeFullScreenChart = closeFullScreenChart;
        window.toggleExportMenu = toggleExportMenu;
        window.exportChart = exportChart;
        window.exportData = exportData;




    </script>

    <!-- Modal Selección de Reporte -->
    <div id="exportModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeExportModal()"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">
                                Generar Reporte
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500">
                                    Selecciona el tipo de reporte que deseas generar según la información disponible en cada pestaña.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <form id="exportForm" method="GET" class="mt-6">
                        <div class="space-y-4">
                            <!-- Opción: Reporte de Seguimiento -->
                            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                                <label class="flex items-start cursor-pointer">
                                    <input type="radio" name="report_type" value="seguimiento" class="mt-1 text-blue-600 focus:ring-blue-500 border-slate-300">
                                    <div class="ml-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-slate-900">Reporte de Seguimiento</span>
                                        </div>
                                        <p class="text-xs text-slate-600">
                                            Incluye todas las operaciones filtradas con campos estándar y campos personalizados habilitados para tu usuario.
                                        </p>
                                    </div>
                                </label>
                                
                                <!-- Opciones adicionales para seguimiento -->
                                <div class="ml-6 mt-3 space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="include_custom_fields" value="1" class="text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                                        <span class="ml-2 text-xs text-slate-700">Incluir campos personalizados habilitados</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="include_comments" value="1" class="text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                                        <span class="ml-2 text-xs text-slate-700">Incluir comentarios</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Opción: Reporte de Pedimentos -->
                            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                                <label class="flex items-start cursor-pointer">
                                    <input type="radio" name="report_type" value="pedimentos" class="mt-1 text-purple-600 focus:ring-purple-500 border-slate-300">
                                    <div class="ml-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-slate-900">Reporte de Pedimentos</span>
                                        </div>
                                        <p class="text-xs text-slate-600">
                                            Reporte específico de pedimentos con filtros de estado de pago, tipo de operación, moneda y clave.
                                        </p>
                                    </div>
                                </label>
                            </div>

                            <!-- Opción: Resumen Ejecutivo -->
                            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                                <label class="flex items-start cursor-pointer">
                                    <input type="radio" name="report_type" value="resumen" class="mt-1 text-green-600 focus:ring-green-500 border-slate-300">
                                    <div class="ml-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-slate-900">Resumen Ejecutivo</span>
                                        </div>
                                        <p class="text-xs text-slate-600">
                                            Reporte con métricas ejecutivas, gráficos y análisis de rendimiento general.
                                        </p>
                                    </div>
                                </label>
                            </div>

                            <!-- Formato de archivo -->
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                <h4 class="text-sm font-medium text-slate-900 mb-2">Formato de Archivo</h4>
                                <div class="flex gap-4">
                                    <label class="flex items-center">
                                        <input type="radio" name="format" value="excel" checked class="text-blue-600 focus:ring-blue-500 border-slate-300">
                                        <span class="ml-2 text-sm text-slate-700">Excel (.xlsx)</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="format" value="csv" class="text-blue-600 focus:ring-blue-500 border-slate-300">
                                        <span class="ml-2 text-sm text-slate-700">CSV (.csv)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse mt-6 -mx-4 -mb-4">
                            <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50" 
                                id="generateReportBtn">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span id="generateReportText">Generar Reporte</span>
                            </button>
                            <button type="button" onclick="closeExportModal()" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Enviar por Correo -->
    <div id="emailModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeEmailModal()"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Enviar Reporte por Correo
                            </h3>
                            <div class="mt-4">

                <form id="emailForm" class="space-y-6">
                    <!-- Selección de Cliente -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Cliente <span class="text-red-500">*</span>
                        </label>
                        <select id="clienteEmail" name="cliente"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                onchange="cargarCorreosCliente()" required>
                            <option value="">Seleccionar cliente...</option>
                            <option value="">Seleccionar cliente...</option>
                            <?php if(isset($clientesEmail) && is_array($clientesEmail)): ?>
                                <?php $__currentLoopData = $clientesEmail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(is_array($cliente) && isset($cliente['cliente']) && is_string($cliente['cliente'])): ?>
                                        <option value="<?php echo e($cliente['cliente']); ?>" data-correos="<?php echo e(isset($cliente['correos']) && is_string($cliente['correos']) ? $cliente['correos'] : ''); ?>">
                                            <?php echo e($cliente['cliente']); ?>

                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Destinatarios -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Destinatarios <span class="text-red-500">*</span>
                        </label>
                        <textarea id="destinatarios" name="destinatarios" rows="3"
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 resize-none"
                                  placeholder="Se cargarán automáticamente al seleccionar un cliente..."
                                  required></textarea>
                        <p class="text-xs text-slate-500 mt-1">Los correos se cargan automáticamente del cliente seleccionado. Puede agregar más separando con comas.</p>
                    </div>

                    <!-- Asunto -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Asunto <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="asunto" name="asunto"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               value="Reporte de Operaciones Logísticas - <?php echo e(date('d/m/Y')); ?>"
                               required>
                    </div>

                    <!-- Mensaje -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Mensaje <span class="text-red-500">*</span>
                        </label>
                        <textarea id="mensaje" name="mensaje" rows="5"
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                  required>Estimados,

Adjunto encontrará el reporte de operaciones logísticas correspondiente a la fecha <?php echo e(date('d/m/Y')); ?>.

El reporte incluye todas las operaciones registradas en el sistema según los filtros aplicados.

Saludos cordiales,
<?php echo e(auth()->user()->name ?? 'Equipo de Logística'); ?></textarea>
                    </div>

                    <!-- Opciones de adjunto -->
                    <div>
                        <div class="flex items-center mb-3">
                            <input type="checkbox" id="incluirDatos" name="incluir_datos" checked
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="incluirDatos" class="ml-2 block text-sm text-slate-700">
                                Incluir archivo de datos como adjunto
                            </label>
                        </div>

                        <div id="opcionesAdjunto" class="ml-6 space-y-2">
                            <div>
                                <label class="block text-sm text-slate-600 mb-1">Formato del archivo:</label>
                                <select id="formatoDatos" name="formato_datos"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="csv">CSV (Excel compatible)</option>
                                    <option value="excel">Excel (.xlsx)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Correos CC -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Correos en Copia (CC)
                        </label>
                        <div id="correosCC" class="space-y-2">
                            <!-- Se cargan dinámicamente -->
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            Los correos CC se cargan automáticamente del catálogo.
                            <a href="<?php echo e(route('logistica.correos-cc.index')); ?>" target="_blank"
                               class="text-blue-600 hover:text-blue-800 underline">
                                Ver configuración
                            </a>
                        </p>
                    </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-paper-plane mr-2"></i>
                        <span id="btnEnviarTexto">Enviar Correo</span>
                    </button>
                    <button type="button" onclick="closeEmailModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Variables globales para manejar correos CC
        let correosCC = [];

        // Ensure functions are available in global scope
        window.openEmailModal = function() {
            document.getElementById('emailModal').classList.remove('hidden');
            cargarCorreosCC();
        };

        // Cargar correos del cliente seleccionado
        function cargarCorreosCliente() {
            const clienteSelect = document.getElementById('clienteEmail');
            const destinatariosTextarea = document.getElementById('destinatarios');

            if (clienteSelect.value) {
                const correos = clienteSelect.options[clienteSelect.selectedIndex].dataset.correos;
                if (correos) {
                    destinatariosTextarea.value = correos;
                } else {
                    destinatariosTextarea.placeholder = 'El cliente seleccionado no tiene correos configurados. Ingrese manualmente...';
                    destinatariosTextarea.value = '';
                }
            } else {
                destinatariosTextarea.value = '';
                destinatariosTextarea.placeholder = 'Se cargarán automáticamente al seleccionar un cliente...';
            }
        }

        // Cargar correos CC desde el servidor
        function cargarCorreosCC() {
            fetch('<?php echo e(route("logistica.correos-cc.api")); ?>')
                .then(response => response.json())
                .then(data => {
                    correosCC = data.map(item => ({
                        id: item.id,
                        email: item.email,
                        nombre: item.nombre,
                        tipo: item.tipo,
                        activo: true
                    }));
                    mostrarChipsCC();
                })
                .catch(error => {
                    console.error('Error al cargar correos CC:', error);
                });
        }

        // Mostrar chips de correos CC
        function mostrarChipsCC() {
            const contenedorCC = document.getElementById('correosCC');
            contenedorCC.innerHTML = '';

            if (correosCC.length === 0) {
                contenedorCC.innerHTML = '<p class="text-sm text-gray-500 italic">No hay correos CC configurados</p>';
                return;
            }

            // Crear chips para cada correo CC
            correosCC.forEach(correo => {
                if (correo.activo) {
                    const chip = document.createElement('div');
                    chip.className = 'inline-flex items-center gap-2 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm';
                    chip.innerHTML = `
                        <span class="font-medium">${correo.nombre}</span>
                        <span class="text-blue-600">(${correo.email})</span>
                        <button type="button" onclick="removerCC(${correo.id})"
                                class="ml-1 text-blue-600 hover:text-blue-800 focus:outline-none">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    `;
                    contenedorCC.appendChild(chip);
                }
            });
        }

        // Remover correo CC temporalmente (solo para este envío)
        function removerCC(id) {
            const correo = correosCC.find(c => c.id === id);
            if (correo) {
                correo.activo = false;
                mostrarChipsCC();
            }
        }

        // Obtener correos CC activos para el envío
        function obtenerCorreosCCActivos() {
            return correosCC.filter(c => c.activo).map(c => c.email);
        }

        // Obtener IDs de operaciones actualmente mostradas en la tabla (basado en filtros)
        function obtenerParametrosFiltrosActuales() {
            const parametros = {};
            
            // Obtener todos los parámetros de la URL actual
            const urlParams = new URLSearchParams(window.location.search);
            
            // Lista de parámetros de filtros válidos
            const filtrosValidos = [
                'periodo', 'mes', 'anio', 'cliente', 'status', 
                'fecha_desde', 'fecha_hasta', 'ejecutivo', 'tipo_operacion',
                'aduana', 'agente_aduanal', 'search'
            ];
            
            // Obtener parámetros de la URL
            filtrosValidos.forEach(filtro => {
                const valor = urlParams.get(filtro);
                if (valor && valor.trim() !== '') {
                    parametros[filtro] = valor.trim();
                }
            });

            // También obtener valores de los campos de formulario actuales
            const formulario = document.querySelector('form[action*="reportes"]');
            if (formulario) {
                const campos = formulario.querySelectorAll('select, input');
                campos.forEach(campo => {
                    if (campo.name && filtrosValidos.includes(campo.name)) {
                        if (campo.value && campo.value.trim() !== '') {
                            parametros[campo.name] = campo.value.trim();
                        }
                    }
                });
            }

            return parametros;
        }

        function obtenerOperacionesActuales(clienteFiltro = null) {
            const operacionesIds = [];
            const filas = document.querySelectorAll('#operacionesTable tbody tr[data-operacion-id]');

            filas.forEach(fila => {
                if (clienteFiltro) {
                    const clienteCelda = fila.querySelector('td:nth-child(3)');
                    const clienteTexto = clienteCelda ? clienteCelda.textContent.trim() : '';

                    if (clienteTexto.toLowerCase() !== clienteFiltro.toLowerCase()) {
                        return;
                    }
                }

                const operacionId = fila.getAttribute('data-operacion-id');
                if (operacionId) {
                    operacionesIds.push(parseInt(operacionId));
                }
            });

            return operacionesIds;
        }

        window.closeEmailModal = function() {
            document.getElementById('emailModal').classList.add('hidden');
            // Resetear formulario
            document.getElementById('emailForm').reset();
            document.getElementById('asunto').value = 'Reporte de Operaciones Logísticas - <?php echo e(date("d/m/Y")); ?>';
            document.getElementById('mensaje').value = `Estimados,

Adjunto encontrará el reporte de operaciones logísticas correspondiente a la fecha <?php echo e(date('d/m/Y')); ?>.

El reporte incluye todas las operaciones registradas en el sistema según los filtros aplicados.

Saludos cordiales,
<?php echo e(auth()->user()->name ?? 'Equipo de Logística'); ?>`;
        }

        // Manejar el envío del formulario
        document.getElementById('emailForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const btnEnviar = document.querySelector('#emailForm button[type="submit"]');
            const btnTexto = document.getElementById('btnEnviarTexto');

            // Deshabilitar botón y mostrar loading
            btnEnviar.disabled = true;
            btnTexto.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';

            try {
                // Validar campos requeridos
                const destinatarios = document.getElementById('destinatarios').value.trim();
                const asunto = document.getElementById('asunto').value.trim();
                const mensaje = document.getElementById('mensaje').value.trim();
                const clienteSeleccionado = document.getElementById('clienteEmail').value.trim();

                if (!destinatarios) {
                    throw new Error('Por favor ingrese al menos un destinatario');
                }

                if (!asunto) {
                    throw new Error('Por favor ingrese un asunto');
                }

                if (!mensaje) {
                    throw new Error('Por favor ingrese un mensaje');
                }

                if (!clienteSeleccionado) {
                    throw new Error('Por favor selecciona un cliente');
                }

                const formData = new FormData();
                formData.append('destinatarios', destinatarios);
                formData.append('asunto', asunto);
                formData.append('mensaje', mensaje);
                formData.append('incluir_datos', document.getElementById('incluirDatos').checked ? '1' : '0');
                formData.append('formato_datos', document.getElementById('formatoDatos').value);

                // Incluir correos CC activos
                const correosCCActivos = obtenerCorreosCCActivos();
                formData.append('correos_cc', JSON.stringify(correosCCActivos));

                // Incluir TODOS los parámetros de filtros actuales de la página
                const parametrosFiltros = obtenerParametrosFiltrosActuales();
                Object.keys(parametrosFiltros).forEach(key => {
                    if (key === 'cliente') {
                        return;
                    }

                    if (parametrosFiltros[key] !== '' && parametrosFiltros[key] !== null) {
                        formData.append(key, parametrosFiltros[key]);
                    }
                });

                // Forzar el cliente seleccionado, sin importar el filtro de la página
                formData.set('cliente', clienteSeleccionado);

                // Obtener operaciones de la tabla actual (basado en filtros aplicados)
                const operacionesIds = obtenerOperacionesActuales(clienteSeleccionado);
                if (operacionesIds.length > 0) {
                    formData.append('operaciones_ids', JSON.stringify(operacionesIds));
                }

                const response = await fetch('<?php echo e(route("logistica.reportes.enviar-correo")); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Descargar archivo si existe
                    if (result.download_url) {
                        const downloadLink = document.createElement('a');
                        downloadLink.href = result.download_url;
                        downloadLink.download = result.download_filename || 'reporte_logistica.csv';
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                    }

                    // Abrir Outlook con datos precargados
                    if (result.outlook_data) {
                        const outlookData = result.outlook_data;
                        let mailtoUrl = `mailto:${outlookData.to}`;

                        const params = [];
                        if (outlookData.cc) params.push(`cc=${encodeURIComponent(outlookData.cc)}`);
                        if (outlookData.subject) params.push(`subject=${encodeURIComponent(outlookData.subject)}`);
                        if (outlookData.body) params.push(`body=${encodeURIComponent(outlookData.body)}`);

                        if (params.length > 0) {
                            mailtoUrl += '?' + params.join('&');
                        }

                        window.location.href = mailtoUrl;
                    }

                    // Mostrar mensaje informativo
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'fixed top-4 right-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg shadow-lg z-[60]';
                    alertDiv.innerHTML = `
                        <div class="flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            <div>
                                <p class="font-medium">Outlook abierto</p>
                                <p class="text-sm">Se ha abierto Outlook con los datos del correo${result.download_url ? ' y descargado el archivo adjunto' : ''}</p>
                            </div>
                            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-blue-600 hover:text-blue-800">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    document.body.appendChild(alertDiv);

                    // Remover alerta después de 7 segundos
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.parentNode.removeChild(alertDiv);
                        }
                    }, 7000);

                    // Cerrar modal
                    closeEmailModal();
                } else {
                    throw new Error(result.message || 'Error al preparar el correo');
                }

            } catch (error) {
                console.error('Error al enviar correo:', error);

                // Mostrar mensaje de error
                const alertDiv = document.createElement('div');
                alertDiv.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg z-[60]';
                alertDiv.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <div>
                            <p class="font-medium">Error al enviar correo</p>
                            <p class="text-sm">${error.message}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-red-600 hover:text-red-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                document.body.appendChild(alertDiv);

                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.parentNode.removeChild(alertDiv);
                    }
                }, 5000);

            } finally {
                // Rehabilitar botón
                btnEnviar.disabled = false;
                btnTexto.textContent = 'Enviar Correo';
            }
        });

        // Controlar visibilidad de opciones de adjunto
        document.getElementById('incluirDatos').addEventListener('change', function() {
            const opcionesAdjunto = document.getElementById('opcionesAdjunto');
            if (this.checked) {
                opcionesAdjunto.style.display = 'block';
            } else {
                opcionesAdjunto.style.display = 'none';
            }
        });

        // Sistema simplificado - Solo botón directo de exportación CSV

        // Mostrar loader de exportación
        function showExportLoader(message) {
            const loader = document.createElement('div');
            loader.id = 'exportLoader';
            loader.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[70]';
            loader.innerHTML = `
                <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="text-gray-700 font-medium">${message}</span>
                </div>
            `;
            document.body.appendChild(loader);
        }

        // Ocultar loader de exportación
        function hideExportLoader() {
            const loader = document.getElementById('exportLoader');
            if (loader) {
                loader.remove();
            }
        }

        // Mostrar notificaciones elegantes
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-[60] transition-all duration-300 transform translate-x-full`;

            const colors = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                info: 'bg-blue-500 text-white',
                warning: 'bg-yellow-500 text-black'
            };

            notification.className += ` ${colors[type] || colors.success}`;
            notification.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            // Animar entrada
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Auto-remover después de 3 segundos
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // Función para limpiar filtros de pedimentos
        function limpiarFiltrosPedimentos() {
            const form = document.querySelector('[data-tab-panel="pedimentos"] form');
            if (form) {
                // Limpiar todos los selects
                const selects = form.querySelectorAll('select');
                selects.forEach(select => {
                    select.selectedIndex = 0;
                });

                // Limpiar todos los inputs
                const inputs = form.querySelectorAll('input');
                inputs.forEach(input => {
                    input.value = '';
                });

                // Opcional: enviar formulario para actualizar la vista
                // form.submit();
            }
        }

        // Funciones del modal de exportación
        window.openExportModal = function() {
            document.getElementById('exportModal').classList.remove('hidden');
        }

        window.closeExportModal = function() {
            document.getElementById('exportModal').classList.add('hidden');
            // Resetear formulario
            document.getElementById('exportForm').reset();
            document.querySelector('input[name="format"][value="excel"]').checked = true;
        }

        // Manejar el envío del formulario de exportación
        document.getElementById('exportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const reportType = formData.get('report_type');
            const format = formData.get('format');
            
            if (!reportType) {
                alert('Por favor selecciona un tipo de reporte');
                return;
            }

            const btn = document.getElementById('generateReportBtn');
            const btnText = document.getElementById('generateReportText');
            
            // Deshabilitar botón y mostrar loading
            btn.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generando...';

            // Construir URL según el tipo de reporte
            let exportUrl = '';
            const currentParams = new URLSearchParams(window.location.search);
            const baseUrl = '<?php echo e(route("logistica.reportes.export")); ?>';
            
            switch(reportType) {
                case 'seguimiento':
                    // Para seguimiento, usar la ruta existente pero con parámetros adicionales
                    exportUrl = baseUrl;
                    
                    // Agregar parámetros de campos personalizados si están marcados
                    if (formData.get('include_custom_fields')) {
                        currentParams.set('include_custom_fields', '1');
                    }
                    if (formData.get('include_comments')) {
                        currentParams.set('include_comments', '1');
                    }
                    break;
                    
                case 'pedimentos':
                    // Para pedimentos, usar una nueva ruta
                    exportUrl = baseUrl.replace('/export', '/pedimentos/export');
                    break;
                    
                case 'resumen':
                    // Para resumen ejecutivo, usar una nueva ruta
                    exportUrl = baseUrl.replace('/export', '/resumen/export');
                    break;
            }
            
            // Agregar formato
            currentParams.set('format', format);
            currentParams.set('report_type', reportType);
            
            // Construir URL final
            const finalUrl = exportUrl + '?' + currentParams.toString();
            
            // Crear enlace temporal para descarga
            const link = document.createElement('a');
            link.href = finalUrl;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Cerrar modal y rehabilitar botón después de un breve delay
            setTimeout(() => {
                btn.disabled = false;
                btnText.innerHTML = 'Generar Reporte';
                closeExportModal();
                
                // Mostrar notificación de éxito
                showNotification('Descarga iniciada', 'El reporte se está generando y descargando.', 'success');
            }, 1000);
        });
    </script>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sistemas\Downloads\PROYECTOS EI\ERP_EstrategiaeInnovacion\resources\views/Logistica/reportes.blade.php ENDPATH**/ ?>