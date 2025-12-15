<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Empleado;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    protected $fillable = [
        'empleado_no',
        'nombre',
        'fecha',
        'entrada',
        'salida',
        'checadas',
        'empleado_id',
        'tipo_registro', // Nuevo
        'es_retardo',    // Nuevo
        'es_justificado',// Nuevo
        'comentarios',   // Nuevo
    ];

    protected $casts = [
        'fecha' => 'date',
        'checadas' => 'array',
        'es_retardo' => 'boolean',
        'es_justificado' => 'boolean',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function getHorasTrabajadasAttribute()
    {
        if ($this->entrada && $this->salida) {
            try {
                $inicio = \Carbon\Carbon::parse($this->entrada);
                $fin = \Carbon\Carbon::parse($this->salida);
                return $inicio->diff($fin)->format('%H:%I');
            } catch (\Throwable $e) { return '--:--'; }
        }
        return '--:--';
    }
}