<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('id_area')->nullable()->constrained('areas')->nullOnDelete();
            $table->enum('estado', ['activo', 'inactivo', 'mantenimiento'])->default('activo');
            $table->string('tipo')->nullable();
            $table->string('cod_inventario')->nullable()->unique();
            $table->string('nombre')->nullable();
            $table->nullableUserStamps();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('equipo_comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->text('comentario');
            $table->nullableUserStamps();
            $table->timestamps();
        });


        Schema::create('equipo_archivos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            
            $table->string('nombre');
            $table->string('nombre_interno');

            $table->nullableUserStamps();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_comentarios');
        Schema::dropIfExists('equipo_archivos');
        Schema::dropIfExists('equipos');
    }
};