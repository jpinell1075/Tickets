<?php

namespace App\Traits\TableFormData\Inventory;

use App\Traits\TableFormData\TableFormDataTrait;

/**
 * Trait Equipo
 * Provides functionality for managing equipo-related data in tables and forms.
 */
trait Equipo
{
    use TableFormDataTrait;

    /**
     * @var array<string> Modules that the user has permission to access.
     */
    public static $permisson_modules = [
        'equipos',
    ];

    /**
     * @var array<string> Fields to display in the table.
     */
    public static $table_fields = [
        'nombre',
        'cod_inventario',
        'estado',
    ];

    /**
     * @var array<string> Fields in the table that can be searched.
     */
    public static $table_fields_searchable = [
        'nombre',
    ];

    /**
     * @var array<string, string> Field names for the table.
     */
    public static $table_fields_names = [
        'nombre' => 'Nombre',
        'cod_inventario' => 'Código de Inventario',
        'estado' => 'Estado',
    ];

    /**
     * @var string The field name representing the model's name.
     */
    public static $name_field = 'nombre';

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
                    'href' => route('equipos.show', ['equipo' => $this]),
                    'class' => 'min-w-[35px] h-[35px] inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white rounded transition-colors',
                ],
                'inner' => '<i class=\'bx bxs-show\'></i>',
            ],
            'delete' => [
                'type' => 'button',
                'attr' => [
                    'class' => 'min-w-[35px] h-[35px] inline-flex items-center justify-center bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-white rounded transition-colors',
                    'data-url' => route('equipos.delete', ['equipo' => $this]),
                ],
                'inner' => '<i class=\'bx bxs-trash-alt\'></i>',
            ],
        ];
    }
}