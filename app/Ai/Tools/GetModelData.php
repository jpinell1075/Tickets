<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetModelData implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return '
            Obtiene la estructura detallada de los modelos de Eloquent especificados.
            Recibe como parámetro un array con los nombres de las clases de los modelos (ej. ["User", "Post"]), el nombre del parametro debe ser "models".
            Devuelve la descripción, campos con sus tipos de datos y descripción y el nombre de las relaciones de cada modelo solicitado.
            DEBES usar esta herramienta SIEMPRE antes de intentar armar una consulta para no inventar columnas que no existen.
            Si el modelo solicitado no existe o no tiene información disponible, devuelve un mensaje claro indicando que no se pudo obtener la información del modelo.
            Nunca inventes información sobre modelos, columnas o relaciones que no estén explícitamente listadas en la respuesta de esta herramienta.
        ';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $models = $request['models'];
        $result = [];

        foreach ($models as $model) {
            $model = config('model')[$model] ?? null;
            if (class_exists($model)) {
                $traits = class_uses_recursive($model);
                if (in_array(\App\Traits\AiTraits\HasAiData::class, $traits)) {
                    $result[$model] = [
                        'description' => $model::$description ?? 'No description available.',
                        'fields' => $model::$fields ?? [],
                        'relationships' => $model::$relationships ?? [],
                    ];
                } else {
                    $result[$model] = 'No se pudo obtener la información del modelo.';
                }
            } else {
                $result[$model] = 'No se pudo obtener la información del modelo.';
            }
        }
        return json_encode($result);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'models' => $schema->array()->items($schema->string())->required(),
        ];
    }
}
