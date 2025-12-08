<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use App\Models\Empleado;

class CampoPersonalizadoMatriz extends Model
{
    protected $table = 'campos_personalizados_matriz';

    protected $fillable = [
        'nombre',
        'tipo',
        'activo',
        'orden',
        'mostrar_despues_de',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    /**
     * Los ejecutivos asignados a este campo personalizado
     */
    public function ejecutivos()
    {
        return $this->belongsToMany(Empleado::class, 'campo_personalizado_ejecutivo', 'campo_personalizado_id', 'empleado_id');
    }

    /**
     * Valores de este campo en las operaciones
     */
    public function valores()
    {
        return $this->hasMany(ValorCampoPersonalizado::class, 'campo_personalizado_id');
    }

    /**
     * Scope para campos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope ordenado
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden');
    }
}
