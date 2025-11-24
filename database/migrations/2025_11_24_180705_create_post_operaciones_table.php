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
        Schema::create('post_operaciones', function (Blueprint $table) {
            $table->id();
            $table->string('post_operacion');
            $table->enum('status', ['completada', 'pendiente', 'no_aplica'])->default('pendiente');
            $table->timestamps();
            
            $table->index('post_operacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_operaciones');
    }
};
