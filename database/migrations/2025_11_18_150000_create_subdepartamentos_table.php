<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subdepartamentos', function (Blueprint $table) {
            $table->id();
            $table->string('area'); // Área principal (e.g. Comercio Exterior)
            $table->string('nombre'); // Nombre del subdepartamento
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['area', 'nombre']);
        });

        // Seed inicial para Comercio Exterior
        DB::table('subdepartamentos')->insert([
            ['area' => 'Comercio Exterior', 'nombre' => 'Auditoria', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['area' => 'Comercio Exterior', 'nombre' => 'Operaciones Virtuales', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['area' => 'Comercio Exterior', 'nombre' => 'Anexo 24', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('subdepartamentos');
    }
};
