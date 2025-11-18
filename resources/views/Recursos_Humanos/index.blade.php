@extends('layouts.master')

@section('title', 'Recursos Humanos - Portal Interno')

@section('content')
    @vite(['resources/css/Recursos_Humanos/index.css','resources/js/Recursos_Humanos/index.js'])
    <main class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white/90 backdrop-blur border border-blue-100 rounded-3xl shadow-lg p-10">
            <h1 class="text-3xl font-bold text-slate-900 mb-4">Recursos Humanos</h1>
            <p class="text-slate-600 mb-6">Área preparada para futuras funcionalidades (gestión de personal, solicitudes, documentación). Este es un placeholder inicial.</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-blue-100 bg-blue-50/40 p-5">
                    <h2 class="text-sm font-semibold text-blue-700 mb-2">Próximas secciones</h2>
                    <ul class="text-xs text-slate-600 space-y-1 list-disc list-inside">
                        <li>Gestión de empleados</li>
                        <li>Solicitudes de vacaciones</li>
                        <li>Documentación interna</li>
                    </ul>
                </div>
                <div class="rounded-xl border border-green-100 bg-green-50/40 p-5">
                    <h2 class="text-sm font-semibold text-green-700 mb-2">Estado</h2>
                    <p class="text-xs text-slate-600">Plantilla creada en <code>resources/views/Recursos_Humanos/index.blade.php</code>.</p>
                </div>
            </div>
        </div>
    </main>
@endsection
