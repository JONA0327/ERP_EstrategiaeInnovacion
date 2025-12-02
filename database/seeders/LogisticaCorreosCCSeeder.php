<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogisticaCorreosCCSeeder extends Seeder
{
    public function run()
    {
        // Insertar administrador de logística por defecto
        DB::table('logistica_correos_cc')->insert([
            [
                'nombre' => 'Administrador Logística',
                'email' => 'logistica@estrategiaeinnovacion.com.mx',
                'tipo' => 'administrador',
                'descripcion' => 'Administrador principal del área de logística',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Supervisor Logística',
                'email' => 'supervisor.logistica@estrategiaeinnovacion.com.mx', 
                'tipo' => 'supervisor',
                'descripcion' => 'Supervisor del área de logística',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Notificaciones Sistemas',
                'email' => 'sistemas@estrategiaeinnovacion.com.mx',
                'tipo' => 'notificacion',
                'descripcion' => 'Área de sistemas para notificaciones',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}