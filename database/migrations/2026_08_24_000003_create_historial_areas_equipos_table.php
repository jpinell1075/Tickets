<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_areas_equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_equipo')->constrained('equipos')->cascadeOnDelete();
            $table->foreignId('id_area')->constrained('areas')->cascadeOnDelete();
            $table->dateTime('fecha_asignacion');
            $table->dateTime('fecha_fin')->nullable();
            $table->text('observacion')->nullable();
            $table->nullableUserStamps();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_areas_equipos');
    }
};

