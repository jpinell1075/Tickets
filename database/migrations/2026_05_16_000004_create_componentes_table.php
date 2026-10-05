<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('componentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_equipo')->constrained('equipos')->cascadeOnDelete();
            $table->string('nombre');
            $table->nullableUserStamps();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('componentes');
    }
};