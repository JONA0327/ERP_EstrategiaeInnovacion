@extends('layouts.erp')

@section('title', 'Recursos Humanos - Portal Interno')

@section('content')
    @vite(['resources/css/Recursos_Humanos/index.css','resources/js/Recursos_Humanos/index.js'])
    <main class="max-w-5xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
        <div class="bg-white/90 backdrop-blur border border-blue-100 rounded-3xl shadow-lg p-12 text-center">
            <h1 class="text-4xl font-bold text-slate-900 mb-4">Administración de Recursos Humanos</h1>
            <p class="text-slate-600 max-w-2xl mx-auto mb-8">Espacio dedicado a futuras herramientas de gestión de personal, directorio interno y procesos administrativos del área. Utiliza el enlace "Soporte Técnico" en la barra superior para acceder al sistema de tickets corporativo.</p>
            <div class="mt-10">
                <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 border border-blue-100 px-4 py-2 text-sm font-medium text-blue-700">Módulos en desarrollo</span>
            </div>
        </div>
    </main>
@endsection
