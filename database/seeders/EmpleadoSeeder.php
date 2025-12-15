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
            ['id' => '1',   'nombre' => 'AGAG', 'area' => ''],
            ['id' => '2',   'nombre' => 'LHC', 'area' => ''],
            ['id' => '22',  'nombre' => 'ZIMU', 'area' => ''],
            ['id' => '23',  'nombre' => 'SRC', 'area' => ''],
            ['id' => '30',  'nombre' => 'NBGH', 'area' => ''],
            ['id' => '56',  'nombre' => 'JJAC', 'area' => ''],
            ['id' => '57',  'nombre' => 'MMM', 'area' => ''],
            ['id' => '70',  'nombre' => 'KMEG', 'area' => ''],
            ['id' => '73',  'nombre' => 'MRR', 'area' => ''],
            ['id' => '74',  'nombre' => 'AAHH', 'area' => ''],
            ['id' => '78',  'nombre' => 'OscarM', 'area' => ''],
            ['id' => '80',  'nombre' => 'SofiaC', 'area' => ''],
            ['id' => '82',  'nombre' => 'AlissonC', 'area' => ''],
            ['id' => '84',  'nombre' => 'MarianaC', 'area' => 'RH'],
            ['id' => '86',  'nombre' => 'IvanR', 'area' => 'Logistica'],
            ['id' => '87',  'nombre' => 'KarenB', 'area' => 'Logistica'],
            ['id' => '90',  'nombre' => 'JessicaE', 'area' => 'Comercio Exterior'],
            ['id' => '91',  'nombre' => 'FernandaS', 'area' => 'Comercio Exterior'],
            ['id' => '95',  'nombre' => 'JonathanL', 'area' => 'TI'],
            ['id' => '96',  'nombre' => 'JacobM', 'area' => 'Logistica'],
            ['id' => '97',  'nombre' => 'DavidR', 'area' => 'Legal'],
            ['id' => '98',  'nombre' => 'FelipeR', 'area' => 'Comercio Exterior'],
            ['id' => '99',  'nombre' => 'FatimaT', 'area' => 'Logistica'],
            ['id' => '100', 'nombre' => 'MayraC', 'area' => 'Comercio Exterior'],
            ['id' => '101', 'nombre' => 'ErikaM', 'area' => 'Comercio Exterior'],
            ['id' => '102', 'nombre' => 'CarlosM', 'area' => 'Legal'],
            ['id' => '103', 'nombre' => 'IsaacQ', 'area' => 'TI'],
            ['id' => '104', 'nombre' => 'JaimeM', 'area' => '']
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