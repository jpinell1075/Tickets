<?php

namespace Database\Factories;

use App\Models\Equipos\CaracteristicaComponente;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaracteristicaComponenteFactory extends Factory
{
    protected $model = CaracteristicaComponente::class;

    public function definition(): array
    {
        return [
            'id_componente' => null,
            'nombre' => 'PropiedadComp',
            'valor' => 'ValorComp',
        ];
    }
}
