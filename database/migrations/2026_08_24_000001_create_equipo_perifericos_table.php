<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipo_perifericos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->string('tipo', 100);
            $table->string('marca', 150)->nullable();
            $table->string('modelo', 150)->nullable();
            $table->string('num_serie', 150)->nullable();
            $table->string('cod_inventario', 100)->nullable();
            $table->string('estado', 50)->default('operativo');
            $table->text('observacion')->nullable();
            $table->string('imagen')->nullable();
            $table->nullableUserStamps();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_perifericos');
    }
};

