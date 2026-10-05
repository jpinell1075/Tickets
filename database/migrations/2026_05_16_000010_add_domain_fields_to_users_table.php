<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nombre')->nullable()->after('name');
            $table->string('telefono')->nullable()->after('nombre');
            $table->string('especialidad')->nullable()->after('telefono');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo')->after('especialidad');
            $table->foreignId('id_area')->nullable()->after('estado')->constrained('areas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_area');
            $table->dropColumn(['nombre', 'telefono', 'especialidad', 'estado']);
        });
    }
};