@extends('layouts.master')

@section('title', 'Mis Tickets - Sistema IT')

@section('content')
    <main class="relative min-h-screen overflow-hidden bg-gradient-to-br from-white via-blue-50 to-blue-100" data-my-tickets>
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-40 -left-28 h-[26rem] w-[26rem] rounded-full bg-blue-200/50 blur-3xl"></div>
            <div class="absolute top-1/3 -right-24 h-80 w-80 rounded-full bg-blue-300/30 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/2 h-40 w-full -translate-x-1/2 bg-gradient-to-t from-white"></div>
        </div>

        <div class="relative max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-10">
            @if(session('success'))
                <div class="mb-6 rounded-2xl border-l-4 border-green-500 bg-white/70 p-4 shadow-md backdrop-blur">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 text-green-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </span>
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-6 rounded-2xl border-l-4 border-blue-500 bg-white/70 p-4 shadow-md backdrop-blur">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </span>
                        <p class="text-sm font-medium text-blue-900">{{ session('info') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-2xl border-l-4 border-red-500 bg-white/70 p-4 shadow-md backdrop-blur">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </span>
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Hero -->
            <section class="relative overflow-hidden rounded-3xl border border-blue-100/70 bg-white/85 px-8 py-10 shadow-xl shadow-blue-500/10 backdrop-blur">
                <div class="absolute -right-24 -top-20 h-48 w-48 rounded-full bg-blue-200/50 blur-3xl"></div>
                <div class="absolute -left-20 bottom-0 h-36 w-36 rounded-full bg-blue-100/70 blur-3xl"></div>

                <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-4">
                        <div class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-4 py-1 text-sm font-semibold text-blue-700">
                            Mis tickets de soporte
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-bold text-slate-900">Centro personal de seguimiento</h1>
                        <p class="max-w-2xl text-base text-slate-600">
                            Revisa el estado de tus solicitudes, responde al equipo de TI y mantén una vista clara de las acciones recientes.
                        </p>
                        <div class="flex flex-wrap gap-3 text-sm text-slate-500">
                            <div class="flex items-center gap-2 rounded-2xl border border-blue-100 bg-white/80 px-3 py-2 shadow-sm">
                                <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                {{ auth()->user()->name }} · {{ auth()->user()->email }}
                            </div>
                            <div class="flex items-center gap-2 rounded-2xl border border-blue-100 bg-white/80 px-3 py-2 shadow-sm">
                                <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v13H4V8a1 1 0 011-1h3z"></path>
                                </svg>
                                {{ count($tickets) }} ticket(s) registrados
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 rounded-3xl border border-blue-100/80 bg-gradient-to-br from-blue-50/80 via-white to-blue-50/60 px-6 py-5 shadow-inner backdrop-blur lg:max-w-sm">
                        <h2 class="text-lg font-semibold text-slate-900">Resumen rápido</h2>
                        <div class="flex items-center justify-between text-sm text-slate-600">
                            <span>Actualizaciones pendientes</span>
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">{{ $notificationsCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-slate-600">
                            <span>Tickets abiertos</span>
                            <span class="font-semibold text-blue-600">{{ $openTickets ?? $tickets->where('estado', '!=', 'cerrado')->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-slate-600">
                            <span>Última actualización</span>
                            <span class="font-semibold text-blue-600">{{ optional($tickets->max('updated_at'))->diffForHumans() ?? 'Sin registros' }}</span>
                        </div>
                        <div class="flex flex-col gap-2 pt-2 text-xs text-slate-500">
                            <span class="font-semibold text-slate-700">Consejo:</span>
                            <span>Marca como revisados los tickets con actualización para mantener la comunicación fluida.</span>
                        </div>
                    </div>
                </div>
            </section>

            @if(($notificationsCount ?? 0) > 0)
                <div class="mt-8 rounded-3xl border border-blue-200/60 bg-gradient-to-r from-blue-50/70 via-white to-blue-50/50 px-6 py-5 shadow-inner">
                    <div class="flex items-start gap-4">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-blue-800">Tienes {{ $notificationsCount }} ticket(s) con nuevas actualizaciones del equipo de TI.</p>
                            <p class="mt-1 text-sm text-blue-600">Revisa los tickets marcados para conocer el progreso y confirmar que recibiste la información.</p>
                        </div>
                    </div>
                </div>
            @endif

            <section class="mt-10">
                @if(count($tickets) > 0)
                    <div class="overflow-hidden rounded-3xl border border-blue-100/70 bg-white/85 shadow-2xl shadow-blue-500/10 backdrop-blur">
                        <div class="border-b border-blue-100/70 bg-gradient-to-r from-blue-50/80 to-white px-8 py-6">
                            <h2 class="text-xl font-semibold text-slate-900">Mis tickets registrados</h2>
                            <p class="text-sm text-slate-500">{{ count($tickets) }} ticket(s) encontrados</p>
                        </div>

                        <div class="divide-y divide-blue-100/70">
                            @foreach($tickets as $ticket)
                                <article id="ticket-{{ $ticket->id }}" class="relative px-8 py-6 transition duration-200 {{ $ticket->user_has_updates ? 'bg-blue-50/80 hover:bg-blue-100/70 border-l-4 border-blue-400' : 'hover:bg-slate-50' }}">
                                    <div class="flex flex-col gap-6 lg:flex-row lg:justify-between">
                                        <div class="flex-1 space-y-4">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="text-lg font-semibold text-blue-600">{{ $ticket->folio }}</span>
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $ticket->estado_badge }}">
                                                    {{ ucfirst(str_replace('_', ' ', $ticket->estado)) }}
                                                </span>
                                                @if($ticket->closed_by_user)
                                                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                                        Cancelado por ti
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $ticket->tipo_problema === 'software' ? 'bg-blue-100 text-blue-700' : ($ticket->tipo_problema === 'hardware' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700') }}">
                                                    {{ ucfirst($ticket->tipo_problema) }}
                                                </span>
                                                @if($ticket->user_has_updates)
                                                    <span class="inline-flex items-center rounded-full bg-blue-600 px-3 py-1 text-xs font-semibold text-white shadow-sm">Actualización</span>
                                                @endif
                                            </div>

                                            @if($ticket->nombre_programa)
                                                @php
                                                    $programLabel = match ($ticket->tipo_problema) {
                                                        'hardware' => 'Tipo de equipo',
                                                        'software' => 'Programa',
                                                        default => 'Programa/Equipo',
                                                    };
                                                @endphp
                                                <p class="text-sm text-slate-600"><strong>{{ $programLabel }}:</strong> {{ $ticket->nombre_programa }}</p>
                                            @endif

                                            <p class="text-sm leading-relaxed text-slate-700">{{ Str::limit($ticket->descripcion_problema, 160) }}</p>

                                            @if($ticket->tipo_problema === 'mantenimiento')
                                                <div class="space-y-2 rounded-2xl border border-green-100 bg-green-50/60 px-4 py-3 text-sm text-green-800">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-semibold text-green-900">Fecha programada:</span>
                                                        {{ optional($ticket->maintenance_scheduled_at)->format('d/m/Y H:i') ?? 'Por asignar' }}
                                                    </div>
                                                    @if($ticket->maintenance_details)
                                                        <p><strong>Detalles del equipo:</strong> {{ $ticket->maintenance_details }}</p>
                                                    @endif
                                                </div>
                                            @endif

                                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500">
                                                <span>📅 {{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                                                @if($ticket->prioridad)
                                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $ticket->prioridad_badge }}">
                                                        Prioridad: {{ ucfirst($ticket->prioridad) }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($ticket->observaciones)
                                                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                                    <strong>Observaciones:</strong> {{ $ticket->observaciones }}
                                                </div>
                                            @endif

                                            @if($ticket->user_notification_summary)
                                                <div class="rounded-3xl border {{ $ticket->user_has_updates ? 'border-blue-300 bg-blue-50/70' : 'border-slate-200 bg-slate-50' }} px-5 py-4">
                                                    <div class="flex items-start gap-3">
                                                        <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <div class="flex-1 space-y-2 text-sm text-slate-700">
                                                            <p class="leading-relaxed">{{ $ticket->user_notification_summary }}</p>
                                                            @if($ticket->user_notified_at)
                                                                <p class="text-xs text-slate-500">Actualizado {{ $ticket->user_notified_at->diffForHumans() }}</p>
                                                            @endif
                                                            @if($ticket->user_has_updates)
                                                                <form method="POST" action="{{ route('tickets.acknowledge', $ticket) }}">
                                                                    @csrf
                                                                    <button type="submit" class="inline-flex items-center rounded-2xl bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:from-blue-700 hover:to-blue-800">
                                                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                        </svg>
                                                                        Marcar como revisado
                                                                    </button>
                                                                </form>
                                                            @elseif($ticket->user_last_read_at)
                                                                <p class="text-xs text-slate-500">Última revisión: {{ $ticket->user_last_read_at->diffForHumans() }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($ticket->imagenes && count($ticket->imagenes) > 0)
                                                <div class="space-y-3">
                                                    <p class="text-sm font-semibold text-slate-700">Imágenes adjuntas:</p>
                                                    <div class="flex flex-wrap gap-3">
                                                        @foreach($ticket->imagenes as $index => $imagen)
                                                            @php
                                                                $imageSrc = '';
                                                                if (is_array($imagen) && isset($imagen['data'])) {
                                                                    $imageSrc = 'data:' . ($imagen['mime'] ?? 'image/jpeg') . ';base64,' . $imagen['data'];
                                                                } elseif (is_string($imagen)) {
                                                                    $imageSrc = $imagen;
                                                                }
                                                            @endphp

                                                            @if($imageSrc)
                                                                <div class="group relative overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm">
                                                                    <img src="{{ $imageSrc }}" alt="Imagen del ticket {{ $index + 1 }}" class="h-28 w-28 cursor-pointer object-cover transition group-hover:scale-105" onclick="expandImage(this)">
                                                                    <div class="absolute inset-0 flex items-center justify-center bg-blue-600/0 transition group-hover:bg-blue-900/40">
                                                                        <svg class="h-6 w-6 text-white opacity-0 transition group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="flex h-28 w-28 items-center justify-center rounded-2xl border border-red-200 bg-red-50">
                                                                    <div class="text-center text-red-500">
                                                                        <svg class="mx-auto h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                        </svg>
                                                                        <span class="mt-1 block text-xs">Error</span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex w-full flex-col gap-3 lg:max-w-xs">
                                            @if(($supportEmail ?? null) || ($supportTeamsUrl ?? null))
                                                <div class="rounded-3xl border border-blue-100 bg-white/80 p-4 shadow-sm">
                                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Contacto rápido</p>
                                                    <div class="mt-3 flex flex-col gap-2">
                                                        @if(!empty($supportEmail))
                                                            <a href="mailto:{{ $supportEmail }}?subject={{ urlencode('Consulta sobre el ticket ' . $ticket->folio) }}"
                                                               class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:from-blue-700 hover:to-blue-800">
                                                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H8m8 0l-3-3m3 3l-3 3m7-6V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2v-1"></path>
                                                                </svg>
                                                                Contactar por correo
                                                            </a>
                                                        @endif
                                                        @if(!empty($supportTeamsUrl))
                                                            <a href="{{ $supportTeamsUrl }}"
                                                               target="_blank"
                                                               rel="noopener noreferrer"
                                                               class="inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50">
                                                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                                Contactar por Teams (urgente)
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif

                                            @if($ticket->estado !== 'cerrado')
                                                <button type="button"
                                                        data-cancel-ticket
                                                        data-ticket-id="{{ $ticket->id }}"
                                                        data-ticket-folio="{{ $ticket->folio }}"
                                                        class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 transition hover:border-red-300 hover:bg-red-100">
                                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Cancelar ticket
                                                </button>
                                            @else
                                                <span class="text-xs italic text-slate-500">
                                                    @if($ticket->closed_by_user)
                                                        Ticket cancelado por ti
                                                    @else
                                                        Ticket cerrado
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="rounded-3xl border border-blue-100/70 bg-white/85 px-10 py-16 text-center shadow-xl shadow-blue-500/10 backdrop-blur">
                        <svg class="mx-auto h-16 w-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-6 text-2xl font-semibold text-slate-900">No tienes tickets registrados</h3>
                        <p class="mt-2 text-sm text-slate-500">Aún no has creado ningún ticket. ¡Crea tu primer ticket ahora!</p>
                        <a href="{{ route('welcome') }}" class="mt-6 inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:from-blue-700 hover:to-blue-800">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Crear primer ticket
                        </a>
                    </div>
                @endif
            </section>
        </div>
    </main>

    <footer class="bg-white border-t border-blue-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 py-6">
            <p class="text-center text-sm text-slate-500">&copy; {{ date('Y') }} Sistema de Tickets TI. Todos los derechos reservados.</p>
        </div>
    </footer>

    <div id="cancelModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="w-full max-w-md transform overflow-hidden rounded-3xl border border-blue-100 bg-white shadow-2xl">
            <div class="px-6 py-6">
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.64 5.64l12.72 12.72M7.757 17.657A8 8 0 017.757 6.343 8 8 0 0116.243 6.343a8 8 0 010 11.314 8 8 0 01-8.486 0z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Confirmar cancelación</p>
                        <h3 class="text-lg font-bold text-slate-900">Cancelar ticket</h3>
                    </div>
                </div>
                <p id="cancelModalMessage" class="text-sm leading-relaxed text-slate-600"></p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button type="button" id="cancelModalClose" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">Cerrar</button>
                <button type="button" id="cancelModalConfirm" class="rounded-2xl bg-gradient-to-r from-red-500 to-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:from-red-600 hover:to-red-700">Aceptar</button>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/Sistemas_IT/tickets-my.js')
    @endpush
@endsection
