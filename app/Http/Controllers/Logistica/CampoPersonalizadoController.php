<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Logistica\CampoPersonalizadoMatriz;
use App\Models\Logistica\ValorCampoPersonalizado;

class CampoPersonalizadoController extends Controller
{
    public function index() {
        return CampoPersonalizadoMatriz::orderBy('orden')->get();
    }

    public function store(Request $request) {
        $campo = CampoPersonalizadoMatriz::create($request->all());
        return response()->json(['success' => true, 'campo' => $campo]);
    }

    public function destroy($id) {
        CampoPersonalizadoMatriz::destroy($id);
        return response()->json(['success' => true]);
    }

    public function getCamposActivos() {
        // Retorna campos activos para el usuario actual (lógica simplificada)
        return CampoPersonalizadoMatriz::where('activo', true)->get();
    }

    public function getValoresOperacion($id) {
        $valores = ValorCampoPersonalizado::where('operacion_logistica_id', $id)->get()
            ->mapWithKeys(function($item) {
                return [$item->campo_personalizado_id => $item->valor];
            });
        return response()->json($valores);
    }
}