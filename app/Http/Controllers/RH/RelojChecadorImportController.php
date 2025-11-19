<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessAsistenciaImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RelojChecadorImportController extends Controller
{
    /** Muestra vista principal del módulo (ya creada en Blade). */
    public function index()
    {
        return view('Recursos_Humanos.reloj_checador');
    }

    /** Inicia importación y devuelve clave de progreso. */
    public function start(Request $request)
    {
        $request->validate([
            'archivo' => ['required','file','mimes:xls,xlsx','max:10240'],
        ]);
        $file = $request->file('archivo');
        $storedPath = $file->storeAs('imports/reloj', Str::uuid().'_'.$file->getClientOriginalName());
        $progressKey = 'import_reloj_' . Str::uuid();

        Cache::put($progressKey, [
            'status' => 'en-cola',
            'mensaje' => 'En cola para procesamiento',
            'filename' => $file->getClientOriginalName(),
            'sheet_actual' => 0,
            'sheet_total' => 0,
            'registros' => 0,
            'finalizado' => false,
        ], now()->addMinutes(30));

        ProcessAsistenciaImportJob::dispatch(Storage::path($storedPath), $progressKey);

        return response()->json([
            'progress_key' => $progressKey,
            'filename' => $file->getClientOriginalName(),
        ]);
    }

    /** Devuelve estado de progreso para polling. */
    public function progress(string $key)
    {
        $data = Cache::get($key);
        if (!$data) {
            return response()->json(['error' => 'clave inválida'], 404);
        }
        $percent = 0;
        if (($data['sheet_total'] ?? 0) > 0) {
            $percent = min(100, round(($data['sheet_actual'] / $data['sheet_total']) * 100));
        }
        $data['percent'] = $percent;
        return response()->json($data);
    }
}
