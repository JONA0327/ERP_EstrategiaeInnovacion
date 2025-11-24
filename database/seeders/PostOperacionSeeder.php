<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Logistica\PostOperacion;

class PostOperacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $postOperaciones = [
            ['post_operacion' => 'Entrega completa', 'status' => 'completada'],
            ['post_operacion' => 'Documentos pendientes', 'status' => 'pendiente'],
            ['post_operacion' => 'Reclamo con proveedor', 'status' => 'pendiente'],
            ['post_operacion' => 'Facturación', 'status' => 'completada'],
            ['post_operacion' => 'Revisión de calidad', 'status' => 'pendiente'],
            ['post_operacion' => 'Archivo digital', 'status' => 'completada'],
            ['post_operacion' => 'No aplica', 'status' => 'no_aplica'],
        ];

        foreach ($postOperaciones as $postOperacion) {
            PostOperacion::create($postOperacion);
        }
    }
}