<?php

namespace App\Traits\TableFormData\Maintenances;

use App\Traits\TableFormData\TableFormDataTrait;

/**
 * Trait PlanificacionMantenimiento
 * Provides functionality for managing maintenance planning data in tables and forms.
 */
trait PlanificacionMantenimiento
{
    use TableFormDataTrait;

    /**
     * @var array<string> Modules that the user has permission to access.
     */
    public static $permisson_modules = [
        'maintenance_plans',
    ];

    /**
     * @var array<string> Fields to display in the table.
     */
    public static $table_fields = [
        'area.nombre',
        'fecha_inicio',
        'fecha_fin',
    ];

    /**
     * @var array<string> Fields in the table that can be searched.
     */
    public static $table_fields_searchable = [
        'area.nombre',
    ];

    /**
     * @var array<string, string> Field names for the table.
     */
    public static $table_fields_names = [
        'area.nombre' => 'Área',
        'fecha_inicio' => 'Fecha de Inicio',
        'fecha_fin' => 'Fecha de Fin',
    ];

    public static $table_fields_types = [
        'area.nombre' => 'text',
        'fecha_inicio' => 'formatted_date',
        'fecha_fin' => 'formatted_date',
    ];

    /**
     * @var string The field name representing the model's name.
     */
    public static $name_field = 'area.nombre';

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
                    'href' => route('maintenance_plans.show', ['planificacionMantenimiento' => $this]),
                    'class' => 'min-w-[35px] h-[35px] inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white rounded transition-colors',
                ],
                'inner' => '<i class=\'bx bxs-show\'></i>',
            ],
            'delete' => [
                'type' => 'button',
                'attr' => [
                    'class' => 'min-w-[35px] h-[35px] inline-flex items-center justify-center bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-white rounded transition-colors',
                    'data-url' => route('maintenance_plans.delete', ['planificacionMantenimiento' => $this]),
                ],
                'inner' => '<i class=\'bx bxs-trash-alt\'></i>',
            ],
        ];
    }
}