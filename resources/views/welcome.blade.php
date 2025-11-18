@extends('layouts.master')

@section('title', 'Sistema de Tickets - E&I Tecnología')

@section('content')
    <main class="relative overflow-hidden bg-gradient-to-br from-white via-blue-50 to-blue-100">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-32 -left-20 w-96 h-96 bg-blue-200/40 blur-3xl rounded-full"></div>
            <div class="absolute top-40 -right-24 w-96 h-96 bg-blue-300/30 blur-3xl rounded-full"></div>
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-32 bg-gradient-to-t from-white"></div>
        </div>

        <div class="relative max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-10">
            @if(session('success'))
                <div class="bg-white/70 backdrop-blur border-l-4 border-green-400 rounded-2xl shadow-md p-5 mb-8 mx-auto max-w-4xl">
                    <div class="flex items-start space-x-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-green-100 text-green-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="text-sm sm:text-base text-green-800 font-medium leading-relaxed">
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div class="bg-white/70 backdrop-blur border-l-4 border-blue-400 rounded-2xl shadow-md p-5 mb-8 mx-auto max-w-4xl">
                    <div class="flex items-start space-x-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-100 text-blue-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="text-sm sm:text-base text-blue-900 font-medium leading-relaxed">
                            {{ session('info') }}
                        </div>
                    </div>
                </div>
            @endif

            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-slate-900">Centro de Soporte Técnico</h1>
                <p class="mx-auto mt-2 max-w-2xl text-sm text-slate-600">Gestiona y crea tus solicitudes de soporte técnico.</p>
            </div>

            @guest
                <section id="acceso" class="relative mb-14">
                    <div class="max-w-3xl mx-auto">
                        <div class="relative overflow-hidden rounded-3xl border border-blue-200/60 bg-white/80 backdrop-blur shadow-xl">
                            <div class="absolute -top-16 -right-16 w-40 h-40 bg-blue-200/50 blur-3xl rounded-full"></div>
                            <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-blue-100/40"></div>

                            <div class="relative px-8 py-10 sm:px-12">
                                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/40 mx-auto mb-6">
                                        <x-ui.icon-box name="squares-2x2" boxClass="h-16 w-16 rounded-2xl" iconClass="h-8 w-8" />
                                </div>
                                <h2 class="text-2xl sm:text-3xl font-bold text-center text-slate-900 mb-4">¡Bienvenido al Sistema de Tickets!</h2>
                                <p class="text-center text-slate-600 mb-8 text-sm sm:text-base max-w-xl mx-auto">
                                    Inicia sesión con tu correo corporativo para crear solicitudes, monitorear avances y comunicarte directamente con el equipo de soporte técnico.
                                </p>
                                <div class="flex flex-col sm:flex-row justify-center gap-3 mb-4">
                                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 hover:from-blue-700 hover:to-blue-800 transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                        </svg>
                                        Iniciar sesión
                                    </a>
                                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-white px-6 py-3 text-sm font-semibold text-blue-700 shadow-sm hover:border-blue-300 hover:bg-blue-50 transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Solicitar registro
                                    </a>
                                </div>
                                <p class="text-xs sm:text-sm text-center text-slate-500">
                                    Las nuevas cuentas deben ser aprobadas por el administrador para garantizar que pertenezcan a la organización.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-6">
                        <a href="mailto:soporte@estrategiaeinnovacion.com.mx" class="inline-flex items-center text-sm font-medium text-blue-700 hover:text-blue-900 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3.063h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Necesito ayuda
                        </a>
                    </div>
                </section>
            @endguest

            @auth
                <section id="acciones" class="relative">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        @php
                            $cards = [
                                [
                                    'title' => 'Reportar Problema de Software',
                                    'description' => 'Reporta errores, fallos o comportamientos inesperados en programas o aplicaciones.',
                                    'route' => route('tickets.create', 'software'),
                                    'cta' => 'Crear reporte',
                                    'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                ],
                                [
                                    'title' => 'Programar Mantenimiento',
                                    'description' => 'Solicita mantenimiento preventivo o correctivo para tus equipos y agenda visitas.',
                                    'route' => route('tickets.create', 'mantenimiento'),
                                    'cta' => 'Programar cita',
                                    'icon' => 'M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v9a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h3z M12 12h.01M8 16h8',
                                ],
                                [
                                    'title' => 'Reportar Problema de Equipo',
                                    'description' => 'Reporta fallas en computadoras, impresoras u otros equipos físicos de tu área.',
                                    'route' => route('tickets.create', 'hardware'),
                                    'cta' => 'Reportar falla',
                                    'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                                ],
                            ];
                        @endphp

                        @foreach($cards as $card)
                            <div class="relative overflow-hidden rounded-3xl border border-blue-100/80 bg-white/90 backdrop-blur shadow-lg shadow-blue-500/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                                <div class="absolute -top-20 -right-16 w-40 h-40 bg-gradient-to-br from-blue-200/50 to-transparent blur-3xl"></div>
                                <div class="relative p-8">
                                    <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-100 to-blue-200 text-blue-600 shadow-inner shadow-white/40 mx-auto mb-6">
                                        <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-center text-slate-900 mb-3">
                                        {{ $card['title'] }}
                                    </h3>
                                    <p class="text-center text-slate-600 leading-relaxed mb-8">
                                        {{ $card['description'] }}
                                    </p>
                                    <a href="{{ $card['route'] }}" class="group inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition-all duration-300 hover:from-blue-700 hover:to-blue-800">
                                        <svg class="w-5 h-5 mr-2 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        {{ $card['cta'] }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="relative mt-16 max-w-5xl mx-auto">
                    <div class="relative overflow-hidden rounded-3xl border border-blue-200/70 bg-white/90 backdrop-blur shadow-2xl shadow-blue-500/10">
                        <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-blue-100/60 to-transparent"></div>
                        <div class="relative px-10 py-12">
                            <div class="text-center">
                                <div class="mb-6 inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/40">
                                    <x-ui.icon name="lifebuoy" class="h-10 w-10" />
                                </div>
                                <h2 class="mb-4 text-3xl font-bold text-slate-900">¿Necesitas ayuda?</h2>
                                <p class="text-lg text-slate-600 mb-8 max-w-2xl mx-auto leading-relaxed">
                                    Contacta al equipo de soporte para orientación rápida y efectiva.
                                </p>
                                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                    <a href="mailto:soporte@estrategiaeinnovacion.com.mx" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition-colors hover:from-blue-700 hover:to-blue-800">
                                        <x-ui.icon name="envelope" class="mr-2 h-5 w-5" />
                                        Contacto directo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endauth
        </div>
    </main>

    <footer class="bg-white border-t border-blue-100">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="text-center text-slate-500 text-sm">
                <p>&copy; {{ date('Y') }} E&I - Comercio Exterior, Logística y Tecnología. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
@endsection
