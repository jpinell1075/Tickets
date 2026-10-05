<?php

namespace App\Traits\AiTraits;

trait HasAiData
{
    /**
     * Retrieves static data properties defined in the implementing class.
     *
     * @return array<string, mixed> An associative array of static data properties.
     */
    public static function getAiData()
    {
        return [
            'description' => static::$description ?? null,
            'fields' => static::$fields ?? [],
            'relationships' => static::$relationships ?? [],
        ];
    }
}