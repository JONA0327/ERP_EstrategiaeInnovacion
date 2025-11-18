<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Iniciar Sesión - E&I Sistema de Tickets</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gradient-to-br from-white via-blue-50 to-blue-100 font-sans antialiased">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full bg-blue-200/40 blur-3xl"></div>
            <div class="absolute top-1/3 -right-24 h-80 w-80 rounded-full bg-blue-300/40 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/2 h-40 w-full -translate-x-1/2 bg-gradient-to-t from-white"></div>
        </div>

        <div class="relative z-10 mx-auto flex min-h-screen flex-col px-4 py-6 sm:px-6 lg:px-10">
            <header class="mb-10 flex flex-col gap-6 rounded-3xl border border-blue-100/70 bg-white/80 px-6 py-6 shadow-lg shadow-blue-500/10 backdrop-blur sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('images/logo-ei.png') }}" alt="E&I Logo" class="h-12 w-auto">
                    <div>
                        <h1 class="text-lg font-semibold text-slate-900 sm:text-xl">Sistema de Tickets</h1>
                        <p class="text-sm text-slate-500">E&I - Tecnología</p>
                    </div>
                </div>
                <a href="{{ route('welcome') }}" class="inline-flex items-center justify-center rounded-2xl border border-blue-100 bg-white px-4 py-2 text-sm font-medium text-blue-700 transition-colors hover:border-blue-200 hover:bg-blue-50">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Volver al portal
                </a>
            </header>

            <div class="mx-auto flex w-full flex-1 items-center justify-center">
                <div class="w-full max-w-6xl">
                    <div class="relative overflow-hidden rounded-3xl border border-blue-200/70 bg-white/90 shadow-2xl shadow-blue-500/10 backdrop-blur">
                        <div class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-blue-200/50 blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-blue-50/60"></div>

                        <div class="relative grid grid-cols-1 gap-0 lg:grid-cols-2">
                            <div class="flex flex-col justify-between border-b border-blue-100/60 px-8 py-10 text-center lg:border-b-0 lg:border-r lg:text-left">
                                <div>
                                    <div class="mx-auto mb-8 flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/30 lg:mx-0">
                                        <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <h2 class="mb-4 text-3xl font-bold text-slate-900">Bienvenido de vuelta</h2>
                                    <p class="mx-auto max-w-md text-sm text-slate-600 lg:mx-0 lg:text-base">
                                        Accede con tu correo corporativo para monitorear solicitudes, recibir actualizaciones y colaborar con el equipo de soporte.
                                    </p>
                                </div>
                                <div class="mt-10 space-y-4 text-left text-sm text-slate-600">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                                            <x-ui.icon name="chat-bubble-left-right" class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900">Soporte centralizado</p>
                                            <p class="text-slate-500">Gestiona todos tus tickets en un solo lugar.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                                            <x-ui.icon name="sparkles" class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900">Actualizaciones en tiempo real</p>
                                            <p class="text-slate-500">Recibe notificaciones del estado de tus solicitudes.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                                            <x-ui.icon name="shield-check" class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900">Acceso seguro</p>
                                            <p class="text-slate-500">Protegemos tu información con estándares corporativos.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="px-8 py-10">
                                <div class="mb-8 text-center">
                                    <h2 class="text-2xl font-bold text-slate-900">Iniciar sesión</h2>
                                    <p class="mt-1 text-sm text-slate-500">Usa tu correo corporativo para continuar</p>
                                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50/80 p-4">
                        <div class="mb-2 flex items-center">
                            <svg class="mr-2 h-5 w-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="text-sm font-semibold text-rose-800">Error al iniciar sesión:</h3>
                        </div>
                        <ul class="list-disc space-y-1 pl-5 text-sm text-rose-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-5">
                        <label for="email" class="mb-2 block text-sm font-medium text-slate-700">
                            Correo Electrónico Corporativo
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               autocomplete="username"
                               placeholder="nombre.apellido@estrategiaeinnovacion.com.mx"
                               class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder-slate-400 transition-colors duration-200 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-rose-300 @enderror">
                        <p class="mt-1 text-xs text-slate-500">Solo se aceptan correos con el dominio &#64;estrategiaeinnovacion.com.mx.</p>
                    </div>

                    <!-- Password -->
                    <div class="mb-5">
                        <label for="password" class="mb-2 block text-sm font-medium text-slate-700">
                            Contraseña
                        </label>
                        <input type="password"
                               id="password" 
                               name="password" 
                               required 
                               autocomplete="current-password"
                               placeholder="Tu contraseña"
                               class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder-slate-400 transition-colors duration-200 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-rose-300 @enderror">
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-8">
                        <div class="flex items-center">
                            <input type="checkbox"
                                   id="remember"
                                   name="remember"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-slate-600">
                                Recordarme en este dispositivo
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition-colors hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            Iniciar sesión
                        </button>
                        <p class="text-center text-xs text-slate-500">
                            ¿Aún no tienes acceso? <a href="{{ route('register') }}" class="font-semibold text-blue-600 transition-colors hover:text-blue-800">Solicita tu registro</a> y espera la aprobación del administrador.
                        </p>
                    </div>
                </form>
            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
