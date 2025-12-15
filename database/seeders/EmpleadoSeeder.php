<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Empleado;
use Illuminate\Support\Facades\Hash;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lista extraída directamente de tus archivos Excel/CSV
        // El campo 'id' aquí se guardará en 'id_empleado' para hacer el match.
        $personalReloj = [
            ['id' => '1',   'nombre' => 'AGAG'],
            ['id' => '2',   'nombre' => 'LHC'],
            ['id' => '22',  'nombre' => 'ZIMU'],
            ['id' => '23',  'nombre' => 'SRC'],
            ['id' => '30',  'nombre' => 'NBGH'],
            ['id' => '56',  'nombre' => 'JJAC'],
            ['id' => '57',  'nombre' => 'MMM'],
            ['id' => '70',  'nombre' => 'KMEG'],
            ['id' => '73',  'nombre' => 'MRR'],
            ['id' => '74',  'nombre' => 'AAHH'],
            ['id' => '78',  'nombre' => 'OscarM'],
            ['id' => '80',  'nombre' => 'SofiaC'],
            ['id' => '82',  'nombre' => 'AlissonC'],
            ['id' => '84',  'nombre' => 'MarianaC'],
            ['id' => '87',  'nombre' => 'KarenB'],
            ['id' => '90',  'nombre' => 'JessicaE'],
            ['id' => '91',  'nombre' => 'FernandaS'],
            ['id' => '92',  'nombre' => 'ValeriaL'],
            ['id' => '93',  'nombre' => 'AlejandroC'],
            ['id' => '95',  'nombre' => 'JonathanL'],
            ['id' => '96',  'nombre' => 'JacobM'],
            ['id' => '97',  'nombre' => 'DavidR'],
            ['id' => '98',  'nombre' => 'FelipeR'],
            ['id' => '99',  'nombre' => 'FatimaT'],
            ['id' => '100', 'nombre' => 'MayraC'],
            ['id' => '101', 'nombre' => 'ErikaM'],
            ['id' => '102', 'nombre' => 'CarlosM'],
            ['id' => '103', 'nombre' => 'IsaacQ'],
            ['id' => '104', 'nombre' => 'JaimeM'],
        ];

        foreach ($personalReloj as $persona) {
            // 1. Crear Usuario (Login)
            // Generamos un correo dummy usando el ID para asegurar unicidad
            $email = strtolower(preg_replace('/\s+/', '', $persona['nombre'])) . '.' . $persona['id'] . '@reloj.com';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $persona['nombre'],
                    'password' => Hash::make('password'), // Contraseña genérica
                    'role' => 'user',
                    'status' => 'approved', // Aprobado para que puedan entrar si es necesario
                ]
            );

            // 2. Crear Ficha de Empleado (Datos RH)
            Empleado::updateOrCreate(
                ['user_id' => $user->id],
                [
                    // ¡AQUÍ ESTÁ LA MAGIA! 👇
                    'id_empleado' => $persona['id'], // Esto vincula con la columna "No" del Excel
                    'nombre' => $persona['nombre'],
                    'correo' => $email,
                    'area' => 'Office', // Valor por defecto según tu Excel
                    'posicion' => 'Personal Operativo',
                ]
            );
        }
    }
}