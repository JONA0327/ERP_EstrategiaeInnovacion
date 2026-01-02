<?php $__env->startSection('title', 'Configuración de Mantenimientos - Panel Administrativo'); ?>

<?php $__env->startSection('content'); ?>
    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Horarios de mantenimiento</h2>
                <p class="text-gray-600">Administra la agenda de mantenimientos y la documentación técnica de los equipos.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                <a href="<?php echo e(route('admin.maintenance.computers.index')); ?>"
                    class="inline-flex items-center px-4 py-2 rounded-lg border border-green-300 bg-green-50 text-green-700 hover:bg-green-100 transition-colors">
                    Expedientes de equipos
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <form method="POST" action="<?php echo e(route('admin.maintenance.slots.destroy-past')); ?>" class="inline-flex"
                    onsubmit="return confirm('¿Eliminar todos los horarios pasados? Esta acción cancelará las reservaciones asociadas.');">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 text-sm font-medium transition-colors">
                        Eliminar horarios pasados
                    </button>
                </form>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-8">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm text-green-800 font-medium"><?php echo e(session('success')); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-blue-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-slate-50 border-b border-blue-100 flex flex-wrap items-center gap-2 px-4 sm:px-6 py-3">
                <button type="button" data-tab-target="tab-profiles"
                    class="tab-trigger inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-blue-700 bg-white shadow-sm">
                    <span class="hidden sm:inline">Ficha técnica</span>
                    <span class="sm:hidden">Ficha</span>
                </button>
                <button type="button" data-tab-target="tab-bulk"
                    class="tab-trigger inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:text-blue-700 hover:bg-white/80">
                    Horarios en lote
                </button>
                <button type="button" data-tab-target="tab-individual"
                    class="tab-trigger inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:text-blue-700 hover:bg-white/80">
                    Horario individual
                </button>
                <button type="button" data-tab-target="tab-agenda"
                    class="tab-trigger inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:text-blue-700 hover:bg-white/80">
                    Agenda programada
                </button>
            </div>

            <div class="p-6 sm:p-8 space-y-10">
                <section id="tab-profiles" data-tab-panel class="space-y-8">
                    <div class="space-y-2">
                        <h3 class="text-xl font-semibold text-slate-900">Registrar ficha técnica de equipo</h3>
                        <p class="text-sm text-slate-500 max-w-2xl">Documenta la configuración técnica, los componentes reemplazados y el estado de préstamo para tener un seguimiento completo desde el expediente de mantenimientos.</p>
                    </div>

                    <form method="POST" action="<?php echo e(route('admin.maintenance.computers.store')); ?>" class="space-y-8">
                        <?php echo csrf_field(); ?>

                        <div class="grid grid-cols-1 lg:grid-cols-[2fr,3fr] gap-8">
                            <div class="space-y-6">
                                <div class="space-y-4">
                                    <div>
                                        <label for="identifier" class="block text-sm font-medium text-gray-700 mb-1">Identificador del equipo <span class="text-red-500">*</span></label>
                                        <input type="text" id="identifier" name="identifier" value="<?php echo e(old('identifier')); ?>"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            required>
                                        <?php $__errorArgs = ['identifier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="maintenance_ticket_id" class="block text-sm font-medium text-gray-700 mb-1">Ticket de mantenimiento relacionado</label>
                                            <select id="maintenance_ticket_id" name="maintenance_ticket_id"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">Selecciona un ticket</option>
                                                <?php $__currentLoopData = $maintenanceTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $createdAt = optional($ticket->created_at)->timezone('America/Mexico_City');
                                                    ?>
                                                    <option value="<?php echo e($ticket->id); ?>" <?php echo e((string) old('maintenance_ticket_id') === (string) $ticket->id ? 'selected' : ''); ?>>
                                                        <?php echo e($ticket->folio); ?> · <?php echo e($ticket->nombre_solicitante); ?> · <?php echo e($createdAt ? $createdAt->format('d/m/Y H:i') : 'Sin fecha'); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <p class="text-xs text-gray-500 mt-1">El ticket seleccionado se vinculará como el último mantenimiento realizado.</p>
                                            <?php $__errorArgs = ['maintenance_ticket_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div>
                                            <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                                            <input type="text" id="brand" name="brand" value="<?php echo e(old('brand')); ?>"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <?php $__errorArgs = ['brand'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div>
                                            <label for="model" class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                                            <input type="text" id="model" name="model" value="<?php echo e(old('model')); ?>"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <?php $__errorArgs = ['model'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label for="disk_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de disco</label>
                                        <input type="text" id="disk_type" name="disk_type" value="<?php echo e(old('disk_type')); ?>"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <?php $__errorArgs = ['disk_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div>
                                        <label for="ram_capacity" class="block text-sm font-medium text-gray-700 mb-1">Capacidad de RAM</label>
                                        <input type="text" id="ram_capacity" name="ram_capacity" value="<?php echo e(old('ram_capacity')); ?>"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <?php $__errorArgs = ['ram_capacity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div>
                                        <label for="battery_status" class="block text-sm font-medium text-gray-700 mb-1">Estado de batería</label>
                                        <select id="battery_status" name="battery_status"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Selecciona una opción</option>
                                            <option value="functional" <?php echo e(old('battery_status') === 'functional' ? 'selected' : ''); ?>>Funcional</option>
                                            <option value="partially_functional" <?php echo e(old('battery_status') === 'partially_functional' ? 'selected' : ''); ?>>Parcialmente funcional</option>
                                            <option value="damaged" <?php echo e(old('battery_status') === 'damaged' ? 'selected' : ''); ?>>Dañada</option>
                                        </select>
                                        <?php $__errorArgs = ['battery_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div>
                                        <label for="last_maintenance_at" class="block text-sm font-medium text-gray-700 mb-1">Último mantenimiento registrado</label>
                                        <input type="datetime-local" id="last_maintenance_at" name="last_maintenance_at"
                                            value="<?php echo e(old('last_maintenance_at')); ?>"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <?php $__errorArgs = ['last_maintenance_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div>
                                        <label for="aesthetic_observations" class="block text-sm font-medium text-gray-700 mb-1">Observaciones estéticas</label>
                                        <textarea id="aesthetic_observations" name="aesthetic_observations" rows="3"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?php echo e(old('aesthetic_observations')); ?></textarea>
                                        <?php $__errorArgs = ['aesthetic_observations'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Componentes reemplazados</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                        <?php $__currentLoopData = $componentOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <label class="inline-flex items-center text-xs text-gray-600 bg-slate-50 border border-gray-200 rounded-lg px-3 py-2">
                                                <input type="checkbox" name="replacement_components[]" value="<?php echo e($value); ?>"
                                                    class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                    <?php echo e(is_array(old('replacement_components')) && in_array($value, old('replacement_components', []), true) ? 'checked' : ''); ?>>
                                                <?php echo e($label); ?>

                                            </label>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <?php $__errorArgs = ['replacement_components'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <?php $__errorArgs = ['replacement_components.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="border border-blue-100 rounded-xl bg-blue-50/50 p-5 space-y-4">
                                    <label class="flex items-start text-sm text-gray-700">
                                        <input type="checkbox" name="is_loaned" value="1" id="is_loaned"
                                            class="mt-1 mr-2 rounded border-gray-300 text-green-600 focus:ring-green-500"
                                            <?php echo e(old('is_loaned') ? 'checked' : ''); ?>>
                                        <span>Marcar equipo como prestado actualmente<br><span class="text-xs text-gray-500">Selecciona a la persona responsable desde el directorio de usuarios.</span></span>
                                    </label>

                                    <div id="loanDetails" class="grid grid-cols-1 md:grid-cols-2 gap-4 <?php echo e(old('is_loaned') ? '' : 'hidden'); ?>">
                                        <div>
                                            <label for="loaned_to_name" class="block text-xs font-medium text-gray-600 mb-1">Nombre de la persona</label>
                                            <input list="loanedNameOptions" type="text" id="loaned_to_name" name="loaned_to_name"
                                                value="<?php echo e(old('loaned_to_name')); ?>"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <datalist id="loanedNameOptions">
                                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($user->name); ?>"></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </datalist>
                                            <?php $__errorArgs = ['loaned_to_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div>
                                            <label for="loaned_to_email" class="block text-xs font-medium text-gray-600 mb-1">Correo electrónico</label>
                                            <input list="loanedEmailOptions" type="email" id="loaned_to_email" name="loaned_to_email"
                                                value="<?php echo e(old('loaned_to_email')); ?>"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <datalist id="loanedEmailOptions">
                                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($user->email); ?>"></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </datalist>
                                            <?php $__errorArgs = ['loaned_to_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Registrar ficha técnica
                            </button>
                        </div>
                    </form>

                    <div class="border border-blue-100 rounded-2xl bg-white shadow-sm">
                        <div class="px-5 py-4 border-b border-blue-100 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-slate-900">Seguimiento administrativo de tickets</h4>
                                <p class="text-sm text-slate-500">Actualiza observaciones, reportes y evidencias directamente desde la ficha técnica.</p>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-500 bg-blue-50 border border-blue-100 rounded-lg px-3 py-1.5">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Selecciona un ticket para mostrar el formulario.
                            </div>
                        </div>

                        <div class="p-6 space-y-6">
                            <?php
                                $activeTicketId = old('target_ticket_id', session('active_ticket_form'));
                            ?>

                            <?php if($maintenanceTickets->isEmpty()): ?>
                                <p class="text-sm text-slate-500">Aún no hay tickets de mantenimiento registrados para administrar desde esta vista.</p>
                            <?php else: ?>
                                <div class="space-y-2">
                                    <label for="maintenanceTicketSelector" class="block text-sm font-medium text-slate-700">Ticket de mantenimiento</label>
                                    <select id="maintenanceTicketSelector" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" data-default="<?php echo e($activeTicketId ? 'ticket-' . $activeTicketId : ''); ?>">
                                        <option value="">Selecciona un ticket para gestionarlo</option>
                                        <?php $__currentLoopData = $maintenanceTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $createdAt = optional($ticket->created_at)->timezone('America/Mexico_City');
                                                $closedAt = optional($ticket->fecha_cierre)->timezone('America/Mexico_City');
                                                $label = $ticket->folio . ' · ' . $ticket->nombre_solicitante;
                                            ?>
                                            <option value="ticket-<?php echo e($ticket->id); ?>" <?php echo e((string) $activeTicketId === (string) $ticket->id ? 'selected' : ''); ?>>
                                                <?php echo e($label); ?> (<?php echo e($createdAt ? $createdAt->format('d/m/Y H:i') : 'Sin fecha'); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="space-y-6" id="maintenanceTicketForms">
                                    <?php $__currentLoopData = $maintenanceTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $isActiveTicket = (string) $activeTicketId === (string) $ticket->id;
                                            $createdAt = optional($ticket->created_at)->timezone('America/Mexico_City');
                                            $closedAt = optional($ticket->fecha_cierre)->timezone('America/Mexico_City');
                                            $scheduledAt = optional($ticket->maintenance_scheduled_at)->timezone('America/Mexico_City');
                                            $observacionesValue = $isActiveTicket ? old('observaciones', $ticket->observaciones) : $ticket->observaciones;
                                            $maintenanceReportValue = $isActiveTicket ? old('maintenance_report', $ticket->maintenance_report) : $ticket->maintenance_report;
                                            $closureObservationsValue = $isActiveTicket ? old('closure_observations', $ticket->closure_observations) : $ticket->closure_observations;
                                            $removedImages = $isActiveTicket ? (array) old('removed_admin_images', []) : [];
                                        ?>
                                        <div class="border border-slate-200 rounded-xl bg-slate-50/60 p-5 space-y-4 hidden" data-ticket-panel="ticket-<?php echo e($ticket->id); ?>">
                                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                                <div>
                                                    <p class="text-xs font-semibold tracking-[0.3em] uppercase text-slate-500">Ticket de mantenimiento</p>
                                                    <h5 class="text-lg font-semibold text-slate-900"><?php echo e($ticket->folio); ?></h5>
                                                    <p class="text-xs text-slate-500 mt-1">
                                                        Creado <?php echo e($createdAt ? $createdAt->format('d/m/Y H:i') : 'sin fecha'); ?> ·
                                                        Estado <?php echo e(ucfirst(str_replace('_', ' ', $ticket->estado))); ?>

                                                        <?php if($closedAt): ?>
                                                            · Cerrado <?php echo e($closedAt->format('d/m/Y H:i')); ?>

                                                        <?php endif; ?>
                                                        <?php if($scheduledAt): ?>
                                                            · Programado <?php echo e($scheduledAt->format('d/m/Y H:i')); ?>

                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                <a href="<?php echo e(route('admin.tickets.show', $ticket)); ?>" class="inline-flex items-center px-3 py-2 text-xs font-semibold text-blue-600 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition">
                                                    Ver ticket completo
                                                    <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </a>
                                            </div>

                                            <form method="POST" action="<?php echo e(route('admin.tickets.update', $ticket)); ?>" class="space-y-5" enctype="multipart/form-data">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>

                                                <input type="hidden" name="estado" value="<?php echo e($isActiveTicket ? old('estado', $ticket->estado) : $ticket->estado); ?>">
                                                <input type="hidden" name="target_ticket_id" value="<?php echo e($ticket->id); ?>">
                                                <input type="hidden" name="redirect_to" value="<?php echo e(request()->fullUrl()); ?>">

                                                <div class="space-y-2">
                                                    <label for="adminObservations<?php echo e($ticket->id); ?>" class="block text-xs font-medium text-slate-700">Observaciones del administrador</label>
                                                    <textarea id="adminObservations<?php echo e($ticket->id); ?>" name="observaciones" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?php echo e($observacionesValue); ?></textarea>
                                                    <?php if($isActiveTicket): ?>
                                                        <?php $__errorArgs = ['observaciones'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="space-y-3">
                                                    <div class="space-y-2">
                                                        <label for="adminImages<?php echo e($ticket->id); ?>" class="block text-xs font-medium text-slate-700">Imágenes del administrador</label>
                                                        <input type="file" id="adminImages<?php echo e($ticket->id); ?>" name="imagenes_admin[]" multiple accept="image/*" class="block w-full text-sm border border-slate-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:bg-blue-100 file:text-blue-800 hover:file:bg-blue-200" data-maintenance-upload>
                                                        <p class="text-xs text-slate-500" data-upload-status>0 archivos seleccionados.</p>
                                                        <?php if($isActiveTicket): ?>
                                                            <?php $__errorArgs = ['imagenes_admin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                            <?php $__errorArgs = ['imagenes_admin.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if($ticket->imagenes_admin && count($ticket->imagenes_admin) > 0): ?>
                                                        <div class="bg-white border border-slate-200 rounded-lg p-3 space-y-2">
                                                            <p class="text-xs font-semibold text-slate-700">Imágenes existentes</p>
                                                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                                                <?php $__currentLoopData = $ticket->imagenes_admin; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $imagen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <label class="group relative cursor-pointer border border-slate-200 rounded-lg overflow-hidden">
                                                                        <img src="data:image/jpeg;base64,<?php echo e($imagen); ?>" alt="Imagen administrador <?php echo e($index + 1); ?>" class="h-24 w-full object-cover">
                                                                        <span class="absolute bottom-1 left-1 bg-slate-900/80 text-white text-[10px] font-medium px-2 py-0.5 rounded">IMG <?php echo e($index + 1); ?></span>
                                                                        <input type="checkbox" name="removed_admin_images[]" value="<?php echo e($index); ?>" class="absolute top-2 right-2 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" <?php echo e(in_array($index, $removedImages, true) ? 'checked' : ''); ?>>
                                                                    </label>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </div>
                                                            <p class="text-[11px] text-slate-500">Marca las imágenes que deseas eliminar antes de guardar.</p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="space-y-2">
                                                        <label for="maintenanceReport<?php echo e($ticket->id); ?>" class="block text-xs font-medium text-slate-700">Reporte técnico</label>
                                                        <textarea id="maintenanceReport<?php echo e($ticket->id); ?>" name="maintenance_report" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?php echo e($maintenanceReportValue); ?></textarea>
                                                        <?php if($isActiveTicket): ?>
                                                            <?php $__errorArgs = ['maintenance_report'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label for="closureObservations<?php echo e($ticket->id); ?>" class="block text-xs font-medium text-slate-700">Observaciones al cerrar</label>
                                                        <textarea id="closureObservations<?php echo e($ticket->id); ?>" name="closure_observations" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?php echo e($closureObservationsValue); ?></textarea>
                                                        <?php if($isActiveTicket): ?>
                                                            <?php $__errorArgs = ['closure_observations'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="pt-3 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                                    <p class="text-[11px] text-slate-500">Los cambios se guardarán directamente en el ticket seleccionado.</p>
                                                    <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Guardar seguimiento
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <section id="tab-bulk" data-tab-panel class="space-y-8 hidden">
                    <div class="space-y-2">
                        <h3 class="text-xl font-semibold text-slate-900">Agregar horarios en lote</h3>
                        <p class="text-sm text-slate-500 max-w-2xl">Crea múltiples horarios seleccionando un rango de fechas, los días de la semana y la capacidad disponible. El sistema calcula automáticamente los bloques de tiempo.</p>
                    </div>

                    <form method="POST" action="<?php echo e(route('admin.maintenance.slots.store-bulk')); ?>" id="bulkScheduleForm" class="space-y-8">
                        <?php echo csrf_field(); ?>
                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div class="space-y-3">
                                    <h4 class="font-medium text-gray-800">Rango de fechas</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio <span class="text-red-500">*</span></label>
                                            <input type="date" id="start_date" name="start_date" value="<?php echo e(old('start_date')); ?>"
                                                min="<?php echo e(date('Y-m-d')); ?>"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                required>
                                            <?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div>
                                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha fin <span class="text-red-500">*</span></label>
                                            <input type="date" id="end_date" name="end_date" value="<?php echo e(old('end_date')); ?>"
                                                min="<?php echo e(date('Y-m-d')); ?>"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                required>
                                            <?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <h4 class="font-medium text-gray-800">Días de aplicación</h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        <?php
                                            $daysSelected = old('days', []);
                                            $daysOptions = [
                                                'monday' => 'Lunes',
                                                'tuesday' => 'Martes',
                                                'wednesday' => 'Miércoles',
                                                'thursday' => 'Jueves',
                                                'friday' => 'Viernes',
                                                'saturday' => 'Sábado',
                                                'sunday' => 'Domingo',
                                            ];
                                        ?>
                                        <?php $__currentLoopData = $daysOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <label class="flex items-center bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700">
                                                <input type="checkbox" name="days[]" value="<?php echo e($value); ?>"
                                                    class="rounded border-gray-300 text-blue-600 mr-2"
                                                    <?php echo e(in_array($value, $daysSelected, true) ? 'checked' : ''); ?>>
                                                <?php echo e($label); ?>

                                            </label>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <?php $__errorArgs = ['days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="bulk_start_time" class="block text-sm font-medium text-gray-700 mb-1">Hora inicio <span class="text-red-500">*</span></label>
                                        <input type="time" id="bulk_start_time" name="bulk_start_time"
                                            value="<?php echo e(old('bulk_start_time', '09:00')); ?>"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required>
                                        <?php $__errorArgs = ['bulk_start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div>
                                        <label for="bulk_end_time" class="block text-sm font-medium text-gray-700 mb-1">Hora fin <span class="text-red-500">*</span></label>
                                        <input type="time" id="bulk_end_time" name="bulk_end_time"
                                            value="<?php echo e(old('bulk_end_time', '13:00')); ?>"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required>
                                        <?php $__errorArgs = ['bulk_end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <div>
                                    <label for="total_capacity" class="block text-sm font-medium text-gray-700 mb-1">Capacidad total <span class="text-red-500">*</span></label>
                                    <input type="number" min="1" max="20" id="total_capacity" name="total_capacity"
                                        value="<?php echo e(old('total_capacity', 4)); ?>"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        onchange="calculateSlots()" required>
                                    <p class="text-xs text-gray-500 mt-1">Número de horarios en los que se dividirá el rango seleccionado.</p>
                                    <?php $__errorArgs = ['total_capacity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div id="slotsPreview" class="bg-slate-50 border border-blue-100 rounded-xl p-4 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <h5 class="text-sm font-semibold text-gray-700">Vista previa de horarios</h5>
                                        <span class="text-xs text-gray-500" id="previewSlotCount">0</span>
                                    </div>
                                    <div id="slotsContainer" class="space-y-1 text-xs text-gray-600">
                                        <p class="text-gray-500">Completa los campos para ver la vista previa.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="text-sm text-gray-600 bg-slate-50 border border-gray-200 rounded-lg px-4 py-3 space-y-1 sm:space-y-0 sm:flex sm:flex-wrap sm:items-center sm:gap-2">
                                <span><span id="totalDays" class="font-semibold text-blue-600">0</span> días seleccionados</span>
                                <span class="hidden sm:inline">·</span>
                                <span><span id="totalSlots" class="font-semibold text-blue-600">0</span> horarios por día</span>
                                <span class="hidden sm:inline">·</span>
                                <span><span id="totalSchedules" class="font-semibold text-blue-600">0</span> horarios totales</span>
                            </div>
                            <button type="submit"
                                class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Crear horarios en lote
                            </button>
                        </div>
                    </form>
                </section>

                <section id="tab-individual" data-tab-panel class="space-y-8 hidden">
                    <div class="space-y-2">
                        <h3 class="text-xl font-semibold text-slate-900">Agregar horario individual</h3>
                        <p class="text-sm text-slate-500 max-w-2xl">Utiliza este formulario cuando necesites un ajuste puntual en la agenda sin afectar otras fechas.</p>
                    </div>

                    <form method="POST" action="<?php echo e(route('admin.maintenance.slots.store')); ?>" class="space-y-6">
                        <?php echo csrf_field(); ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Fecha <span class="text-red-500">*</span></label>
                                <input type="date" id="date" name="date" value="<?php echo e(old('date')); ?>"
                                    min="<?php echo e(date('Y-m-d')); ?>"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <?php $__errorArgs = ['date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Hora inicio <span class="text-red-500">*</span></label>
                                <input type="time" id="start_time" name="start_time" value="<?php echo e(old('start_time', '09:00')); ?>"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <?php $__errorArgs = ['start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">Hora fin <span class="text-red-500">*</span></label>
                                <input type="time" id="end_time" name="end_time" value="<?php echo e(old('end_time', '10:00')); ?>"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <?php $__errorArgs = ['end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">Capacidad <span class="text-red-500">*</span></label>
                                <input type="number" id="capacity" name="capacity" min="1" max="10" value="<?php echo e(old('capacity', 2)); ?>"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <?php $__errorArgs = ['capacity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                                Guardar horario
                            </button>
                        </div>
                    </form>
                </section>

                <section id="tab-agenda" data-tab-panel class="space-y-8 hidden">
                    <div class="space-y-2">
                        <h3 class="text-xl font-semibold text-slate-900">Agenda programada</h3>
                        <p class="text-sm text-slate-500 max-w-2xl">Consulta los horarios configurados, ajusta su capacidad y desactiva los que no estén disponibles temporalmente.</p>
                    </div>

                    <?php
                        $allSlots = $groupedSlots->flatten(1);
                        $totalConfigured = $allSlots->count();
                        $totalCapacity = $allSlots->sum('capacity');
                        $totalBooked = $allSlots->sum('booked_count');
                        $activeSlots = $allSlots->filter(fn($slot) => $slot->is_active)->count();
                    ?>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                        <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-4">
                            <p class="text-xs uppercase font-semibold text-blue-600 tracking-wide">Horarios configurados</p>
                            <p class="text-2xl font-bold text-slate-900 mt-2"><?php echo e($totalConfigured); ?></p>
                        </div>
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4">
                            <p class="text-xs uppercase font-semibold text-emerald-600 tracking-wide">Capacidad total</p>
                            <p class="text-2xl font-bold text-slate-900 mt-2"><?php echo e($totalCapacity); ?></p>
                        </div>
                        <div class="rounded-xl border border-amber-100 bg-amber-50/60 p-4">
                            <p class="text-xs uppercase font-semibold text-amber-600 tracking-wide">Reservas activas</p>
                            <p class="text-2xl font-bold text-slate-900 mt-2"><?php echo e($totalBooked); ?></p>
                        </div>
                        <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-4">
                            <p class="text-xs uppercase font-semibold text-indigo-600 tracking-wide">Horarios activos</p>
                            <p class="text-2xl font-bold text-slate-900 mt-2"><?php echo e($activeSlots); ?></p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <?php $__empty_1 = true; $__currentLoopData = $groupedSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $slots): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="border border-gray-200 rounded-2xl overflow-hidden">
                                <div class="px-6 py-4 bg-slate-50 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900"><?php echo e(\Carbon\Carbon::parse($date)->translatedFormat('d \d\e F, Y')); ?></h4>
                                        <p class="text-sm text-gray-500"><?php echo e($slots->count()); ?> horario(s) configurado(s)</p>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-gray-600">
                                        <span>Capacidad total: <span class="font-semibold text-blue-600"><?php echo e($slots->sum('capacity')); ?></span></span>
                                        <span>Reservados: <span class="font-semibold text-amber-600"><?php echo e($slots->sum('booked_count')); ?></span></span>
                                    </div>
                                </div>
                                <div class="divide-y divide-gray-100">
                                    <?php $__currentLoopData = $slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="px-6 py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4">
                                                <div class="px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg text-sm font-semibold text-blue-700">
                                                    <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('H:i')); ?> - <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('H:i')); ?>

                                                </div>
                                                <div class="text-sm text-gray-600 grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-1">
                                                    <span>Capacidad: <span class="font-semibold text-gray-900"><?php echo e($slot->capacity); ?></span></span>
                                                    <span>Reservados: <span class="font-semibold text-gray-900"><?php echo e($slot->booked_count); ?></span></span>
                                                    <span>Estado: <span class="font-semibold <?php echo e($slot->is_active ? 'text-green-600' : 'text-gray-500'); ?>"><?php echo e($slot->is_active ? 'Activo' : 'Inactivo'); ?></span></span>
                                                </div>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-3">
                                                <form method="POST" action="<?php echo e(route('admin.maintenance.slots.update', $slot)); ?>" class="flex items-center gap-2">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PUT'); ?>
                                                    <input type="number" min="1" max="20" name="capacity" value="<?php echo e($slot->capacity); ?>"
                                                        class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <label class="flex items-center text-xs text-gray-600">
                                                        <input type="checkbox" name="is_active" value="1"
                                                            class="mr-2 rounded border-gray-300 text-green-600 focus:ring-green-500" <?php echo e($slot->is_active ? 'checked' : ''); ?>>
                                                        Activo
                                                    </label>
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors">Actualizar</button>
                                                </form>
                                                <form method="POST" action="<?php echo e(route('admin.maintenance.slots.destroy', $slot)); ?>"
                                                    onsubmit="return confirm('¿Seguro que deseas eliminar este horario?');">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors">Eliminar</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="bg-slate-50 border border-gray-200 rounded-xl p-6 text-center text-gray-600">
                                <p>No hay horarios configurados. ¡Comienza agregando uno!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabButtons = document.querySelectorAll('.tab-trigger');
            const tabPanels = document.querySelectorAll('[data-tab-panel]');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetId = button.getAttribute('data-tab-target');

                    tabButtons.forEach(btn => {
                        btn.classList.remove('bg-white', 'shadow-sm', 'text-blue-700');
                        btn.classList.add('text-slate-600');
                    });

                    button.classList.add('bg-white', 'shadow-sm', 'text-blue-700');
                    button.classList.remove('text-slate-600');

                    tabPanels.forEach(panel => {
                        panel.classList.toggle('hidden', panel.id !== targetId);
                    });
                });
            });

            const isLoanedCheckbox = document.getElementById('is_loaned');
            const loanDetails = document.getElementById('loanDetails');
            const nameInput = document.getElementById('loaned_to_name');
            const emailInput = document.getElementById('loaned_to_email');
            const maintenanceUsers = <?php echo json_encode($users->map(function ($user) {
                return ['name' => $user->name, 'email' => $user->email];
            }), 512) ?>;

            function toggleLoanDetails() {
                if (!loanDetails) {
                    return;
                }

                if (isLoanedCheckbox && isLoanedCheckbox.checked) {
                    loanDetails.classList.remove('hidden');
                } else {
                    loanDetails.classList.add('hidden');
                }
            }

            function syncFromName() {
                if (!nameInput || !emailInput) {
                    return;
                }

                const value = nameInput.value.trim().toLowerCase();
                const user = maintenanceUsers.find(user => user.name.toLowerCase() === value);
                if (user) {
                    emailInput.value = user.email;
                }
            }

            function syncFromEmail() {
                if (!nameInput || !emailInput) {
                    return;
                }

                const value = emailInput.value.trim().toLowerCase();
                const user = maintenanceUsers.find(user => user.email.toLowerCase() === value);
                if (user) {
                    nameInput.value = user.name;
                }
            }

            if (isLoanedCheckbox) {
                isLoanedCheckbox.addEventListener('change', toggleLoanDetails);
                toggleLoanDetails();
            }

            if (nameInput) {
                nameInput.addEventListener('change', syncFromName);
                nameInput.addEventListener('blur', syncFromName);
            }

            if (emailInput) {
                emailInput.addEventListener('change', syncFromEmail);
                emailInput.addEventListener('blur', syncFromEmail);
            }

            calculateSlots();
            updateTotalDays();

            const startTimeInput = document.getElementById('bulk_start_time');
            const endTimeInput = document.getElementById('bulk_end_time');
            const capacityInput = document.getElementById('total_capacity');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            if (startTimeInput) {
                startTimeInput.addEventListener('change', calculateSlots);
            }
            if (endTimeInput) {
                endTimeInput.addEventListener('change', calculateSlots);
            }
            if (capacityInput) {
                capacityInput.addEventListener('change', calculateSlots);
            }
            if (startDateInput) {
                startDateInput.addEventListener('change', function () {
                    updateEndDateMin();
                    updateTotalDays();
                });
            }
            if (endDateInput) {
                endDateInput.addEventListener('change', updateTotalDays);
            }

            document.querySelectorAll('input[name="days[]"]').forEach(checkbox => {
                checkbox.addEventListener('change', updateTotalDays);
            });
        });

        function updateEndDateMin() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            if (!startDateInput || !endDateInput) {
                return;
            }

            const startDate = startDateInput.value;
            if (startDate) {
                endDateInput.min = startDate;
                if (endDateInput.value && endDateInput.value < startDate) {
                    endDateInput.value = '';
                }
            } else {
                endDateInput.min = '<?php echo e(date('Y-m-d')); ?>';
            }
        }

        function calculateSlots() {
            const startTimeInput = document.getElementById('bulk_start_time');
            const endTimeInput = document.getElementById('bulk_end_time');
            const capacityInput = document.getElementById('total_capacity');
            const container = document.getElementById('slotsContainer');
            const totalSlotsLabel = document.getElementById('totalSlots');
            const totalSchedulesLabel = document.getElementById('totalSchedules');
            const previewSlotCount = document.getElementById('previewSlotCount');

            if (!startTimeInput || !endTimeInput || !capacityInput || !container) {
                return;
            }

            const startTime = startTimeInput.value;
            const endTime = endTimeInput.value;
            const capacity = parseInt(capacityInput.value, 10);

            if (!startTime || !endTime || !capacity) {
                container.innerHTML = '<p class="text-gray-500">Completa los campos para ver la vista previa.</p>';
                if (totalSlotsLabel) totalSlotsLabel.textContent = '0';
                if (totalSchedulesLabel) totalSchedulesLabel.textContent = '0';
                if (previewSlotCount) previewSlotCount.textContent = '0';
                return;
            }

            const start = new Date(`1970-01-01T${startTime}:00`);
            const end = new Date(`1970-01-01T${endTime}:00`);
            const diffMinutes = Math.abs((end - start) / 60000);

            if (diffMinutes === 0 || capacity === 0) {
                container.innerHTML = '<p class="text-red-500 text-sm">Verifica la hora de inicio, fin y capacidad.</p>';
                if (totalSlotsLabel) totalSlotsLabel.textContent = '0';
                if (totalSchedulesLabel) totalSchedulesLabel.textContent = '0';
                if (previewSlotCount) previewSlotCount.textContent = '0';
                return;
            }

            const slotDuration = Math.floor(diffMinutes / capacity);
            if (slotDuration < 1) {
                container.innerHTML = '<p class="text-red-500 text-sm">La duración calculada por horario es menor a un minuto. Ajusta la capacidad o el rango de tiempo.</p>';
                if (totalSlotsLabel) totalSlotsLabel.textContent = '0';
                if (totalSchedulesLabel) totalSchedulesLabel.textContent = '0';
                if (previewSlotCount) previewSlotCount.textContent = '0';
                return;
            }

            let currentTime = new Date(start);
            const rows = [];

            for (let i = 0; i < capacity; i++) {
                const slotStart = new Date(currentTime);
                const slotEnd = new Date(currentTime.getTime() + slotDuration * 60000);
                if (slotEnd > end) {
                    break;
                }

                rows.push(`<div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg px-3 py-2">
                        <span class="font-medium text-gray-700">${slotStart.toTimeString().slice(0, 5)} - ${slotEnd.toTimeString().slice(0, 5)}</span>
                        <span class="text-xs text-gray-500">${slotDuration} minutos</span>
                    </div>`);
                currentTime = slotEnd;
            }

            container.innerHTML = rows.join('');
            if (totalSlotsLabel) totalSlotsLabel.textContent = rows.length.toString();
            if (previewSlotCount) previewSlotCount.textContent = rows.length.toString();
            updateTotalDays();
        }

        function updateTotalDays() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const totalDaysLabel = document.getElementById('totalDays');
            const totalSlotsLabel = document.getElementById('totalSlots');
            const totalSchedulesLabel = document.getElementById('totalSchedules');

            if (!startDateInput || !endDateInput) {
                return;
            }

            const startDateValue = startDateInput.value;
            const endDateValue = endDateInput.value;
            const selectedDays = Array.from(document.querySelectorAll('input[name="days[]"]:checked'));

            if (!startDateValue || !endDateValue || selectedDays.length === 0) {
                if (totalDaysLabel) {
                    totalDaysLabel.textContent = '0';
                }
                if (totalSchedulesLabel) {
                    totalSchedulesLabel.textContent = '0';
                }
                return;
            }

            const startDate = new Date(startDateValue);
            const endDate = new Date(endDateValue);
            let count = 0;

            for (let date = new Date(startDate); date <= endDate; date.setDate(date.getDate() + 1)) {
                const dayOfWeek = date.getDay();
                const matches = selectedDays.some(checkbox => {
                    const value = checkbox.value;
                    const map = {
                        sunday: 0,
                        monday: 1,
                        tuesday: 2,
                        wednesday: 3,
                        thursday: 4,
                        friday: 5,
                        saturday: 6,
                    };
                    return map[value] === dayOfWeek;
                });

                if (matches) {
                    count++;
                }
            }

            if (totalDaysLabel) totalDaysLabel.textContent = count.toString();

            const slotsContainer = document.getElementById('slotsContainer');
            const totalSlots = slotsContainer ? slotsContainer.children.length : 0;

            if (totalSchedulesLabel) {
                totalSchedulesLabel.textContent = (count * totalSlots).toString();
            }
            if (totalSlotsLabel) {
                totalSlotsLabel.textContent = totalSlots.toString();
        }

        const maintenanceSelector = document.getElementById('maintenanceTicketSelector');
        const maintenancePanels = document.querySelectorAll('[data-ticket-panel]');

        if (maintenanceSelector && maintenancePanels.length) {
            const showMaintenancePanel = (panelId) => {
                maintenancePanels.forEach((panel) => {
                    panel.classList.toggle('hidden', panel.dataset.ticketPanel !== panelId || !panelId);
                });
            };

            const applyDefaultPanel = () => {
                const defaultValue = maintenanceSelector.dataset.default;

                if (maintenanceSelector.value) {
                    showMaintenancePanel(maintenanceSelector.value);
                } else if (defaultValue) {
                    maintenanceSelector.value = defaultValue;
                    showMaintenancePanel(defaultValue);
                } else {
                    showMaintenancePanel('');
                }
            };

            maintenanceSelector.addEventListener('change', (event) => {
                showMaintenancePanel(event.target.value);
            });

            applyDefaultPanel();
        }

        document.querySelectorAll('[data-ticket-panel] input[type="file"][data-maintenance-upload]').forEach((input) => {
            const panel = input.closest('[data-ticket-panel]');
            const statusLabel = panel ? panel.querySelector('[data-upload-status]') : null;

            if (!statusLabel) {
                return;
            }

            input.addEventListener('change', () => {
                const count = input.files ? input.files.length : 0;
                statusLabel.textContent = count === 1
                    ? '1 archivo seleccionado.'
                    : `${count} archivos seleccionados.`;
            });
        });
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views\Sistemas_IT/admin/maintenance/index.blade.php ENDPATH**/ ?>