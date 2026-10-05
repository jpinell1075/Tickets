<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('1'); // 0: Administrador, 1: Usuario
            $table->boolean('it_staff')->default(false); // Indica si el usuario es parte del personal de TI
            $table->boolean('can_login')->default(true)->nullable();

            $table->rememberToken();
            $table->nullableUserStamps();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider_name');
            $table->string('provider_id');
            $table->string('provider_email')->nullable();

            $table->nullableUserStamps();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('apps', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();
            $table->string('internal_name')->nullable();
            $table->string('access_route_name')->nullable();
            $table->string('icon')->nullable();
            $table->integer('order')->default(0)->nullable();
            $table->boolean('show_in_menu')->default(true)->nullable();

            $table->nullableUserStamps();
            $table->timestamps();
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();
            $table->string('internal_name')->nullable();
            $table->string('access_route_name')->nullable();
            $table->string('icon')->nullable();
            $table->integer('order')->default(0)->nullable();
            $table->boolean('show_in_menu')->default(true)->nullable();

            $table->unsignedBigInteger('app_id')->nullable();
            $table->foreign('app_id')->references('id')->on('apps')
            ->onDelete('cascade')->onUpdate('cascade');

            $table->nullableUserStamps();
            $table->timestamps();
        });

        
        Schema::create('users_modules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')
            ->onDelete('cascade')->onUpdate('cascade');

            $table->foreignId('module_id')->constrained('modules')
            ->onDelete('cascade')->onUpdate('cascade');

            $table->nullableUserStamps();
            $table->timestamps();
        });

        Schema::create('users_modules_actions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_module_id')->constrained('users_modules')
            ->onDelete('cascade')->onUpdate('cascade');

            $table->enum('action', ['create', 'read', 'update', 'delete', 'search'])->nullable();

            $table->nullableUserStamps();
            $table->timestamps();
        });

        Schema::create('user_model_filters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')
            ->onDelete('cascade')->onUpdate('cascade');

            $table->string('description')->nullable(); // descripción del filtro
            $table->string('model')->nullable(); // nombre del modelo al que se aplica el filtro
            $table->string('field')->nullable(); // campo del modelo al que se aplica el filtro
            $table->string('operator')->nullable(); // operador de comparación, por ejemplo: '=', '!=', '>', '<', 'LIKE', etc.
            $table->string('value')->nullable();
            $table->string('comparison_type')->default('simple'); // simple, relation, function
            $table->string('relation')->nullable(); // nombre de la relación si es un filtro de relación ejemplo: 'userModule.user'
            $table->json('extra')->nullable(); // campo extra para almacenar información adicional del filtro

            $table->nullableUserStamps();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
        Schema::dropIfExists('users');
        Schema::dropIfExists('apps');
        Schema::dropIfExists('user_providers');
        Schema::dropIfExists('users_modules');
        Schema::dropIfExists('users_modules_actions');
        Schema::dropIfExists('user_model_filters');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
