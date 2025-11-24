<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('cliente');
            $table->foreignId('ejecutivo_asignado_id')->nullable()->constrained('empleados')->onDelete('set null');
            $table->timestamps();
            
            $table->index('cliente');
            $table->index('ejecutivo_asignado_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
