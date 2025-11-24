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
        Schema::create('transportes', function (Blueprint $table) {
            $table->id();
            $table->string('transporte');
            $table->enum('tipo_operacion', ['Aerea', 'Terrestre', 'Maritima', 'Ferrocarril']);
            $table->timestamps();
            
            $table->index('tipo_operacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportes');
    }
};
