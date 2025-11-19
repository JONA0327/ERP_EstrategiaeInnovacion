<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Services\ProcesarAsistenciaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RelojChecadorController extends Controller
{
    public function index()
    {
        return view('Recursos_Humanos.reloj.index');
    }

    public function procesar(Request $request, ProcesarAsistenciaService $procesarAsistenciaService)
    {
        $validated = $request->validate([
            'archivo' => 'required|file|mimes:xls,xlsx,xlsm',
        ]);

        $path = $validated['archivo']->store('tmp/reloj-checador');
        $absolutePath = Storage::path($path);

        try {
            $resultado = $procesarAsistenciaService->process($absolutePath, false);
        } finally {
            Storage::delete($path);
        }

        $grupos = collect($resultado['registros'] ?? [])
            ->groupBy('empleado_no')
            ->map(function ($items, $empleadoNo) {
                $first = $items->first();

                return [
                    'empleado_no' => $empleadoNo,
                    'nombre' => $first['nombre'] ?? 'DESCONOCIDO',
                    'total' => $items->count(),
                    'registros' => $items->map(fn ($registro) => [
                        'fecha' => $registro['fecha'],
                        'entrada' => $registro['entrada'],
                        'salida' => $registro['salida'],
                        'checadas' => $registro['checadas'],
                    ])->values(),
                ];
            })
            ->values();

        return view('Recursos_Humanos.reloj.index', [
            'resultado' => $resultado,
            'grupos' => $grupos,
        ]);
    }
}
