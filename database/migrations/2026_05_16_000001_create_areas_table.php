<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_encargado')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->nullableUserStamps();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};