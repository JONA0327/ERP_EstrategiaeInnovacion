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
        // Campos de nombres directos (no IDs)
        'ejecutivo',
        'cliente',
        'agente_aduanal',
        'transporte',
        
        // Campos de operación
        'operacion',
        'operacion_tipo',
        'proveedor_o_cliente',
        'fecha_embarque',
        'no_factura',
        'tipo_operacion',
        'tipo_operacion_enum',
        'clave',
        'referencia_interna',
        'aduana',
        'referencia_aa',
        'no_pedimento',
        'fecha_arribo_aduana',
        'guia_bl',
        'status',
        'status_enum',
        'fecha_modulacion',
        'fecha_arribo_planta',
        'resultado',
        'target',
        'dias_transito',
        'post_operacion_id',
        'status_calculado',
        'color_status',
        'dias_transcurridos_calculados',
        'fecha_ultimo_calculo',
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
        if ($this->fecha_embarque && $this->fecha_arribo_planta) {
            $this->dias_transito = $this->fecha_embarque->diffInDays($this->fecha_arribo_planta);
            return $this->dias_transito;
        }
        
        if ($this->fecha_embarque && !$this->fecha_arribo_planta) {
            $this->dias_transito = $this->fecha_embarque->diffInDays(now());
            return $this->dias_transito;
        }
        
        return null;
    }

    /**
     * Verificar si la operación está retrasada
     */
    public function estaRetrasada()
    {
        if ($this->target && $this->dias_transito) {
            return $this->dias_transito > $this->target;
        }
        return false;
    }

    /**
     * Calcular el status automático basado en días transcurridos
     * Lógica: 
     * - Rojo: Más de 3 días desde fecha_arribo_aduana y no está Done
     * - Amarillo: Menos de 3 días desde fecha_arribo_aduana y no está Done
     * - Verde: Solo cuando status_calculado = 'Done'
     */
    public function calcularStatusAutomatico()
    {
        $fechaRegistro = now();
        
        // Si no hay fecha de arribo a aduana, no se puede calcular
        if (!$this->fecha_arribo_aduana) {
            $this->color_status = 'sin_fecha';
            $this->status_calculado = 'In Process';
            $this->dias_transcurridos_calculados = null;
            $this->fecha_ultimo_calculo = $fechaRegistro;
            return;
        }

        // Calcular días transcurridos desde fecha_arribo_aduana
        $fechaArribo = \Carbon\Carbon::parse($this->fecha_arribo_aduana);
        $diasTranscurridos = $fechaArribo->diffInDays($fechaRegistro);
        
        $this->dias_transcurridos_calculados = $diasTranscurridos;
        $this->fecha_ultimo_calculo = $fechaRegistro;

        // Si está marcado como Done, siempre verde
        if ($this->status_calculado === 'Done') {
            $this->color_status = 'verde';
            return;
        }

        // Aplicar lógica de colores basada en días
        if ($diasTranscurridos > 3) {
            $this->color_status = 'rojo';
            $this->status_calculado = 'Out of Metric';
        } else {
            $this->color_status = 'amarillo';
            $this->status_calculado = 'In Process';
        }
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
     * Boot del modelo para calcular automáticamente el status
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($operacion) {
            $operacion->calcularStatusAutomatico();
        });
    }
}
