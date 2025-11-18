@extends('layouts.master')

@section('title', 'Gestión de Tickets - Panel Administrativo')

@section('content')
        <!-- Main Content -->
        <main class="relative overflow-hidden bg-gradient-to-br from-white via-blue-50 to-blue-100">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-28 -left-24 w-80 h-80 bg-blue-200/40 blur-3xl rounded-full"></div>
                <div class="absolute top-40 -right-24 w-96 h-96 bg-blue-300/40 blur-3xl rounded-full"></div>
                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-32 bg-gradient-to-t from-white"></div>
            </div>

            <div class="relative max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-10">
                <!-- Success Message -->
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

                <!-- Header Section -->
                <div class="relative overflow-hidden rounded-3xl border border-blue-200/60 bg-white/90 backdrop-blur shadow-xl shadow-blue-500/10 p-8 mb-10">
                    <div class="absolute -top-24 -right-16 w-40 h-40 bg-gradient-to-br from-blue-200/60 to-transparent blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-10 w-52 h-52 bg-gradient-to-tr from-blue-100/80 to-transparent blur-3xl"></div>
                    <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="inline-flex items-center rounded-full bg-blue-50 border border-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 mb-4">
                                Panel Administrativo
                            </div>
                            <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">Gestión de Tickets</h1>
                            <p class="mt-2 text-sm sm:text-base text-slate-600 max-w-2xl leading-relaxed">
                                Administra, organiza y da seguimiento a cada solicitud para ofrecer una atención oportuna a todos los usuarios.
                            </p>
                        </div>
                        <div class="self-start lg:self-center">
                            <div class="rounded-2xl border border-blue-100 bg-white/80 px-6 py-5 shadow-inner shadow-blue-100/40">
                                <p class="text-xs font-semibold uppercase tracking-wide text-blue-500">Total de tickets</p>
                                <p class="mt-1 text-3xl font-bold text-blue-700">{{ $tickets->total() }}</p>
                                <p class="text-xs text-slate-500 mt-1">Incluye todos los estados y tipos registrados</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-6 mb-12">
                    @php
                        $abiertos = $tickets->where('estado', 'abierto')->count();
                        $enProceso = $tickets->where('estado', 'en_proceso')->count();
                        $cerrados = $tickets->where('estado', 'cerrado')->count();
                        $software = $tickets->where('tipo_problema', 'software')->count();
                        $hardware = $tickets->where('tipo_problema', 'hardware')->count();
                        $mantenimiento = $tickets->where('tipo_problema', 'mantenimiento')->count();
                    @endphp

                <div class="relative overflow-hidden rounded-2xl border border-red-100/60 bg-white/90 backdrop-blur px-6 py-6 shadow-lg shadow-red-200/30">
                    <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full bg-red-100/60 blur-2xl"></div>
                    <div class="relative flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-red-100 to-red-200 text-red-600 shadow-inner shadow-white/60">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-slate-500">Abiertos</p>
                                <p class="text-2xl font-bold text-slate-900">{{ $abiertos }}</p>
                                <p class="text-xs text-slate-400 mt-1">Requieren atención inmediata</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-amber-100/60 bg-white/90 backdrop-blur px-6 py-6 shadow-lg shadow-amber-200/30">
                    <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full bg-amber-100/60 blur-2xl"></div>
                    <div class="relative flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-100 to-amber-200 text-amber-600 shadow-inner shadow-white/60">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-slate-500">En proceso</p>
                                <p class="text-2xl font-bold text-slate-900">{{ $enProceso }}</p>
                                <p class="text-xs text-slate-400 mt-1">Seguimiento activo por el equipo</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-emerald-100/60 bg-white/90 backdrop-blur px-6 py-6 shadow-lg shadow-emerald-200/30">
                    <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full bg-emerald-100/60 blur-2xl"></div>
                    <div class="relative flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-100 to-emerald-200 text-emerald-600 shadow-inner shadow-white/60">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-slate-500">Cerrados</p>
                                <p class="text-2xl font-bold text-slate-900">{{ $cerrados }}</p>
                                <p class="text-xs text-slate-400 mt-1">Finalizados y documentados</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-blue-100/60 bg-white/90 backdrop-blur px-6 py-6 shadow-lg shadow-blue-200/30">
                    <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full bg-blue-100/60 blur-2xl"></div>
                    <div class="relative flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-100 to-blue-200 text-blue-600 shadow-inner shadow-white/60">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-slate-500">Software</p>
                                <p class="text-2xl font-bold text-slate-900">{{ $software }}</p>
                                <p class="text-xs text-slate-400 mt-1">Incidentes relacionados con aplicaciones</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-orange-100/60 bg-white/90 backdrop-blur px-6 py-6 shadow-lg shadow-orange-200/30">
                    <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full bg-orange-100/60 blur-2xl"></div>
                    <div class="relative flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-orange-100 to-orange-200 text-orange-600 shadow-inner shadow-white/60">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a1 1 0 011-1h4a1 1 0 011 1v2m3 4H6a2 2 0 01-2-2V7a2 2 0 012-2h3l2-2h2l2 2h3a2 2 0 012 2v12a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-slate-500">Hardware</p>
                                <p class="text-2xl font-bold text-slate-900">{{ $hardware }}</p>
                                <p class="text-xs text-slate-400 mt-1">Reportes de equipos físicos</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-emerald-100/60 bg-white/90 backdrop-blur px-6 py-6 shadow-lg shadow-emerald-200/30">
                    <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full bg-emerald-100/60 blur-2xl"></div>
                    <div class="relative flex flex-col gap-4">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-100 to-emerald-200 text-emerald-600 shadow-inner shadow-white/60">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v9a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h3z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-slate-500">Mantenimiento</p>
                                <p class="text-2xl font-bold text-slate-900">{{ $mantenimiento }}</p>
                                <p class="text-xs text-slate-400 mt-1">Visitas programadas y preventivas</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.maintenance.index') }}" class="inline-flex items-center text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                            Configurar horarios
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                </div>

                <!-- Tickets Table -->
                <div class="relative overflow-hidden rounded-3xl border border-blue-200/70 bg-white/90 backdrop-blur shadow-2xl shadow-blue-500/10">
                    <div class="absolute inset-x-0 top-0 h-16 bg-gradient-to-b from-blue-100/50 to-transparent"></div>
                    <div class="relative px-6 py-6 border-b border-blue-100/80">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-500">Monitoreo</p>
                                <h2 class="text-xl font-bold text-slate-900">Lista de Tickets</h2>
                            </div>
                            <div class="inline-flex items-center rounded-full border border-blue-100 bg-blue-50/60 px-4 py-1 text-xs font-semibold text-blue-700">
                                Actualizado al {{ now()->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>

                    <div class="relative overflow-x-auto">
                        <table class="min-w-full divide-y divide-blue-100">
                            <thead class="bg-blue-50/60">
                                <tr>
                                    <th class="px-6 py-3 text-left text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">Folio</th>
                                    <th class="px-6 py-3 text-left text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">Solicitante</th>
                                    <th class="px-6 py-3 text-left text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">Tipo</th>
                                    <th class="px-6 py-3 text-left text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                                    <th class="px-6 py-3 text-left text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">Prioridad</th>
                                    <th class="px-6 py-3 text-left text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">Fecha</th>
                                    <th class="px-6 py-3 text-left text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white/80 divide-y divide-blue-50">
                            @forelse($tickets as $ticket)
                            <tr class="transition-colors duration-200 hover:bg-blue-50/60">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-blue-600">{{ $ticket->folio }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">{{ $ticket->nombre_solicitante }}</div>
                                    <div class="text-xs text-slate-500">{{ $ticket->correo_solicitante }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($ticket->tipo_problema === 'software') bg-blue-100 text-blue-800
                                        @elseif($ticket->tipo_problema === 'hardware') bg-orange-100 text-orange-800
                                        @else bg-green-100 text-green-800 @endif">
                                        {{ ucfirst($ticket->tipo_problema) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->estado_badge }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->estado)) }}
                                    </span>
                                    @if($ticket->closed_by_user)
                                        <p class="text-xs text-red-600 mt-1 font-medium">Cancelado por el usuario</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($ticket->prioridad)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->prioridad_badge }}">
                                            {{ ucfirst($ticket->prioridad) }}
                                        </span>
                                @else
                                        <span class="text-slate-400 text-sm">No asignada</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    {{ $ticket->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.tickets.show', $ticket) }}"
                                           class="inline-flex items-center justify-center rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-blue-600 transition-colors duration-200 hover:border-blue-200 hover:bg-blue-100 hover:text-blue-700">
                                            Ver Detalles
                                        </a>
                                        <form method="POST" action="{{ route('tickets.destroy', $ticket) }}"
                                              onsubmit="return confirm('¿Estás seguro de que deseas eliminar este ticket? Esta acción no se puede deshacer.')"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center justify-center rounded-md border border-red-100 bg-red-50 px-3 py-1 text-red-600 transition-colors duration-200 hover:border-red-200 hover:bg-red-100 hover:text-red-700">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="mb-4 w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-slate-600 text-base font-medium">No hay tickets registrados</p>
                                        <p class="text-slate-400 text-sm">Los tickets aparecerán aquí cuando los usuarios los creen</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($tickets->hasPages())
                <div class="bg-white/80 px-4 py-4 border-t border-blue-100 sm:px-6">
                    {{ $tickets->links() }}
                </div>
                @endif
            </div>
        </main>
@endsection