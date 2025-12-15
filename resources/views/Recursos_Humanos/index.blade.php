<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Recursos Humanos') }}
        </h2>
    </x-slot>

    @section('title', 'Recursos Humanos')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Mensaje de bienvenida --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-2">Bienvenido al Módulo de RH</h3>
                    <p class="text-gray-600">Gestione expedientes, control de asistencia y evaluaciones desde este panel.</p>
                </div>
            </div>

            {{-- Grid de accesos rápidos --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Card: Expedientes -->
                <a href="{{ route('rh.expedientes.index') }}" class="block p-6 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .883.393 1.627 1 2.188m-4.546.364l-3.364-1.591m12.728 0l-3.364 1.591" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-800">Expedientes</h4>
                            <p class="text-sm text-gray-500">Gestión de personal</p>
                        </div>
                    </div>
                </a>

                <!-- Card: Reloj Checador -->
                <a href="{{ route('rh.reloj.index') }}" class="block p-6 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-800">Reloj Checador</h4>
                            <p class="text-sm text-gray-500">Control de asistencia</p>
                        </div>
                    </div>
                </a>

                <!-- Card: Evaluación de Desempeño (NUEVA) -->
                <a href="{{ route('rh.evaluacion.index') }}" class="block p-6 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border-l-4 border-indigo-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-800">Evaluación</h4>
                            <p class="text-sm text-gray-500">Desempeño y objetivos</p>
                        </div>
                    </div>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>