<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_equipo')->nullable()->constrained('equipos')->nullOnDelete();
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('id_encargado')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('id_programacion_mantenimiento')->nullable()->constrained('planificacion_mantenimientos')->nullOnDelete();
            $table->string('tipo')->nullable(); // 'hardware', 'software', 'network', 'other'
            $table->enum('prioridad', ['baja', 'media', 'alta', 'critica'])->default('media');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->enum('estado_actual', ['abierto', 'en_proceso', 'resuelto', 'cerrado', 'cancelado'])->default('abierto');
            $table->dateTime('fecha_creacion');
            $table->text('observacion_resolucion')->nullable();
            $table->dateTime('fecha_resolucion')->nullable();
            $table->date('fecha_planificada')->nullable();  // fecha en la que se debe realizar el mantenimiento segun planificacion
            $table->nullableUserStamps();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};