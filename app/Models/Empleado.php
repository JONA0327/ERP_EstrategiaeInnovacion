<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'es_practicante',
        'telefono',
        'direccion',
        'correo_personal',
        'foto_path',
        'supervisor_id',
        'ciudad', 'estado_federativo', 'codigo_postal', 'telefono_casa',
        'alergias', 'enfermedades_cronicas',
        'contacto_emergencia_nombre', 'contacto_emergencia_numero', 'contacto_emergencia_parentesco',
        'rfc', 'curp', 'nss'
    ];

    // --- RELACIONES ---

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Empleado::class, 'supervisor_id');
    }

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
        return $this->hasMany(\App\Models\EmpleadoDocumento::class);
    }

    // --- LÓGICA DE NEGOCIO CENTRALIZADA ---

    /**
     * Devuelve la lista de documentos requeridos según el tipo de empleado.
     * Esta es la "Fuente de la Verdad".
     */
    public static function getRequisitos($esPracticante)
    {
        if ($esPracticante) {
            return [
                'INE', 
                'CURP', 
                'Comprobante de Domicilio', 
                'Estado de Cuenta', 
                'Formato ID', 
                'Contrato'
            ];
        }

        return [
            'INE', 
            'CURP', 
            'Comprobante de Domicilio', 
            'NSS',    
            'Titulo', // O Carta Pasante / Cedula
            'Constancia de Situacion Fiscal', 
            'Formato ID', 
            'Contrato'
        ];
    }

    /**
     * Calcula el porcentaje de completitud del expediente (0-100).
     */
    public function getPorcentajeExpedienteAttribute()
    {
        // 1. Datos obligatorios en Base de Datos
        $datosRequeridos = [
            'nombre', 'correo', 'telefono', 'direccion', 
            'contacto_emergencia_numero', 'contacto_emergencia_nombre'
        ];

        // 2. Obtener lista de docs desde la función centralizada
        $docsRequeridos = self::getRequisitos($this->es_practicante);

        // 3. Reglas extra de BD según tipo
        if ($this->es_practicante) {
            $datosRequeridos[] = 'curp';
        } else {
            $datosRequeridos[] = 'nss';
            $datosRequeridos[] = 'rfc';
            $datosRequeridos[] = 'curp';
        }

        $puntosPosibles = count($docsRequeridos) + count($datosRequeridos);
        $puntosObtenidos = 0;

        // A) Calcular puntos por Información en BD
        foreach ($datosRequeridos as $campo) {
            if (!empty($this->$campo)) {
                $puntosObtenidos++;
            }
        }

        // B) Calcular puntos por Documentos Subidos
        $docsSubidos = $this->documentos->map(function ($doc) {
            return Str::lower($doc->nombre);
        });

        foreach ($docsRequeridos as $req) {
            $reqLower = Str::lower($req);
            
            // Lógica de coincidencia flexible (Fuzzy Matching)
            if ($req === 'Titulo') {
                if ($docsSubidos->contains(fn($d) => Str::contains($d, ['titulo', 'cedula', 'pasante', 'profesional']))) $puntosObtenidos++;
            } elseif ($req === 'NSS') {
                if ($docsSubidos->contains(fn($d) => Str::contains($d, ['nss', 'imss', 'seguro']))) $puntosObtenidos++;
            } else {
                if ($docsSubidos->contains(fn($d) => Str::contains($d, $reqLower))) $puntosObtenidos++;
            }
        }

        if ($puntosPosibles == 0) return 0;

        return round(($puntosObtenidos / $puntosPosibles) * 100);
    }
}