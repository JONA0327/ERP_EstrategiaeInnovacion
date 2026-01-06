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
        'es_activo',
        'subdepartamento_id',
        'posicion',
        'telefono',
        'direccion',
        'correo_personal',
        'foto_path',
        'supervisor_id',
        'ciudad', 'estado_federativo', 'codigo_postal', 'telefono_casa',
        'alergias', 'enfermedades_cronicas',
        'contacto_emergencia_nombre', 'contacto_emergencia_numero', 'contacto_emergencia_parentesco'
    ];

    // --- RELACIONES ---

    /**
     * Relación: Un empleado pertenece a un Usuario de sistema (Login).
     * Esta es la que faltaba y causaba el error.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

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

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'empleado_id');
    }

    public function documentos()
    {
        return $this->hasMany(\App\Models\EmpleadoDocumento::class); // Asegúrate de crear este modelo también
    }

    // Helper para calcular completitud (Opcional pero pro)
    public function getPorcentajeExpedienteAttribute()
    {
        $puntos = 0;
        $total = 5; // Digamos que pedimos 5 cosas obligatorias
        
        if ($this->rfc) $puntos++;
        if ($this->nss) $puntos++;
        if ($this->documentos()->where('categoria', 'Identificación')->exists()) $puntos++;
        if ($this->documentos()->where('categoria', 'Contrato')->exists()) $puntos++;
        // ... más lógica
        
        return ($puntos / $total) * 100;
    }
}