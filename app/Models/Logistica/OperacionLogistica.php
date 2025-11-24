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
        'ejecutivo_empleado_id',
        'cliente_id',
        'agente_aduanal_id',
        'transporte_id',
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
        'agente_aduanal',
        'referencia_aa',
        'no_pedimento',
        'transporte',
        'fecha_arribo_aduana',
        'guia_bl',
        'status',
        'status_enum',
        'fecha_modulacion',
        'fecha_arribo_planta',
        'resultado',
        'target',
        'dias_transito',
        'pendientes_pos_operaciones',
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
        'pendientes_pos_operaciones' => 'boolean',
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
     * Obtener el color del status para la UI
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'En Tránsito' => 'bg-yellow-100 text-yellow-800',
            'En Aduana' => 'bg-blue-100 text-blue-800',
            'Entregado' => 'bg-green-100 text-green-800',
            'Pendiente' => 'bg-red-100 text-red-800',
            'Cancelado' => 'bg-gray-100 text-gray-800',
            default => 'bg-slate-100 text-slate-800',
        };
    }
}
