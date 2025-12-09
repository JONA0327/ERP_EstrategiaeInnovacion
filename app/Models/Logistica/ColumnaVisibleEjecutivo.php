<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use App\Models\Empleado;

class ColumnaVisibleEjecutivo extends Model
{
    protected $table = 'columnas_visibles_ejecutivo';

    protected $fillable = [
        'empleado_id',
        'columna',
        'visible'
    ];

    protected $casts = [
        'visible' => 'boolean'
    ];

    // Columnas que son opcionales (las nuevas que agregamos)
    public static $columnasOpcionales = [
        'tipo_carga' => 'Tipo de Carga',
        'tipo_incoterm' => 'Incoterm',
        'puerto_salida' => 'Puerto de Salida'
    ];

    // Columnas predeterminadas (siempre visibles, no se pueden ocultar)
    public static $columnasPredeterminadas = [
        'id' => 'No.',
        'ejecutivo' => 'Ejecutivo',
        'operacion' => 'Operación',
        'cliente' => 'Cliente',
        'proveedor_o_cliente' => 'Proveedor o Cliente',
        'fecha_embarque' => 'Fecha de Embarque',
        'no_factura' => 'No. De Factura',
        'tipo_operacion_enum' => 'T. Operación',
        'clave' => 'Clave',
        'referencia_interna' => 'Referencia Interna',
        'aduana' => 'Aduana',
        'agente_aduanal' => 'A.A',
        'referencia_aa' => 'Referencia A.A',
        'no_pedimento' => 'No Ped',
        'transporte' => 'Transporte',
        'fecha_arribo_aduana' => 'Fecha de Arribo a Aduana',
        'guia_bl' => 'Guía //BL',
        'status' => 'Status',
        'fecha_modulacion' => 'Fecha de Modulación',
        'fecha_arribo_planta' => 'Fecha de Arribo a Planta',
        'resultado' => 'Resultado',
        'target' => 'Target',
        'dias_transito' => 'Días en Tránsito',
        'post_operaciones' => 'Post-Operaciones',
        'comentarios' => 'Comentarios'
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    /**
     * Obtener las columnas visibles para un ejecutivo específico
     */
    public static function getColumnasVisiblesParaEjecutivo($empleadoId)
    {
        $configuracion = self::where('empleado_id', $empleadoId)
            ->where('visible', true)
            ->pluck('columna')
            ->toArray();
        
        return $configuracion;
    }

    /**
     * Guardar configuración de columnas para un ejecutivo
     */
    public static function guardarConfiguracion($empleadoId, $columnas)
    {
        // Eliminar configuración anterior
        self::where('empleado_id', $empleadoId)->delete();
        
        // Guardar nueva configuración
        foreach (self::$columnasOpcionales as $columna => $nombre) {
            self::create([
                'empleado_id' => $empleadoId,
                'columna' => $columna,
                'visible' => in_array($columna, $columnas)
            ]);
        }
    }
}
