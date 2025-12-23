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
    
    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-50/50 blur-3xl rounded-full mix-blend-multiply"></div>
        <div class="absolute top-1/4 right-0 w-64 h-64 bg-emerald-50/50 blur-3xl rounded-full mix-blend-multiply"></div>
    </div>

     <?php $__env->slot('header', null, []); ?> 
        <div class="relative z-10 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h2 class="font-bold text-xl text-slate-800 leading-tight tracking-tight">
                    <?php echo e(__('Reporte de Actividades')); ?>

                </h2>
                
                <?php if(request('ver_equipo_de')): ?>
                    <?php $lider = \App\Models\Empleado::find(request('ver_equipo_de')); ?>
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1 border border-indigo-200 shadow-sm transition-all hover:bg-indigo-200">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Equipo: <?php echo e($lider ? Str::limit($lider->nombre_completo, 15) : '...'); ?>

                    </span>
                    <a href="<?php echo e(route('activities.index')); ?>" class="text-slate-400 hover:text-red-500 font-bold px-2 text-sm transition-colors" title="Quitar filtro">✕</a>
                <?php endif; ?>
            </div>
            
            <?php if(isset($esDireccion) && $esDireccion): ?>
                <button x-data @click="$dispatch('open-director-sidebar')" class="bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-slate-200 flex items-center gap-2 transition-all transform hover:scale-105 active:scale-95 border border-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Estructura
                </button>
            <?php endif; ?>
        </div>
     <?php $__env->endSlot(); ?>

    <style>
        .animate-pulse-fast { animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
    </style>

    <div class="relative py-8 min-h-screen z-10" 
         x-data="{ 
            historyOpen: false, notesOpen: false, directorOpen: false,
            currentActivity: null, currentActivityId: null, currentNotes: '', historyLogs: [],
            
            async openHistory(id, name) { 
                this.currentActivity = name; 
                this.historyOpen = true; 
                this.historyLogs = []; 
                try {
                    const rawData = document.getElementById('history-data-' + id).value;
                    this.historyLogs = JSON.parse(rawData);
                } catch(e) { console.error('Error logs', e); } 
            },
            
            openNotes(id, name) { 
                this.currentActivityId = id; 
                this.currentActivity = name; 
                this.currentNotes = document.getElementById('notes-data-' + id).value; 
                this.notesOpen = true; 
            },

            appendSignature() { const d=new Date().toLocaleString('es-MX', {day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'}); this.currentNotes += (this.currentNotes?'\n\n':'') + `[${d} - <?php echo e(Auth::user()->name); ?>]: `; this.$nextTick(()=>document.getElementById('big-note-area').focus()); }
         }"
         @open-director-sidebar.window="directorOpen = true">
         
        <div class="max-w-[98%] mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl shadow-sm border border-white/50 flex items-center justify-between transition-transform hover:-translate-y-1">
                    <div><p class="text-[10px] uppercase text-slate-400 font-bold tracking-wider">Total Actividades</p><p class="text-3xl font-extrabold text-slate-700 mt-1"><?php echo e($activities->count()); ?></p></div>
                    <div class="p-3 bg-slate-50 rounded-2xl text-slate-400"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg></div>
                </div>
                <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl shadow-sm border border-white/50 flex items-center justify-between transition-transform hover:-translate-y-1">
                    <div><p class="text-[10px] uppercase text-indigo-400 font-bold tracking-wider">En Proceso</p><p class="text-3xl font-extrabold text-indigo-600 mt-1"><?php echo e($activities->where('estatus', 'En proceso')->count()); ?></p></div>
                    <div class="p-3 bg-indigo-50 rounded-2xl text-indigo-500"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                </div>
                <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl shadow-sm border border-white/50 flex items-center justify-between transition-transform hover:-translate-y-1">
                    <div><p class="text-[10px] uppercase text-red-400 font-bold tracking-wider">Con Retardo</p><p class="text-3xl font-extrabold text-red-600 mt-1"><?php echo e($activities->whereIn('estatus', ['Retardo', 'Completado con retardo'])->count()); ?></p></div>
                    <div class="p-3 bg-red-50 rounded-2xl text-red-500 animate-pulse-fast"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
                </div>
                <?php
                    $completed = $activities->whereNotNull('porcentaje')->where('porcentaje', '>', 0);
                    $avg = $completed->count() > 0 ? $completed->avg('porcentaje') : 0;
                    $effColor = $avg >= 90 ? 'text-emerald-600' : ($avg >= 70 ? 'text-yellow-600' : 'text-red-600');
                    $bgEff = $avg >= 90 ? 'bg-emerald-50 text-emerald-500' : ($avg >= 70 ? 'bg-yellow-50 text-yellow-500' : 'bg-red-50 text-red-500');
                ?>
                <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl shadow-sm border border-white/50 flex items-center justify-between transition-transform hover:-translate-y-1">
                    <div><p class="text-[10px] uppercase text-emerald-600/70 font-bold tracking-wider">Eficiencia</p><p class="text-3xl font-extrabold <?php echo e($effColor); ?> mt-1"><?php echo e(number_format($avg, 1)); ?>%</p></div>
                    <div class="p-3 rounded-2xl <?php echo e($bgEff); ?>"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg></div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm p-5 rounded-2xl border border-slate-200/60 shadow-sm">
                <form method="GET" action="<?php echo e(route('activities.index')); ?>" class="flex flex-wrap gap-4 items-end">
                    <?php if(request('ver_equipo_de')): ?> <input type="hidden" name="ver_equipo_de" value="<?php echo e(request('ver_equipo_de')); ?>"> <?php endif; ?>
                    
                    <?php if(isset($filterUsers) && $filterUsers->count() > 1): ?>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Responsable</label>
                        <select name="user_filter" class="w-full text-xs border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm cursor-pointer bg-white">
                            <option value="">-- Todos --</option>
                            <?php $__currentLoopData = $filterUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($u->id); ?>" <?php echo e(request('user_filter') == $u->id ? 'selected' : ''); ?>><?php echo e($u->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="w-28">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Prioridad</label>
                        <select name="prioridad" class="w-full text-xs border-slate-300 rounded-lg focus:ring-indigo-500 cursor-pointer shadow-sm bg-white">
                            <option value="">Todas</option>
                            <option value="Alta" <?php echo e(request('prioridad') == 'Alta' ? 'selected' : ''); ?>>Alta 🔥</option>
                            <option value="Media" <?php echo e(request('prioridad') == 'Media' ? 'selected' : ''); ?>>Media</option>
                            <option value="Baja" <?php echo e(request('prioridad') == 'Baja' ? 'selected' : ''); ?>>Baja</option>
                        </select>
                    </div>

                    <div class="w-36">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Estatus</label>
                        <select name="estatus" class="w-full text-xs border-slate-300 rounded-lg focus:ring-indigo-500 cursor-pointer shadow-sm bg-white">
                            <option value="">Todos</option>
                            <option value="En blanco" <?php echo e(request('estatus') == 'En blanco' ? 'selected' : ''); ?>>⚪ En blanco</option>
                            <option value="En proceso" <?php echo e(request('estatus') == 'En proceso' ? 'selected' : ''); ?>>🔵 En proceso</option>
                            <option value="Completado" <?php echo e(request('estatus') == 'Completado' ? 'selected' : ''); ?>>🟢 Completado</option>
                            <option value="Retardo" <?php echo e(request('estatus') == 'Retardo' ? 'selected' : ''); ?>>🔴 Retardo</option>
                            <option value="Completado con retardo" <?php echo e(request('estatus') == 'Completado con retardo' ? 'selected' : ''); ?>>🟠 Con Retardo</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Desde</label>
                            <input type="date" name="fecha_inicio" value="<?php echo e(request('fecha_inicio')); ?>" class="text-xs border-slate-300 rounded-lg w-32 shadow-sm focus:ring-indigo-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Hasta</label>
                            <input type="date" name="fecha_fin" value="<?php echo e(request('fecha_fin')); ?>" class="text-xs border-slate-300 rounded-lg w-32 shadow-sm focus:ring-indigo-500 bg-white">
                        </div>
                    </div>

                    <div class="flex gap-2 pb-0.5">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-1 shadow-md hover:shadow-lg active:scale-95">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Filtrar
                        </button>
                        
                        <?php if(request()->hasAny(['prioridad', 'estatus', 'fecha_inicio', 'fecha_fin', 'user_filter', 'ver_equipo_de'])): ?>
                            <a href="<?php echo e(route('activities.index')); ?>" class="bg-white text-slate-600 border border-slate-300 px-3 py-2 rounded-lg text-xs font-bold hover:bg-slate-50 transition shadow-sm">
                                Limpiar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="bg-white p-5 shadow-lg rounded-2xl border-l-4 border-indigo-500 relative overflow-hidden">
                <form action="<?php echo e(route('activities.store')); ?>" method="POST" class="relative z-10 grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <?php echo csrf_field(); ?>
                    <div class="col-span-12 md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Área</label>
                        <input type="text" name="area" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej. Sistemas" required>
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tipo</label>
                        <input type="text" name="tipo_actividad" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej. Proyecto" required>
                    </div>
                    <div class="col-span-12 md:col-span-4">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Actividad</label>
                        <input type="text" name="nombre_actividad" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Descripción detallada..." required>
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Compromiso</label>
                        <input type="date" name="fecha_compromiso" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <div class="col-span-12 md:col-span-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Prio</label>
                        <select name="prioridad" class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer bg-white">
                            <option value="Baja">Baja</option>
                            <option value="Media" selected>Media</option>
                            <option value="Alta">Alta</option>
                        </select>
                    </div>
                    <div class="col-span-12 md:col-span-1">
                        <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 px-2 rounded-lg text-xs shadow-md hover:bg-indigo-700 transition hover:shadow-lg transform active:scale-95 flex justify-center items-center gap-1">
                            <span>+</span> Agregar
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-left">
                        <thead class="bg-slate-800 text-slate-100 font-semibold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-3 py-4 w-12 text-center">Resp.</th>
                                <th class="px-3 py-4 w-48">Supervisor</th>
                                <th class="px-3 py-4 w-24">Tipo</th>
                                <th class="px-3 py-4 w-28 text-center">Prio</th>
                                <th class="px-3 py-4 min-w-[250px]">Actividad</th>
                                <th class="px-3 py-4 w-20 text-center">Inicio</th>
                                <th class="px-3 py-4 w-20 text-center">Promesa</th>
                                <th class="px-3 py-4 w-20 text-center">Fin</th>
                                <th class="px-2 py-4 w-16 text-center bg-slate-700">Meta</th>
                                <th class="px-2 py-4 w-16 text-center bg-slate-700">Real</th>
                                <th class="px-2 py-4 w-16 text-center bg-slate-700">Efic.</th>
                                <th class="px-3 py-4 w-40 text-center">Estatus</th>
                                <th class="px-2 py-4 w-16 text-center">Nota</th>
                                <th class="px-2 py-4 w-12 text-center">Log</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-indigo-50/40 transition-colors group <?php echo e(str_contains($act->estatus, 'Completado') ? 'bg-slate-50/80' : ''); ?>">
                                
                                <td class="px-3 py-3 text-center">
                                    <?php if($act->user_id === Auth::id()): ?>
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold text-[9px] border border-indigo-200 shadow-sm">YO</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-700 font-bold text-[9px] border border-orange-200 shadow-sm" title="<?php echo e($act->user->name); ?>">
                                            <?php echo e(strtoupper(substr($act->user->name ?? 'U', 0, 2))); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-3 py-3 text-slate-600 font-medium leading-tight text-[11px]">
                                    <?php echo e(Str::limit($act->user->empleado->supervisor->nombre ?? '-', 25)); ?>

                                </td>

                                <td class="px-3 py-3 text-slate-500">
                                    <span class="px-2 py-1 rounded bg-slate-100 border border-slate-200 text-[10px]"><?php echo e(Str::limit($act->tipo_actividad, 12)); ?></span>
                                </td>
                                
                                <td class="px-3 py-3 text-center">
                                    <?php
                                        $puedeEditar = false;
                                        $miEmpleado = Auth::user()->empleado;
                                        $suEmpleado = $act->user->empleado ?? null;
                                        if (isset($esDireccion) && $esDireccion) { $puedeEditar = true; }
                                        elseif ($miEmpleado && $suEmpleado && $miEmpleado->id === $suEmpleado->supervisor_id) { $puedeEditar = true; }
                                        
                                        $prioColor = match($act->prioridad) { 
                                            'Alta'=>'bg-red-50 text-red-700 border-red-200', 
                                            'Media'=>'bg-yellow-50 text-yellow-700 border-yellow-200', 
                                            default=>'bg-blue-50 text-blue-700 border-blue-200' 
                                        };
                                    ?>
                                    <form action="<?php echo e(route('activities.update', $act->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                        <select name="prioridad" onchange="this.form.submit()" 
                                                class="text-[10px] py-1 pl-2 pr-6 rounded-md border <?php echo e($prioColor); ?> font-bold shadow-sm focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed appearance-none w-full text-center"
                                                <?php echo e(!$puedeEditar ? 'disabled' : ''); ?>>
                                            <option value="Baja" <?php echo e($act->prioridad == 'Baja' ? 'selected' : ''); ?>>Baja</option>
                                            <option value="Media" <?php echo e($act->prioridad == 'Media' ? 'selected' : ''); ?>>Media</option>
                                            <option value="Alta" <?php echo e($act->prioridad == 'Alta' ? 'selected' : ''); ?>>Alta 🔥</option>
                                        </select>
                                    </form>
                                </td>

                                <td class="px-3 py-3 text-slate-800 font-medium leading-snug break-words">
                                    <?php echo e($act->nombre_actividad); ?>

                                </td>
                                
                                <td class="px-3 py-3 text-center text-slate-500"><?php echo e($act->fecha_inicio ? $act->fecha_inicio->format('d/m') : '-'); ?></td>
                                <td class="px-3 py-3 text-center text-indigo-700 font-bold"><?php echo e($act->fecha_compromiso ? $act->fecha_compromiso->format('d/m') : '-'); ?></td>
                                <td class="px-3 py-3 text-center text-slate-500"><?php echo e($act->fecha_final ? $act->fecha_final->format('d/m') : '-'); ?></td>
                                
                                <td class="px-2 py-3 text-center bg-slate-50 font-mono text-slate-600 border-l border-slate-100"><?php echo e($act->metrico); ?></td>
                                <td class="px-2 py-3 text-center bg-slate-50 font-mono font-bold border-l border-slate-100 <?php echo e(($act->resultado_dias > $act->metrico) ? 'text-red-600' : 'text-emerald-600'); ?>">
                                    <?php echo e($act->resultado_dias ?? '-'); ?>

                                </td>
                                <td class="px-2 py-3 text-center bg-slate-50 font-bold text-slate-800 border-l border-slate-100">
                                    <?php echo e(isset($act->porcentaje) ? number_format($act->porcentaje, 0).'%' : '-'); ?>

                                </td>

                                <td class="px-3 py-3">
                                    <form action="<?php echo e(route('activities.update', $act->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                        <?php
                                            $statusStyle = match($act->estatus) {
                                                'Completado' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                'Retardo' => 'bg-red-100 text-red-800 border-red-200 animate-pulse',
                                                'Completado con retardo' => 'bg-orange-100 text-orange-800 border-orange-200',
                                                'En proceso' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                'En blanco' => 'bg-slate-100 text-slate-500 border-slate-200',
                                                default => 'bg-white text-slate-700 border-slate-300'
                                            };
                                        ?>
                                        <select name="estatus" onchange="this.form.submit()" class="text-[10px] py-1 pl-2 pr-6 w-full rounded-md border <?php echo e($statusStyle); ?> font-semibold shadow-sm focus:ring-2 focus:ring-indigo-500 cursor-pointer appearance-none">
                                            <option value="En blanco" <?php echo e($act->estatus == 'En blanco' ? 'selected' : ''); ?>>⚪ Pendiente</option>
                                            <option value="En proceso" <?php echo e($act->estatus == 'En proceso' ? 'selected' : ''); ?>>🔵 En proceso</option>
                                            <option value="Completado" <?php echo e(str_contains($act->estatus, 'Completado') ? 'selected' : ''); ?>>🟢 Listo</option>
                                            <?php if(in_array($act->estatus, ['Retardo', 'Completado con retardo'])): ?>
                                                <option value="<?php echo e($act->estatus); ?>" selected disabled>
                                                    <?php echo e($act->estatus == 'Retardo' ? '🔴 Retardo' : '🟠 Tardío'); ?>

                                                </option>
                                            <?php endif; ?>
                                        </select>
                                    </form>
                                </td>

                                <td class="px-2 py-3 text-center">
                                    <button @click="openNotes(<?php echo e($act->id); ?>, '<?php echo e(addslashes($act->nombre_actividad)); ?>')" 
                                            class="text-indigo-600 hover:text-white hover:bg-indigo-600 border border-indigo-200 bg-indigo-50 p-1.5 rounded-lg transition-all shadow-sm group-hover:border-indigo-300" title="Ver Bitácora">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <textarea id="notes-data-<?php echo e($act->id); ?>" class="hidden"><?php echo e($act->comentarios); ?></textarea>
                                </td>

                                <td class="px-2 py-3 text-center">
                                    <button @click="openHistory(<?php echo e($act->id); ?>, '<?php echo e(addslashes($act->nombre_actividad)); ?>')" 
                                            class="text-slate-400 hover:text-slate-700 p-1 rounded-full hover:bg-slate-100 transition-colors" title="Ver Historial">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </button>
                                    <textarea id="history-data-<?php echo e($act->id); ?>" class="hidden"><?php echo e(json_encode($act->historial)); ?></textarea>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="14" class="py-12 text-center text-slate-400 italic bg-slate-50/50 rounded-b-xl border-t border-slate-100">No se encontraron actividades con estos filtros.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if(isset($esDireccion) && $esDireccion): ?>
        <div x-show="directorOpen" style="display: none;" class="fixed inset-0 overflow-hidden z-50">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="directorOpen = false"></div>
            <div class="fixed inset-y-0 left-0 flex max-w-full pr-10 pointer-events-none">
                <div class="w-screen max-w-xs pointer-events-auto bg-slate-900 shadow-2xl flex flex-col h-full transform transition ease-in-out duration-300" 
                     x-transition:enter="translate-x-full" x-transition:leave="-translate-x-full">
                    
                    <div class="bg-slate-800 px-6 py-6 flex justify-between items-center border-b border-slate-700/50">
                        <div><h2 class="text-xl font-bold text-white tracking-tight">Estructura</h2><p class="text-xs text-slate-400">Auditoría de Equipos</p></div>
                        <button @click="directorOpen = false" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto py-6 px-3">
                        <nav class="space-y-2">
                            <a href="<?php echo e(route('activities.index')); ?>" 
                               class="text-slate-300 hover:bg-slate-800 hover:text-white group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all <?php echo e(!request('ver_equipo_de') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : ''); ?>">
                                <span class="truncate">🏢 Ver Empresa Completa</span>
                            </a>

                            <div class="mt-6 mb-3 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                Gerentes Directos
                            </div>

                            <?php if(isset($listaSupervisores) && count($listaSupervisores) > 0): ?>
                                <?php $__currentLoopData = $listaSupervisores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e(route('activities.index', ['ver_equipo_de' => $sup->id])); ?>" 
                                       class="text-slate-300 hover:bg-slate-800 hover:text-white group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all <?php echo e(request('ver_equipo_de') == $sup->id ? 'bg-slate-800 text-white border-l-4 border-indigo-500' : ''); ?>">
                                        <div class="mr-3 flex-shrink-0 h-8 w-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-white shadow-sm border border-slate-600">
                                            <?php echo e(substr($sup->nombre_completo ?? 'S', 0, 1)); ?>

                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="truncate font-semibold"><?php echo e($sup->nombre_completo); ?></p>
                                            <p class="text-[10px] text-slate-500 truncate"><?php echo e($sup->posicion); ?></p>
                                        </div>
                                        <?php if(request('ver_equipo_de') == $sup->id): ?>
                                            <span class="w-2 h-2 rounded-full bg-indigo-500 ml-2 shadow-[0_0_10px_rgba(99,102,241,0.8)]"></span>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <div class="px-4 py-8 text-center">
                                    <p class="text-xs text-slate-500 italic">No tienes supervisores directos asignados.</p>
                                </div>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div x-show="notesOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="notesOpen = false"></div>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form :action="'/activities/' + currentActivityId" method="POST">
                        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-bold text-slate-900">Bitácora de Seguimiento</h3>
                                <button type="button" @click="notesOpen = false" class="text-slate-400 hover:text-slate-500">✕</button>
                            </div>
                            <p class="text-sm text-slate-500 mb-4">Actividad: <span x-text="currentActivity" class="text-indigo-600 font-bold"></span></p>
                            
                            <div class="relative">
                                <div class="absolute top-2 right-2 z-10">
                                    <button type="button" @click="appendSignature()" class="inline-flex items-center px-3 py-1 border border-indigo-200 text-xs font-bold rounded-full text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none transition shadow-sm">
                                        ✍️ Firmar hoy
                                    </button>
                                </div>
                                <textarea id="big-note-area" name="comentarios" x-model="currentNotes" rows="12" class="shadow-inner focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-slate-300 rounded-xl font-mono bg-slate-50 p-4 leading-relaxed resize-none" placeholder="Escribe aquí los avances o comentarios importantes..."></textarea>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-100">
                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-md px-5 py-2.5 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition-transform active:scale-95">Guardar Cambios</button>
                            <button type="button" @click="notesOpen = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-5 py-2.5 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="historyOpen" style="display: none;" class="fixed inset-0 overflow-hidden z-50">
             <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="historyOpen = false"></div>
             <div class="fixed inset-y-0 right-0 flex max-w-full pl-10 pointer-events-none">
                <div class="w-screen max-w-md pointer-events-auto bg-white shadow-2xl flex flex-col transform transition ease-in-out duration-300">
                    <div class="bg-slate-800 px-6 py-6 flex justify-between items-center shadow-md z-10">
                        <h2 class="text-white font-bold text-lg">Historial de Cambios</h2>
                        <button @click="historyOpen = false" class="text-slate-400 hover:text-white transition-colors">✕</button>
                    </div>
                    <div class="p-6 flex-1 overflow-y-auto bg-slate-50">
                        <ul class="space-y-6 relative border-l-2 border-slate-200 ml-3">
                            <template x-for="log in historyLogs" :key="log.id">
                                <li class="mb-10 ml-6 relative">
                                    <span class="absolute -left-[31px] top-1 flex items-center justify-center w-4 h-4 bg-indigo-100 rounded-full ring-4 ring-white">
                                        <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                                    </span>
                                    <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-200">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-[10px] font-bold uppercase text-slate-500 tracking-wide" x-text="log.campo_modificado"></span>
                                            <span class="text-[10px] text-slate-400" x-text="new Date(log.fecha_cambio).toLocaleString()"></span>
                                        </div>
                                        <div class="flex gap-2 items-center text-xs">
                                            <span class="text-red-400 line-through decoration-red-200 bg-red-50 px-2 py-0.5 rounded" x-text="log.valor_anterior || 'Vacío'"></span>
                                            <span class="text-slate-300">➔</span>
                                            <span class="text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded" x-text="log.valor_nuevo"></span>
                                        </div>
                                    </div>
                                </li>
                            </template>
                            <li x-show="historyLogs.length === 0" class="text-center text-slate-400 mt-10 italic">No hay registros de cambios aún.</li>
                        </ul>
                    </div>
                </div>
             </div>
        </div>

    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/activities/index.blade.php ENDPATH**/ ?>