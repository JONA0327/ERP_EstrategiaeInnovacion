<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Evaluaciones de Desempeño') }}
        </h2>
    </x-slot>

    @php
        // Definimos las categorías principales en el orden deseado
        $categoriasPrincipales = ['Logistica', 'Legal', 'Pedimentos', 'Anexo 24', 'Auditoria', 'TI', 'Recursos Humanos'];
        
        // Obtenemos todos los puestos únicos de la BD para asegurar que no se oculte nadie
        $todosLosPuestos = $empleados->pluck('posicion')
            ->map(function($puesto) {
                // Normalizamos nombres compuestos o variaciones si es necesario, 
                // pero aquí tomamos el valor directo de la BD
                return $puesto;
            })
            ->unique()
            ->values()
            ->toArray();
        
        // Unimos las listas: primero las principales, luego el resto
        $todasLasCategorias = array_unique(array_merge($categoriasPrincipales, $todosLosPuestos));
    @endphp

    <div class="py-12" x-data="{ activeTab: 'Logistica' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Personal por Área</h3>
                    <p class="mt-1 text-sm text-gray-600">Seleccione un área para ver y gestionar las evaluaciones.</p>
                </div>
            </div>

            <!-- Navegación de Pestañas (Scroll horizontal habilitado) -->
            <div class="mb-6 border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 overflow-x-auto pb-1" aria-label="Tabs">
                    @foreach($todasLasCategorias as $categoria)
                        @if(!empty($categoria))
                            <button 
                                @click="activeTab = '{{ $categoria }}'"
                                :class="activeTab === '{{ $categoria }}' 
                                    ? 'border-indigo-500 text-indigo-600' 
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition duration-150 ease-in-out"
                            >
                                {{ $categoria }}
                            </button>
                        @endif
                    @endforeach
                </nav>
            </div>

            <!-- Contenido -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @foreach($todasLasCategorias as $categoria)
                        @if(!empty($categoria))
                            <div x-show="activeTab === '{{ $categoria }}'" 
                                 x-transition:enter="transition ease-out duration-300" 
                                 x-transition:enter-start="opacity-0 translate-y-2" 
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 style="display: none;">
                                
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-xl font-bold text-gray-800">Área: {{ $categoria }}</h4>
                                    <span class="bg-indigo-100 text-indigo-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-indigo-400">
                                        {{ $empleados->filter(fn($e) => str_contains($e->posicion, $categoria) || $e->posicion == $categoria)->count() }} Colaboradores
                                    </span>
                                </div>

                                <!-- Filtramos empleados que coincidan exactamente o contengan la palabra (ej. 'Logistica (Home Office)') -->
                                @php
                                    $empleadosCategoria = $empleados->filter(function($empleado) use ($categoria) {
                                        return $empleado->posicion === $categoria || str_contains($empleado->posicion, $categoria);
                                    });
                                @endphp

                                @if($empleadosCategoria->isEmpty())
                                    <div class="text-center py-10 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Sin registros</h3>
                                        <p class="mt-1 text-sm text-gray-500">No hay colaboradores asignados a {{ $categoria }} actualmente.</p>
                                    </div>
                                @else
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                        @foreach($empleadosCategoria as $empleado)
                                            <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 p-6 relative group">
                                                
                                                <div class="flex items-center space-x-4">
                                                    <div class="flex-shrink-0">
                                                        <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 font-bold text-lg uppercase">
                                                            {{ substr($empleado->nombre, 0, 1) }}
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate" title="{{ $empleado->nombre }} {{ $empleado->apellido_paterno }}">
                                                            {{ $empleado->nombre }} {{ $empleado->apellido_paterno }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 truncate">
                                                            {{ $empleado->posicion }}
                                                        </p>
                                                        <p class="text-xs text-indigo-600 mt-1">
                                                            {{ $empleado->correo }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="mt-4 border-t border-gray-100 pt-4 flex justify-between items-center">
                                                    <span class="text-xs text-gray-500">Estado: <span class="text-green-600 font-semibold">Activo</span></span>
                                                    <a href="{{ route('rh.evaluacion.show', $empleado->id) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                        Evaluar &rarr;
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</x-app-layout>