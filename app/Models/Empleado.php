<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';

    protected $fillable = [
        'user_id',
        'nombre',
        'correo',
        'area',
        'id_empleado',
        'subdepartamento_id',
        'posicion',
        'telefono',
        'direccion',
        'correo_personal',
        'foto_path',
        'supervisor_id', // <--- Nuevo campo agregado
    ];

    /**
     * Relación: Un empleado tiene un supervisor (Jefe).
     */
    public function supervisor()
    {
        return $this->belongsTo(Empleado::class, 'supervisor_id');
    }

    /**
     * Relación: Un empleado (Jefe) tiene muchos subordinados.
     */
    public function subordinados()
    {
        return $this->hasMany(Empleado::class, 'supervisor_id');
    }
}