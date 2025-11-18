@extends('layouts.master')

@section('title', 'Panel de Administración - Sistema IT')

@section('header')
<!-- Back to Home Button -->
@if (!Auth::user()->isAdmin())
<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex items-center justify-center sm:justify-start">
            <a href="{{ route('welcome') }}"
               class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-200 group">
                <svg class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Volver al Portal de Tickets
            </a>
        </div>
    </div>
</div>
@endif
@endsection

@section('content')
    <main class="relative min-h-screen overflow-hidden bg-gradient-to-br from-white via-blue-50 to-blue-100">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-32 -left-24 h-80 w-80 rounded-full bg-blue-200/50 blur-3xl"></div>
            <div class="absolute top-32 -right-24 h-72 w-72 rounded-full bg-blue-300/40 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/2 h-40 w-full -translate-x-1/2 bg-gradient-to-t from-white"></div>
        </div>

        <div class="relative max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-10">
            <!-- Hero Section -->
            <section class="relative overflow-hidden rounded-3xl border border-blue-200/60 bg-white/85 px-8 py-10 shadow-xl shadow-blue-500/10 backdrop-blur">
                <div class="absolute -right-24 -top-20 h-48 w-48 rounded-full bg-blue-200/50 blur-3xl"></div>
                <div class="absolute -left-16 bottom-0 h-36 w-36 rounded-full bg-blue-100/70 blur-3xl"></div>

                <div class="relative flex flex-col items-center gap-6 text-center sm:flex-row sm:items-center sm:text-left sm:justify-between">
                    <div class="flex items-center justify-center h-20 w-20 rounded-3xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/40">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 7a2 2 0 012-2h12a2 2 0 012 2m-2 0v10a2 2 0 01-2 2H8l-4 4V7z" />
                        </svg>
                    </div>
                    <div class="flex-1 space-y-3">
                        <div class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-4 py-1 text-sm font-semibold text-blue-700">
                            Panel de Administración
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 leading-tight">
                            Elige la acción que necesitas gestionar
                        </h1>
                        <p class="text-sm sm:text-base text-slate-600 max-w-xl">
                            Accede rápidamente a las herramientas administrativas del sistema.
                        </p>
                    </div>
                </div>
            </section>

            @php
                $adminCards = [
                    [
                        'title' => 'Tickets de soporte',
                        'description' => 'Revisa, asigna y actualiza los tickets registrados.',
                        'route' => route('admin.tickets.index'),
                        'cta' => 'Gestionar tickets',
                        'icon' => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V9.414a2 2 0 00-.586-1.414L13 4.586A2 2 0 0011.586 4H11',
                        'accent' => 'from-emerald-500 to-emerald-600',
                    ],
                    [
                        'title' => 'Usuarios',
                        'description' => 'Administra cuentas y permisos del personal.',
                        'route' => route('admin.users'),
                        'cta' => 'Gestionar usuarios',
                        'icon' => 'M16 14a4 4 0 10-8 0m8 0v5H8v-5m8 0h3a2 2 0 002-2V7a2 2 0 00-2-2h-1M8 14H5a2 2 0 01-2-2V7a2 2 0 002-2h1',
                        'accent' => 'from-sky-500 to-sky-600',
                    ],
                ];
            @endphp

            <!-- Admin Cards Grid -->
            <section class="mt-12">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($adminCards as $card)
                        <article class="relative overflow-hidden rounded-3xl border border-blue-100/70 bg-white/85 p-8 shadow-lg shadow-blue-500/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl backdrop-blur">
                            <div class="absolute -right-20 -top-16 h-36 w-36 rounded-full bg-blue-100/50 blur-3xl"></div>
                            <div class="absolute -left-16 bottom-0 h-32 w-32 rounded-full bg-blue-50/80 blur-3xl"></div>

                            <div class="relative flex flex-col items-center text-center sm:items-start sm:text-left">
                                <span class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br {{ $card['accent'] }} text-white shadow-lg shadow-blue-500/30">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}" />
                                    </svg>
                                </span>
                                <h2 class="text-xl font-semibold text-slate-900">{{ $card['title'] }}</h2>
                                <p class="mt-2 text-sm text-slate-600">{{ $card['description'] }}</p>
                                <a
                                    href="{{ $card['route'] }}"
                                    class="group mt-6 inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r {{ $card['accent'] }} px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition-all duration-300 hover:brightness-110"
                                >
                                    <svg class="mr-2 h-5 w-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                    {{ $card['cta'] }}
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-blue-100">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="text-center text-slate-500 text-sm">
                <p>&copy; {{ date('Y') }} Panel de Administración - Sistema IT. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Notification System JavaScript -->
@endsection
