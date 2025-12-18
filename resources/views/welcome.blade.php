@extends(request()->get('from') === 'tickets' ? 'Sistemas_IT.layouts.master' : 'layouts.erp')

@section('title', request()->get('from') === 'tickets' ? 'Inicio - Sistema de Tickets' : 'SISTEMA ERP ESTRATEGIA E INNOVACIÓN')

@section('content')
    <main class="relative overflow-hidden bg-slate-50 min-h-screen">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-50/50 blur-3xl rounded-full"></div>
            <div class="absolute top-1/4 right-0 w-64 h-64 bg-slate-100/50 blur-3xl rounded-full"></div>
        </div>

        <div class="relative max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-white border-l-4 border-emerald-500 rounded-xl shadow-sm p-4 mb-8 mx-auto max-w-4xl flex items-center gap-4 animate-fade-in-up">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('info'))
                <div class="bg-white border-l-4 border-indigo-500 rounded-xl shadow-sm p-4 mb-8 mx-auto max-w-4xl flex items-center gap-4 animate-fade-in-up">
                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-sm font-medium text-indigo-900">{{ session('info') }}</p>
                </div>
            @endif

            <div class="text-center mb-12">
                @if(request()->get('from') === 'tickets')
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold uppercase tracking-wider mb-4 border border-indigo-100">Soporte Técnico</span>
                    <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight sm:text-5xl mb-4">Sistema de Tickets</h1>
                    <p class="mx-auto max-w-2xl text-lg text-slate-500">Portal de atención y soporte para Estrategia e Innovación.</p>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold uppercase tracking-wider mb-4 border border-slate-200">Plataforma Corporativa</span>
                    <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight sm:text-5xl mb-4">ERP Estrategia e Innovación</h1>
                    <p class="mx-auto max-w-2xl text-lg text-slate-500">Gestión integral de recursos, logística y capital humano.</p>
                @endif
            </div>

            @guest
                <section class="max-w-md mx-auto">
                    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden relative group hover:shadow-2xl transition-all duration-300">
                        <div class="h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-500"></div>
                        
                        <div class="p-8 sm:p-10 text-center">
                            <div class="w-20 h-20 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-sm border border-indigo-100">
                                <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                            </div>
                            
                            <h2 class="text-2xl font-bold text-slate-900 mb-2">Acceso al Sistema</h2>
                            <p class="text-slate-500 text-sm mb-8 leading-relaxed">
                                Inicie sesión con sus credenciales corporativas para acceder a sus herramientas de trabajo.
                            </p>

                            <div class="space-y-4">
                                <a href="{{ route('login') }}" class="flex items-center justify-center w-full px-6 py-3.5 bg-indigo-600 text-white font-bold text-sm rounded-xl hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-100 transition-all shadow-lg shadow-indigo-200 hover:-translate-y-0.5">
                                    Iniciar Sesión
                                </a>
                                <a href="{{ route('register') }}" class="flex items-center justify-center w-full px-6 py-3.5 bg-white text-slate-700 font-bold text-sm rounded-xl border border-slate-200 hover:bg-slate-50 hover:border-slate-300 focus:ring-4 focus:ring-slate-100 transition-all">
                                    Solicitar Acceso
                                </a>
                            </div>
                        </div>
                        <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 text-center">
                            <p class="text-xs text-slate-400">Acceso restringido a personal autorizado.</p>
                        </div>
                    </div>

                    <div class="text-center mt-8">
                        <a href="mailto:soporte@estrategiaeinnovacion.com.mx" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3.063h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            ¿Problemas para ingresar? Contactar Soporte
                        </a>
                    </div>
                </section>
            @endguest

            @auth
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @php
                        $cards = [
                            [
                                'title' => 'Soporte de Software',
                                'desc' => 'Errores en aplicaciones, instalaciones o acceso a sistemas.',
                                'route' => route('tickets.create', 'software'),
                                'cta' => 'Crear Reporte',
                                'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
                                'color' => 'indigo'
                            ],
                            [
                                'title' => 'Mantenimiento',
                                'desc' => 'Solicitud de limpieza preventiva y revisión de equipos.',
                                'route' => route('tickets.create', 'mantenimiento'),
                                'cta' => 'Agendar Cita',
                                'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                                'color' => 'blue' // Mantenemos variación sutil
                            ],
                            [
                                'title' => 'Fallas de Hardware',
                                'desc' => 'Problemas físicos con computadoras, impresoras o redes.',
                                'route' => route('tickets.create', 'hardware'),
                                'cta' => 'Reportar Avería',
                                'icon' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
                                'color' => 'slate'
                            ],
                        ];
                    @endphp

                    @foreach($cards as $card)
                        <a href="{{ $card['route'] }}" class="group relative bg-white rounded-3xl p-8 shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col h-full">
                            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity text-{{ $card['color'] }}-600">
                                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"></path></svg>
                            </div>

                            <div class="relative z-10 flex-1">
                                <div class="w-14 h-14 rounded-2xl bg-{{ $card['color'] }}-50 text-{{ $card['color'] }}-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 border border-{{ $card['color'] }}-100">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"></path></svg>
                                </div>
                                <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-{{ $card['color'] }}-600 transition-colors">
                                    {{ $card['title'] }}
                                </h3>
                                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                                    {{ $card['desc'] }}
                                </p>
                            </div>
                            
                            <div class="relative z-10 mt-auto pt-4 border-t border-slate-100 flex items-center text-{{ $card['color'] }}-600 font-bold text-sm">
                                {{ $card['cta'] }} <span class="ml-2 group-hover:translate-x-1 transition-transform">→</span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-16 text-center">
                    <p class="text-slate-400 text-sm mb-4">¿Necesitas asistencia inmediata?</p>
                    <a href="mailto:soporte@estrategiaeinnovacion.com.mx" class="inline-flex items-center px-6 py-3 bg-white border border-slate-200 rounded-full text-slate-600 text-sm font-semibold hover:bg-slate-50 hover:text-indigo-600 transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        Contactar Soporte IT
                    </a>
                </div>
            @endauth

        </div>
    </main>

    <footer class="bg-white border-t border-slate-100 py-8">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-slate-400 text-xs">
                &copy; {{ date('Y') }} Estrategia e Innovación. Todos los derechos reservados. <br>
                <span class="text-slate-300">v2.0.0 Enterprise Edition</span>
            </p>
        </div>
    </footer>
@endsection