<?php

namespace App\Traits\TableFormData;

use App\Traits\TableFormData\TableFormDataTrait;

/**
 * Trait User
 * Provides functionality for managing user-related data in tables and forms.
 */
trait User
{
    use TableFormDataTrait;

    /**
     * @var array<string> Modules that the user has permission to access.
     */
    public static $permisson_modules = [
        'users',
    ];

    /**
     * @var array<string> Fields to display in the table.
     */
    public static $table_fields = [
        'name',
        'role',
        'can_login',
    ];

    /**
     * @var array<string> Fields in the table that can be searched.
     */
    public static $table_fields_searchable = [
        'name',
        'role',
    ];

    /**
     * @var array<string, string> Field names for the table.
     */
    public static $table_fields_names = [
        'name' => 'Nombre',
        'email' => 'Correo',
        'role' => 'Rol',
        'can_login' => 'Puede iniciar sesión',
    ];

    /**
     * @var array<string, string> Field types for the table.
     */
    public static $table_fields_types = [
        'name' => 'text',
        'email' => 'text',
        'role' => 'text',
        'can_login' => 'text',
    ];

    /**
     * @var array<string, string> Fields that can be morphed.
     */
    public static $morphable_fiels = [
        'role' => 'rol_name',
        'can_login' => 'can_login_name',
    ];

    /**
     * @var array<string, string> Role names mapped to their IDs.
     */
    public static $rol_name = [
        '0' => 'Administrador',
        '1' => 'Usuario',
    ];

    public static $can_login_name = [
        '0' => 'No',
        '1' => 'Sí',
    ];

    /**
     * @var string The field name representing the model's name.
     */
    public static $name_field = 'name';

    /**
     * Generates options for the field content in the table.
     *
     * @return array<string, array<string, mixed>> The options for the field content.
     */
    public function getOptionsFieldContent()
    {
        return [
            'show' => [
                'type' => 'link',
                'attr' => [
                    'href' => route('users.show', ['user' => $this]),
                    'class' => 'min-w-[35px] h-[35px] inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white rounded transition-colors',
                ],
                'inner' => '<i class=\'bx bxs-show\'></i>',
            ],
            'delete' => [
                'type' => 'button',
                'attr' => [
                    'class' => 'min-w-[35px] h-[35px] inline-flex items-center justify-center bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-white rounded transition-colors',
                    'data-url' => route('users.delete', ['user' => $this]),
                ],
                'inner' => '<i class=\'bx bxs-trash-alt\'></i>',
            ],
        ];
    }

    /**
     * @var array<string, array<string, mixed>> Form fields configuration.
     * Defines the input type, validation rules, and other attributes for each field.
     */
    public static $form_fields = [
        'name' => [
            'input_type' => 'input', //input, textarea, select
            'type' => 'text',
            'required' => true,
            'readonly' => false,
            'label' => 'Nombre',
            'name' => 'name',
            'id' => 'user_name',
            'placeholder' => 'Nombre',
        ],
        'password' => [
            'input_type' => 'input',
            'type' => 'password',
            'required' => true,
            'readonly' => false,
            'label' => 'Contraseña',
            'name' => 'password',
            'id' => 'user_password',
            'placeholder' => 'Contraseña',
        ],
        'role' => [
            'input_type' => 'select',
            'type' => 'text',
            'required' => true,
            'readonly' => false,
            'label' => 'Rol',
            'name' => 'role',
            'id' => 'role',
            'placeholder' => 'Rol',
            'options_function' => 'getRoles',
            'options' => [
                '0' => 'Administrador',
                '1' => 'Usuario',
            ],
        ],
        'it_staff' => [
            'input_type' => 'select',
            'type' => 'text',
            'required' => true,
            'readonly' => false,
            'label' => 'Parte del personal de TI?',
            'name' => 'it_staff',
            'id' => 'it_staff',
            'placeholder' => 'Parte del personal de TI?',
            'options_function' => 'getItStaffOptions',
            'options' => [
                '0' => 'No',
                '1' => 'Sí',
            ],
        ],
        'can_login' => [
            'input_type' => 'select',
            'type' => 'text',
            'required' => true,
            'readonly' => false,
            'label' => 'Puede iniciar sesión',
            'name' => 'can_login',
            'id' => 'can_login',
            'placeholder' => 'Puede iniciar sesión',
            'options_function' => 'getCanLoginOptions',
            'options' => [
                '1' => 'Sí',
                '0' => 'No',
            ],
        ],

        'email' => [
            'input_type' => 'input', //input, textarea, select
            'type' => 'email',
            'required' => false,
            'readonly' => false,
            'label' => 'Correo Electrónico',
            'name' => 'email',
            'id' => 'user_email',
            'placeholder' => 'Correo Electrónico',
        ],
        'nombre' => [
            'input_type' => 'input', //input, textarea, select
            'type' => 'text',
            'required' => false,
            'readonly' => false,
            'label' => 'Nombre Completo',
            'name' => 'nombre',
            'id' => 'user_nombre',
            'placeholder' => 'Nombre Completo',
        ],
        'telefono' => [
            'input_type' => 'input', //input, textarea, select
            'type' => 'tel',
            'required' => false,
            'readonly' => false,
            'label' => 'Teléfono',
            'name' => 'telefono',
            'id' => 'user_telefono',
            'placeholder' => 'Teléfono',
        ],
        'especialidad' => [
            'input_type' => 'input', //input, textarea, select
            'type' => 'text',
            'required' => false,
            'readonly' => false,
            'label' => 'Especialidad',
            'name' => 'especialidad',
            'id' => 'user_especialidad',
            'placeholder' => 'Especialidad',
        ],
        'estado' => [
            'input_type' => 'select', //input, textarea, select
            'type' => 'text',
            'required' => true,
            'readonly' => false,
            'label' => 'Estado',
            'name' => 'estado',
            'id' => 'user_estado',
            'placeholder' => 'Estado',
            'options_function' => 'getEstadoOptions',
            'options' => [
                'activo' => 'Activo',
                'inactivo' => 'Inactivo',
            ],
        ],
    ];

    /**
     * Retrieves the available roles.
     *
     * @return array<string, string> The roles mapped to their IDs.
     */
    public static function getRoles()
    {
        return self::$rol_name;
    }

    /**
     * Retrieves the available IT staff options.
     *
     * @return array<string, string> The IT staff options mapped to their IDs.
     */
    public static function getItStaffOptions()
    {
        return [
            '0' => 'No',
            '1' => 'Sí',
        ];
    }

    /**
     * Retrieves the role name for the current user.
     *
     * @return string|null The role name, or null if not found.
     */
    public function rol()
    {
        return self::$rol_name[$this->role] ?? null;
    }
}