@php
    $user = Auth::user();
    $areaContext = request()->routeIs('recursos-humanos.index') ? 'RH' : (request()->routeIs('logistica.index') ? 'Logística' : 'ERP');
    $initials = $user ? strtoupper(mb_substr($user->name, 0, 1, 'UTF-8')) : 'U';
@endphp
<nav x-data="{ open:false }" class="border-b border-slate-200 bg-white shadow-sm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
        <a href="{{ route('welcome') }}" class="flex items-center gap-3 py-3">
            <img src="{{ asset('images/logo-ei.png') }}" alt="E&I" class="h-10 w-auto">
            <div class="leading-tight">
                @if($areaContext==='RH')
                    <p class="text-sm font-semibold text-slate-800">Administración de RH</p>
                    <p class="text-xs text-slate-500">E&I - Recursos Humanos</p>
                @elseif($areaContext==='Logística')
                    <p class="text-sm font-semibold text-slate-800">Panel Logística</p>
                    <p class="text-xs text-slate-500">E&I - Operaciones</p>
                @else
                    <p class="text-sm font-semibold text-slate-800">ERP E&I</p>
                    <p class="text-xs text-slate-500">Portal Corporativo</p>
                @endif
            </div>
        </a>
        <div class="flex items-center gap-4">
            <a href="{{ route('tickets.mis-tickets') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 text-white px-4 py-2 text-sm font-medium shadow hover:bg-blue-700">
                <x-ui.icon name="lifebuoy" class="h-4 w-4" />
                Soporte Técnico
            </a>
            @auth
            <div class="relative" x-data="{ profile:false }">
                <button @click="profile=!profile" @click.outside="profile=false" class="flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-2 py-1.5 text-sm text-slate-700 hover:bg-blue-100">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-white font-semibold">{{ $initials }}</span>
                    <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-cloak x-show="profile" x-transition class="absolute right-0 mt-3 w-72 rounded-2xl border border-blue-100 bg-white shadow-lg">
                    <div class="px-4 py-3 border-b border-blue-100">
                        <p class="text-xs font-semibold text-slate-900">{{ $user->name }}</p>
                        <p class="text-[11px] text-slate-500">{{ $user->email }}</p>
                        <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700">
                            <x-ui.icon name="check-badge" class="h-3.5 w-3.5" />
                            {{ $areaContext }}
                        </span>
                    </div>
                    <div class="py-2">
                        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-xs text-slate-600 hover:bg-blue-50">
                            <x-ui.icon name="pencil-square" class="mr-3 h-4 w-4 text-slate-400" />Mi perfil
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="mt-1">
                            @csrf
                            <button type="submit" class="flex w-full items-center px-4 py-2 text-xs font-medium text-red-600 hover:bg-red-50">
                                <x-ui.icon name="arrow-right-on-rectangle" class="mr-3 h-4 w-4 text-red-400" />Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endauth
        </div>
    </div>
</nav>
