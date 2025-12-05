<?php $__env->startSection('title', 'Editar Usuario - ' . $user->name); ?>

<?php $__env->startSection('content'); ?>
<main class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8" data-admin-user-edit>
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="<?php echo e(route('admin.users.show', $user)); ?>" 
               class="text-blue-600 hover:text-blue-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">✏️ Editar Usuario</h1>
                <p class="text-gray-600 mt-1">Puedes modificar nombre, correo, contraseña, rol y área.</p>
            </div>
        </div>
    </div>

    <!-- Información del Usuario (Solo Lectura) -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                <span class="text-lg font-bold text-white">
                    <?php echo e(strtoupper(substr($user->name, 0, 2))); ?>

                </span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900"><?php echo e($user->name); ?></h2>
                <p class="text-gray-600">
                    ID: #<?php echo e($user->id); ?> | 
                    Rol: <?php echo e($user->role === 'admin' ? '👑 Administrador' : '👤 Usuario'); ?> |
                    Estado: <?php echo e($user->status === 'approved' ? '✅ Aprobado' : ($user->status === 'pending' ? '⏳ Pendiente' : '❌ Rechazado')); ?>

                </p>
                <p class="text-sm text-gray-500 mt-1">
                    🔒 <strong>Nombre y rol protegidos</strong> - Se mantienen para preservar la integridad del historial de tickets y préstamos
                </p>
            </div>
        </div>
    </div>

    <!-- Formulario de Edición -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200">
        <div class="p-8">
            <form method="POST" action="<?php echo e(route('admin.users.update', $user)); ?>" class="space-y-6">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        👤 Nombre Completo *
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="<?php echo e(old('name', $user->name)); ?>" 
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <p class="mt-1 text-sm text-gray-500">
                        Nombre completo del usuario como aparecerá en el sistema
                    </p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        📧 Correo Electrónico *
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo e(old('email', $user->email)); ?>" 
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           placeholder="correo@ejemplo.com">
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <p class="mt-1 text-sm text-gray-500">
                        Se permite cualquier dirección de correo electrónico válida
                    </p>
                </div>

                <!-- Área + Subdepartamento -->
                <div class="space-y-4">
                    <div>
                        <label for="area" class="block text-sm font-medium text-gray-700 mb-2">
                            🗂️ Área *
                        </label>
                        <?php ($areas = ['Legal','Logistica','RH','Comercio Exterior','Sistemas','Socio']); ?>
                        <select id="area" name="area" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?php $__errorArgs = ['area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="">Selecciona un área</option>
                            <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($a); ?>" <?php echo e(old('area', optional($user->empleado)->area) === $a ? 'selected' : ''); ?>><?php echo e($a); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <p class="mt-1 text-sm text-gray-500">Área organizacional asociada al empleado</p>
                    </div>
                    <div id="subdepartamentoWrapper" class="<?php echo e(old('area', optional($user->empleado)->area) === 'Comercio Exterior' ? '' : 'hidden'); ?>">
                        <label for="subdepartamento_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Subdepartamento (Comercio Exterior) <span class="text-red-500">*</span>
                        </label>
                        <select id="subdepartamento_id" name="subdepartamento_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?php $__errorArgs = ['subdepartamento_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="">Selecciona un subdepartamento</option>
                            <?php $__currentLoopData = $subdepartamentosCE; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($sd->id); ?>" <?php echo e((string)old('subdepartamento_id', optional($user->empleado)->subdepartamento_id) === (string)$sd->id ? 'selected' : ''); ?>><?php echo e($sd->nombre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['subdepartamento_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <p class="mt-1 text-sm text-gray-500">Selecciona subdepartamento si el área es Comercio Exterior</p>
                    </div>
                </div>

                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        🔐 Nueva Contraseña
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <p class="mt-1 text-sm text-gray-500">
                        Dejar en blanco si no deseas cambiar la contraseña. Mínimo 8 caracteres.
                    </p>
                </div>

                <!-- Confirmar Contraseña -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        🔐 Confirmar Nueva Contraseña
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="mt-1 text-sm text-gray-500">
                        Repite la nueva contraseña para confirmarla
                    </p>
                </div>

                <!-- Rol -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">🎚️ Rol *</label>
                    <select id="role" name="role" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php ($roles = ['user' => 'Usuario','colaborador' => 'Colaborador','invitado' => 'Invitado','admin' => 'Administrador']); ?>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php echo e(old('role', $user->role) === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <p class="mt-1 text-sm text-gray-500">Invitado: acceso mínimo · Colaborador: funciones extendidas · Administrador: gestión avanzada.</p>
                </div>

                <!-- Botones -->
                <div class="flex flex-col sm:flex-row gap-3 pt-6">
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        💾 Guardar Cambios
                    </button>
                    <a href="<?php echo e(route('admin.users.show', $user)); ?>" 
                       class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        ❌ Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Información Adicional -->
    <div class="mt-8 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">ℹ️ Información del Sistema</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-600">🆔 ID de Usuario:</span>
                <span class="font-medium text-gray-900 ml-2">#<?php echo e($user->id); ?></span>
            </div>
            <div>
                <span class="text-gray-600">📅 Fecha de Registro:</span>
                <span class="font-medium text-gray-900 ml-2"><?php echo e($user->created_at->format('d/m/Y H:i')); ?></span>
            </div>
            <div>
                <span class="text-gray-600">🎫 Tickets Creados:</span>
                <span class="font-medium text-gray-900 ml-2"><?php echo e($user->tickets()->count()); ?></span>
            </div>
            <div>
                <span class="text-gray-600">📦 Préstamos Realizados:</span>
                <span class="font-medium text-gray-900 ml-2">—</span>
            </div>
        </div>
        <p class="mt-4 text-xs text-gray-500">
            Todos los datos del historial permanecen intactos y vinculados al ID único del usuario.
        </p>
    </div>
</main>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const areaSelect = document.getElementById('area');
            const wrapper = document.getElementById('subdepartamentoWrapper');
            function toggleSub() {
                if (!areaSelect || !wrapper) return;
                if (areaSelect.value === 'Comercio Exterior') {
                    wrapper.classList.remove('hidden');
                } else {
                    wrapper.classList.add('hidden');
                    const subSelect = document.getElementById('subdepartamento_id');
                    if (subSelect) subSelect.value = '';
                }
            }
            toggleSub();
            areaSelect.addEventListener('change', toggleSub);
        });
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SISTEMAS\Downloads\ERP EstrategiaeInnovacion\Sistema_Tickets_E-I\resources\views\Sistemas_IT/admin/users/edit.blade.php ENDPATH**/ ?>