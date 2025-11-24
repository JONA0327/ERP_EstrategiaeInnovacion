<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Logistica\OperacionLogistica;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Logistica\OperacionLogistica>
 */
class OperacionLogisticaFactory extends Factory
{
    /**
     * El nombre del modelo que corresponde a este factory.
     *
     * @var string
     */
    protected $model = OperacionLogistica::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fechaEmbarque = $this->faker->dateTimeBetween('-3 months', 'now');
        
        // Calcular fechas posteriores basadas en la fecha de embarque
        $fechaArriboAduana = $this->faker->dateTimeBetween(
            $fechaEmbarque->format('Y-m-d'), 
            date('Y-m-d', strtotime($fechaEmbarque->format('Y-m-d') . ' +14 days'))
        );
        
        $fechaModulacion = $this->faker->dateTimeBetween(
            $fechaArriboAduana->format('Y-m-d'), 
            date('Y-m-d', strtotime($fechaArriboAduana->format('Y-m-d') . ' +7 days'))
        );
        
        $fechaArriboPlanta = $this->faker->dateTimeBetween(
            $fechaModulacion->format('Y-m-d'), 
            date('Y-m-d', strtotime($fechaModulacion->format('Y-m-d') . ' +7 days'))
        );
        
        $target = $this->faker->numberBetween(5, 20);
        $diasTransito = $fechaEmbarque->diff($fechaArriboPlanta)->days;
        
        return [
            'ejecutivo_empleado_id' => null, // Se asignará después con empleados de logística
            'operacion' => $this->faker->randomElement(['IMP', 'EXP']) . '-' . $this->faker->numberBetween(1000, 9999),
            'cliente' => $this->faker->company(),
            'proveedor_o_cliente' => $this->faker->company() . ' ' . $this->faker->randomElement(['S.A.', 'LLC', 'Corp']),
            'fecha_embarque' => $fechaEmbarque,
            'no_factura' => 'FAC-' . $this->faker->numberBetween(10000, 99999),
            'tipo_operacion' => $this->faker->randomElement(['Importación', 'Exportación', 'Tránsito']),
            'clave' => 'CL' . $this->faker->numberBetween(100, 999),
            'referencia_interna' => 'REF-INT-' . $this->faker->numberBetween(1000, 9999),
            'aduana' => $this->faker->randomElement(['Veracruz', 'CDMX', 'Tijuana', 'Nuevo Laredo', 'Manzanillo']),
            'agente_aduanal' => 'AA' . $this->faker->numberBetween(100, 999),
            'referencia_aa' => 'REF-AA-' . $this->faker->numberBetween(1000, 9999),
            'no_pedimento' => 'PED' . $this->faker->numberBetween(100000, 999999),
            'transporte' => $this->faker->randomElement(['Terrestre', 'Marítimo', 'Aéreo', 'Ferroviario']),
            'fecha_arribo_aduana' => $fechaArriboAduana,
            'guia_bl' => 'BL-' . $this->faker->numberBetween(100000, 999999),
            'status' => $this->faker->randomElement(['En Tránsito', 'En Aduana', 'Entregado', 'Pendiente', 'Cancelado']),
            'fecha_modulacion' => $fechaModulacion,
            'fecha_arribo_planta' => $fechaArriboPlanta,
            'resultado' => $this->faker->numberBetween(0, 100),
            'target' => $target,
            'dias_transito' => $diasTransito,
            'pendientes_pos_operaciones' => $this->faker->boolean(30), // 30% probabilidad de tener pendientes
            'comentarios' => $this->faker->optional(0.7)->sentence(10),
        ];
    }
}
