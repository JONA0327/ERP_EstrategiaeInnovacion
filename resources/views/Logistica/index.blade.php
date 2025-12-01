@extends('layouts.erp')

@section('title', 'Logística - Portal Interno')

@section('content')
    @vite(['resources/css/Logistica/index.css','resources/js/Logistica/index.js'])
    <main class="relative overflow-hidden bg-gradient-to-br from-white via-blue-50 to-blue-100">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-32 -left-20 w-96 h-96 bg-blue-200/40 blur-3xl rounded-full"></div>
            <div class="absolute top-40 -right-24 w-96 h-96 bg-blue-300/30 blur-3xl rounded-full"></div>
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-32 bg-gradient-to-t from-white"></div>
        </div>

        <div class="relative max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-10">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-slate-900">Administración Logística</h1>
                <p class="mx-auto mt-2 max-w-2xl text-sm text-slate-600">Accede a los módulos del área.</p>
            </div>

            @php
                $cards = [
                    [
                        'title' => 'Matriz de seguimiento',
                        'description' => 'Gestiona las operaciones del área logística.',
                        'href' => route('logistica.matriz-seguimiento'),
                        'cta' => 'Abrir módulo',
                        'status' => 'Disponible',
                        'icon' => 'M3 7V5a2 2 0 012-2h9a2 2 0 012 2v2M3 7v10a2 2 0 002 2h9a2 2 0 002-2V7M3 7h13M8 9h4m-4 4h4m-4 4h4',
                    ],
                    [
                        'title' => 'Catálogos',
                        'description' => 'Administra clientes, agentes aduanales, transportes y ejecutivos del área logística.',
                        'href' => route('logistica.catalogos'),
                        'cta' => 'Administrar catálogos',
                        'status' => 'Disponible',
                        'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                    ],
                    [
                        'title' => 'Evaluación de Desempeño',
                        'description' => 'Monitorea el rendimiento del equipo y evalúa el cumplimiento de metas logísticas.',
                        'href' => route('logistica.index') . '#evaluacion-desempeno',
                        'cta' => 'En construcción',
                        'status' => 'Próximamente',
                        'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    ],
                ];
            @endphp

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                @foreach($cards as $card)
                    <div class="relative overflow-hidden rounded-3xl border border-blue-100/80 bg-white/90 backdrop-blur shadow-lg shadow-blue-500/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                        <div class="absolute -top-20 -right-16 w-40 h-40 bg-gradient-to-br from-blue-200/50 to-transparent blur-3xl"></div>
                        <div class="relative p-8">
                            <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-100 to-blue-200 text-blue-600 shadow-inner shadow-white/40 mx-auto mb-6">
                                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-center text-slate-900 mb-3">{{ $card['title'] }}</h3>
                            <p class="text-center text-slate-600 leading-relaxed mb-8">{{ $card['description'] }}</p>
                            <a href="{{ $card['href'] }}" class="group inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition-all duration-300 hover:from-blue-700 hover:to-blue-800">
                                <svg class="w-5 h-5 mr-2 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                {{ $card['cta'] }}
                            </a>
                            <p class="mt-3 text-center text-[11px] text-slate-400">{{ $card['status'] ?? 'En construcción' }}</p>
                        </div>
                    </div>
                @endforeach
                    <div class="relative overflow-hidden rounded-3xl border border-blue-100/80 bg-white/90 backdrop-blur shadow-lg shadow-blue-500/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                        <div class="absolute -top-20 -right-16 w-40 h-40 bg-gradient-to-br from-blue-200/50 to-transparent blur-3xl"></div>
                        <div class="relative p-8">
                            <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-100 to-blue-200 text-blue-600 shadow-inner shadow-white/40 mx-auto mb-6">
                                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-center text-slate-900 mb-3">Reportes</h3>
                            <p class="text-center text-slate-600 leading-relaxed mb-8">Descarga CSV y gráfico de status</p>
                            <a href="{{ route('logistica.reportes') }}" class="group inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition-all duration-300 hover:from-blue-700 hover:to-blue-800">
                                <svg class="w-5 h-5 mr-2 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Abrir módulo
                            </a>
                            <p class="mt-3 text-center text-[11px] text-slate-400">Disponible</p>
                        </div>
                    </div>
            </div>
        </div>
    </main>
@endsection
