<?php

namespace Database\Seeders;

use App\Models\Areas\Area;
use App\Models\Equipos\CaracteristicaComponente;
use App\Models\Equipos\CaracteristicaEquipo;
use App\Models\Equipos\Componente;
use App\Models\Equipos\Equipo;
use App\Models\Tickets\Mantenimiento;
use App\Models\Tickets\PlanificacionMantenimiento;
use App\Models\Tickets\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class BulkTicketMaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Create deterministic users (IT staff and operators)
        $users = [];
        for ($i = 1; $i <= 50; $i++) {
            $email = "user{$i}@it.example.local";
            $users[$i] = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'User' . $i,
                    'nombre' => 'IT',
                    'password' => Hash::make('password'),
                    'role' => '1',
                    'can_login' => true,
                    'telefono' => '600-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'especialidad' => ($i % 5 === 0) ? 'Redes' : (($i % 3 === 0) ? 'Sistemas' : 'Soporte'),
                    'estado' => 'activo',
                ]
            );
        }

        // Create areas (IT, Infraestructura, Operaciones, Desarrollo, Soporte)
        $areaNames = ['IT', 'Infraestructura', 'Operaciones', 'Desarrollo', 'Soporte'];
        $areas = [];
        foreach ($areaNames as $idx => $name) {
            $areas[$idx] = Area::firstOrCreate([
                'nombre' => $name,
            ], [
                'descripcion' => "$name area",
                'estado' => 'activo',
                'id_usuario_encargado' => $users[$idx + 1]->id,
            ]);
        }

        // Inventory patterns for IT devices
        $itTypes = ['cpu', 'monitor', 'ups', 'switches', 'aps', 'telefonia_ip', 'impresoras', 'reloj_biometrico', 'data_show', 'pantalla_proyector', 'laptops'];

        // Create many equipos (mostly IT)
        $equipos = [];
        $totalEquipos = 300;
        for ($i = 1; $i <= $totalEquipos; $i++) {
            $type = $itTypes[$i % count($itTypes)];
            $area = $areas[array_rand($areas)];
            $user = $users[($i % 50) + 1];

            $cod = strtoupper(substr($area->nombre, 0, 3)) . '-' . strtoupper($type) . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);

            $equipos[$i] = Equipo::firstOrCreate(
                ['cod_inventario' => $cod],
                [
                    'id_area' => $area->id,
                    'id_usuario' => $user->id,
                    'estado' => ($i % 10 === 0) ? 'mantenimiento' : 'activo',
                    'tipo' => $type,
                ]
            );

            // Add 2-4 characteristics deterministically
            $carCount = 2 + ($i % 3);
            for ($c = 1; $c <= $carCount; $c++) {
                CaracteristicaEquipo::firstOrCreate(
                    ['id_equipo' => $equipos[$i]->id, 'nombre' => "propiedad_{$c}"],
                    ['valor' => "valor_{$i}_{$c}"]
                );
            }

            $functionalState = ($i % 3 === 0) ? 'no_funcional' : 'funcional';
            CaracteristicaEquipo::firstOrCreate(
                ['id_equipo' => $equipos[$i]->id, 'nombre' => "estado_funcional"],
                ['valor' => $functionalState]
            );

            // Add 1-3 componentes deterministically
            $compCount = 1 + ($i % 3);
            for ($k = 1; $k <= $compCount; $k++) {
                $comp = Componente::firstOrCreate(
                    ['id_equipo' => $equipos[$i]->id, 'nombre' => "comp_{$k}"],
                );

                // Each componente gets 1-2 caracteristicas
                $cc = 1 + ($k % 2);
                for ($x = 1; $x <= $cc; $x++) {
                    CaracteristicaComponente::firstOrCreate(
                        ['id_componente' => $comp->id, 'nombre' => "cprop_{$x}"],
                        ['valor' => "cvalor_{$i}_{$k}_{$x}"]
                    );
                }
            }
        }

        /* // Create planificaciones for each area (one per area in next 30 days)
        $planificaciones = [];
        foreach ($areas as $area) {
            $start = $now->copy()->addDays(7 + ($area->id));
            $planificaciones[$area->id] = PlanificacionMantenimiento::firstOrCreate(
                ['id_area' => $area->id, 'fecha_inicio' => $start->startOfDay()],
                ['fecha_fin' => $start->copy()->addHours(6)]
            );
        }

        // Create tickets: 30% of equipos get 1-3 tickets
        $ticketsCreated = [];
        foreach ($equipos as $i => $equipo) {
            if ($i % 10 <= 2) { // ~30%
                $num = 1 + ($i % 3);
                for ($t = 1; $t <= $num; $t++) {
                    $priority = ['baja', 'media', 'alta', 'critica'][$i % 4];
                    $estado = ['abierto', 'en_proceso', 'resuelto', 'cerrado', 'cancelado'][$t % 5];
                    $fechaCreacion = $now->copy()->subDays(($i + $t) % 60);

                    $ticket = Ticket::firstOrCreate(
                        ['titulo' => "Ticket {$equipo->cod_inventario} #{$t}", 'fecha_creacion' => $fechaCreacion],
                        [
                            'id_equipo' => $equipo->id,
                            'id_usuario' => $users[(($i + $t) % 50) + 1]->id,
                            'id_encargado' => $users[(($i + $t + 2) % 50) + 1]->id,
                            'id_programacion_mantenimiento' => $planificaciones[$equipo->id_area]->id ?? null,
                            'tipo' => ($i % 2 === 0) ? 'correctivo' : 'preventivo',
                            'prioridad' => $priority,
                            'descripcion' => "Autogenerated ticket for {$equipo->cod_inventario}",
                            'estado_actual' => $estado,
                        ]
                    );

                    $ticketsCreated[] = $ticket;

                    // If ticket is resolved or closed, add a mantenimiento record
                    if (in_array($estado, ['resuelto', 'cerrado'])) {
                        Mantenimiento::firstOrCreate(
                            ['id_ticket' => $ticket->id, 'fecha' => $fechaCreacion->copy()->addDays(1)],
                            [
                                'id_equipo' => $equipo->id,
                                'id_encargado' => $ticket->id_encargado ?? $users[1]->id,
                                'observacion' => 'Mantenimiento automático post-resolución',
                            ]
                        );
                    }
                }
            }
        } */

        // Final summary in logs
        $this->command->info("Bulk seeder finished: created " . count($equipos) . " equipos, " /* . count($ticketsCreated) . " tickets." */);
    }
}
