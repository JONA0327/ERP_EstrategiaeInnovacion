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
        $now = Carbon::now();

        // 1. SOFT SKILLS (COMPETENCIAS BLANDAS) - 40%
        // Se guardan bajo el área 'Recursos Humanos' para que Admin RH las encuentre.
        $softSkills = [
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

        // 2. HARD SKILLS (COMPETENCIAS TÉCNICAS) - 60%
        // Específicas por departamento.
        $areasConfig = [
            'Logistica' => [
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
            ],
            'Legal' => [
                [
                    'criterio' => 'Diseño de Programas y Diagnósticos',
                    'descripcion' => 'Diseño de programas de cumplimiento normativo y realización precisa de diagnósticos de comercio exterior.',
                    'peso' => 20,
                ],
                [
                    'criterio' => 'Representación y Defensa',
                    'descripcion' => 'Efectividad en la representación ante autoridades aduaneras y regulatorias en litigios o procedimientos.',
                    'peso' => 20,
                ],
                [
                    'criterio' => 'Opiniones Legales y Reporte',
                    'descripcion' => 'Calidad en la elaboración de opiniones legales, análisis de riesgo y entrega puntual del reporte de actividades.',
                    'peso' => 20,
                ],
            ],
            // Puedes agregar más áreas aquí (Sistemas, Pedimentos, etc.)
        ];

        // 3. LIMPIEZA E INSERCIÓN
        $dataToInsert = [];

        // Definimos las áreas a limpiar (Técnicas + Recursos Humanos)
        $areasToClean = array_keys($areasConfig);
        $areasToClean[] = 'Recursos Humanos';

        // Borramos criterios viejos para evitar duplicados
        DB::table('criterios_evaluacion')->whereIn('area', $areasToClean)->delete();

        // A. Insertar Criterios Técnicos (Con su nombre de área real)
        foreach ($areasConfig as $areaName => $technicalSkills) {
            foreach ($technicalSkills as $tech) {
                $dataToInsert[] = [
                    'area' => $areaName,
                    'criterio' => $tech['criterio'],
                    'descripcion' => $tech['descripcion'],
                    'peso' => $tech['peso'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // B. Insertar Soft Skills (Bajo el área "Recursos Humanos")
        foreach ($softSkills as $soft) {
            $dataToInsert[] = [
                'area' => 'Recursos Humanos', // CLAVE: Etiqueta especial para filtrado
                'criterio' => $soft['criterio'],
                'descripcion' => $soft['descripcion'],
                'peso' => $soft['peso'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insertar todo de una sola vez
        DB::table('criterios_evaluacion')->insert($dataToInsert);
    }
}