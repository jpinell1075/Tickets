<?php

namespace App\Traits\TableFormData;

/**
 * Trait TableFormDataTrait
 * Provides shared functionality for managing table and form data across models and traits.
 */
trait TableFormDataTrait
{
    /**
     * Retrieves static data properties defined in the implementing class.
     *
     * @return array<string, mixed> An associative array of static data properties.
     */
    public static function getStaticData()
    {
        return [
            'permisson_modules' => property_exists(static::class, 'permisson_modules') ? static::$permisson_modules : null,
            'table_fields' => property_exists(static::class, 'table_fields') ? static::$table_fields : null,
            'table_fields_searchable' => property_exists(static::class, 'table_fields_searchable') ? static::$table_fields_searchable : null,
            'table_fields_names' => property_exists(static::class, 'table_fields_names') ? static::$table_fields_names : null,
            'table_fields_types' => property_exists(static::class, 'table_fields_types') ? static::$table_fields_types : null,
            'morphable_fiels' => property_exists(static::class, 'morphable_fiels') ? static::$morphable_fiels : null,
            'name_field' => property_exists(static::class, 'name_field') ? static::$name_field : null,
            'model' => class_basename(static::class),
            'form_fields' => property_exists(static::class, 'form_fields') ? static::$form_fields : null,
        ];
    }
}