

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/Logistica/pedimentos.css')); ?>?v=<?php echo e(time()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <button onclick="history.back()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center space-x-2">
                            <span>←</span>
                            <span>Regresar</span>
                        </button>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">📄 Control de Pagos de Pedimentos</h1>
                            <p class="text-gray-600 mt-1">Gestiona el estado de pago de pedimentos extraídos de la matriz de seguimiento logístico</p>
                        </div>
                    </div>
                    <div class="flex space-x-4">
                        <a href="/reportes/pedimentos" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center space-x-2">
                            <span>📊</span>
                            <span>Reportes</span>
                        </a>
                        <button onclick="marcarPagadosSeleccionados()" id="btnMarcarPagados" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 hidden">
                            ✅ Marcar como Pagados
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="px-6 py-4 bg-gray-50">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600"><?php echo e($stats['total_claves'] ?? 0); ?></div>
                        <div class="text-sm text-gray-600">Tipos Operación</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-indigo-600"><?php echo e($stats['total_pedimentos'] ?? 0); ?></div>
                        <div class="text-sm text-gray-600">Total Pedimentos</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600"><?php echo e($stats['pendientes']); ?></div>
                        <div class="text-sm text-gray-600">Por Pagar</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600"><?php echo e($stats['pagados']); ?></div>
                        <div class="text-sm text-gray-600">Pagados</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4">
                <form method="GET" action="<?php echo e(route('logistica.pedimentos.index')); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Pedimento</label>
                        <input type="text" name="buscar" value="<?php echo e(request('buscar')); ?>" 
                               placeholder="Número de pedimento..." 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado de Pago</label>
                        <select name="estado_pago" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" <?php echo e(request('estado_pago') == 'pendiente' ? 'selected' : ''); ?>>⏳ Por Pagar</option>
                            <option value="pagado" <?php echo e(request('estado_pago') == 'pagado' ? 'selected' : ''); ?>>✅ Pagado</option>
                            <option value="vencido" <?php echo e(request('estado_pago') == 'vencido' ? 'selected' : ''); ?>>❌ Vencido</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                            🔍 Filtrar
                        </button>
                    </div>
                    <div class="flex items-end">
                        <a href="<?php echo e(route('logistica.pedimentos.index')); ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200">
                            🔄 Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Pedimentos -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="rounded border-gray-300">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clave Operación</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pedimentos</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Por Pagar</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pagados</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clientes</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ejecutivos</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periodo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado General</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $paginatedPedimentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedimento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <?php if($pedimento->estado_pago !== 'pagado'): ?>
                                        <input type="checkbox" name="pedimentos[]" value="<?php echo e($pedimento->id); ?>" onchange="updateSelectAll()" class="pedimento-checkbox rounded border-gray-300">
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="font-bold text-blue-700 text-lg"><?php echo e($pedimento->clave); ?></div>
                                    <div class="text-sm text-gray-500">Tipo de Operación</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <div class="font-bold text-2xl text-indigo-600"><?php echo e($pedimento->total_pedimentos ?? 0); ?></div>
                                    <div class="text-sm text-gray-500">pedimentos</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <div class="font-bold text-2xl text-yellow-600"><?php echo e($pedimento->pedimentos_por_pagar ?? 0); ?></div>
                                    <div class="text-sm text-gray-500">por pagar</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <div class="font-bold text-2xl text-green-600"><?php echo e($pedimento->pedimentos_pagados ?? 0); ?></div>
                                    <div class="text-sm text-gray-500">pagados</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap max-w-xs">
                                    <div class="font-medium text-gray-900 truncate" title="<?php echo e($pedimento->clientes); ?>">
                                        <?php echo e(Str::limit($pedimento->clientes ?? 'Sin clientes', 40)); ?>

                                    </div>
                                    <div class="text-sm text-gray-500"><?php echo e(Str::plural('Cliente', $pedimento->total_pedimentos)); ?></div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap max-w-xs">
                                    <div class="font-medium text-gray-700 truncate" title="<?php echo e($pedimento->ejecutivos); ?>">
                                        <?php echo e(Str::limit($pedimento->ejecutivos ?? 'Sin ejecutivos', 30)); ?>

                                    </div>
                                    <div class="text-sm text-gray-500"><?php echo e(Str::plural('Ejecutivo', $pedimento->total_pedimentos)); ?></div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">
                                    <?php if($pedimento->primera_fecha && $pedimento->ultima_fecha): ?>
                                        <div class="text-gray-900"><?php echo e(\Carbon\Carbon::parse($pedimento->primera_fecha)->format('d/m/Y')); ?></div>
                                        <div class="text-gray-500">al <?php echo e(\Carbon\Carbon::parse($pedimento->ultima_fecha)->format('d/m/Y')); ?></div>
                                    <?php else: ?>
                                        <div class="text-gray-500">Sin fechas</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php if($pedimento->estado_pago == 'pagado'): ?> bg-green-100 text-green-800
                                        <?php else: ?> bg-yellow-100 text-yellow-800 <?php endif; ?>">
                                        <?php if($pedimento->estado_pago == 'pagado'): ?> ✅ Pagado
                                        <?php else: ?> ⏳ Por Pagar <?php endif; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button onclick="togglePedimentosClave('<?php echo e($pedimento->clave); ?>')" class="text-green-600 hover:text-green-900 bg-green-50 px-2 py-1 rounded">
                                        <span id="icon-<?php echo e($pedimento->clave); ?>">👁️</span> <span id="text-<?php echo e($pedimento->clave); ?>">Ver Pedimentos</span>
                                    </button>
                                </td>
                            </tr>
                            <!-- Fila expandible para mostrar pedimentos individuales -->
                            <tr id="pedimentos-<?php echo e($pedimento->clave); ?>" class="hidden bg-blue-50">
                                <td colspan="7" class="px-6 py-4">
                                    <div class="bg-white rounded-lg p-4 shadow-sm">
                                        <h4 class="text-lg font-semibold text-gray-800 mb-3">
                                            📋 Pedimentos de la clave: <?php echo e($pedimento->clave); ?>

                                        </h4>
                                        <div id="loading-<?php echo e($pedimento->clave); ?>" class="text-center py-4">
                                            <div class="inline-flex items-center">
                                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Cargando pedimentos...
                                            </div>
                                        </div>
                                        <div id="pedimentos-lista-<?php echo e($pedimento->clave); ?>" class="hidden">
                                            <!-- Los pedimentos se cargarán aquí via AJAX -->
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <div class="text-6xl mb-4">📄</div>
                                        <div class="text-lg font-medium">No hay pedimentos disponibles</div>
                                        <div class="text-sm">Los pedimentos aparecerán aquí cuando se generen operaciones logísticas</div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if($paginatedPedimentos->hasPages()): ?>
                <div class="px-6 py-4 border-t border-gray-200">
                    <?php echo e($paginatedPedimentos->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Edición -->
<div id="modalEditar" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Estado de Pago</h3>
            <form id="formEditar">
                <input type="hidden" id="pedimentoId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado de Pago</label>
                    <select id="estadoPago" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="pendiente">⏳ Pendiente</option>
                        <option value="pagado">✅ Pagado</option>
                        <option value="vencido">❌ Vencido</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Pago</label>
                    <input type="date" id="fechaPago" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monto</label>
                    <input type="number" id="monto" step="0.01" placeholder="0.00" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Vencimiento</label>
                    <input type="date" id="fechaVencimiento" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                    <textarea id="observaciones" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="Observaciones sobre el pago..."></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="cerrarModalEditar()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Detalles -->
<div id="modalDetalles" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Detalles del Pedimento</h3>
            <div id="contenidoDetalles">
                <!-- Contenido se carga dinámicamente -->
            </div>
            <div class="flex justify-end mt-6">
                <button onclick="cerrarModalDetalles()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>



<!-- Modal para editar pedimento individual -->
<div id="modalEditarPedimento" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="formEditarPedimento" onsubmit="return guardarPedimento(event)">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                Editar Estado de Pago - Clave de Operación
                            </h3>
                            
                            <input type="hidden" id="clave-operacion-hidden" name="clave_operacion">
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Clave de Operación</label>
                                    <input type="text" id="clave-operacion-display" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Total de Pedimentos</label>
                                    <input type="text" id="total-pedimentos" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Estado de Pago *</label>
                                        <select id="pedimento-estado" name="estado_pago" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="pendiente">⏳ Por Pagar</option>
                                            <option value="pagado">✅ Pagado</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Moneda *</label>
                                        <select id="pedimento-moneda" name="moneda" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <!-- Se cargarán las monedas via AJAX -->
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Monto</label>
                                        <input type="number" step="0.01" min="0" id="pedimento-monto" name="monto" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Fecha Tentativa Pago</label>
                                        <input type="date" id="pedimento-fecha-tentativa" name="fecha_tentativa_pago" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                                    <textarea id="pedimento-observaciones" name="observaciones_pago" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Notas sobre el pago..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        💾 Guardar
                    </button>
                    <button type="button" onclick="cerrarModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        ❌ Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Pedimento Individual -->
<div id="modalEditarPedimentoIndividual" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="formEditarPedimentoIndividual" onsubmit="return guardarPedimentoIndividual(event)">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                📋 Editar Pago de Pedimento Individual
                            </h3>
                            
                            <input type="hidden" id="pedimento-individual-no" name="no_pedimento">
                            <input type="hidden" id="pedimento-individual-operacion-id" name="operacion_logistica_id">
                            <input type="hidden" id="pedimento-individual-clave" name="clave">
                            
                            <div class="space-y-4">
                                <div class="bg-blue-50 p-3 rounded-lg">
                                    <div class="text-sm text-blue-800">
                                        <strong>No. Pedimento:</strong> <span id="display-pedimento-no"></span><br>
                                        <strong>Clave:</strong> <span id="display-pedimento-clave"></span><br>
                                        <strong>Cliente:</strong> <span id="display-pedimento-cliente"></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Estado de Pago *</label>
                                    <select id="pedimento-individual-estado" name="estado_pago" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="pendiente">⏳ Por Pagar</option>
                                        <option value="pagado">✅ Pagado</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha de Pago</label>
                                    <input type="date" id="pedimento-individual-fecha-pago" name="fecha_pago" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Monto</label>
                                        <input type="number" step="0.01" min="0" id="pedimento-individual-monto" name="monto" 
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                               placeholder="0.00">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Moneda</label>
                                        <select id="pedimento-individual-moneda" name="moneda" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="MXN">💵 MXN - Peso Mexicano</option>
                                            <option value="USD">💰 USD - Dólar Estadounidense</option>
                                            <option value="EUR">💶 EUR - Euro</option>
                                            <option value="CAD">🍁 CAD - Dólar Canadiense</option>
                                            <option value="GBP">💷 GBP - Libra Esterlina</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                                    <textarea id="pedimento-individual-observaciones" name="observaciones" rows="3" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                              placeholder="Notas sobre el pago del pedimento..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        💾 Guardar Cambios
                    </button>
                    <button type="button" onclick="cerrarModalPedimentoIndividual()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        ❌ Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/Logistica/pedimentos.js')); ?>?v=<?php echo e(time()); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SISTEMAS\Downloads\ERP EstrategiaeInnovacion\Sistema_Tickets_E-I\resources\views/Logistica/pedimentos/index.blade.php ENDPATH**/ ?>