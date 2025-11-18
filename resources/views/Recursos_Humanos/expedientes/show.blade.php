@extends('layouts.erp')
@section('title','Ver Expediente')
@section('content')
<main class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-bold text-slate-900">Expediente: {{ $empleado->nombre }}</h1>
        <a href="{{ route('rh.expedientes.index') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">← Volver</a>
    </div>
    <div class="rounded-3xl border border-blue-100 bg-white/90 backdrop-blur shadow p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">Nombre</p>
                <p class="text-sm font-semibold text-slate-800">{{ $empleado->nombre }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">Correo</p>
                <p class="text-sm text-slate-700">{{ $empleado->correo }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">Área</p>
                <p class="text-sm text-slate-700">{{ $empleado->area ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">ID Empleado</p>
                <p class="text-sm text-slate-700">{{ $empleado->id_empleado ?? '—' }}</p>
            </div>
        </div>
        <div class="pt-4 border-t border-slate-100 text-right">
            <a href="{{ route('rh.expedientes.edit',$empleado) }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2 text-sm font-semibold text-white shadow hover:from-blue-700 hover:to-blue-800">Editar</a>
        </div>
    </div>
</main>
@endsection