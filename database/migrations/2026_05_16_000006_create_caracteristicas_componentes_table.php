<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caracteristicas_componentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_componente')->constrained('componentes')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('valor');
            $table->nullableUserStamps();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caracteristicas_componentes');
    }
};