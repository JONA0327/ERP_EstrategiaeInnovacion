@extends('layouts.erp')
@section('title','Reloj Checador - Importación')
@section('content')
<div class="max-w-7xl mx-auto py-10 px-4" x-data="importadorReloj()">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Reloj Checador</h1>
    <div class="grid md:grid-cols-2 gap-6">
        <div class="rounded-3xl border border-blue-100 bg-white/90 backdrop-blur p-6 shadow">
            <h2 class="text-lg font-semibold text-slate-800 mb-2">Instrucciones</h2>
            <ol class="text-sm text-slate-600 space-y-2 list-decimal list-inside">
                <li>Sube un archivo .xls o .xlsx exportado del reloj.</li>
                <li>El sistema detecta periodo, empleados y checadas.</li>
                <li>Observa el progreso en tiempo real por hoja.</li>
            </ol>
            <div class="mt-4 text-xs text-slate-500">Tamaño máximo: 10 MB</div>
        </div>
        <div class="rounded-3xl border border-blue-100 bg-white/90 backdrop-blur p-6 shadow flex flex-col">
            <form @submit.prevent="iniciarImport" class="flex flex-col flex-1" enctype="multipart/form-data">
                <label class="flex-1 flex items-center justify-center border-2 border-dashed border-blue-200 rounded-2xl p-6 cursor-pointer hover:border-blue-400">
                    <div class="text-center">
                        <p class="text-sm font-medium text-slate-700" x-text="filename || 'Haz clic para seleccionar' "></p>
                        <p class="text-[11px] text-slate-500 mt-1">Solo .xls / .xlsx</p>
                    </div>
                    <input type="file" class="hidden" @change="onFileChange($event)" accept=".xls,.xlsx">
                </label>
                <button type="submit" class="mt-4 inline-flex justify-center items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 disabled:opacity-50" :disabled="!file || cargando">
                    <template x-if="!cargando && !subiendo"><span>Procesar archivo →</span></template>
                    <template x-if="subiendo"><span>Subiendo… (<span x-text="uploadPercent"></span>%)</span></template>
                    <template x-if="cargando && !subiendo"><span>Iniciando…</span></template>
                </button>
                <template x-if="subiendo">
                    <div class="mt-4 w-full h-3 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-indigo-500 to-indigo-600 transition-all" :style="`width:${uploadPercent}%`"></div>
                    </div>
                </template>
            </form>
        </div>
    </div>

    <template x-if="progressKey">
        <div class="mt-8 rounded-3xl border border-blue-100 bg-white/90 backdrop-blur p-6 shadow">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-semibold text-slate-800">Archivo:</p>
                    <p class="text-xs text-slate-600" x-text="filename"></p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-slate-800">Estado:</p>
                    <p class="text-xs" :class="finalizado ? 'text-green-600' : 'text-blue-600'" x-text="mensaje"></p>
                </div>
            </div>
            <div class="w-full h-4 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-300" :style="`width:${porcentaje}%`"></div>
            </div>
            <div class="mt-3 flex flex-wrap gap-4 text-xs">
                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-blue-700 border border-blue-200">Hojas: <span x-text="sheet_actual"></span>/<span x-text="sheet_total"></span></span>
                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-indigo-700 border border-indigo-200">Registros: <span x-text="registros"></span></span>
                <template x-if="periodo">
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-green-700 border border-green-200" x-text="`Periodo: ${periodo.inicio} → ${periodo.fin}`"></span>
                </template>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function importadorReloj(){
    return {
        file:null, filename:'', progressKey:null,
        porcentaje:0, sheet_actual:0, sheet_total:0, registros:0, mensaje:'', periodo:null, finalizado:false, cargando:false,
        subiendo:false, uploadPercent:0, pollsWithoutProgress:0,
        onFileChange(e){ const f = e.target.files[0]; if(!f) return; this.file=f; this.filename=f.name; },
        iniciarImport(){
            if(!this.file) return; this.cargando=true; this.subiendo=true; this.uploadPercent=0;
            const formData = new FormData(); formData.append('archivo', this.file);
            const xhr = new XMLHttpRequest();
            xhr.open('POST','{{ route('rh.reloj.import.start') }}', true);
            xhr.setRequestHeader('X-CSRF-TOKEN','{{ csrf_token() }}');
            xhr.upload.onprogress = (e)=>{ if(e.lengthComputable){ this.uploadPercent = Math.round((e.loaded/e.total)*100); }};
            xhr.onreadystatechange = ()=>{ if(xhr.readyState===4){ this.subiendo=false; if(xhr.status===200){ try { const d = JSON.parse(xhr.responseText); this.progressKey=d.progress_key; this.mensaje='En cola'; this.cargando=false; this.poll(); } catch { this.mensaje='Error parseando respuesta'; this.cargando=false; } } else { this.mensaje='Error al subir'; this.cargando=false; } }};
            xhr.send(formData);
        },
        poll(){ if(!this.progressKey) return; // Corrige URL (antes incluía 'importar' causando 404)
            fetch('{{ url('/recursos-humanos/reloj-checador/progreso') }}/'+this.progressKey)
            .then(r=>r.json())
            .then(d=>{ if(d.error){ this.mensaje='Error'; return; }
                this.sheet_actual=d.sheet_actual||0; this.sheet_total=d.sheet_total||0; this.registros=d.registros||0; this.porcentaje=d.percent||0; this.mensaje=d.mensaje||''; this.finalizado=!!d.finalizado; this.periodo=d.periodo?{inicio:d.periodo.inicio.date?d.periodo.inicio.date:d.periodo.inicio, fin:d.periodo.fin.date?d.periodo.fin.date:d.periodo.fin}:null;
                if(!this.finalizado){
                    if((this.sheet_total||0)===0){ this.pollsWithoutProgress++; if(this.pollsWithoutProgress>=5){ this.mensaje='Esperando worker de colas. Ejecuta: php artisan queue:work'; } }
                    setTimeout(()=>this.poll(),1000);
                }
            }); }
    }
}
</script>
@endpush
@endsection