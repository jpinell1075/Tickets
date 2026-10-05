<?php

namespace Database\Factories;

use App\Models\Tickets\Mantenimiento;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MantenimientoFactory extends Factory
{
    protected $model = Mantenimiento::class;

    public function definition(): array
    {
        return [
            'id_ticket' => null,
            'id_equipo' => null,
            'id_encargado' => null,
            'fecha' => Carbon::now(),
            'observacion' => 'Registro de mantenimiento',
        ];
    }
}
