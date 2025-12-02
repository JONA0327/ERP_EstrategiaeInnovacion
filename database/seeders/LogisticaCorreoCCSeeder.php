<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Logistica\LogisticaCorreoCC;

class LogisticaCorreoCCSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $correos = [
            [
                'nombre' => 'Gerente Logística',
                'email' => 'gerencia.logistica@empresa.com',
                'tipo' => 'administrador',
                'descripcion' => 'Gerente del departamento de logística',
                'activo' => true,
            ],
            [
                'nombre' => 'Coordinador Operaciones',
                'email' => 'coordinador.ops@empresa.com',
                'tipo' => 'supervisor',
                'descripcion' => 'Coordinador de operaciones logísticas',
                'activo' => true,
            ],
            [
                'nombre' => 'Analista Senior',
                'email' => 'analista.senior@empresa.com',
                'tipo' => 'notificacion',
                'descripcion' => 'Analista senior de reportes',
                'activo' => true,
            ],
            [
                'nombre' => 'Director Regional',
                'email' => 'director.regional@empresa.com',
                'tipo' => 'administrador',
                'descripcion' => 'Director regional de operaciones',
                'activo' => false,
            ],
            [
                'nombre' => 'Soporte TI',
                'email' => 'soporte.ti@empresa.com',
                'tipo' => 'notificacion',
                'descripcion' => 'Equipo de soporte técnico para reportes',
                'activo' => true,
            ]
        ];

        foreach ($correos as $correo) {
            LogisticaCorreoCC::firstOrCreate(
                ['email' => $correo['email']],
                $correo
            );
        }
    }
}