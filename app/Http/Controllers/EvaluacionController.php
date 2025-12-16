<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;

class EvaluacionController extends Controller
{
    /**
     * Muestra la lista de empleados para evaluación, filtrada por área.
     */
    public function index(Request $request)
    {
        // 1. Obtener todas las áreas únicas que existen en la tabla empleados
        $areas = Empleado::select('area')
            ->whereNotNull('area')
            ->where('area', '!=', '')
            ->distinct()
            ->pluck('area');

        // 2. Iniciar la consulta base de empleados
        $query = Empleado::query();

        // 3. Aplicar el filtro si se recibió el parámetro 'area' y no es 'Todos'
        if ($request->has('area') && $request->area !== 'Todos') {
            $query->where('area', $request->area);
        }

        // 4. Obtener los resultados
        $empleados = $query->get();

        // 5. Retornar la vista con los datos
        return view('Recursos_Humanos.evaluacion.index', compact('areas', 'empleados'));
    }

    public function show($id)
    {
        $empleado = Empleado::findOrFail($id);
        
        // Extraemos el área del empleado
        $area = $empleado->area;

        // CORRECCIÓN: Obtenemos también la lista de empleados (filtrados por la misma área)
        // Esto soluciona el error "Undefined variable $empleados" en la vista show.blade.php
        $empleados = Empleado::where('area', $area)->get();

        return view('Recursos_Humanos.evaluacion.show', compact('empleado', 'area', 'empleados'));
    }
}