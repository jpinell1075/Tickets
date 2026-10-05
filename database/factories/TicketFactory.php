<?php

namespace Database\Factories;

use App\Models\Tickets\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        $now = Carbon::now();
        return [
            'id_equipo' => null,
            'id_usuario' => null,
            'id_encargado' => null,
            'id_programacion_mantenimiento' => null,
            'tipo' => 'correctivo',
            'prioridad' => 'media',
            'titulo' => 'Incidente generado ' . $now->format('YmdHis'),
            'descripcion' => 'Descripción de ejemplo',
            'estado_actual' => 'abierto',
            'fecha_creacion' => $now,
        ];
    }
}
