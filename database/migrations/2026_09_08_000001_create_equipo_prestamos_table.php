<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipo_prestamos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_equipo')->constrained('equipos')->cascadeOnDelete();
            $table->foreignId('id_area_origen')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('id_area_destino')->constrained('areas')->cascadeOnDelete();
            $table->foreignId('id_usuario_responsable')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_prestamo');
            $table->dateTime('fecha_devolucion_estimada')->nullable();
            $table->dateTime('fecha_devolucion_real')->nullable();
            $table->string('estado')->default('activo'); // 'activo', 'devuelto'
            $table->text('motivo');
            $table->text('observacion_devolucion')->nullable();
            $table->nullableUserStamps();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_prestamos');
    }
};

