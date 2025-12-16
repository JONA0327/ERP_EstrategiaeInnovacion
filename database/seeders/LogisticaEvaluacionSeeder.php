<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogisticaEvaluacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $area = 'Logistica';

        // 1. Limpiamos los criterios existentes de Logística para evitar duplicados si corres el seeder varias veces
        DB::table('criterios_evaluacion')->where('area', $area)->delete();

        // 2. Definimos los criterios basados en tus imágenes.
        // NOTA: Por favor, actualiza los textos y los pesos ('peso') con la información exacta de tus imágenes.
        $criterios = [
            [
                'criterio' => 'Exactitud de Inventarios (IRA)',
                'descripcion' => 'Mantiene la confiabilidad del inventario físico contra el sistema.',
                'peso' => 20, // Ajustar según imagen
            ],
            [
                'criterio' => 'Cumplimiento de Entregas (On-Time Delivery)',
                'descripcion' => 'Asegura que los pedidos lleguen al cliente en el tiempo prometido.',
                'peso' => 20, // Ajustar según imagen
            ],
            [
                'criterio' => 'Costo Logístico',
                'descripcion' => 'Gestión eficiente de los costos de transporte y almacenamiento.',
                'peso' => 15, // Ajustar según imagen
            ],
            [
                'criterio' => 'Gestión de Devoluciones',
                'descripcion' => 'Eficiencia en el manejo de logística inversa y RMA.',
                'peso' => 15, // Ajustar según imagen
            ],
            [
                'criterio' => 'Seguridad y Mantenimiento',
                'descripcion' => 'Cumplimiento de normas de seguridad e higiene en almacén y transporte.',
                'peso' => 15, // Ajustar según imagen
            ],
            [
                'criterio' => 'Trabajo en Equipo y Comunicación',
                'descripcion' => 'Colaboración efectiva con ventas, compras y almacén.',
                'peso' => 15, // Ajustar según imagen
            ],
        ];

        // 3. Insertamos los datos
        foreach ($criterios as $item) {
            DB::table('criterios_evaluacion')->insert([
                'area' => $area,
                'criterio' => $item['criterio'],
                'descripcion' => $item['descripcion'],
                'peso' => $item['peso'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}