<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Users\Module;
use App\Models\Backups\ScheduledBackup;
use App\Models\Configurations\Configuration;
use App\Models\Users\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'admin',
            'password' => bcrypt('admin'),
            'role' => '0',
        ]);

        ScheduledBackup::create([
            'days' => [0],
            'times' => ['17:00'],
            'active' => false,
        ]);

        $app = App::create([
            'name' => 'Administración',
            'internal_name' => 'administration_app',
            'access_route_name' => 'administration_app.index',
            'icon' => 'bx bx-grid-alt fs-1 text-primary',
            'order' => 1,
            'show_in_menu' => true,
        ]);

        $inventoryApp = App::create([
            'name' => 'Inventario',
            'internal_name' => 'inventory_app',
            'access_route_name' => 'inventory_app.index',
            'icon' => 'bx bx-box fs-1 text-primary',
            'order' => 2,
            'show_in_menu' => true,
        ]);

        $ticketsApp = App::create([
            'name' => 'Tickets',
            'internal_name' => 'tickets_app',
            'access_route_name' => 'tickets_app.index',
            'icon' => 'bx bx-support fs-1 text-primary',
            'order' => 3,
            'show_in_menu' => true,
        ]);

        /* $ai = App::create([
            'name' => 'Asistente IA',
            'internal_name' => 'ai_assistant_app',
            'access_route_name' => 'ai_assistant_app.index',
            'icon' => 'bx bx-chip fs-1 text-primary',
            'order' => 2,
            'show_in_menu' => true,
        ]); */

        $modules = [
            [
                'name' => 'Usuarios',
                'internal_name' => 'users',
                'access_route_name' => 'users.index',
                'icon' => 'bx bx-group nav_icon',
                'order' => 1,
                'show_in_menu' => true,
                'app_id' => $app->id,
            ],
            [
                'name' => 'Respaldos',
                'internal_name' => 'backups',
                'access_route_name' => 'backups.index',
                'icon' => 'bx bx-save nav_icon',
                'order' => 2,
                'show_in_menu' => true,
                'app_id' => $app->id,
            ],
            [
                'name' => 'Configuración',
                'internal_name' => 'config',
                'access_route_name' => 'config.index',
                'icon' => 'bx bx-cog nav_icon',
                'order' => 3,
                'show_in_menu' => true,
                'app_id' => $app->id,
            ],

            //modulos de IA
            /* [
                'name' => 'Reportes de IA',
                'internal_name' => 'ai_reports',
                'access_route_name' => 'ai_reports.index',
                'icon' => 'bx bx-chart nav_icon',
                'order' => 1,
                'show_in_menu' => true,
                'app_id' => $ai->id,
            ], */

            //modulos de inventario
            [
                'name' => 'Áreas',
                'internal_name' => 'areas',
                'access_route_name' => 'areas.index',
                'icon' => 'bx bx-map nav_icon',
                'order' => 1,
                'show_in_menu' => true,
                'app_id' => $inventoryApp->id,
            ],
            [
                'name' => 'Equipos',
                'internal_name' => 'equipos',
                'access_route_name' => 'equipos.index',
                'icon' => 'bx bx-desktop nav_icon',
                'order' => 2,
                'show_in_menu' => true,
                'app_id' => $inventoryApp->id,
            ],
            [
                'name' => 'Reportes de Inventario',
                'internal_name' => 'inventory_reports',
                'access_route_name' => 'inventory_reports.index',
                'icon' => 'bx bx-chart nav_icon',
                'order' => 3,
                'show_in_menu' => true,
                'app_id' => $inventoryApp->id,
            ],

            //modulos de tickets
            [
                'name' => 'Solicitudes',
                'internal_name' => 'requests',
                'access_route_name' => 'requests.index',
                'icon' => 'bx bx-file nav_icon',
                'order' => 1,
                'show_in_menu' => true,
                'app_id' => $ticketsApp->id,
            ],
            [
                'name' => 'Planificación Mantenimiento',
                'internal_name' => 'maintenance_plans',
                'access_route_name' => 'maintenance_plans.index',
                'icon' => 'bx bx-calendar nav_icon',
                'order' => 2,
                'show_in_menu' => true,
                'app_id' => $ticketsApp->id,
            ],
            [
                'name' => 'Administración de Tickets',
                'internal_name' => 'ticket_admin',
                'access_route_name' => 'ticket_admin.index',
                'icon' => 'bx bx-task nav_icon',
                'order' => 3,
                'show_in_menu' => true,
                'app_id' => $ticketsApp->id,
            ],
            [
                'name' => 'Reportes de Tickets',
                'internal_name' => 'ticket_reports',
                'access_route_name' => 'ticket_reports.index',
                'icon' => 'bx bx-chart nav_icon',
                'order' => 4,
                'show_in_menu' => true,
                'app_id' => $ticketsApp->id,
            ]
        ];

        foreach ($modules as $module) {
            Module::create([
                'name' => $module['name'],
                'internal_name' => $module['internal_name'],
                'access_route_name' => $module['access_route_name'],
                'icon' => $module['icon'],
                'order' => $module['order'],
                'show_in_menu' => $module['show_in_menu'],
                'app_id' => $module['app_id'],
            ]);
        }

        $configurations = [
            ['name' => 'Eliminar Temporalmente', 'key' => 'global_use_soft_deletes', 'value' => 'false', 'type' => 'boolean'],
            ['name' => 'Permitir Sesiones de Redes Sociales', 'key' => 'global_use_social_login', 'value' => 'false', 'type' => 'boolean'],
            ['name' => 'Habilitar Logs Globales', 'key' => 'global_use_logs', 'value' => 'false', 'type' => 'boolean'],
        ];

        foreach ($configurations as $configuration) {
            Configuration::create([
                'name' => $configuration['name'],
                'key' => $configuration['key'],
                'value' => $configuration['value'],
                'type' => $configuration['type'],
            ]);
        }
    }
}
