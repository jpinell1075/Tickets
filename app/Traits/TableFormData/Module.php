<?php

namespace App\Traits\TableFormData;

/**
 * Trait Module
 * Provides functionality for managing module-related data in tables and forms.
 */
trait Module
{
    /**
     * @var array<string> Modules that have permission to access this trait.
     */
    public static $permisson_modules = [
        'users',
    ];

    /**
     * @var string The field name representing the module's name.
     */
    public static $name_field = 'name';
}