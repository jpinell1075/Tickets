<?php

namespace App\Traits\TableFormData;

use App\Traits\TableFormData\TableFormDataTrait;

/**
 * Trait User
 * Provides functionality for managing backup-related data in tables and forms.
 */
trait Backup
{
    use TableFormDataTrait;

    /**
     * @var array<string> Modules that the user has permission to access.
     */
    public static $permisson_modules = [
        'backups',
    ];

    /**
     * @var array<string> Fields to display in the table.
     */
    public static $table_fields = [
        'name',
    ];

    /**
     * @var array<string> Fields in the table that can be searched.
     */
    public static $table_fields_searchable = [
        'name',
    ];

    /**
     * @var array<string, string> Field names for the table.
     */
    public static $table_fields_names = [
        'name' => 'Nombre',
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
        if(!$this->name) {
            return [];
        }

        return [
            'show' => [
                'type' => 'normal-link',
                'attr' => [
                    'href' => route('backups.download', ['name' => $this->name]),
                    'class' => 'min-w-[35px] h-[35px] inline-flex items-center justify-center bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white rounded transition-colors',
                ],
                'inner' => '<i class=\'bx bxs-download\'></i>',
            ],
            'delete' => [
                'type' => 'button',
                'attr' => [
                    'class' => 'min-w-[35px] h-[35px] inline-flex items-center justify-center bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-white rounded transition-colors',
                    'data-url' => route('backups.delete', ['name' => $this->name]),
                ],
                'inner' => '<i class=\'bx bxs-trash-alt\'></i>',
            ],
        ];
    }
}