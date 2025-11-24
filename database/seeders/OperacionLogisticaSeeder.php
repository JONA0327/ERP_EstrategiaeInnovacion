<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Logistica\OperacionLogistica;
use App\Models\Empleado;

class OperacionLogisticaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar empleados del área de logística
        $empleadosLogistica = Empleado::where('area', 'LIKE', '%Logistica%')
            ->orWhere('area', 'LIKE', '%Logística%')
            ->get();

        // Si no hay empleados de logística, usar los existentes o crear algunos básicos
        if ($empleadosLogistica->isEmpty()) {
            // Buscar cualquier empleado para usar como ejemplo
            $empleadosLogistica = Empleado::take(3)->get();
            
            // Si no hay empleados, crear algunos básicos
            if ($empleadosLogistica->isEmpty()) {
                $empleadosLogistica = collect([
                    Empleado::create([
                        'nombre' => 'Juan Carlos López',
                        'area' => 'Logística',
                        'puesto' => 'Ejecutivo Logístico Senior',
                    ]),
                    Empleado::create([
                        'nombre' => 'María Elena García', 
                        'area' => 'Logística',
                        'puesto' => 'Ejecutivo de Importaciones',
                    ]),
                ]);
            }
        }

        // Crear 50 operaciones logísticas de ejemplo
        OperacionLogistica::factory(50)->create()->each(function ($operacion) use ($empleadosLogistica) {
            // Asignar un empleado de logística al azar
            $empleado = $empleadosLogistica->random();
            $operacion->update(['ejecutivo_empleado_id' => $empleado->id]);
            
            // Calcular días en tránsito
            $operacion->calcularDiasTransito();
            $operacion->save();
        });

        $this->command->info('Se han creado 50 operaciones logísticas con empleados asignados.');
    }
}
