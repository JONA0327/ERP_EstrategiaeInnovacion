<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\CriterioEvaluacion; // Importante importar esto
use Illuminate\Http\Request;

class EvaluacionController extends Controller
{
    /**
     * Muestra la lista de empleados para evaluación, filtrada por área.
     */
    public function index(Request $request)
    {
        // ... (Tu código index se mantiene igual, ya que usa 'posicion' para las pestañas en la vista)
        
        // Pero para asegurar que el filtro funcione bien en el index también:
        // Obtenemos todos los puestos únicos para usarlos como filtro en el frontend
        $areas = Empleado::select('posicion') // Cambiamos area por posicion para el filtro visual
            ->whereNotNull('posicion')
            ->distinct()
            ->pluck('posicion');

        $query = Empleado::query();

        if ($request->has('area') && $request->area !== 'Todos') {
            // Filtramos por posición que contenga la palabra clave
            $query->where('posicion', 'LIKE', '%' . $request->area . '%');
        }

        $empleados = $query->get();

        return view('Recursos_Humanos.evaluacion.index', compact('areas', 'empleados'));
    }

    public function show($id)
    {
        $empleado = Empleado::findOrFail($id);
        
        // --- LÓGICA DE CORRECCIÓN ---
        // En lugar de usar $empleado->area (que es la empresa), determinamos
        // el Área de Evaluación basándonos en su POSICIÓN.
        
        $puesto = $empleado->posicion;
        $areaEvaluacion = 'General'; // Valor por defecto

        // Lógica de coincidencia (Case Insensitive)
        if (str_contains($puesto, 'Logistica')) {
            $areaEvaluacion = 'Logistica';
        } elseif (str_contains($puesto, 'Pedimentos') || str_contains($puesto, 'Comercio Exterior')) {
            $areaEvaluacion = 'Pedimentos'; // Asegúrate de tener un Seeder para esto o usa Logistica
        } elseif (str_contains($puesto, 'TI') || str_contains($puesto, 'Sistemas') || str_contains($puesto, 'Tecnica')) {
            $areaEvaluacion = 'TI';
        } elseif (str_contains($puesto, 'Legal')) {
            $areaEvaluacion = 'Legal';
        } elseif (str_contains($puesto, 'Recursos Humanos') || str_contains($puesto, 'RH')) {
            $areaEvaluacion = 'RH';
        } elseif (str_contains($puesto, 'Auditoria')) {
            $areaEvaluacion = 'Auditoria';
        }

        // Buscamos los criterios usando esta $areaEvaluacion calculada
        $criterios = CriterioEvaluacion::where('area', $areaEvaluacion)->get();

        // Si no encuentra específicos, intenta buscar "General"
        if ($criterios->isEmpty()) {
            $criterios = CriterioEvaluacion::where('area', 'General')->get();
        }

        // Para el sidebar, mostramos compañeros del mismo "Grupo de Evaluación"
        // Opcional: Puedes seguir mostrando compañeros de la misma empresa ($empleado->area)
        // o del mismo puesto. Aquí uso el area de la empresa para mantener el contexto de equipo.
        $areaEmpresa = $empleado->area; 
        $empleados = Empleado::where('area', $areaEmpresa)->get();

        // Pasamos $areaEvaluacion a la vista como 'area' para que el título sea "Evaluación: Logistica"
        return view('Recursos_Humanos.evaluacion.show', [
            'empleado' => $empleado,
            'area' => $areaEvaluacion, // Pasamos el área funcional calculada
            'empleados' => $empleados,
            'criterios' => $criterios
        ]);
    }
}