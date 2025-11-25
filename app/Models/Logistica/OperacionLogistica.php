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
        'resultado' => 'integer',
        'target' => 'integer',
        'dias_transito' => 'integer',
        'dias_transcurridos_calculados' => 'integer',
        'fecha_ultimo_calculo' => 'datetime',
        'procesado' => 'boolean'
    ];

    /**
     * Relación con el empleado ejecutivo
     * Solo empleados del área de logística
     */
    public function ejecutivo()
    {
        return $this->belongsTo(Empleado::class, 'ejecutivo_empleado_id');
    }

    /**
     * Relación con el cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Relación con el agente aduanal
     */
    public function agenteAduanal()
    {
        return $this->belongsTo(AgenteAduanal::class, 'agente_aduanal_id');
    }

    /**
     * Relación con el transporte
     */
    public function transporte()
    {
        return $this->belongsTo(Transporte::class, 'transporte_id');
    }

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
     * Lógica: fecha_registro vs fecha_actual, comparar días con target
     */
    public function calcularStatusPorDias()
    {
        // Siempre calcular días desde registro hasta hoy (nuevo flujo corporativo)
        $fechaRegistro = $this->created_at ?? now(); 
        $fechaActual = now();
        $diasTranscurridos = $fechaRegistro->diffInDays($fechaActual);
        
        $statusAnterior = $this->status_calculado;
        $colorAnterior = $this->color_status;
        
        // Obtener target
        $target = $this->target ?? $this->calcularTargetAutomatico() ?? 3;
        
        // Determinar status según el flujo corporativo:
        
        // Si ya está marcado como done o tiene fecha de arribo a planta = VERDE
        if ($this->status_calculado === 'Done' || $this->fecha_arribo_planta) {
            $nuevoStatus = 'Done';
            $nuevoColor = 'verde';
        }
        // Si no hay fecha de arribo a aduana = EN PROCESO (amarillo)
        elseif (!$this->fecha_arribo_aduana) {
            $nuevoStatus = 'En Proceso';
            $nuevoColor = 'amarillo';
        }
        // Si los días desde registro superan el target = ROJO (Fuera de Métrica)
        elseif ($diasTranscurridos > $target) {
            $nuevoStatus = 'Fuera de Métrica';
            $nuevoColor = 'rojo';
        }
        // Si está dentro del target = AMARILLO (En Proceso)
        else {
            $nuevoStatus = 'En Proceso';
            $nuevoColor = 'amarillo';
        }

        
        // Actualizar campos
        $this->dias_transcurridos_calculados = $diasTranscurridos;
        $this->status_calculado = $nuevoStatus;
        $this->color_status = $nuevoColor;
        $this->fecha_ultimo_calculo = now();
        
        // Determinar si hubo cambio de status
        $huboCambio = ($statusAnterior !== $nuevoStatus) || ($colorAnterior !== $nuevoColor);
        
        return [
            'status' => $nuevoStatus,
            'color' => $nuevoColor,
            'dias_transcurridos' => $diasTranscurridos,
            'target' => $target,
            'cambio' => $huboCambio,
            'status_anterior' => $statusAnterior,
            'color_anterior' => $colorAnterior
        ];
    }

    /**
     * Generar historial automáticamente cuando hay cambio de status
     */
    public function generarHistorialCambioStatus($resultado)
    {
        // Solo generar historial si hubo cambio o es la primera vez
        if (!$resultado['cambio'] && $this->historicoMatrizSgm()->exists()) {
            return null;
        }
        
        $descripcion = "Status actualizado automáticamente: ";
        
        if ($resultado['status_anterior'] && $resultado['cambio']) {
            $descripcion .= "Cambió de '{$resultado['status_anterior']}' a '{$resultado['status']}'";
        } else {
            $descripcion .= "Establecido como '{$resultado['status']}'";
        }
        
        $descripcion .= ". Días transcurridos: {$resultado['dias_transcurridos']}, Target: {$resultado['target']}";
        
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
