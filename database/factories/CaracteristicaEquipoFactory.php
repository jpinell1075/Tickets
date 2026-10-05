<?php

namespace Database\Factories;

use App\Models\Equipos\CaracteristicaEquipo;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaracteristicaEquipoFactory extends Factory
{
    protected $model = CaracteristicaEquipo::class;

    public function definition(): array
    {
        return [
            'id_equipo' => null,
            'nombre' => 'Propiedad',
            'valor' => 'Valor',
        ];
    }
}
