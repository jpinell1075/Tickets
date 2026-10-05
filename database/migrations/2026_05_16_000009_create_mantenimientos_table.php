<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_ticket')->constrained('tickets')->cascadeOnDelete()->nullable();
            $table->foreignId('id_equipo')->nullable()->constrained('equipos')->nullOnDelete();
            $table->foreignId('id_encargado')->nullable()->constrained('users')->nullOnDelete();
            $table->text('tipo')->nullable();
            $table->dateTime('fecha');
            $table->text('observacion')->nullable();
            $table->nullableUserStamps();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};