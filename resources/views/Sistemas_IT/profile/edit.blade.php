<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Perfil de Usuario - Sistema IT</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-100">
        <header class="bg-white shadow-sm border-b border-blue-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 py-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center">
                        <img src="{{ asset('images/logo-ei.png') }}" alt="E&I Logo" class="h-12 w-auto mr-3">
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Sistema de Tickets</h1>
                            <p class="text-sm text-gray-600">E&amp;I - Tecnología</p>
                        </div>
                    </div>

                    <x-authenticated-actions theme="blue" />
                </div>
            </div>
        </header>

        <main>
            <section class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:items-center">
                        <div class="space-y-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 text-sm font-medium uppercase tracking-wide">
                                Gestión de perfil
                            </span>
                            <h2 class="text-3xl sm:text-4xl font-bold leading-tight">
                                Centro de <span class="text-blue-100">Configuración Personal</span>
                            </h2>
                            <p class="text-base sm:text-lg text-blue-50 leading-relaxed">
                                Administra tus datos personales, preferencias de seguridad y controla el acceso a tus tickets de soporte desde un solo lugar.
                            </p>

                            <div class="flex flex-wrap items-center gap-3 text-sm text-blue-100/90">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
                                    </svg>
                                    <span>Última actualización: {{ optional(auth()->user()->updated_at)->format('d/m/Y H:i') }}</span>
                                </div>
                                <span class="hidden h-4 w-px bg-white/40 lg:block"></span>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Estado de cuenta: <span class="font-semibold">Perfil activo</span></span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <a href="#informacion-perfil" class="group rounded-2xl bg-white/10 p-5 transition hover:bg-white/15">
                                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 text-white mb-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 10-6 0 3 3 0 006 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold leading-tight">Actualizar información</h3>
                                <p class="mt-2 text-sm text-blue-100/90">Modifica tu nombre y correo para mantenerte siempre informado.</p>
                            </a>
                            <a href="#seguridad" class="group rounded-2xl bg-white/10 p-5 transition hover:bg-white/15">
                                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 text-white mb-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c.414 0 .75-.336.75-.75v-1.5a.75.75 0 00-1.5 0v1.5c0 .414.336.75.75.75z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.25 11.75a5.25 5.25 0 10-10.5 0v1.5a2.25 2.25 0 00-1.5 2.122v3.878A2.25 2.25 0 007.5 21.5h9a2.25 2.25 0 002.25-2.25v-3.878a2.25 2.25 0 00-1.5-2.122v-1.5z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold leading-tight">Refuerza tu seguridad</h3>
                                <p class="mt-2 text-sm text-blue-100/90">Actualiza tu contraseña periódicamente y protege tus accesos.</p>
                            </a>
                            <a href="#preferencias" class="group rounded-2xl bg-white/10 p-5 transition hover:bg-white/15">
                                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 text-white mb-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A2 2 0 0122 9.528v4.944a2 2 0 01-2.447 1.804L15 14m-6 0l-4.553 2.276A2 2 0 012 14.472V9.528a2 2 0 012.447-1.804L9 10m6 0l-6 4m0-4l6 4" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold leading-tight">Controla tus notificaciones</h3>
                                <p class="mt-2 text-sm text-blue-100/90">Recibe avisos de tickets y novedades según tus preferencias.</p>
                            </a>
                            <a href="#seguridad" class="group rounded-2xl bg-white/10 p-5 transition hover:bg-white/15">
                                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 text-white mb-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M5.455 5.455l13.09 13.09M19.071 4.929A10 10 0 104.93 19.07 10 10 0 0019.07 4.93z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold leading-tight">Gestiona tu cuenta</h3>
                                <p class="mt-2 text-sm text-blue-100/90">Descarga tus datos o solicita la eliminación definitiva del perfil.</p>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 pb-16">
                <div class="grid grid-cols-1 gap-8 xl:grid-cols-3">
                    <div class="xl:col-span-2 space-y-8">
                        <div id="informacion-perfil">
                            @include('profile.partials.update-profile-information-form')
                        </div>

                        <div id="seguridad">
                            @include('profile.partials.update-password-form')
                        </div>

                        <div id="gestion-cuenta">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>

                    <aside class="space-y-6" id="preferencias">
                        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-lg border border-blue-100 p-6">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl font-semibold">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Cuenta verificada</p>
                                    <h2 class="text-lg font-semibold text-gray-900 leading-tight">{{ auth()->user()->name }}</h2>
                                    <p class="text-sm text-gray-500 break-all">{{ auth()->user()->email }}</p>
                                </div>
                            </div>

                            <dl class="mt-6 space-y-4 text-sm text-gray-600">
                                <div class="flex items-start gap-3">
                                    <span class="mt-1 inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14c-3.866 0-7 1.79-7 4v1h14v-1c0-2.21-3.134-4-7-4z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Rol en el sistema</p>
                                        <p class="font-medium text-gray-900">{{ auth()->user()->isAdmin() ? 'Administrador' : 'Usuario' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <span class="mt-1 inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2h-1V3a2 2 0 00-2-2H8a2 2 0 00-2 2v2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Miembro desde</p>
                                        <p class="font-medium text-gray-900">{{ optional(auth()->user()->created_at)->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <span class="mt-1 inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A2 2 0 0122 9.528v4.944a2 2 0 01-2.447 1.804L15 14m-6 0l-4.553 2.276A2 2 0 012 14.472V9.528a2 2 0 012.447-1.804L9 10m6 0l-6 4m0-4l6 4" />
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Notificaciones de tickets</p>
                                        <p class="font-medium text-gray-900">Recibe avisos por correo electrónico</p>
                                        <p class="text-xs text-gray-500 mt-1">Puedes modificar esta preferencia desde tus tickets activos.</p>
                                    </div>
                                </div>
                            </dl>
                        </div>

                        <div class="bg-blue-600 text-white rounded-2xl shadow-lg p-6 space-y-4">
                            <h3 class="text-lg font-semibold">Consejos de seguridad</h3>
                            <ul class="space-y-3 text-sm text-blue-50">
                                <li class="flex items-start gap-3">
                                    <span class="mt-1">🔒</span>
                                    <span>Actualiza tu contraseña regularmente y evita reutilizarla en otros servicios.</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="mt-1">📧</span>
                                    <span>Verifica tu correo electrónico para recibir notificaciones importantes del sistema.</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="mt-1">✅</span>
                                    <span>Revisa tus tickets y solicitudes para asegurarte de que toda la información esté actualizada.</span>
                                </li>
                            </ul>
                        </div>

                        <a
                            href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('welcome') }}"
                            class="group flex items-center justify-between gap-3 rounded-2xl border border-blue-100 bg-white/90 p-5 text-sm font-semibold text-blue-700 transition hover:bg-blue-50"
                        >
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </span>
                                <div class="text-left">
                                    <p>{{ auth()->user()->isAdmin() ? 'Volver al Panel de Administración' : 'Volver al Portal de Tickets' }}</p>
                                    <p class="text-xs font-normal text-blue-500">Sigue gestionando tus solicitudes y reportes</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-blue-500 transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </aside>
                </div>
            </section>
        </main>
    </body>
</html>
