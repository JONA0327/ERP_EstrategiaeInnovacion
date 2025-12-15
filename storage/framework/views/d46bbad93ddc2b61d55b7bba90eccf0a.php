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
                <?php echo e(__('Control de Asistencia')); ?>

            </h2>
            <div class="flex gap-3">
                <button onclick="abrirModalIncidencia()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded transition shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Registrar Incidencia / Vacaciones
                </button>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-8 bg-gray-50 min-h-screen" x-data="{ openImport: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- DASHBOARD DE KPIs (NUEVO) -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Card: Asistencia General -->
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Eficiencia Asistencia</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo e($porcentajeAsistencia); ?>%</p>
                    </div>
                    <div class="p-2 bg-blue-50 rounded-full text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <!-- Card: Retardos -->
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Retardos (Injust.)</p>
                        <p class="text-2xl font-bold text-yellow-600"><?php echo e($retardos); ?></p>
                    </div>
                    <div class="p-2 bg-yellow-50 rounded-full text-yellow-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <!-- Card: Faltas -->
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Faltas / Ausencias</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo e($faltas); ?></p>
                    </div>
                    <div class="p-2 bg-red-50 rounded-full text-red-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <!-- Card: Top Retardos (Lista mini) -->
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase mb-2">Más Retardos</p>
                    <div class="space-y-2">
                        <?php $__empty_1 = true; $__currentLoopData = $topRetardos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $top): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-700 truncate w-24" title="<?php echo e($top->nombre); ?>"><?php echo e($top->nombre); ?></span>
                                <span class="font-bold text-red-500"><?php echo e($top->total); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <span class="text-xs text-gray-400">Sin datos</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- PANEL IMPORTAR (Se mantiene igual) -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="p-4 flex justify-between items-center cursor-pointer hover:bg-gray-50 transition" @click="openImport = !openImport">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Importar / Gestionar Datos</h3>
                            <p class="text-xs text-gray-500">Cargar archivos Excel o limpiar base de datos</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 transform transition-transform" :class="{'rotate-180': openImport}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>

                <div x-show="openImport" class="border-t border-gray-100 p-6 bg-gray-50" style="display: none;">
                    <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                        <div class="flex-1 w-full">
                            <form id="importForm" class="flex flex-col sm:flex-row gap-4">
                                <?php echo csrf_field(); ?>
                                <div class="flex-1">
                                    <input type="file" name="archivo" id="archivo" accept=".xls,.xlsx,.csv" 
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                </div>
                                <button type="submit" 
                                    class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition h-[38px] shadow-sm">
                                    Procesar
                                </button>
                            </form>
                            <div id="progressContainer" class="hidden mt-4">
                                <div class="flex justify-between mb-1">
                                    <span id="progressText" class="text-xs font-medium text-gray-600">Preparando...</span>
                                    <span id="progressPercent" class="text-xs font-medium text-gray-600">0%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div id="progressBar" class="bg-blue-600 h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                                <p id="progressMessage" class="text-xs text-gray-400 mt-1"></p>
                            </div>
                        </div>
                        <div class="border-l border-gray-200 pl-6 md:ml-6">
                            <form action="<?php echo e(route('reloj.clear')); ?>" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar TODOS los registros?');">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center gap-2 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Vaciar Registros
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VISTA DE REPORTE -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col lg:flex-row gap-4 justify-between items-end lg:items-center">
                    <form method="GET" action="<?php echo e(route('reloj.index')); ?>" class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto flex-1">
                        <div class="w-full sm:w-64">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 block">Buscar Empleado</label>
                            <div class="relative">
                                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Nombre o ID..." 
                                    class="pl-3 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>
                        <div class="flex gap-2 w-full sm:w-auto">
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 block">Periodo</label>
                                <div class="flex gap-2 items-center">
                                    <input type="date" name="fecha_inicio" value="<?php echo e(request('fecha_inicio', now()->startOfMonth()->toDateString())); ?>" class="w-full sm:w-36 rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <span class="text-gray-400">-</span>
                                    <input type="date" name="fecha_fin" value="<?php echo e(request('fecha_fin', now()->endOfMonth()->toDateString())); ?>" class="w-full sm:w-36 rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="h-[38px] px-4 bg-gray-800 hover:bg-gray-700 text-white rounded-md text-sm font-medium transition shadow-sm">Filtrar</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empleado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Entrada</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Salida</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Horas</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Incidencia</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $currentMonth = null; ?>
                            <?php $__empty_1 = true; $__currentLoopData = $asistencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asistencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                
                                <?php if($currentMonth !== $asistencia->fecha->format('Y-m')): ?>
                                    <?php $currentMonth = $asistencia->fecha->format('Y-m'); ?>
                                    <tr class="bg-gray-100 border-t border-b border-gray-200">
                                        <td colspan="7" class="px-6 py-2 text-xs font-bold text-gray-600 uppercase tracking-widest">
                                            <?php echo e($asistencia->fecha->isoFormat('MMMM YYYY')); ?>

                                        </td>
                                    </tr>
                                <?php endif; ?>

                                
                                <?php 
                                    $isWeekend = $asistencia->fecha->isWeekend(); 
                                    $rowClass = $isWeekend ? 'bg-gray-50/50' : 'hover:bg-gray-50 transition-colors';
                                ?>

                                <tr class="<?php echo e($rowClass); ?>">
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-xs font-bold border border-gray-300">
                                                <?php echo e(substr($asistencia->nombre, 0, 2)); ?>

                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900"><?php echo e($asistencia->nombre); ?></div>
                                                <div class="text-xs text-gray-500">ID: <?php echo e($asistencia->empleado_no); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo e($asistencia->fecha->format('d/m')); ?></div>
                                        <div class="text-xs <?php echo e($isWeekend ? 'text-indigo-500 font-semibold' : 'text-gray-500'); ?>">
                                            <?php echo e($asistencia->fecha->isoFormat('ddd')); ?>

                                        </div>
                                    </td>
                                    <?php if($asistencia->tipo_registro == 'asistencia'): ?>
                                        <td class="px-6 py-3 whitespace-nowrap text-center">
                                            <span class="text-sm font-mono <?php echo e(($asistencia->es_retardo && !$asistencia->es_justificado) ? 'text-red-600 font-bold' : 'text-gray-700'); ?>">
                                                <?php echo e($asistencia->entrada ? substr($asistencia->entrada, 0, 5) : '--:--'); ?>

                                            </span>
                                            <?php if($asistencia->es_retardo): ?>
                                                <?php if($asistencia->es_justificado): ?>
                                                    <span class="block text-[10px] text-green-600 font-medium">Justificado</span>
                                                <?php else: ?>
                                                    <span class="block text-[10px] text-red-500 font-medium">Retardo > 8:45</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-center text-sm font-mono text-gray-700">
                                            <?php echo e($asistencia->salida ? substr($asistencia->salida, 0, 5) : '--:--'); ?>

                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-center text-sm text-gray-500">
                                            <?php echo e($asistencia->horas_trabajadas); ?>

                                        </td>
                                    <?php else: ?>
                                        
                                        <td colspan="3" class="px-6 py-3 text-center">
                                            <div class="text-sm text-gray-500 italic bg-gray-50 rounded py-1 border border-gray-100">
                                                <?php echo e(ucfirst($asistencia->tipo_registro)); ?>

                                                <?php if($asistencia->comentarios): ?>
                                                    <span class="text-xs text-gray-400 block max-w-[200px] truncate mx-auto" title="<?php echo e($asistencia->comentarios); ?>">
                                                        <?php echo e($asistencia->comentarios); ?>

                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                    <td class="px-6 py-3 whitespace-nowrap text-center">
                                        <?php switch($asistencia->tipo_registro):
                                            case ('vacaciones'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Vacaciones</span> <?php break; ?>
                                            <?php case ('incapacidad'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Incapacidad</span> <?php break; ?>
                                            <?php case ('falta'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Falta</span> <?php break; ?>
                                            <?php case ('permiso'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-teal-100 text-teal-800">Permiso</span> <?php break; ?>
                                            <?php case ('descanso'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-200 text-gray-600">Descanso</span> <?php break; ?>
                                            <?php default: ?>
                                                <?php if(!$asistencia->entrada && !$asistencia->salida): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Falta / Sin Reg</span>
                                                <?php elseif(!$asistencia->entrada || !$asistencia->salida): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">Incompleto</span>
                                                <?php elseif($asistencia->es_retardo && !$asistencia->es_justificado): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Retardo</span>
                                                <?php elseif($asistencia->es_justificado): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Justificado</span>
                                                <?php else: ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-50 text-green-700">A Tiempo</span>
                                                <?php endif; ?>
                                        <?php endswitch; ?>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <button onclick="abrirModalEdicion(<?php echo e($asistencia); ?>)" class="text-indigo-600 hover:text-indigo-900 transition">Editar</button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No hay registros para mostrar.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200"><?php echo e($asistencias->links()); ?></div>
            </div>
        </div>
    </div>

    <!-- MODAL EDICIÓN RÁPIDA -->
    <div id="modalEdicion" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="cerrarModalEdicion()"></div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form id="formEdicion" method="POST">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Registro Individual</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Incidencia</label>
                                <select name="tipo_registro" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="asistencia">Asistencia Normal</option>
                                    <option value="falta">Falta</option>
                                    <option value="vacaciones">Vacaciones</option>
                                    <option value="incapacidad">Incapacidad</option>
                                    <option value="permiso">Permiso con Goce</option>
                                    <option value="descanso">Día de Descanso</option>
                                </select>
                            </div>
                            <div class="flex items-center bg-gray-50 p-2 rounded border border-gray-200">
                                <input id="es_justificado" name="es_justificado" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <label for="es_justificado" class="ml-2 block text-sm text-gray-900 font-medium">Justificar Retardo / Falta</label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Comentarios</label>
                                <textarea name="comentarios" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Guardar</button>
                        <button type="button" onclick="cerrarModalEdicion()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL CREAR INCIDENCIA MASIVA (Vacaciones/Incapacidad) -->
    <div id="modalIncidencia" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="cerrarModalIncidencia()"></div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="<?php echo e(route('reloj.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6" x-data="{ tipo: 'vacaciones' }">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Registrar Incidencia / Periodo</h3>
                            <button type="button" onclick="cerrarModalIncidencia()" class="text-gray-400 hover:text-gray-500">
                                <span class="sr-only">Cerrar</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Selección de Empleado -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Empleado</label>
                                <select name="empleado_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <?php $__currentLoopData = \App\Models\Empleado::orderBy('nombre')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($emp->id); ?>"><?php echo e($emp->nombre); ?> (<?php echo e($emp->id_empleado); ?>)</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <!-- Tipo de Registro -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Registro</label>
                                <select name="tipo_registro" x-model="tipo" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="vacaciones">🌴 Vacaciones</option>
                                    <option value="incapacidad">🏥 Incapacidad</option>
                                    <option value="permiso">📄 Permiso Especial</option>
                                    <option value="falta">❌ Falta Injustificada</option>
                                    <option value="descanso">🏠 Día de Descanso</option>
                                </select>
                            </div>

                            <!-- Fechas (Rango) -->
                            <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Periodo a Aplicar</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Desde</label>
                                        <input type="date" name="fecha_inicio" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                    <div x-show="['vacaciones', 'incapacidad', 'permiso'].includes(tipo)" x-transition>
                                        <label class="block text-sm font-medium text-gray-700">Hasta (Inclusive)</label>
                                        <input type="date" name="fecha_fin" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                                <p x-show="['vacaciones', 'incapacidad', 'permiso'].includes(tipo)" class="text-xs text-gray-500 mt-2">
                                    * Se crearán registros para todos los días del rango seleccionado.
                                </p>
                            </div>

                            <!-- Comentarios -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Motivo / Comentarios</label>
                                <textarea name="comentarios" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Ej: Autorizado por Gerencia"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar Registros
                        </button>
                        <button type="button" onclick="cerrarModalIncidencia()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JS Scripts -->
    <script>
        // Importador JS (Igual que antes)
        document.getElementById('importForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const uniqueKey = 'import_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            formData.append('progress_key', uniqueKey);

            const btn = this.querySelector('button');
            const originalText = btn.innerHTML;
            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const progressPercent = document.getElementById('progressPercent');
            const progressMessage = document.getElementById('progressMessage');

            btn.disabled = true;
            btn.innerHTML = 'Cargando...';
            progressContainer.classList.remove('hidden');
            progressBar.style.width = '0%';
            progressPercent.innerText = '0%';
            progressMessage.innerText = 'Iniciando...';

            let pollInterval = setInterval(() => {
                fetch(`/recursos-humanos/reloj/progress/${uniqueKey}`)
                    .then(r => r.json())
                    .then(status => {
                        let p = status.percent || 0;
                        progressBar.style.width = p + '%';
                        progressPercent.innerText = p + '%';
                        progressMessage.innerText = status.mensaje || 'Procesando...';
                        if (status.finalizado || status.status === 'error') {
                            clearInterval(pollInterval);
                            if(status.status === 'error') {
                                alert('Error: ' + status.mensaje);
                                btn.disabled = false;
                                btn.innerHTML = originalText;
                            }
                        }
                    }).catch(err => console.log(err));
            }, 1000);

            fetch("<?php echo e(route('reloj.start')); ?>", {
                method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>" }
            })
            .then(r => r.json())
            .then(data => {
                clearInterval(pollInterval);
                if (data.error) throw new Error(data.error);
                progressBar.style.width = '100%';
                progressPercent.innerText = '100%';
                progressMessage.innerText = '¡Completado!';
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(error => {
                clearInterval(pollInterval);
                console.error(error);
                alert('Error al procesar: ' + error.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });

        // Modales
        function abrirModalEdicion(asistencia) {
            const form = document.getElementById('formEdicion');
            form.action = `/recursos-humanos/reloj/update/${asistencia.id}`;
            form.querySelector('[name="tipo_registro"]').value = asistencia.tipo_registro;
            form.querySelector('[name="comentarios"]').value = asistencia.comentarios || '';
            form.querySelector('[name="es_justificado"]').checked = asistencia.es_justificado;
            document.getElementById('modalEdicion').classList.remove('hidden');
        }
        function cerrarModalEdicion() {
            document.getElementById('modalEdicion').classList.add('hidden');
        }
        function abrirModalIncidencia() {
            document.getElementById('modalIncidencia').classList.remove('hidden');
        }
        function cerrarModalIncidencia() {
            document.getElementById('modalIncidencia').classList.add('hidden');
        }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/Recursos_Humanos/reloj_checador.blade.php ENDPATH**/ ?>