<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Leccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CursoController extends Controller
{
    // Catálogo de Cursos
    public function index()
    {
        $cursos = Curso::where('activo', true)->withCount('lecciones')->get();
        return view('Recursos_Humanos.cursos.index', compact('cursos'));
    }

    // El "Aula" (Reproductor estilo Udemy)
    public function aprender(Curso $curso, $leccion_id = null)
    {
        // 1. Cargar lecciones ordenadas
        $lecciones = $curso->lecciones;

        // 2. Decidir qué video mostrar
        if ($leccion_id) {
            $leccionActual = $lecciones->where('id', $leccion_id)->firstOrFail();
        } else {
            // Si no especifica, ir a la primera (o la primera no vista)
            $leccionActual = $lecciones->first();
        }

        // 3. Obtener lección siguiente y anterior para los botones
        $indice = $lecciones->search(function($item) use ($leccionActual) {
            return $item->id === $leccionActual->id;
        });
        
        $siguiente = $lecciones->get($indice + 1);
        $anterior = $lecciones->get($indice - 1);

        // 4. Checar si ya la completó
        $completada = $leccionActual->usuariosQueCompletaron()
                                    ->where('user_id', Auth::id())
                                    ->exists();

        return view('Recursos_Humanos.cursos.classroom', compact(
            'curso', 'lecciones', 'leccionActual', 'siguiente', 'anterior', 'completada'
        ));
    }

    // Marcar como vista
    public function completarLeccion(Request $request, $id)
    {
        $leccion = Leccion::findOrFail($id);
        
        // Usar syncWithoutDetaching para no duplicar registros
        $leccion->usuariosQueCompletaron()->syncWithoutDetaching([
            Auth::id() => ['completada' => true]
        ]);

        return response()->json(['success' => true]);
    }
}
