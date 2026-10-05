<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_encargados_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_area')->constrained('areas')->cascadeOnDelete();
            $table->foreignId('id_usuario_encargado')->nullable()->constrained('users')->nullOnDelete();
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
        Schema::dropIfExists('historial_encargados_areas');
    }
};