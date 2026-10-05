<?php

namespace Database\Factories;

use App\Models\Equipos\Equipo;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipoFactory extends Factory
{
    protected $model = Equipo::class;

    public function definition(): array
    {
        return [
            'id_usuario' => null,
            'id_area' => null,
            'estado' => 'activo',
            'tipo' => 'desktop',
            'cod_inventario' => 'EQ-' . now()->format('YmdHis') . '-' . rand(1000, 9999),
        ];
    }
}
