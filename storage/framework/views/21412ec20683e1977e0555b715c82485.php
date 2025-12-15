<?php $__env->startSection('title', 'Crear Ticket - ' . ucfirst($tipo) . ' - Sistema IT'); ?>

<?php $__env->startSection('content'); ?>
    <main class="relative min-h-screen overflow-hidden bg-gradient-to-br from-white via-blue-50 to-blue-100" data-ticket-create data-ticket-type="<?php echo e($tipo); ?>">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-40 -left-32 h-[28rem] w-[28rem] rounded-full bg-blue-200/40 blur-3xl"></div>
            <div class="absolute top-1/4 -right-24 h-80 w-80 rounded-full bg-blue-300/30 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/2 h-40 w-full -translate-x-1/2 bg-gradient-to-t from-white"></div>
        </div>

        <div class="relative max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <?php
                $tipoConfig = [
                    'software' => [
                        'title' => 'Reportar Problema de Software',
                        'subtitle' => 'Reporta errores, fallos o comportamientos inesperados en programas o aplicaciones',
                        'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    ],
                    'hardware' => [
                        'title' => 'Reportar Problema de Hardware',
                        'subtitle' => 'Reporta fallas en computadoras, impresoras u otros equipos físicos',
                        'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                    ],
                    'mantenimiento' => [
                        'title' => 'Programar Mantenimiento',
                        'subtitle' => 'Solicita mantenimiento preventivo o correctivo para tus equipos',
                        'icon' => 'M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v9a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h3z M12 12h.01M8 16h8',
                    ],
                ];

                $config = $tipoConfig[$tipo];
            ?>

            <!-- Banner removed: focusing page on the form -->

            <!-- Formulario -->
            <div class="relative mt-10 overflow-hidden rounded-3xl border border-blue-100/60 bg-white/90 shadow-2xl shadow-blue-500/10 backdrop-blur">
                <div class="absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-blue-50/70 to-transparent"></div>
                <div class="relative px-6 py-10 sm:px-10">
                    <form method="POST" action="<?php echo e(route('tickets.store')); ?>" enctype="multipart/form-data" class="space-y-10">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="tipo_problema" value="<?php echo e($tipo); ?>">

                        <!-- Información del Usuario -->
                        <section class="rounded-3xl border border-blue-100/60 bg-gradient-to-br from-blue-50/70 via-white to-blue-50/40 px-6 py-6 shadow-inner">
                            <h2 class="flex items-center text-lg font-semibold text-slate-900">
                                <span class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600/90 text-white shadow-lg">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </span>
                                Información del solicitante
                            </h2>
                            <div class="mt-4 rounded-2xl border border-white/70 bg-white/80 px-5 py-4 shadow-sm backdrop-blur">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nombre</p>
                                        <p class="text-base font-medium text-slate-900"><?php echo e(auth()->user()->name); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Correo</p>
                                        <p class="text-base font-medium text-slate-900"><?php echo e(auth()->user()->email); ?></p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Detalles del Problema -->
                        <section class="space-y-6 rounded-3xl border border-blue-100/60 bg-white/90 px-6 py-6 shadow-lg shadow-blue-500/10 backdrop-blur">
                            <h2 class="flex items-center text-lg font-semibold text-slate-900">
                                <span class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600 shadow-inner">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </span>
                                Detalles del <?php echo e($tipo === 'mantenimiento' ? 'mantenimiento solicitado' : 'incidente'); ?>

                            </h2>

                            <?php if($tipo === 'software'): ?>
                                <div>
                                    <label for="nombre_programa" class="mb-2 block text-sm font-medium text-slate-700">Programa / Software</label>
                                    <select name="nombre_programa"
                                            id="nombre_programa"
                                            class="block w-full rounded-2xl border border-blue-100 bg-white/80 px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-300 focus:ring-2 focus:ring-blue-200">
                                        <option value="">Selecciona un programa</option>
                                        <option value="Microsoft Outlook" <?php echo e(old('nombre_programa') === 'Microsoft Outlook' ? 'selected' : ''); ?>>Microsoft Outlook</option>
                                        <option value="Microsoft OneDrive" <?php echo e(old('nombre_programa') === 'Microsoft OneDrive' ? 'selected' : ''); ?>>Microsoft OneDrive</option>
                                        <option value="Microsoft Word" <?php echo e(old('nombre_programa') === 'Microsoft Word' ? 'selected' : ''); ?>>Microsoft Word</option>
                                        <option value="Microsoft Excel" <?php echo e(old('nombre_programa') === 'Microsoft Excel' ? 'selected' : ''); ?>>Microsoft Excel</option>
                                        <option value="Microsoft PowerPoint" <?php echo e(old('nombre_programa') === 'Microsoft PowerPoint' ? 'selected' : ''); ?>>Microsoft PowerPoint</option>
                                        <option value="Microsoft Teams" <?php echo e(old('nombre_programa') === 'Microsoft Teams' ? 'selected' : ''); ?>>Microsoft Teams</option>
                                        <option value="Google Chrome" <?php echo e(old('nombre_programa') === 'Google Chrome' ? 'selected' : ''); ?>>Google Chrome</option>
                                        <option value="Mozilla Firefox" <?php echo e(old('nombre_programa') === 'Mozilla Firefox' ? 'selected' : ''); ?>>Mozilla Firefox</option>
                                        <option value="Microsoft Edge" <?php echo e(old('nombre_programa') === 'Microsoft Edge' ? 'selected' : ''); ?>>Microsoft Edge</option>
                                        <option value="Adobe Acrobat Reader" <?php echo e(old('nombre_programa') === 'Adobe Acrobat Reader' ? 'selected' : ''); ?>>Adobe Acrobat Reader</option>
                                        <option value="Zoom" <?php echo e(old('nombre_programa') === 'Zoom' ? 'selected' : ''); ?>>Zoom</option>
                                        <option value="Skype" <?php echo e(old('nombre_programa') === 'Skype' ? 'selected' : ''); ?>>Skype</option>
                                        <option value="WhatsApp Desktop" <?php echo e(old('nombre_programa') === 'WhatsApp Desktop' ? 'selected' : ''); ?>>WhatsApp Desktop</option>
                                        <option value="Sistema ERP" <?php echo e(old('nombre_programa') === 'Sistema ERP' ? 'selected' : ''); ?>>Sistema ERP</option>
                                        <option value="Sistema CRM" <?php echo e(old('nombre_programa') === 'Sistema CRM' ? 'selected' : ''); ?>>Sistema CRM</option>
                                        <option value="Sistema de Nómina" <?php echo e(old('nombre_programa') === 'Sistema de Nómina' ? 'selected' : ''); ?>>Sistema de Nómina</option>
                                        <option value="Sistema Contable" <?php echo e(old('nombre_programa') === 'Sistema Contable' ? 'selected' : ''); ?>>Sistema Contable</option>
                                        <option value="Antivirus" <?php echo e(old('nombre_programa') === 'Antivirus' ? 'selected' : ''); ?>>Antivirus</option>
                                        <option value="Otro" <?php echo e(old('nombre_programa') === 'Otro' ? 'selected' : ''); ?>>Otro</option>
                                    </select>
                                    <?php $__errorArgs = ['nombre_programa'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                                    <div id="otroPrograma" class="mt-3 <?php echo e(old('nombre_programa') === 'Otro' ? '' : 'hidden'); ?>">
                                        <label for="otro_programa_nombre" class="mb-2 block text-sm font-medium text-slate-700">Especifica el nombre del programa/sistema</label>
                                        <input type="text"
                                               name="otro_programa_nombre"
                                               id="otro_programa_nombre"
                                               value="<?php echo e(old('otro_programa_nombre')); ?>"
                                               class="block w-full rounded-2xl border border-blue-100 bg-white/80 px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-300 focus:ring-2 focus:ring-blue-200"
                                               placeholder="Ej: Sistema interno de la empresa, aplicación específica...">
                                        <?php $__errorArgs = ['otro_programa_nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            <?php elseif($tipo === 'hardware'): ?>
                                <div class="space-y-4">
                                    <div>
                                        <label for="tipo_equipo" class="mb-2 block text-sm font-medium text-slate-700">Tipo de equipo</label>
                                        <select name="nombre_programa"
                                                id="tipo_equipo"
                                                required
                                                class="block w-full rounded-2xl border border-blue-100 bg-white/80 px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-300 focus:ring-2 focus:ring-blue-200">
                                            <option value="">Selecciona el tipo de equipo</option>
                                            <option value="Computadora" <?php echo e(old('nombre_programa') === 'Computadora' ? 'selected' : ''); ?>>Computadora</option>
                                            <option value="Impresora" <?php echo e(old('nombre_programa') === 'Impresora' ? 'selected' : ''); ?>>Impresora</option>
                                        </select>
                                        <?php $__errorArgs = ['nombre_programa'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div id="hardwareComputerInfo" class="hidden">
                                        <div class="rounded-2xl border border-blue-200/60 bg-blue-50/80 px-4 py-4 shadow-inner">
                                            <div class="flex items-start gap-3">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <div class="space-y-1 text-sm">
                                                    <h3 class="font-semibold text-blue-900">Computadora detectada</h3>
                                                    <?php if($assignedComputerLoan && $assignedComputerLoan->inventario): ?>
                                                        <p class="text-blue-800">
                                                            <?php echo e($assignedComputerLoan->inventario->codigo_inventario ?? 'Sin código'); ?> ·
                                                            <?php echo e($assignedComputerLoan->inventario->articulo); ?>

                                                            <?php if($assignedComputerLoan->inventario->modelo): ?>
                                                                – <?php echo e($assignedComputerLoan->inventario->modelo); ?>

                                                            <?php endif; ?>
                                                        </p>
                                                        <p class="text-xs text-blue-600">
                                                            Prestada desde <?php echo e(optional($assignedComputerLoan->fecha_prestamo)->format('d/m/Y') ?? 'fecha no disponible'); ?>.
                                                        </p>
                                                    <?php elseif($assignedComputerProfile): ?>
                                                        <p class="text-blue-800">
                                                            <?php echo e($assignedComputerProfile->identifier ?? 'Equipo sin identificador'); ?>

                                                            <?php if($assignedComputerProfile->brand || $assignedComputerProfile->model): ?>
                                                                – <?php echo e(trim(($assignedComputerProfile->brand ? $assignedComputerProfile->brand : '') . ' ' . ($assignedComputerProfile->model ? $assignedComputerProfile->model : ''))); ?>

                                                            <?php endif; ?>
                                                        </p>
                                                        <p class="text-xs text-blue-600">
                                                            Información tomada del historial de mantenimiento.
                                                        </p>
                                                    <?php else: ?>
                                                        <p class="text-blue-800">No se detectó una computadora asociada a tu usuario en el sistema.</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="hardwarePrinterInfo" class="hidden">
                                        <div class="rounded-2xl border border-purple-200/60 bg-purple-50/80 px-4 py-4 shadow-inner">
                                            <div class="flex items-start gap-3">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 text-purple-600">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8V4a1 1 0 011-1h8a1 1 0 011 1v4m3 4h1a1 1 0 011 1v6a1 1 0 01-1 1h-1M4 12H3a1 1 0 00-1 1v6a1 1 0 001 1h1m3-3h10m-6 3h2" />
                                                    </svg>
                                                </div>
                                                <div class="space-y-1 text-sm">
                                                    <h3 class="font-semibold text-purple-900">Impresora detectada</h3>
                                                    <?php if($assignedPrinterLoan && $assignedPrinterLoan->inventario): ?>
                                                        <p class="text-purple-800">
                                                            <?php echo e($assignedPrinterLoan->inventario->codigo_inventario ?? 'Sin código'); ?> ·
                                                            <?php echo e($assignedPrinterLoan->inventario->articulo); ?>

                                                            <?php if($assignedPrinterLoan->inventario->modelo): ?>
                                                                – <?php echo e($assignedPrinterLoan->inventario->modelo); ?>

                                                            <?php endif; ?>
                                                        </p>
                                                        <p class="text-xs text-purple-600">
                                                            Prestada desde <?php echo e(optional($assignedPrinterLoan->fecha_prestamo)->format('d/m/Y') ?? 'fecha no disponible'); ?>.
                                                        </p>
                                                    <?php else: ?>
                                                        <p class="text-purple-800">No se detectó una impresora asociada a tu usuario en el sistema.</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div>
                                <label for="descripcion_problema" class="mb-2 block text-sm font-semibold text-slate-700">
                                    <?php if($tipo === 'mantenimiento'): ?>
                                        Añadir detalles de problemas presentados en el equipo <span class="text-slate-400">(opcional)</span>
                                    <?php elseif($tipo === 'hardware'): ?>
                                        Descripción de la falla del equipo <span class="text-red-500">*</span>
                                    <?php else: ?>
                                        Descripción de la falla del programa <span class="text-red-500">*</span>
                                    <?php endif; ?>
                                </label>
                                <textarea name="descripcion_problema"
                                          id="descripcion_problema"
                                          rows="5"
                                          <?php if($tipo !== 'mantenimiento'): ?> required <?php endif; ?>
                                          class="block w-full rounded-3xl border border-blue-100 bg-white/70 px-4 py-3 text-sm text-slate-700 shadow-sm transition focus:border-blue-300 focus:ring-2 focus:ring-blue-200"
                                          placeholder="<?php echo e($tipo === 'mantenimiento' ? 'Describe qué tipo de mantenimiento necesitas, cuándo y cualquier detalle importante...' : ($tipo === 'hardware' ? 'Describe la falla del equipo con el mayor detalle posible. ¿Qué estaba ocurriendo cuando falló? ¿Se muestran luces o mensajes en el dispositivo?' : 'Describe la falla del programa con el mayor detalle posible. ¿Qué estabas haciendo cuando ocurrió? ¿Qué mensajes de error aparecen?')); ?>"><?php echo e(old('descripcion_problema')); ?></textarea>
                                <?php $__errorArgs = ['descripcion_problema'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </section>

                        <?php if(in_array($tipo, ['software', 'hardware'])): ?>
                            <section class="rounded-3xl border border-blue-100/60 bg-white/90 px-6 py-6 shadow-lg shadow-blue-500/10 backdrop-blur">
                                <h2 class="mb-3 text-lg font-semibold text-slate-900">Imágenes del problema <span class="text-slate-400 text-sm font-normal">(opcional)</span></h2>
                                <p class="text-sm text-slate-500 mb-4">Sube capturas o fotos que ayuden a entender el problema. Máximo 5 imágenes.</p>

                                <input type="file" id="imageInput" name="imagenes[]" multiple accept="image/*" class="hidden">

                                <div class="flex flex-wrap items-center gap-3">
                                    <button type="button"
                                            id="uploadButton"
                                            class="inline-flex items-center rounded-2xl border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50">
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Seleccionar imágenes
                                    </button>
                                    <span id="imageCount" class="text-sm font-medium text-slate-500">0/5 imágenes</span>
                                </div>

                                <div id="imagePreview" class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4"></div>

                                <?php $__errorArgs = ['imagenes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php $__errorArgs = ['imagenes.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </section>
                        <?php endif; ?>

                        <?php if($tipo === 'mantenimiento'): ?>
                            <section class="space-y-6 rounded-3xl border border-green-200/60 bg-white/90 px-6 py-6 shadow-lg shadow-green-500/10 backdrop-blur">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">Agenda tu mantenimiento</h2>
                                    <p class="mt-1 text-sm text-slate-500">Selecciona el día y horario disponible. Verde: disponible, amarillo: horarios reservados, rojo: sin disponibilidad.</p>
                                </div>

                                <input type="hidden" name="maintenance_slot_id" id="maintenance_slot_id" value="<?php echo e(old('maintenance_slot_id')); ?>">
                                <input type="hidden" name="maintenance_selected_date" id="maintenance_selected_date" value="<?php echo e(old('maintenance_selected_date')); ?>">

                                <div id="maintenanceScheduling"
                                     data-availability-url="<?php echo e(route('maintenance.availability')); ?>"
                                     data-slots-url="<?php echo e(route('maintenance.slots')); ?>"
                                     class="rounded-3xl border border-green-100 bg-gradient-to-br from-green-50/60 via-white to-green-50/40 px-4 py-4 shadow-inner">
                                    <div class="mb-4 flex items-center justify-between">
                                        <button type="button" id="calendarPrev" class="flex items-center text-sm font-semibold text-blue-600 transition hover:text-blue-800">
                                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                            </svg>
                                            Mes anterior
                                        </button>
                                        <div id="calendarMonthLabel" class="text-lg font-semibold text-slate-900"></div>
                                        <button type="button" id="calendarNext" class="flex items-center text-sm font-semibold text-blue-600 transition hover:text-blue-800">
                                            Mes siguiente
                                            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="mb-2 grid grid-cols-7 gap-2 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                        <span>Dom</span>
                                        <span>Lun</span>
                                        <span>Mar</span>
                                        <span>Mié</span>
                                        <span>Jue</span>
                                        <span>Vie</span>
                                        <span>Sáb</span>
                                    </div>

                                    <div id="calendarGrid" class="grid grid-cols-7 gap-2"></div>

                                    <div class="mt-4 flex flex-wrap gap-4 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-green-500"></span>Disponible</span>
                                        <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-yellow-400"></span>Reservado</span>
                                        <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-red-500"></span>Sin disponibilidad</span>
                                    </div>
                                </div>

                                <?php $__errorArgs = ['maintenance_slot_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                                <div id="timeSlotsWrapper" class="hidden rounded-3xl border border-blue-100/60 bg-white/90 px-5 py-5 shadow-inner">
                                    <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                        <h3 class="text-sm font-semibold text-slate-800">Horarios disponibles para <span id="selectedDateLabel" class="text-blue-600"></span></h3>
                                        <span id="selectedSlotLabel" class="text-xs text-slate-500"></span>
                                    </div>
                                    <div id="timeSlotsList" class="grid gap-3 sm:grid-cols-2"></div>
                                    <p id="noSlotsMessage" class="hidden text-sm text-red-600">No hay horarios disponibles para la fecha seleccionada.</p>
                                </div>
                            </section>
                        <?php endif; ?>

                        <!-- Botones -->
                        <div class="flex flex-col gap-4 border-t border-blue-100/50 pt-6 sm:flex-row sm:items-center sm:justify-between">
                            <a href="<?php echo e(route('welcome')); ?>"
                               class="inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-white px-6 py-3 text-sm font-semibold text-blue-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Cancelar
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:from-blue-700 hover:to-blue-800">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Crear ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-blue-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-sm text-slate-500">&copy; <?php echo e(date('Y')); ?> Sistema de Tickets TI. Todos los derechos reservados.</p>
        </div>
    </footer>

    <?php $__env->startPush('scripts'); ?>
        <?php echo app('Illuminate\Foundation\Vite')('resources/js/Sistemas_IT/tickets-create.js'); ?>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views\Sistemas_IT/tickets/create.blade.php ENDPATH**/ ?>