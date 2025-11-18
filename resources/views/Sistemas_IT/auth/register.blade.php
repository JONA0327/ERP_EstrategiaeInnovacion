<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Registro - E&I Sistema de Tickets</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gradient-to-br from-white via-blue-50 to-blue-100 font-sans antialiased">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-24 -left-24 h-80 w-80 rounded-full bg-blue-200/40 blur-3xl"></div>
            <div class="absolute top-1/3 -right-28 h-80 w-80 rounded-full bg-indigo-200/40 blur-3xl"></div>
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
                        <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-blue-200/50 blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-blue-50/60"></div>

                        <div class="relative grid grid-cols-1 gap-0 lg:grid-cols-2">
                            <div class="flex flex-col justify-between border-b border-blue-100/60 px-8 py-10 text-center lg:border-b-0 lg:border-r lg:text-left">
                                <div>
                                    <div class="mx-auto mb-8 flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/30 lg:mx-0">
                                        <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                        </svg>
                                    </div>
                                    <h2 class="mb-4 text-3xl font-bold text-slate-900">Solicita tu cuenta</h2>
                                    <p class="mx-auto max-w-md text-sm text-slate-600 lg:mx-0 lg:text-base">
                                        Regístrate para crear y gestionar tickets, dar seguimiento a incidencias y mantener la comunicación con el equipo de soporte.
                                    </p>
                                </div>
                                <div class="mt-10 space-y-4 text-left text-sm text-slate-600">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                                            <x-ui.icon name="check-badge" class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900">Acceso validado</p>
                                            <p class="text-slate-500">Las cuentas nuevas son verificadas por el administrador.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                                            <x-ui.icon name="sparkles" class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900">Experiencia uniforme</p>
                                            <p class="text-slate-500">Interfaz consistente con el portal principal.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                                            <x-ui.icon name="shield-check" class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900">Información protegida</p>
                                            <p class="text-slate-500">Seguimos lineamientos de seguridad corporativa.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="px-8 py-10">
                                <div class="mb-8 text-center">
                                    <h2 class="text-2xl font-bold text-slate-900">Crear cuenta</h2>
                                    <p class="mt-1 text-sm text-slate-500">Completa tus datos para solicitar acceso</p>
                                </div>

                                <!-- Validation Errors -->
                                @if ($errors->any())
                                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50/80 p-4">
                                        <div class="mb-2 flex items-center">
                                            <svg class="mr-2 h-5 w-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <h3 class="text-sm font-semibold text-rose-800">Por favor corrige los siguientes errores:</h3>
                                        </div>
                                        <ul class="list-disc space-y-1 pl-5 text-sm text-rose-700">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('register.store') }}">
                                    @csrf

                                    <!-- Name -->
                                    <div class="mb-5">
                                        <label for="name" class="mb-2 block text-sm font-medium text-slate-700">
                                            Nombre Completo
                                        </label>
                                        <input type="text"
                                               id="name"
                                               name="name"
                                               value="{{ old('name') }}"
                                               required
                                               autocomplete="name"
                                               placeholder="Tu nombre completo"
                                               class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder-slate-400 transition-colors duration-200 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-rose-300 @enderror">
                                    </div>

                                    <!-- Email Address -->
                                    <div class="mb-5">
                                        <label for="email" class="mb-2 block text-sm font-medium text-slate-700">
                                            Correo Electrónico
                                        </label>
                                        <input type="email"
                                               id="email"
                                               name="email"
                                               value="{{ old('email') }}"
                                               required
                                               autocomplete="username"
                                               placeholder="tu@correo.com"
                                               class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder-slate-400 transition-colors duration-200 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-rose-300 @enderror">
                                        <p class="mt-1 text-xs text-slate-500">Puedes usar cualquier dirección de correo electrónico válida. Tu cuenta será revisada por un administrador.</p>
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
                                               autocomplete="new-password"
                                               placeholder="Mínimo 8 caracteres"
                                               class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder-slate-400 transition-colors duration-200 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-rose-300 @enderror">
                                        <p class="mt-1 text-xs text-slate-500">Debe tener entre 8 y 16 caracteres e incluir números y símbolos.</p>
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="mb-8">
                                        <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700">
                                            Confirmar Contraseña
                                        </label>
                                        <input type="password"
                                               id="password_confirmation"
                                               name="password_confirmation"
                                               required
                                               autocomplete="new-password"
                                               placeholder="Repite tu contraseña"
                                               class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder-slate-400 transition-colors duration-200 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="flex flex-col gap-4">
                                        <button type="submit"
                                                class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition-colors hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                            Crear cuenta
                                        </button>
                                        <a href="{{ route('login') }}" class="text-center text-xs font-semibold text-blue-600 transition-colors hover:text-blue-800">
                                            ¿Ya tienes cuenta? Inicia sesión
                                        </a>
                                        <p class="text-center text-xs text-slate-500">Tu solicitud será revisada por un administrador antes de habilitar el acceso.</p>
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
