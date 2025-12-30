@extends('layouts.erp')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">
    <div class="mb-4">
        <a href="{{ route('capacitacion.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
            &larr; Volver a Capacitaciones
        </a>
    </div>

    <div class="bg-black rounded-xl overflow-hidden shadow-2xl">
        <video controls class="w-full aspect-video" controlsList="nodownload">
            <source src="{{ asset('storage/' . $video->archivo_path) }}" type="video/mp4">
            Tu navegador no soporta la reproducción de video.
        </video>
    </div>

    <div class="mt-6 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <h1 class="text-2xl font-bold text-gray-900">{{ $video->titulo }}</h1>
        <div class="mt-2 text-sm text-gray-500">
            Publicado el {{ $video->created_at->format('d/m/Y') }}
        </div>
        <hr class="my-4 border-gray-100">
        <div class="prose max-w-none text-gray-700">
            {{ $video->descripcion }}
        </div>
    </div>
</div>
@endsection