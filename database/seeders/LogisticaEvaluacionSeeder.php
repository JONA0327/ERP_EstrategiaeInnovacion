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

        // ==========================================
        // 1. SOFT SKILLS (COMPETENCIAS BLANDAS) - 40%
        // ==========================================
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

        // ==========================================
        // 2. HARD SKILLS (COMPETENCIAS TÉCNICAS) - 60%
        // ==========================================
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

        // ==========================================
        // 3. EVALUACIÓN DE SUPERVISOR (100% TOTAL)
        // ==========================================
        // Estrategia de pesos: 25% por Categoría para sumar 100% exacto.
        $criteriosSupervisor = [
            // CATEGORÍA 1: LIDERAZGO (25%) -> 6, 6, 6, 7
            [
                'categoria' => 'Liderazgo y apoyo al equipo',
                'indicador' => 'Brinda orientación clara sobre las tareas y prioridades.',
                'peso' => 6
            ],
            [
                'categoria' => 'Liderazgo y apoyo al equipo',
                'indicador' => 'Está disponible para resolver dudas o apoyar cuando se requiere.',
                'peso' => 6
            ],
            [
                'categoria' => 'Liderazgo y apoyo al equipo',
                'indicador' => 'Genera confianza y un ambiente de trabajo respetuoso.',
                'peso' => 6
            ],
            [
                'categoria' => 'Liderazgo y apoyo al equipo',
                'indicador' => 'Motiva al equipo a dar lo mejor de si.',
                'peso' => 7
            ],

            // CATEGORÍA 2: COMUNICACIÓN (25%) -> 6, 6, 6, 7
            [
                'categoria' => 'Comunicación',
                'indicador' => 'Comunica instrucciones de manera clara y oportuna.',
                'peso' => 6
            ],
            [
                'categoria' => 'Comunicación',
                'indicador' => 'Escucha activamente las inquietudes del equipo.',
                'peso' => 6
            ],
            [
                'categoria' => 'Comunicación',
                'indicador' => 'Informa cambios, decisiones o prioridades a tiempo.',
                'peso' => 6
            ],
            [
                'categoria' => 'Comunicación',
                'indicador' => 'Mantiene una comunicación respetuosa y profesional.',
                'peso' => 7
            ],

            // CATEGORÍA 3: ORGANIZACIÓN (25%) -> 6, 6, 6, 7
            [
                'categoria' => 'Organización y gestión del trabajo',
                'indicador' => 'Define prioridades claras.',
                'peso' => 6
            ],
            [
                'categoria' => 'Organización y gestión del trabajo',
                'indicador' => 'Distribuye el trabajo de forma equitativa.',
                'peso' => 6
            ],
            [
                'categoria' => 'Organización y gestión del trabajo',
                'indicador' => 'Da seguimiento adecuado a las actividades asignadas.',
                'peso' => 6
            ],
            [
                'categoria' => 'Organización y gestión del trabajo',
                'indicador' => 'Cumple y promueve el cumplimiento de tiempos y objetivos.',
                'peso' => 7
            ],

            // CATEGORÍA 4: TOMA DE DECISIONES (25%) -> 5, 5, 5, 5, 5
            [
                'categoria' => 'Toma de decisiones, feedback y desarrollo',
                'indicador' => 'Considera la opinión del equipo cuando es pertinente.',
                'peso' => 5
            ],
            [
                'categoria' => 'Toma de decisiones, feedback y desarrollo',
                'indicador' => 'Resuelve conflictos de forma justa.',
                'peso' => 5
            ],
            [
                'categoria' => 'Toma de decisiones, feedback y desarrollo',
                'indicador' => 'Reconoce el buen desempeño.',
                'peso' => 5
            ],
            [
                'categoria' => 'Toma de decisiones, feedback y desarrollo',
                'indicador' => 'Señala áreas de mejora de manera respetuosa.',
                'peso' => 5
            ],
            [
                'categoria' => 'Toma de decisiones, feedback y desarrollo',
                'indicador' => 'Apoya el desarrollo profesional del equipo.',
                'peso' => 5
            ],
        ];

        // ==========================================
        // 4. LIMPIEZA E INSERCIÓN
        // ==========================================
        $dataToInsert = [];

        // Definimos las áreas a limpiar: Técnicas + RH + Evaluación Supervisor
        $areasToClean = array_keys($areasConfig);
        $areasToClean[] = 'Recursos Humanos';
        $areasToClean[] = 'Evaluación Supervisor';

        // Borramos criterios viejos para evitar duplicados
        DB::table('criterios_evaluacion')->whereIn('area', $areasToClean)->delete();

        // A. Insertar Criterios Técnicos (Hard Skills)
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

        // B. Insertar Soft Skills (RH)
        foreach ($softSkills as $soft) {
            $dataToInsert[] = [
                'area' => 'Recursos Humanos',
                'criterio' => $soft['criterio'],
                'descripcion' => $soft['descripcion'],
                'peso' => $soft['peso'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // C. Insertar Criterios Supervisor (NUEVO CON PESOS AJUSTADOS)
        foreach ($criteriosSupervisor as $item) {
            $dataToInsert[] = [
                'area' => 'Evaluación Supervisor',
                'criterio' => $item['categoria'],
                'descripcion' => $item['indicador'],
                'peso' => $item['peso'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insertar todo de una sola vez
        if (!empty($dataToInsert)) {
            DB::table('criterios_evaluacion')->insert($dataToInsert);
        }
    }
}