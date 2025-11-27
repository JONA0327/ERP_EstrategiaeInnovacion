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
        Schema::create('pedimentos', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 10)->index(); // A1, A3, C1, etc.
            $table->text('descripcion');
            $table->timestamps();

            $table->unique('clave');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedimentos');
    }
};