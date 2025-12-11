<?php

namespace Database\Seeders;

use App\Models\Empleado;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        // Crear registros de empleados para usuarios aprobados que no tengan empleado asignado
        User::where('status', User::STATUS_APPROVED)->each(function (User $user) {
            if (!$user->empleado) {
                Empleado::create([
                    'user_id' => $user->id,
                    'nombre' => $user->name,
                    'correo' => $user->email,
                    'area' => 'Sistemas',
                    'posicion' => null,
                    'telefono' => null,
                    'direccion' => null,
                    'correo_personal' => null,
                    'foto_path' => null,
                ]);
            }
        });
    }
}
