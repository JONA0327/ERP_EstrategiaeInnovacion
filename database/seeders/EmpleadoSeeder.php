<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CARGA DE EMPLEADOS REALES (MANUAL)
        $empleados = [
            // ===== DIRECCIÓN =====
            [
                'id_empleado' => '0',
                'nombre' => 'Guillermo Aguilera',
                'correo' => 'guillermo.aguilera@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Direccion',
                'supervisor' => null,
            ],

            // ===== REPORTAN A GUILLERMO =====
            [
                'id_empleado' => '36',
                'nombre' => 'Liliana Hernandez Castilla',
                'correo' => 'liliana.hernandez@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Administracion',
                'supervisor' => 'Guillermo Aguilera',
            ],
            [
                'id_empleado' => '23',
                'nombre' => 'Silvestre Reyes Castillo',
                'correo' => 'silvestre.castillo@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Pedimentos', // Ajustado de 'Comercio Exterior' para coincidir con tus categorías
                'supervisor' => 'Guillermo Aguilera',
            ],
            [
                'id_empleado' => '30',
                'nombre' => 'Nancy Beatriz Gomez Hernandez',
                'correo' => 'nancy.gomez@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Logistica',
                'supervisor' => 'Guillermo Aguilera',
            ],
            [
                'id_empleado' => '56',
                'nombre' => 'Jazzman Jerssain Aguilar Cisneros',
                'correo' => 'jazzman.aguilar@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Legal',
                'supervisor' => 'Guillermo Aguilera',
            ],

            // ===== REPORTAN A SILVESTRE =====
            [
                'id_empleado' => '57',
                'nombre' => 'Mario Mojica Morales',
                'correo' => 'mario.mojica@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Pedimentos', // Ajustado
                'supervisor' => 'Silvestre Reyes Castillo',
            ],
            [
                'id_empleado' => '74',
                'nombre' => 'Aneth Alejandra Herrera Hernandez',
                'correo' => 'aneth.herrera@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Pedimentos', // Ajustado
                'supervisor' => 'Silvestre Reyes Castillo',
            ],
            [
                'id_empleado' => '105',
                'nombre' => 'Patricia Mayela Rivera Martinez',
                'correo' => 'patricia.rivera@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Auditoria',
                'supervisor' => 'Silvestre Reyes Castillo',
            ],

            // ===== REPORTAN A NANCY =====
            [
                'id_empleado' => '22',
                'nombre' => 'Zaira Isabel Martinez Urbina',
                'correo' => 'zaira.martinez@empresa.com',
                'area' => 'Chronos Fullfillment',
                'posicion' => 'Logistica',
                'supervisor' => 'Nancy Beatriz Gomez Hernandez',
            ],
            [
                'id_empleado' => '60',
                'nombre' => 'Luis Eduardo Inclan Soriano',
                'correo' => 'luis.inclan@empresa.com',
                'area' => 'Siegwerk',
                'posicion' => 'Logistica',
                'supervisor' => 'Nancy Beatriz Gomez Hernandez',
            ],
            [
                'id_empleado' => '68',
                'nombre' => 'Guadalupe Jacqueline Mendoza Rodriguez',
                'correo' => 'guadalupe.mendoza@empresa.com',
                'area' => 'AGC',
                'posicion' => 'Logistica (Home Office)',
                'supervisor' => 'Nancy Beatriz Gomez Hernandez',
            ],
            [
                'id_empleado' => '70',
                'nombre' => 'Karen Michelle Echevarria Garcia',
                'correo' => 'karen.echevarria@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Logistica',
                'supervisor' => 'Nancy Beatriz Gomez Hernandez',
            ],
            [
                'id_empleado' => '73',
                'nombre' => 'Mariana Rodriguez Rueda',
                'correo' => 'mariana.rodriguez@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Logistica',
                'supervisor' => 'Nancy Beatriz Gomez Hernandez',
            ],
            [
                'id_empleado' => '78',
                'nombre' => 'Oscar Eduardo Morin Carrizales',
                'correo' => 'oscar.morin@empresa.com',
                'area' => 'PPM Industries',
                'posicion' => 'Logistica',
                'supervisor' => 'Nancy Beatriz Gomez Hernandez',
            ],
            [
                'id_empleado' => '53',
                'nombre' => 'Alisson Cassiel Pineda Martinez',
                'correo' => 'alisson.pineda@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Logistica (Practicante)',
                'supervisor' => 'Nancy Beatriz Gomez Hernandez',
            ],
            [
                'id_empleado' => '86',
                'nombre' => 'Ivan Rodriguez Juarez',
                'correo' => 'ivan.rodriguez@empresa.com',
                'area' => 'Sarrel',
                'posicion' => 'Logistica',
                'supervisor' => 'Nancy Beatriz Gomez Hernandez',
            ],
            [
                'id_empleado' => '87',
                'nombre' => 'Karen Cristina Bonal Mata',
                'correo' => 'karen.bonal@empresa.com',
                'area' => 'EB',
                'posicion' => 'Tecnica',
                'supervisor' => 'Nancy Beatriz Gomez Hernandez',
            ],
            [
                'id_empleado' => '96',
                'nombre' => 'Jacob de Jesus Medina Ramirez',
                'correo' => 'jacob.medina@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'AsiaWay',
                'supervisor' => 'Nancy Beatriz Gomez Hernandez',
            ],
            [
                'id_empleado' => '99',
                'nombre' => 'Fatima Esther Torres Arriaga',
                'correo' => 'fatima.torres@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Logistica',
                'supervisor' => 'Nancy Beatriz Gomez Hernandez',
            ],

            // ===== REPORTAN A LILIANA =====
            [
                'id_empleado' => '84',
                'nombre' => 'Mariana Calderón Ojeda',
                'correo' => 'mariana.calderon@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Recursos Humanos', // Ajustado de 'RR.HH.'
                'supervisor' => 'Liliana Hernandez Castilla',
            ],
            [
                'id_empleado' => '95',
                'nombre' => 'Jonathan Loredo Palacios',
                'correo' => 'jonathan.loredo@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'TI', // Ajustado de 'IT'
                'supervisor' => 'Liliana Hernandez Castilla',
            ],
            [
                'id_empleado' => '103',
                'nombre' => 'Isaac Covarrubias Quintero',
                'correo' => 'isaac.covarrubias@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'TI', // Ajustado de 'IT'
                'supervisor' => 'Liliana Hernandez Castilla',
            ],

            // ===== REPORTAN A MARIO =====
            [
                'id_empleado' => '90',
                'nombre' => 'Jessica Anahi Esparza Gonzalez',
                'correo' => 'jessica.esparza@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Pedimentos',
                'supervisor' => 'Mario Mojica Morales',
            ],
            [
                'id_empleado' => '91',
                'nombre' => 'Maria Fernanda Sanchez Miranda',
                'correo' => 'maria.sanchez@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Pedimentos',
                'supervisor' => 'Mario Mojica Morales',
            ],
            [
                'id_empleado' => '98',
                'nombre' => 'Felipe de Jesus Rodriguez Ledesma',
                'correo' => 'felipe.rodriguez@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Pedimentos',
                'supervisor' => 'Mario Mojica Morales',
            ],
            [
                'id_empleado' => '100',
                'nombre' => 'Mayra Susana Coreño Arriaga',
                'correo' => 'mayra.coreno@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Pedimentos',
                'supervisor' => 'Mario Mojica Morales',
            ],
            [
                'id_empleado' => '101',
                'nombre' => 'Erika Liliana Mireles Sanchez',
                'correo' => 'erika.mireles@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Pedimentos',
                'supervisor' => 'Mario Mojica Morales',
            ],

            // ===== REPORTAN A JAZZMAN =====
            [
                'id_empleado' => '80',
                'nombre' => 'Ana Sofia Cuello Aguilar',
                'correo' => 'ana.cuello@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Legal',
                'supervisor' => 'Jazzman Jerssain Aguilar Cisneros',
            ],
            [
                'id_empleado' => '97',
                'nombre' => 'Jesus David Rivera Romero',
                'correo' => 'jesus.rivera@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Legal',
                'supervisor' => 'Jazzman Jerssain Aguilar Cisneros',
            ],
            [
                'id_empleado' => '102',
                'nombre' => 'Carlos Alfonso Rivera Moran',
                'correo' => 'carlos.rivera@empresa.com',
                'area' => 'Estrategia e Innovacion',
                'posicion' => 'Legal',
                'supervisor' => 'Jazzman Jerssain Aguilar Cisneros',
            ],
        ];

        $mapaEmpleados = [];

        // Crear/Actualizar Empleados Reales
        foreach ($empleados as $emp) {
            $user = User::firstOrCreate(
                ['email' => $emp['correo']],
                [
                    'name' => $emp['nombre'],
                    'password' => Hash::make('password'),
                ]
            );

            $empleado = Empleado::updateOrCreate(
                ['id_empleado' => $emp['id_empleado']],
                [
                    'nombre' => $emp['nombre'],
                    'correo' => $emp['correo'],
                    'area' => $emp['area'],
                    'posicion' => $emp['posicion'],
                    'telefono' => null,
                    'direccion' => null,
                    'correo_personal' => null,
                    'foto_path' => null,
                    'subdepartamento_id' => null,
                    'user_id' => $user->id,
                ]
            );

            $mapaEmpleados[$emp['nombre']] = $empleado->id;
        }

        // Asignar Supervisores
        foreach ($empleados as $emp) {
            if ($emp['supervisor']) {
                Empleado::where('id_empleado', $emp['id_empleado'])
                    ->update([
                        'supervisor_id' => $mapaEmpleados[$emp['supervisor']] ?? null
                    ]);
            }
        }
    }
}