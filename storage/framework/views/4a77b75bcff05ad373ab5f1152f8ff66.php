<?php $__env->startSection('title','Reportes - Logística'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/logistica/matriz-seguimiento.css')); ?>">
    <link href="<?php echo e(asset('css/logistica/export-styles.css')); ?>" rel="stylesheet">
<?php $__env->stopPush(); ?>


<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<?php $__env->startSection('content'); ?>
    <main class="bg-gradient-to-br from-white via-indigo-50 to-indigo-100 min-h-screen">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <a href="<?php echo e(route('logistica.index')); ?>" class="inline-flex items-center text-indigo-700 hover:text-indigo-900 mb-4">
                <span class="mr-2">&larr;</span> Regresar
            </a>
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-slate-800">Reportes de Operaciones</h1>
                <div class="flex gap-3">
                    <button onclick="openEmailModal()" class="email-btn inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 group">
                        <i class="fas fa-envelope mr-2 group-hover:scale-110 transition-transform"></i>
                        Enviar por Correo
                    </button>

                    <!-- Botón de Exportar Directo -->
                    <a href="<?php echo e(route('logistica.reportes.export', request()->query())); ?>"
                       class="export-btn inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 group">
                        <i class="fas fa-download mr-2 group-hover:scale-110 transition-transform"></i>
                        <span class="group-hover:mr-1 transition-all">Exportar</span>
                        <i class="fas fa-table ml-2 text-sm group-hover:scale-110 transition-transform"></i>
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-xl shadow p-6 mb-6">
                <form method="GET" action="<?php echo e(route('logistica.reportes')); ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Período -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Período</label>
                        <select name="periodo" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            <option value="semanal" <?php echo e(request('periodo') === 'semanal' ? 'selected' : ''); ?>>Última Semana</option>
                            <option value="mensual" <?php echo e(request('periodo') === 'mensual' ? 'selected' : ''); ?>>Último Mes</option>
                            <option value="anual" <?php echo e(request('periodo') === 'anual' ? 'selected' : ''); ?>>Último Año</option>
                        </select>
                    </div>

                    <!-- Mes y Año -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Mes/Año</label>
                        <div class="flex gap-2">
                            <select name="mes" class="w-1/2 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">Mes</option>
                                <?php for($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo e($m); ?>" <?php echo e(request('mes') == $m ? 'selected' : ''); ?>><?php echo e(\Carbon\Carbon::create(null, $m)->format('M')); ?></option>
                                <?php endfor; ?>
                            </select>
                            <select name="anio" class="w-1/2 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">Año</option>
                                <?php for($y = now()->year; $y >= now()->year - 5; $y--): ?>
                                    <option value="<?php echo e($y); ?>" <?php echo e(request('anio') == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Cliente -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Cliente</label>
                        <select name="cliente" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todos los Clientes</option>
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
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todos los Status</option>
                            <option value="In Process" <?php echo e(request('status') === 'In Process' ? 'selected' : ''); ?>>En Proceso</option>
                            <option value="Out of Metric" <?php echo e(request('status') === 'Out of Metric' ? 'selected' : ''); ?>>Fuera de Métrica</option>
                            <option value="Done" <?php echo e(request('status') === 'Done' ? 'selected' : ''); ?>>Completado</option>
                        </select>
                    </div>

                    <!-- Fecha Desde -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Desde</label>
                        <input type="date" name="fecha_desde" value="<?php echo e(request('fecha_desde')); ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Fecha Hasta -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Hasta</label>
                        <input type="date" name="fecha_hasta" value="<?php echo e(request('fecha_hasta')); ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Botones -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Filtrar
                        </button>
                        <a href="<?php echo e(route('logistica.reportes')); ?>" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="font-semibold text-slate-700">Resumen por Status</h2>
                        <div class="relative">
                            <select id="chartTypeSelector" class="px-4 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg text-sm font-medium text-blue-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hover:bg-gradient-to-r hover:from-blue-100 hover:to-indigo-100 transition-all cursor-pointer appearance-none pr-8" onchange="changeChartType()">
                                <option value="bar">📊 Barras</option>
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
                        <canvas id="statusChart" style="max-height: 280px"></canvas>
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
                <div class="bg-white rounded-xl shadow p-6 lg:col-span-2">
                    <h2 class="font-semibold text-slate-700 mb-2">Últimas Operaciones</h2>
                    <div class="overflow-x-auto">
                        <table id="operacionesTable" class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-slate-100 text-slate-700">
                                    <th class="px-3 py-2 text-left">ID</th>
                                    <th class="px-3 py-2 text-left">Ejecutivo</th>
                                    <th class="px-3 py-2 text-left">Cliente</th>
                                    <th class="px-3 py-2 text-left">Tipo</th>
                                    <th class="px-3 py-2 text-left">Status Actual</th>
                                    <th class="px-3 py-2 text-left">Resultado</th>
                                    <th class="px-3 py-2 text-left">Días Tránsito</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $operaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="border-b" data-operacion-id="<?php echo e($op->id ?? ''); ?>">
                                        <td class="px-3 py-2"><?php echo e($op->id ?? '-'); ?></td>
                                        <td class="px-3 py-2"><?php echo e(is_string($op->ejecutivo) ? $op->ejecutivo : '-'); ?></td>
                                        <td class="px-3 py-2"><?php echo e(is_string($op->cliente) ? $op->cliente : '-'); ?></td>
                                        <td class="px-3 py-2"><?php echo e(is_string($op->tipo_operacion_enum) ? $op->tipo_operacion_enum : '-'); ?></td>
                                        <td class="px-3 py-2">
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
                                        <td class="px-3 py-2"><?php echo e($op->resultado ?? '-'); ?></td>
                                        <td class="px-3 py-2"><?php echo e($op->dias_transito ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="px-3 py-6 text-center text-slate-500">Sin operaciones recientes</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Resumen Ejecutivo Inteligente -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 mb-8 text-white">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">📊 Resumen Ejecutivo</h2>
                    <div class="text-sm opacity-75">
                        <?php echo e(now()->format('d/m/Y H:i')); ?>

                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-medium mb-2">🎯 Rendimiento General</h3>
                        <?php
                            $totalOps = $statsTemporales['total_operaciones'];
                            $eficienciaGeneral = $totalOps > 0 ? round((($statsTemporales['en_tiempo'] + $statsTemporales['completado_tiempo']) / $totalOps) * 100, 1) : 0;
                            $promedioEjecucion = round($statsTemporales['promedio_dias'] ?? 0, 1);
                            $targetPromedio = round($statsTemporales['promedio_target'] ?? 3, 1);
                        ?>
                        <p class="text-sm opacity-90">
                            <span class="font-semibold">Eficiencia:</span> <?php echo e(isset($stats['eficiencia_general']) && is_scalar($stats['eficiencia_general']) ? $stats['eficiencia_general'] : 0); ?>%
                            <?php if($eficienciaGeneral >= 80): ?>
                                <span class="text-green-300">🟢 Excelente</span>
                            <?php elseif($eficienciaGeneral >= 60): ?>
                                <span class="text-yellow-300">🟡 Regular</span>
                            <?php else: ?>
                                <span class="text-red-300">🔴 Requiere Atención</span>
                            <?php endif; ?>
                        </p>
                        <p class="text-sm opacity-90">
                            <span class="font-semibold">Tiempo Promedio:</span> <?php echo e(isset($stats['promedio_ejecucion']) ? $stats['promedio_ejecucion'] : 0); ?> días
                            (Target: <?php echo e(isset($stats['target_promedio']) ? $stats['target_promedio'] : 0); ?> días)
                        </p>
                    </div>
                    <div>
                        <h3 class="font-medium mb-2">⚠️ Alertas y Recomendaciones</h3>
                        <div class="text-sm space-y-1">
                            <?php if($statsTemporales['con_retraso'] > 0): ?>
                                <p class="text-red-300">• <?php echo e(isset($statsTemporales['con_retraso']) ? $statsTemporales['con_retraso'] : 0); ?> operaciones con retraso activo</p>
                            <?php endif; ?>
                            <?php if($statsTemporales['en_riesgo'] > 0): ?>
                                <p class="text-yellow-300">• <?php echo e(isset($statsTemporales['en_riesgo']) ? $statsTemporales['en_riesgo'] : 0); ?> operaciones en riesgo de retraso</p>
                            <?php endif; ?>
                            <?php if($eficienciaGeneral < 70): ?>
                                <p class="text-orange-300">• Considerar revisión de procesos operativos</p>
                            <?php endif; ?>
                            <?php if($statsTemporales['con_retraso'] == 0 && $statsTemporales['en_riesgo'] == 0): ?>
                                <p class="text-green-300">✅ Todas las operaciones están en tiempo</p>
                            <?php endif; ?>
                        </div>
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
                                <option value="scatter">🎯 Dispersión (Días vs Target)</option>
                                <option value="bar">📊 Categorías de Rendimiento</option>
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
                                        <span>📊</span><span>Datos CSV</span>
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
        </div>

        <!-- Modal para vista de pantalla completa -->
        <div id="fullScreenModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden">
                <div class="flex justify-between items-center p-6 border-b border-slate-200">
                    <h3 class="text-xl font-semibold text-slate-800">Análisis Temporal - Vista Completa</h3>
                    <button onclick="closeFullScreenChart()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
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

    <!-- Modal Enviar por Correo -->
    <div id="emailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-slate-800">Enviar Reporte por Correo</h2>
                    <button onclick="closeEmailModal()" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

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

                    <!-- Botones -->
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="closeEmailModal()"
                                class="flex-1 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-paper-plane mr-2"></i>
                            <span id="btnEnviarTexto">Enviar Correo</span>
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
        function obtenerOperacionesActuales() {
            const operacionesIds = [];
            const filas = document.querySelectorAll('#operacionesTable tbody tr[data-operacion-id]');

            filas.forEach(fila => {
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

                if (!destinatarios) {
                    throw new Error('Por favor ingrese al menos un destinatario');
                }

                if (!asunto) {
                    throw new Error('Por favor ingrese un asunto');
                }

                if (!mensaje) {
                    throw new Error('Por favor ingrese un mensaje');
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

                // Obtener operaciones de la tabla actual (basado en filtros aplicados)
                const operacionesIds = obtenerOperacionesActuales();
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
                btnTexto.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Enviar Correo';
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
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ERP_EstrategiaeInnovacion\resources\views/Logistica/reportes.blade.php ENDPATH**/ ?>