<?php

namespace App\Traits\TableFormData;

use Illuminate\Support\Facades\Route;
use App\Traits\TableFormData\TableFormDataTrait;

/**
 * Trait UserModule
 * Provides functionality for managing user-module relationships in tables and forms.
 */
trait UserModule
{
    use TableFormDataTrait;

    /**
     * @var array<string> Modules that the user has permission to access.
     */
    public static $permisson_modules = [
        'users',
    ];

    /**
     * @var string The field name representing the module ID.
     */
    public static $name_field = 'module_id';

    /**
     * @var array<string> Fields in the table that can be searched.
     */
    public static $table_fields_searchable = [
        'module',
    ];

    /**
     * @var array<string, bool> Indicates if searchable fields are relations.
     */
    public static $searchable_fields_is_relation = [
        'module' => true,
    ];

    /**
     * @var array<string> Fields to display in the table.
     */
    public static $table_fields = [
        'module_name',
        'user_name',
    ];

    /**
     * @var array<string, string> Field names for the table.
     */
    public static $table_fields_names = [
        'module_name' => 'Módulo',
        'user_name' => 'Usuario',
        'module_id' => 'Módulo',
        'module' => 'Módulo',
    ];

    /**
     * @var array<string, array<string, string>> Relation fields configuration.
     */
    public static $relation_fields = [
        'module' => [
            'relation' => 'module',
            'field' => 'module_name',
            'foreign_field' => 'name',
        ],
        'user' => [
            'relation' => 'user',
            'field' => 'user_name',
            'foreign_field' => 'name',
        ],
    ];

    /**
     * Adds extra query conditions based on parameters.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance.
     * @param mixed $extra_query_parameter Additional query parameter.
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder.
     */
    public static function extraQuery($query, $extra_query_parameter)
    {
        return $query->where('user_id', $extra_query_parameter);
    }

    /**
     * Generates options for the field content in the table.
     *
     * @return array<string, array<string, mixed>> The options for the field content.
     */
    public function getOptionsFieldContent()
    {
        $show_route = Route::has($this->module->access_route_name) ? route($this->module->access_route_name) : '';
        $delete_route = route('users.deleteModule', ['userModule' => $this , 'user' => $this->user]);
        
        return [
            'show' => [
                'type' => 'link',
                'attr' => [
                    'href' => $show_route,
                    'class' => 'btn btn-primary',
                ],
                'inner' => '<i class=\'bx bxs-show\'></i>',
            ],
            'delete' => [
                'type' => 'button',
                'attr' => [
                    'class' => 'delete-button btn btn-danger',
                    'data-url' => $delete_route,
                ],
                'inner' => '<i class=\'bx bxs-trash-alt\'></i>',
            ],
        ];
    }
}