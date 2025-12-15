<?php

namespace App\Http\Controllers; // Nota: Ya no dice \RH

use Illuminate\Http\Request;

class EvaluacionController extends Controller
{
    /**
     * Muestra el dashboard de evaluación de desempeño.
     */
    public function index()
    {
        return view('Recursos_Humanos.evaluacion.index');
    }
}