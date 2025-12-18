<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        $now = Carbon::now();

        // 1. Limpiamos criterios anteriores
        DB::table('criterios_evaluacion')->where('area', $area)->delete();

        // 2. Definición de Criterios
        // Estrategia: 3 Objetivos Técnicos (60%) + 8 Competencias (40%) = 100%
        
        $criterios = [
            // --- OBJETIVOS TÉCNICOS (Hard Skills) - 60% del Total ---
            [
                'criterio' => 'Operatividad Import/Export',
                'descripcion' => 'Cumplimiento, seguimiento en tiempo y forma de las actividades por cada una de las operaciones de importación y exportación.',
                'peso' => 20,
            ],
            [
                'criterio' => 'Cumplimiento Legal y Normativo',
                'descripcion' => 'Cumplimiento de la legislación aplicable y vigente por cada una de las operaciones aduaneras y logísticas.',
                'peso' => 20,
            ],
            [
                'criterio' => 'Resultados y Mejora Continua',
                'descripcion' => 'Presentación mensual de resultados e implementación de metodología de mejora continua en los procesos.',
                'peso' => 20,
            ],

            // --- COMPETENCIAS BLANDAS (Soft Skills) - 40% del Total ---
            [
                'criterio' => 'Puntualidad y Asistencia',
                'descripcion' => 'Cumple consistentemente con los horarios establecidos y mantiene un registro de asistencia impecable.',
                'peso' => 5,
            ],
            [
                'criterio' => 'Iniciativa y Proactividad',
                'descripcion' => 'Anticipa necesidades y actúa sin necesidad de supervisión constante para resolver problemas.',
                'peso' => 5,
            ],
            [
                'criterio' => 'Cumplimiento de Normas y Procedimientos',
                'descripcion' => 'Se apega estrictamente a las políticas internas y códigos de conducta de la organización.',
                'peso' => 5,
            ],
            [
                'criterio' => 'Trabajo en Equipo',
                'descripcion' => 'Colabora activamente con compañeros y otros departamentos para alcanzar objetivos comunes.',
                'peso' => 5,
            ],
            [
                'criterio' => 'Comunicación',
                'descripcion' => 'Transmite ideas e información de manera clara, oportuna y respetuosa, tanto oral como escrita.',
                'peso' => 5,
            ],
            [
                'criterio' => 'Actitud Profesional',
                'descripcion' => 'Mantiene un comportamiento ético, respetuoso y formal en el entorno laboral.',
                'peso' => 5,
            ],
            [
                'criterio' => 'Adaptabilidad',
                'descripcion' => 'Capacidad para ajustarse eficazmente a cambios en el entorno, tareas o responsabilidades.',
                'peso' => 5,
            ],
            [
                'criterio' => 'Manejo de Estrés',
                'descripcion' => 'Mantiene la calma y la eficiencia en situaciones de alta presión o carga de trabajo.',
                'peso' => 5,
            ],
        ];

        // 3. Insertar en BD
        $data = [];
        foreach ($criterios as $item) {
            $data[] = [
                'area' => $area,
                'criterio' => $item['criterio'],
                'descripcion' => $item['descripcion'],
                'peso' => $item['peso'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('criterios_evaluacion')->insert($data);
    }
}