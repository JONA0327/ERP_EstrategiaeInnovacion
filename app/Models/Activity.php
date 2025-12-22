<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Activity extends Model
{
    protected $fillable = [
        'user_id', 'area', 'tipo_actividad', 'nombre_actividad', 
        'prioridad', 'fecha_inicio', 'fecha_compromiso', 
        'fecha_final', 'estatus', 'comentarios'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_compromiso' => 'datetime',
        'fecha_final' => 'datetime',
        'metrico' => 'integer',
        'resultado_dias' => 'integer',
    ];

    // --- RELACIONES ---
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function historial()
    {
        return $this->hasMany(ActivityHistory::class)->orderBy('fecha_cambio', 'desc');
    }

    // --- LÓGICA AUTOMÁTICA ---
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($activity) {
            $inicio = $activity->fecha_inicio ? Carbon::parse($activity->fecha_inicio)->startOfDay() : null;
            $compromiso = $activity->fecha_compromiso ? Carbon::parse($activity->fecha_compromiso)->startOfDay() : null;
            $final = $activity->fecha_final ? Carbon::parse($activity->fecha_final)->startOfDay() : null;
            $hoy = Carbon::now()->startOfDay();

            // 1. Cálculos de Días
            // Métrico: Días planeados (Start -> Compromiso)
            if ($compromiso && $inicio) {
                $activity->metrico = (int) $inicio->diffInDays($compromiso, false);
                // Aseguramos que mínimo cuente como 1 día de esfuerzo si es el mismo día, 
                // para evitar divisiones por cero raras en la lógica, aunque la lógica de abajo lo protege.
                if ($activity->metrico < 0) $activity->metrico = 0; 
            }

            // Resultado: Días reales tomados (Start -> Fin)
            if ($final && $inicio) {
                $activity->resultado_dias = (int) $inicio->diffInDays($final, false);
            } else {
                $activity->resultado_dias = null;
            }

            // 2. NUEVA LÓGICA DE PORCENTAJE (EFICIENCIA)
            $activity->porcentaje = 0; // Default por si no ha terminado

            if ($final && $compromiso && $inicio) {
                // CASO A: Terminó A TIEMPO o ANTES
                // Si la fecha final es menor o igual a la del compromiso -> 100%
                if ($final->lessThanOrEqualTo($compromiso)) {
                    $activity->porcentaje = 100;
                } 
                // CASO B: Terminó TARDE (Con Retardo)
                else {
                    // La eficiencia baja mientras más días te tardes comparado con lo planeado.
                    // Fórmula: (Días Planeados / Días Reales) * 100
                    
                    // Evitamos división por cero si resultado_dias fuera 0 (improbable si es tarde)
                    if ($activity->resultado_dias > 0) {
                        $calculo = ($activity->metrico / $activity->resultado_dias) * 100;
                        $activity->porcentaje = round($calculo, 2);
                    } else {
                        // Si tardó 0 días (imposible si es tarde), ponemos 0
                        $activity->porcentaje = 0;
                    }
                }
            }

            // 3. ESTATUS AUTOMÁTICO
            if ($activity->fecha_final) {
                if ($final->gt($compromiso)) {
                    $activity->estatus = 'Completado con retardo';
                } else {
                    $activity->estatus = 'Completado';
                }
            } else {
                if ($compromiso && $hoy->gt($compromiso)) {
                    $activity->estatus = 'Retardo';
                } else {
                    if (empty($activity->estatus)) {
                        $activity->estatus = 'En blanco';
                    } elseif ($activity->estatus === 'Retardo') {
                        // Si era retardo pero movieron fechas y ya no lo es
                        $activity->estatus = 'En proceso';
                    }
                }
            }
        });

        // 4. Historial (Logs)
        static::updated(function ($activity) {
            foreach ($activity->getDirty() as $field => $newValue) {
                if ($field === 'updated_at') continue;
                
                $originalValue = $activity->getOriginal($field);
                if ($originalValue instanceof \DateTime) $originalValue = $originalValue->format('Y-m-d H:i:s');
                if ($newValue instanceof \DateTime) $newValue = $newValue->format('Y-m-d H:i:s');
                
                $logOriginal = strlen($originalValue) > 50 ? substr($originalValue, 0, 50).'...' : $originalValue;
                $logNew = strlen($newValue) > 50 ? substr($newValue, 0, 50).'...' : $newValue;

                \DB::table('activity_histories')->insert([
                    'activity_id' => $activity->id,
                    'user_id' => auth()->id() ?? 1,
                    'campo_modificado' => $field,
                    'valor_anterior' => $logOriginal,
                    'valor_nuevo' => $logNew,
                    'fecha_cambio' => now()
                ]);
            }
        });
    }
}