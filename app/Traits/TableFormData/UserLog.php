<?php

namespace App\Traits\TableFormData;

use App\Traits\TableFormData\TableFormDataTrait;

/**
 * Trait UserLog
 * Provides functionality for managing user log-related data in tables and forms.
 */
trait UserLog
{
    use TableFormDataTrait;

    /**
     * @var array<string> Modules that the user has permission to access.
     */
    public static $permisson_modules = [
        'configurations',
    ];

    /**
     * @var array<string> Fields to display in the table.
     */
    public static $table_fields = [
        'date',
        'user_name',
        'action',
        'method',
        'ip',
        'status_code',
    ];

    /**
     * @var array<string> Fields in the table that can be searched.
     */
    public static $table_fields_searchable = [
        'date',
        'user_name',
        'url',
        'method',
        'ip',
        'status_code',
    ];

    /**
     * @var array<string, string> Field names for the table.
     */
    public static $table_fields_names = [
        'date' => 'Fecha',
        'user_name' => 'Usuario',
        'action' => 'Acción',
        'url' => 'Acción',
        'method' => 'Método',
        'ip' => 'IP',
        'status_code' => 'Estado',
    ];


    /**
     * @var string The field name representing the model's name.
     */
    public static $name_field = 'url';

    /**
     * Generates options for the field content in the table.
     *
     * @return array<string, array<string, mixed>> The options for the field content.
     */
    public function getOptionsFieldContent()
    {
        return null;
    }
}