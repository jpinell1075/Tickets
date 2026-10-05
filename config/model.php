<?php

return [
    'User' => \App\Models\User::class,
    'Module' => \App\Models\Users\Module::class,
    'UserModule' => \App\Models\Users\UserModule::class,
    'Backup' => \App\Models\Backups\Backup::class,
    'UserLog' => \App\Models\Configurations\UserLog::class,
    'UserTemplate' => \App\Models\Users\Templates\UserTemplate::class,

    'Area' => \App\Models\Areas\Area::class,
    'Equipo' => \App\Models\Equipos\Equipo::class,
    'PlanificacionMantenimiento' => \App\Models\Tickets\PlanificacionMantenimiento::class,
];