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
        Schema::create('user_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('user_template_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_template_id')->constrained('user_templates')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('user_template_module_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_template_module_id')->constrained('user_template_modules')->onDelete('cascade');
            $table->string('action');
            $table->timestamps();
        });

        Schema::create('user_template_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_template_id')->constrained('user_templates')->onDelete('cascade');
            $table->string('model');
            $table->string('field')->nullable();
            $table->string('operator')->nullable();
            $table->string('value')->nullable();
            $table->string('comparison_type')->nullable();
            $table->string('relation')->nullable();
            $table->text('extra')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_templates');
    }
};
