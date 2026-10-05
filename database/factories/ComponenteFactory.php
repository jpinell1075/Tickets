<?php

namespace Database\Factories;

use App\Models\Equipos\Componente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComponenteFactory extends Factory
{
    protected $model = Componente::class;

    public function definition(): array
    {
        return [
            'id_equipo' => null,
            'nombre' => 'Componente ' . rand(1, 1000),
        ];
    }
}
