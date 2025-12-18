@extends('layouts.erp')

@section('title', 'Logística - Portal Interno')

@section('content')
<div class="min-h-screen bg-slate-50 pb-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 relative overflow-hidden">
            <div class="absolute right-0 top-0 h-full w-1/2 bg-gradient-to-l from-emerald-50/80 to-transparent pointer-events-none"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold uppercase tracking-wider border border-emerald-200">
                        Portal Logístico
                    </span>
                    <span class="text-sm text-slate-400 font-medium">{{ date('F  d\t\h, Y') }}</span>
                </div>
                <h3 class="text-3xl font-bold text-slate-900 tracking-tight">Panel de Control Logística</h3>
                <p class="mt-2 text-slate-500 max-w-2xl text-lg leading-relaxed">
                    Gestión integral de operaciones, pedimentos, catálogos y seguimiento de procesos aduanales.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            
            <a href="{{ route('logistica.matriz-seguimiento') }}" class="group relative bg-white rounded-3xl p-8 shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity text-emerald-600">
                    <svg class="w-40 h-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V9.414a2 2 0 00-.586-1.414L13 4.586A2 2 0 0011.586 4H11" /></svg>
                </div>
                
                <div class="relative z-10 flex-1">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 border border-emerald-100">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V9.414a2 2 0 00-.586-1.414L13 4.586A2 2 0 0011.586 4H11" /></svg>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-emerald-600 transition-colors">Matriz de Seguimiento</h4>
                    <p class="text-slate-500 text-sm leading-relaxed mb-4">Gestiona y da seguimiento a todas las operaciones logísticas en tiempo real.</p>
                    <div class="text-xs text-emerald-600 font-semibold bg-emerald-50 px-2 py-1 rounded-full w-fit border border-emerald-200">✓ Disponible</div>
                </div>
            </a>

            <a href="{{ route('logistica.pedimentos.index') }}" class="group relative bg-white rounded-3xl p-8 shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity text-blue-600">
                    <svg class="w-40 h-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                
                <div class="relative z-10 flex-1">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 border border-blue-100">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-blue-600 transition-colors">Pedimentos</h4>
                    <p class="text-slate-500 text-sm leading-relaxed mb-4">Administra el estado de pago de pedimentos asociados a las operaciones.</p>
                    <div class="text-xs text-blue-600 font-semibold bg-blue-50 px-2 py-1 rounded-full w-fit border border-blue-200">✓ Disponible</div>
                </div>
            </a>

            <a href="{{ route('logistica.catalogos') }}" class="group relative bg-white rounded-3xl p-8 shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity text-purple-600">
                    <svg class="w-40 h-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
                
                <div class="relative z-10 flex-1">
                    <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 border border-purple-100">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-purple-600 transition-colors">Catálogos</h4>
                    <p class="text-slate-500 text-sm leading-relaxed mb-4">Administra clientes, agentes aduanales, transportes y ejecutivos del área.</p>
                    <div class="text-xs text-purple-600 font-semibold bg-purple-50 px-2 py-1 rounded-full w-fit border border-purple-200">✓ Disponible</div>
                </div>
            </a>

            <div class="group relative bg-white rounded-3xl p-8 shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity text-orange-600">
                    <svg class="w-40 h-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                </div>
                
                <div class="relative z-10 flex-1">
                    <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 border border-orange-100">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-orange-600 transition-colors">Reportes</h4>
                    <p class="text-slate-500 text-sm leading-relaxed mb-4">Descarga reportes en CSV y visualiza gráficos de estado de operaciones.</p>
                    
                    <!-- Opciones de reportes -->
                    <div class="space-y-2 mt-4">
                        <a href="{{ route('logistica.reportes') }}" class="flex items-center gap-2 text-xs text-slate-600 hover:text-orange-600 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Reporte de Seguimiento
                        </a>
                        <a href="{{ route('logistica.reportes') }}?tab=pedimentos" class="flex items-center gap-2 text-xs text-slate-600 hover:text-orange-600 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Reporte de Pedimentos
                        </a>
                    </div>
                    
                    <div class="text-xs text-orange-600 font-semibold bg-orange-50 px-2 py-1 rounded-full w-fit border border-orange-200 mt-4">✓ Disponible</div>
                </div>
            </div>

            <a href="{{ route('logistica.consulta-publica') }}" class="group relative bg-white rounded-3xl p-8 shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity text-cyan-600">
                    <svg class="w-40 h-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                
                <div class="relative z-10 flex-1">
                    <div class="w-14 h-14 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 border border-cyan-100">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-cyan-600 transition-colors">Consulta Pública</h4>
                    <p class="text-slate-500 text-sm leading-relaxed mb-4">Portal de consulta externa para seguimiento de operaciones por parte de clientes.</p>
                    <div class="text-xs text-cyan-600 font-semibold bg-cyan-50 px-2 py-1 rounded-full w-fit border border-cyan-200">✓ Disponible</div>
                </div>
            </a>

            <div class="group relative bg-white rounded-3xl p-8 shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col opacity-75">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity text-slate-400">
                    <svg class="w-40 h-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                
                <div class="relative z-10 flex-1">
                    <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 border border-slate-100">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-slate-600 transition-colors">Evaluación de Desempeño</h4>
                    <p class="text-slate-500 text-sm leading-relaxed mb-4">Monitorea el rendimiento del equipo y evalúa el cumplimiento de metas logísticas.</p>
                    <div class="text-xs text-slate-500 font-semibold bg-slate-50 px-2 py-1 rounded-full w-fit border border-slate-200 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Próximamente
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
