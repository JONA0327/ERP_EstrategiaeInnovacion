<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    use HasFactory;

    protected $table = 'evaluaciones';

    protected $fillable = [
        'empleado_id',
        'evaluador_id',
        'periodo',
        'promedio_final',
        'comentarios_generales',
        'edit_count', // <--- ¡IMPORTANTE! AGREGAR ESTO
    ];

    public function detalles()
    {
        return $this->hasMany(EvaluacionDetalle::class);
    }
    
    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}