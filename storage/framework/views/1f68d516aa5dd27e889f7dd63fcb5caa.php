<?php
    $user = Auth::user();
    
    // Determinar el contexto del área basado en la ruta actual y el referer
    $currentPath = request()->path();
    $referer = request()->headers->get('referer');
    $fromTickets = $referer && (str_contains($referer, 'mis-tickets') || str_contains($referer, 'ticket/create') || str_contains($referer, 'tickets'));
    
    $areaContext = 'ERP'; // default
    
    if (request()->is('recursos-humanos*')) {
        $areaContext = 'RH';
    } elseif (request()->is('logistica*')) {
        $areaContext = 'Logística';
    } elseif ($fromTickets || request()->is('ticket*') || request()->is('mis-tickets*')) {
        $areaContext = 'Tickets';
    }
    
    $initials = $user ? strtoupper(mb_substr($user->name, 0, 1, 'UTF-8')) : 'U';
?>
<nav x-data="{ open:false }" class="relative z-50 border-b border-slate-200 bg-white text-slate-700 shadow-md shadow-slate-200/70">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
        <a href="<?php echo e($areaContext==='RH' ? route('recursos-humanos.index') : ($areaContext==='Logística' ? route('logistica.index') : ($areaContext==='Tickets' ? route('tickets.mis-tickets') : route('welcome')))); ?>" class="flex items-center gap-3 py-3">
            <img src="<?php echo e(asset('images/logo-ei.png')); ?>" alt="E&I" class="h-10 w-auto">
            <div class="leading-tight">
                <?php if($areaContext==='RH'): ?>
                    <p class="text-sm font-semibold text-slate-800">Administración de RH</p>
                    <p class="text-xs text-slate-500">E&I - Recursos Humanos</p>
                <?php elseif($areaContext==='Logística'): ?>
                    <p class="text-sm font-semibold text-slate-800">Administración Logística</p>
                    <p class="text-xs text-slate-500">E&I - Logística</p>
                <?php elseif($areaContext==='Tickets'): ?>
                    <p class="text-sm font-semibold text-slate-800">Sistema de Tickets</p>
                    <p class="text-xs text-slate-500">E&I - Tecnología</p>
                <?php else: ?>
                    <p class="text-sm font-semibold text-slate-800">SISTEMA ERP ESTRATEGIA E INNOVACIÓN</p>
                    <p class="text-xs text-slate-500">Portal Corporativo Integrado</p>
                <?php endif; ?>
            </div>
        </a>
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('tickets.mis-tickets')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 text-white px-4 py-2 text-sm font-medium shadow hover:bg-blue-700">
                <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'lifebuoy','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'lifebuoy','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
                Centro de Soporte
            </a>
            <?php if(auth()->guard()->check()): ?>
            <div class="relative" x-data="{ profile:false }">
                <button
                    @click="profile=!profile"
                    @click.outside="profile=false"
                    class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-sm text-slate-700 shadow-sm shadow-blue-100/50 transition-all duration-200 hover:bg-blue-100 hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/60 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                >
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-white text-sm font-semibold shadow-md shadow-blue-500/30"><?php echo e($initials); ?></span>
                    <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': profile }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div
                    x-cloak
                    x-show="profile"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-3 w-80 overflow-hidden rounded-2xl border border-blue-100/80 bg-white/95 shadow-2xl shadow-blue-500/10 backdrop-blur-xl z-50"
                >
                    <div class="border-b border-blue-100/70 bg-gradient-to-r from-blue-50/70 to-white px-4 py-2.5">
                        <p class="text-xs font-semibold text-slate-900"><?php echo e($user->name); ?></p>
                        <p class="text-[11px] text-slate-500"><?php echo e($user->email); ?></p>
                        <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700">
                            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'check-badge','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-badge','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
                            <?php echo e($areaContext === 'Tickets' ? 'Tecnología' : $areaContext); ?>

                        </span>
                    </div>
                    <div class="py-2">
                        <a href="<?php echo e(route('profile.edit')); ?>" class="flex items-center px-4 py-2 text-xs text-slate-600 transition-colors duration-150 hover:bg-blue-50/60">
                            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'pencil-square','class' => 'mr-3 h-4 w-4 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pencil-square','class' => 'mr-3 h-4 w-4 text-slate-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>Mi perfil
                        </a>
                        <form method="POST" action="<?php echo e(route('logout')); ?>" class="mt-1">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="flex w-full items-center px-4 py-2 text-xs font-medium text-red-600 transition-colors duration-150 hover:bg-red-50">
                                <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'arrow-right-on-rectangle','class' => 'mr-3 h-4 w-4 text-red-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right-on-rectangle','class' => 'mr-3 h-4 w-4 text-red-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
<?php /**PATH C:\Users\trade\Desktop\Proyectos\ERP_EstrategiaeInnovacion\resources\views/layouts/erp-navigation.blade.php ENDPATH**/ ?>