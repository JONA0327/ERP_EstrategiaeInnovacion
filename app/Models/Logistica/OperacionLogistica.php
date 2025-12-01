<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Empleado;

class OperacionLogistica extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\OperacionLogisticaFactory::new();
    }

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'operaciones_logisticas';

    /**
     * Atributos que se pueden asignar de forma masiva
     */
    protected $fillable = [
        // Campos de nombres directos (no IDs) - ÚNICOS CAMPOS UTILIZADOS
        'ejecutivo',
        'cliente',
        'agente_aduanal',
        'transporte',

        // Campos de operación
        'operacion',
        'proveedor_o_cliente',
        'fecha_embarque',
        'no_factura',
        'tipo_operacion_enum', // Este SÍ se usa
        'clave',
        'referencia_interna',
        'aduana',
        'referencia_aa',
        'no_pedimento',
        'fecha_arribo_aduana',
        'guia_bl',
        'fecha_modulacion',
        'fecha_arribo_planta',
        'resultado',
        'target',
        'dias_transito',
        'post_operacion_id',
        'status_calculado', // Campo calculado automáticamente
        'status_manual', // Campo manual controlado por usuario
        'fecha_status_manual',
        'color_status',
        'dias_transcurridos_calculados',
        'fecha_ultimo_calculo',
        'comentarios',
    ];

    /**
     * Atributos que deben ser convertidos a tipos nativos
     */
    protected $casts = [
        'fecha_embarque' => 'date',
        'fecha_arribo_aduana' => 'date',
        'fecha_modulacion' => 'date',
        'fecha_arribo_planta' => 'date',
        'fecha_status_manual' => 'datetime',
        'resultado' => 'integer',
        'target' => 'integer',
        'dias_transito' => 'integer',
        'dias_transcurridos_calculados' => 'integer',
        'fecha_ultimo_calculo' => 'datetime',
        'procesado' => 'boolean'
    ];

    /**
     * Status actual de la operación (sin histórico):
     * Prioriza status_manual si existe, de lo contrario usa status_calculado.
     */
    public function getStatusActualAttribute()
    {
        if (!empty($this->status_manual)) {
            return $this->status_manual;
        }
        return $this->status_calculado;
    }

    /**
     * Relación con el empleado ejecutivo
     * Solo empleados del área de logística
     */
    public function ejecutivo()
    {
        return $this->belongsTo(Empleado::class, 'ejecutivo_empleado_id');
    }

    /**
     * NOTA: Las relaciones con cliente, agenteAduanal y transporte fueron eliminadas
     * porque la tabla operaciones_logisticas usa campos de texto directos:
     * - 'cliente' (texto) en lugar de 'cliente_id' (FK)
     * - 'agente_aduanal' (texto) en lugar de 'agente_aduanal_id' (FK)
     * - 'transporte' (texto) en lugar de 'transporte_id' (FK)
     * 
     * Las columnas FK fueron eliminadas en la migración:
     * 2025_11_25_171611_fix_post_operaciones_and_clean_operaciones_logisticas.php
     */

    /**
     * Relación con la post operación
     */
    /**
     * Relación many-to-many con post-operaciones a través de tabla pivot
     */
    public function postOperaciones()
    {
        return $this->belongsToMany(
            PostOperacion::class,
            'post_operacion_operacion',
            'operacion_logistica_id',
            'post_operacion_id'
        )->withPivot([
            'status',
            'fecha_asignacion',
            'fecha_completado',
            'notas_especificas'
        ])->withTimestamps();
    }

    /**
     * Relación directa con las asignaciones de post-operaciones
     */
    public function asignacionesPostOperaciones()
    {
        return $this->hasMany(PostOperacionOperacion::class, 'operacion_logistica_id');
    }

    /**
     * Relación legacy - mantener por compatibilidad (DEPRECATED)
     */
    public function postOperacion()
    {
        return $this->belongsTo(PostOperacion::class, 'post_operacion_id');
    }

    /**
     * Relación con el histórico de la matriz SGM
     */
    public function historicoMatrizSgm()
    {
        return $this->hasMany(HistoricoMatrizSgm::class);
    }

    /**
     * Scope para filtrar por ejecutivos de logística
     */
    public function scopeConEjecutivoLogistica($query)
    {
        return $query->whereHas('ejecutivo', function ($q) {
            $q->where('area', 'Logística')
              ->orWhere('area', 'Logistica');
        });
    }

    /**
     * Scope para filtrar por status
     */
    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para operaciones en tránsito
     */
    public function scopeEnTransito($query)
    {
        return $query->whereIn('status', ['En Tránsito', 'En Aduana', 'Pendiente']);
    }

    /**
     * Calcular días en tránsito automáticamente
     */
    public function calcularDiasTransito()
    {
        // Calcular RESULTADO: días entre fecha_arribo_aduana y fecha_modulacion
        if ($this->fecha_arribo_aduana && $this->fecha_modulacion) {
            $this->resultado = $this->fecha_arribo_aduana->diffInDays($this->fecha_modulacion);
        } else {
            $this->resultado = null;
        }

        // Calcular DIAS_TRANSITO: días entre fecha_embarque y fecha_arribo_planta
        if ($this->fecha_embarque && $this->fecha_arribo_planta) {
            $this->dias_transito = $this->fecha_embarque->diffInDays($this->fecha_arribo_planta);
        } else {
            $this->dias_transito = null;
        }

        // Calcular días transcurridos para el sistema de status
        if (!$this->fecha_embarque) {
            $this->dias_transcurridos_calculados = 0;
            $this->status_calculado = 'Pendiente';
            $this->color_status = 'gray';
            return null;
        }

        // Calcular días transcurridos: fecha embarque vs fecha arribo a planta (o fecha actual)
        $fechaInicio = $this->fecha_embarque;
        $fechaFin = $this->fecha_arribo_planta ?? now();
        $diasTranscurridos = $fechaInicio->diffInDays($fechaFin);

        // Actualizar campos calculados
        $this->dias_transcurridos_calculados = $diasTranscurridos;

        // Calcular status descriptivo
        $this->status_calculado = $this->calcularStatusDescriptivo();

        // Calcular color basado en target
        $this->color_status = $this->calcularColorStatus($diasTranscurridos);

        return $diasTranscurridos;
    }

    /**
     * Calcular status descriptivo basado en el progreso de la operación
     */
    private function calcularStatusDescriptivo()
    {
        if ($this->fecha_arribo_planta) {
            return 'Entregado';
        } elseif ($this->fecha_modulacion) {
            return 'Modulado';
        } elseif ($this->fecha_arribo_aduana) {
            return 'En Aduana';
        } elseif ($this->fecha_embarque) {
            return 'En Tránsito';
        } else {
            return 'Pendiente';
        }
    }

    /**
     * Calcular color del status basado en target y días transcurridos
     */
    private function calcularColorStatus($diasTranscurridos)
    {
        if (!$this->fecha_embarque) {
            return 'gray';
        }

        $target = $this->target ?? $this->dias_transito ?? 30;

        if ($this->fecha_arribo_planta) {
            // Operación completada: verde si dentro del target, rojo si excedió
            return $diasTranscurridos <= $target ? 'green' : 'red';
        } else {
            // Operación en curso: rojo si ya excedió, amarillo si cerca, verde si bien
            if ($diasTranscurridos > $target) {
                return 'red'; // Fuera de métrica
            } elseif ($diasTranscurridos >= ($target * 0.8)) {
                return 'yellow'; // Cerca del límite (80% del target)
            } else {
                return 'green'; // Dentro de métrica
            }
        }
    }

    /**
     * Verificar si la operación está retrasada
     */
    public function estaRetrasada()
    {
        if ($this->target && $this->dias_transcurridos_calculados) {
            return $this->dias_transcurridos_calculados > $this->target;
        }
        return false;
    }

    /**
     * Calcular el status automático basado en días transcurridos (LEGACY - usar calcularDiasTransito)
     */
    public function calcularStatusAutomatico()
    {
        return $this->calcularDiasTransito();
    }

    /**
     * Calcular target automáticamente basado en el tipo de operación
     * Terrestre: 3 días
     * Aerea: 3 días
     * Ferrocarril: 3 días
     * Maritima: 7 días
     */
    public function calcularTargetAutomatico()
    {
        // Usar tipo_operacion_enum si tipo_operacion está vacío
        $tipoOperacion = $this->tipo_operacion_enum ?? $this->tipo_operacion;

        if (empty($tipoOperacion)) {
            return null;
        }

        return match($tipoOperacion) {
            'Terrestre' => 3,
            'Aerea' => 3,
            'Ferrocarril' => 3,
            'Maritima' => 7,
            default => 3, // Default a 3 días para otros tipos
        };
    }

    /**
     * Obtener el color del status para la UI
     */
    public function getStatusColorAttribute()
    {
        return match($this->color_status) {
            'verde' => 'bg-green-100 text-green-800',
            'amarillo' => 'bg-yellow-100 text-yellow-800',
            'rojo' => 'bg-red-100 text-red-800',
            'sin_fecha' => 'bg-gray-100 text-gray-800',
            default => 'bg-slate-100 text-slate-800',
        };
    }

    /**
     * Obtener el texto del status calculado
     */
    public function getStatusTextoAttribute()
    {
        return match($this->color_status) {
            'verde' => 'Completado',
            'amarillo' => 'En Proceso',
            'rojo' => 'Fuera de Métrica',
            'sin_fecha' => 'Sin Fecha',
            default => 'Desconocido',
        };
    }

    /**
     * Calcular status basado en días transcurridos vs target
     * NUEVA LÓGICA: Desde fecha de aduana hasta hoy, NO cambiar automáticamente a Done
     */
    public function calcularStatusPorDias()
    {
        $statusAnterior = $this->status_calculado;
        $colorAnterior = $this->color_status;

        // Obtener target
        $target = $this->target ?? $this->calcularTargetAutomatico() ?? 3;

        // NUEVA LÓGICA: Calcular días desde fecha de aduana hasta hoy (si existe)
        if ($this->fecha_arribo_aduana) {
            $fechaAduana = \Carbon\Carbon::parse($this->fecha_arribo_aduana);
            $fechaActual = now();
            $diasTranscurridos = $fechaAduana->diffInDays($fechaActual);

            // Determinar color SOLO basado en días vs target (NO cambiar status automáticamente)
            if ($diasTranscurridos > $target) {
                $nuevoColor = 'rojo';    // Fuera de métrica
                $nuevoStatus = 'Out of Metric';  // Usar valor válido del enum
            } else {
                $nuevoColor = 'amarillo'; // Dentro de métrica
                $nuevoStatus = 'In Process'; // Usar valor válido del enum
            }
        } else {
            // Si no hay fecha de aduana, calcular desde registro
            $fechaRegistro = $this->created_at ?? now();
            $fechaActual = now();
            $diasTranscurridos = $fechaRegistro->diffInDays($fechaActual);

            // Sin fecha de aduana = En proceso (amarillo por defecto)
            $nuevoStatus = 'In Process'; // Usar valor válido del enum
            $nuevoColor = 'amarillo';
        }

        // Si hay fecha de aduana, considerar como completado automáticamente
        // NOTA: Ya no se marca como Done automáticamente, solo manualmente
        // if ($this->fecha_arribo_planta) {
        //     $nuevoStatus = 'Done';
        //     // Color según si fue dentro o fuera de métrica
        //     $nuevoColor = (isset($diasTranscurridos) && $diasTranscurridos <= $target) ? 'verde' : 'rojo';
        // }

        // El status manual prevalece sobre el automático para "Done"
        if ($this->status_manual === 'Done') {
            $nuevoStatus = 'Done';
            $nuevoColor = 'verde';
        }

        // Actualizar campos
        $this->dias_transcurridos_calculados = $diasTranscurridos ?? 0;
        $this->status_calculado = $nuevoStatus;
        $this->color_status = $nuevoColor;
        $this->fecha_ultimo_calculo = now();

        // Determinar si hubo cambio de status
        $huboCambio = ($statusAnterior !== $nuevoStatus) || ($colorAnterior !== $nuevoColor);

        return [
            'status' => $nuevoStatus,
            'color' => $nuevoColor,
            'dias_transcurridos' => $diasTranscurridos ?? 0,
            'target' => $target,
            'cambio' => $huboCambio,
            'status_anterior' => $statusAnterior,
            'color_anterior' => $colorAnterior
        ];
    }

    /**
     * Generar historial automáticamente cuando hay cambio de status
     */
    public function generarHistorialCambioStatus($resultado, $esManual = false, $accionManual = null)
    {
        // Si es creación inicial o manual, SIEMPRE crear historial
        // Si es actualización automática, solo crear si hubo cambio
        if (!$esManual && !str_contains($accionManual ?? '', 'Creación') && !str_contains($accionManual ?? '', 'Actualización')) {
            if (!$resultado['cambio'] && $this->historicoMatrizSgm()->exists()) {
                return null;
            }
        }

        if ($esManual && $accionManual) {
            $descripcion = $accionManual;
            if (!str_contains($accionManual, 'Status')) {
                $descripcion .= ". Status manual: {$this->status_manual}";
            }
        } elseif ($accionManual) {
            // Descripción personalizada proporcionada
            $descripcion = $accionManual;
        } else {
            $descripcion = "Status actualizado automáticamente: ";

            if ($resultado['status_anterior'] && $resultado['cambio']) {
                $descripcion .= "Cambió de '{$resultado['status_anterior']}' a '{$resultado['status']}'";
            } else {
                $descripcion .= "Establecido como '{$resultado['status']}'";
            }
        }

        $descripcion .= ". Días transcurridos: {$resultado['dias_transcurridos']}, Target: {$resultado['target']}";

        // Agregar información de fechas clave si existen
        if ($this->fecha_arribo_aduana) {
            $descripcion .= ". Fecha aduana: " . \Carbon\Carbon::parse($this->fecha_arribo_aduana)->format('d/m/Y');
        }
        if ($this->fecha_arribo_planta) {
            $descripcion .= ". Fecha entrega: " . \Carbon\Carbon::parse($this->fecha_arribo_planta)->format('d/m/Y');
        }

        // Crear registro de historial (solo si la operación ya fue guardada)
        if ($this->exists) {
            $historial = $this->historicoMatrizSgm()->create([
                'fecha_registro' => $this->created_at ?? now(),
                'fecha_arribo_aduana' => $this->fecha_arribo_aduana,
                'dias_transcurridos' => $resultado['dias_transcurridos'],
                'target_dias' => $resultado['target'],
                'color_status' => $resultado['color'],
                'operacion_status' => $resultado['status'],
                'observaciones' => $descripcion
            ]);
            return $historial;
        }

        return null;
    }

    /**
     * Actualizar status automáticamente (usar en lugar de calcularStatusAutomatico)
     */
    public function actualizarStatusAutomaticamente($guardarCambios = true)
    {
        $resultado = $this->calcularStatusPorDias();

        // Generar historial si hay cambio
        $this->generarHistorialCambioStatus($resultado);

        // Guardar cambios si se solicita
        if ($guardarCambios) {
            $this->saveQuietly(); // Usar saveQuietly para evitar loops infinitos
        }

        return $resultado;
    }

    /**
     * Boot del modelo para calcular automáticamente el status
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($operacion) {
            // Solo calcular en creación o si las fechas clave cambiaron
            if ($operacion->isDirty(['fecha_arribo_aduana', 'target', 'fecha_arribo_planta']) || !$operacion->exists) {
                $operacion->calcularStatusPorDias();
            }
        });

        static::created(function ($operacion) {
            // Generar historial inicial después de crear
            $operacion->refresh(); // Asegurarse de que tiene todos los datos
            $resultado = $operacion->calcularStatusPorDias();
            $operacion->generarHistorialCambioStatus($resultado);
            $operacion->saveQuietly();
        });
    }
}
