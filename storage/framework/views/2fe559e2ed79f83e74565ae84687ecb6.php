<?php $__env->startSection('title', 'Expediente Digital'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50 py-8" x-data="{ tab: 'general' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        
        <div class="mb-6">
            <a href="<?php echo e(route('rh.expedientes.index')); ?>" class="inline-flex items-center text-gray-500 hover:text-indigo-600 transition font-bold group">
                <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center mr-2 shadow-sm group-hover:border-indigo-300 group-hover:bg-indigo-50">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </div>
                Regresar al listado
            </a>
        </div>

        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6 flex flex-col md:flex-row items-center gap-6">
            <div class="relative">
                <div class="w-24 h-24 rounded-full bg-indigo-100 flex items-center justify-center text-3xl font-bold text-indigo-600 border-4 border-white shadow-md overflow-hidden">
                    <?php if($empleado->foto_path): ?>
                        <img src="<?php echo e(Storage::url($empleado->foto_path)); ?>" alt="<?php echo e($empleado->nombre); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?php echo e(substr($empleado->nombre, 0, 1)); ?>

                    <?php endif; ?>
                </div>
                <div class="absolute bottom-1 right-1 w-5 h-5 rounded-full border-2 border-white <?php echo e($empleado->es_activo ? 'bg-emerald-500' : 'bg-gray-400'); ?>"></div>
            </div>
            <div class="flex-1 text-center md:text-left">
                <h1 class="text-2xl font-bold text-gray-900"><?php echo e($empleado->nombre); ?> <?php echo e($empleado->apellido_paterno); ?></h1>
                <p class="text-gray-500"><?php echo e($empleado->posicion ?? 'Sin Puesto'); ?> - <?php echo e($empleado->area ?? 'Sin Área'); ?></p>
                <div class="mt-2 flex flex-wrap justify-center md:justify-start gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        <?php echo e($empleado->id_empleado ?? 'S/N'); ?>

                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                        Ingreso: <?php echo e($empleado->created_at->format('d M Y')); ?>

                    </span>
                </div>
            </div>
            
            
            <div class="w-full md:w-48 bg-gray-100 rounded-full h-4 overflow-hidden border border-gray-200">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-full text-[10px] text-center text-white leading-4" style="width: <?php echo e($empleado->porcentaje_expediente ?? '50'); ?>%"><?php echo e($empleado->porcentaje_expediente ?? '50'); ?>%</div>
            </div>
        </div>

        
        <div class="flex space-x-1 bg-white p-1 rounded-xl shadow-sm border border-gray-200 mb-6 overflow-x-auto">
            <?php $__currentLoopData = ['general' => 'Información General', 'docs' => 'Documentos y Archivos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button @click="tab = '<?php echo e($key); ?>'" 
                    :class="{ 'bg-indigo-50 text-indigo-700 shadow-sm border-indigo-200': tab === '<?php echo e($key); ?>', 'text-gray-500 hover:bg-gray-50': tab !== '<?php echo e($key); ?>' }"
                    class="flex-1 py-2.5 px-4 rounded-lg text-sm font-bold transition-all whitespace-nowrap border border-transparent outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <?php echo e($label); ?>

                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div x-show="tab === 'general'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 border-b pb-4 gap-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Detalles del Empleado</h3>
                    <p class="text-sm text-gray-500">Información personal y de contacto importada.</p>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    
                    <form action="<?php echo e(route('rh.expedientes.import-excel', $empleado->id)); ?>" method="POST" enctype="multipart/form-data" class="flex items-center">
                        <?php echo csrf_field(); ?>
                        <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold border border-emerald-200 hover:bg-emerald-100 transition gap-2 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Importar Formato ID (Excel)</span>
                            <input type="file" name="archivo_excel" class="hidden" onchange="this.form.submit()">
                        </label>
                    </form>

                    <a href="<?php echo e(route('rh.expedientes.edit', $empleado->id)); ?>" class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 rounded-lg text-xs font-bold border border-indigo-200 hover:bg-indigo-50 transition gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Editar Manualmente
                    </a>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-12 gap-y-10">
                
                
                <div>
                    <h4 class="text-sm font-bold text-indigo-900 uppercase tracking-wide border-b border-indigo-100 pb-2 mb-4 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Datos Personales
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs text-gray-400 uppercase font-bold block mb-1">Nombre Completo</label>
                            <p class="text-gray-900 font-medium"><?php echo e($empleado->nombre); ?> <?php echo e($empleado->apellido_paterno); ?> <?php echo e($empleado->apellido_materno); ?></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 uppercase font-bold block mb-1">Correo Electrónico</label>
                            <p class="text-gray-900 font-medium break-all"><?php echo e($empleado->correo); ?></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 uppercase font-bold block mb-1">RFC</label>
                            <p class="text-gray-900 font-medium"><?php echo e($empleado->rfc ?? '--'); ?></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 uppercase font-bold block mb-1">CURP</label>
                            <p class="text-gray-900 font-medium"><?php echo e($empleado->curp ?? '--'); ?></p>
                        </div>
                    </div>
                </div>

                
                <div>
                    <h4 class="text-sm font-bold text-indigo-900 uppercase tracking-wide border-b border-indigo-100 pb-2 mb-4 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Domicilio y Contacto
                    </h4>
                    <div class="space-y-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        
                        <div>
                            <label class="text-xs text-gray-400 uppercase font-bold block mb-1">Dirección</label>
                            <p class="text-gray-900 font-medium text-sm"><?php echo e($empleado->direccion ?? 'Sin calle registrada'); ?></p>
                        </div>
                        
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-gray-400 uppercase font-bold block mb-1">Ciudad / Estado</label>
                                <p class="text-gray-900 font-medium text-sm">
                                    <?php echo e($empleado->ciudad ?? ''); ?>

                                    <?php if($empleado->ciudad && $empleado->estado_federativo): ?>, <?php endif; ?>
                                    <?php echo e($empleado->estado_federativo ?? ''); ?>

                                    <?php if(!$empleado->ciudad && !$empleado->estado_federativo): ?> -- <?php endif; ?>
                                </p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 uppercase font-bold block mb-1">C.P.</label>
                                <span class="inline-block bg-white border border-gray-200 px-2 py-1 rounded text-sm font-mono text-gray-700">
                                    <?php echo e($empleado->codigo_postal ?? '----'); ?>

                                </span>
                            </div>
                        </div>

                        <div class="border-t border-slate-200 my-2"></div>

                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-gray-400 uppercase font-bold block mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    Celular
                                </label>
                                <p class="text-indigo-600 font-bold text-sm"><?php echo e($empleado->telefono ?? '--'); ?></p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 uppercase font-bold block mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                    Tel. Casa
                                </label>
                                <p class="text-gray-700 font-medium text-sm"><?php echo e($empleado->telefono_casa ?? '--'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div>
                    <h4 class="text-sm font-bold text-indigo-900 uppercase tracking-wide border-b border-indigo-100 pb-2 mb-4 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        Información Médica
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs text-gray-400 uppercase font-bold block mb-1">Alergias</label>
                            <?php if($empleado->alergias && strtolower($empleado->alergias) != 'no'): ?>
                                <div class="bg-red-50 border-l-4 border-red-400 p-3 rounded-r-md">
                                    <p class="text-red-700 font-bold text-sm"><?php echo e($empleado->alergias); ?></p>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-500 italic text-sm">Niega alergias.</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 uppercase font-bold block mb-1">Enfermedades Crónicas</label>
                            <?php if($empleado->enfermedades_cronicas && strtolower($empleado->enfermedades_cronicas) != 'no'): ?>
                                <div class="bg-amber-50 border-l-4 border-amber-400 p-3 rounded-r-md">
                                    <p class="text-amber-800 font-bold text-sm"><?php echo e($empleado->enfermedades_cronicas); ?></p>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-500 italic text-sm">Niega enfermedades crónicas.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div>
                    <h4 class="text-sm font-bold text-indigo-900 uppercase tracking-wide border-b border-indigo-100 pb-2 mb-4 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Contacto de Emergencia
                    </h4>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold mr-3">
                                <?php echo e(substr($empleado->contacto_emergencia_nombre ?? '?', 0, 1)); ?>

                            </div>
                            <div>
                                <p class="text-gray-900 font-bold text-sm"><?php echo e($empleado->contacto_emergencia_nombre ?? 'No registrado'); ?></p>
                                <p class="text-indigo-600 text-xs font-semibold"><?php echo e($empleado->contacto_emergencia_parentesco ?? 'Parentesco no especificado'); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center text-sm text-gray-600 bg-white p-2 rounded-lg border border-gray-100 shadow-sm">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <span class="font-mono font-bold"><?php echo e($empleado->contacto_emergencia_numero ?? '--'); ?></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        
        <div x-show="tab === 'docs'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                
                <div class="lg:col-span-1">
                    <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-100 sticky top-4">
                        <h3 class="font-bold text-indigo-900 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Subir Nuevo Documento
                        </h3>
                        
                        <?php if(session('success')): ?>
                            <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-2 rounded-lg text-xs mb-4">
                                <?php echo e(session('success')); ?>

                            </div>
                        <?php endif; ?>

                        <form action="<?php echo e(route('rh.expedientes.upload', $empleado->id)); ?>" method="POST" enctype="multipart/form-data" class="space-y-4 mt-4">
                            <?php echo csrf_field(); ?>
                            <div>
                                <label class="block text-xs font-bold text-indigo-700 mb-1">Nombre del Archivo</label>
                                <input type="text" name="nombre" required class="w-full rounded-lg border-indigo-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej: INE, Contrato 2025...">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-indigo-700 mb-1">Categoría</label>
                                <select name="categoria" class="w-full rounded-lg border-indigo-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option>Identificación</option>
                                    <option>Laboral / Contratos</option>
                                    <option>Médico</option>
                                    <option>Certificaciones</option>
                                    <option>Otros</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-indigo-700 mb-1">Vencimiento (Opcional)</label>
                                <input type="date" name="fecha_vencimiento" class="w-full rounded-lg border-indigo-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <input type="file" name="documento" required class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-200 file:text-indigo-700 hover:file:bg-indigo-300">
                            </div>
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition transform hover:-translate-y-0.5">
                                Guardar Documento
                            </button>
                        </form>
                    </div>
                </div>

                
                <div class="lg:col-span-2 space-y-6">
                    <?php $__empty_1 = true; $__currentLoopData = $docsGrouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria => $docs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 font-bold text-gray-700 flex justify-between items-center">
                                <span><?php echo e($categoria); ?></span>
                                <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full"><?php echo e(count($docs)); ?> archivos</span>
                            </div>
                            <div class="divide-y divide-gray-100">
                                <?php $__currentLoopData = $docs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition group">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <div class="w-10 h-10 rounded-lg bg-red-50 flex-shrink-0 flex items-center justify-center text-red-500">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-gray-800 truncate"><?php echo e($doc->nombre); ?></p>
                                                <div class="flex items-center gap-2">
                                                    <p class="text-xs text-gray-400">Subido: <?php echo e($doc->created_at->format('d/m/Y')); ?></p>
                                                    <?php if($doc->fecha_vencimiento): ?>
                                                        <?php 
                                                            $vence = \Carbon\Carbon::parse($doc->fecha_vencimiento);
                                                            $dias = now()->diffInDays($vence, false);
                                                        ?>
                                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded <?php echo e($dias < 0 ? 'bg-gray-100 text-gray-500 line-through' : ($dias < 30 ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600')); ?>">
                                                            <?php echo e($dias < 0 ? 'Vencido' : 'Vence en ' . round($dias) . ' días'); ?>

                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex gap-2 opacity-50 group-hover:opacity-100 transition-opacity">
                                            <a href="<?php echo e(Storage::url($doc->ruta_archivo)); ?>" target="_blank" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-400 hover:text-indigo-600 hover:border-indigo-300 transition shadow-sm" title="Ver / Descargar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                            
                                            <form action="<?php echo e(route('rh.expedientes.delete-doc', $doc->id)); ?>" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este documento? Esta acción no se puede deshacer.');">
                                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                <button class="p-2 bg-white border border-gray-200 rounded-lg text-gray-400 hover:text-red-600 hover:border-red-300 transition shadow-sm" title="Eliminar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="flex flex-col items-center justify-center py-12 border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50">
                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p class="text-gray-500 font-medium">El expediente de documentos está vacío.</p>
                            <p class="text-xs text-gray-400 mt-1">Usa el formulario de la izquierda para agregar archivos.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/Recursos_Humanos/expedientes/show.blade.php ENDPATH**/ ?>