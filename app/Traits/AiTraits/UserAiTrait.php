<?php

namespace App\Traits\AiTraits;

trait UserAiTrait
{
    use HasAiData;

    public static string $description = '
        Este modelo representa a los usuarios del sistema.
    ';

    public static array $fields = [
        'id' => [
            'type' => 'integer',
            'description' => 'Identificador único del usuario.',
        ],
        'name' => [
            'type' => 'string',
            'description' => 'Nombre completo del usuario.',
        ],
        'email' => [
            'type' => 'string',
            'description' => 'Correo electrónico del usuario.',
        ],
        'role' => [
            'type' => 'integer',
            'description' => 'Rol del usuario (0: Administrador, 1: Usuario).',
        ],
        'can_login' => [
            'type' => 'boolean',
            'description' => 'Indica si el usuario puede iniciar sesión.',
        ],
        'created_at' => [
            'type' => 'datetime',
            'description' => 'Fecha de creación del registro.',
        ],
        'updated_at' => [
            'type' => 'datetime',
            'description' => 'Fecha de actualización del registro.',
        ],
    ];

    public static array $relationships = [];
}