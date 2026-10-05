<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use App\Services\DynamicQueryService;
use Stringable;

class ExecuteQuery implements Tool
{
    public function description(): Stringable|string
    {
        return '
            Ejecuta una consulta a la base de datos utilizando el DSL (Domain Specific Language) en formato JSON.
            DEBES usar esta herramienta enviando el JSON estructurado en el parámetro "value".
            Asegúrate de haber consultado previamente "GetModelData" para conocer las columnas, y "GetQueryBuilderDescription" para recordar la estructura exacta del JSON.
        ';
    }

    public function handle(Request $request): Stringable|string
    {
        try {
            $payload = $request['value'];
            
            if (is_string($payload)) {
                $payload = json_decode($payload, true);
            }

            if (!is_array($payload) || empty($payload['model'])) {
                return json_encode(['error' => 'El JSON proporcionado es inválido o falta el modelo.']);
            }

            $queryService = app(DynamicQueryService::class);
            $results = $queryService->execute($payload);

            return json_encode([
                'status' => 'success',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return json_encode(['error' => 'Error ejecutando la consulta: ' . $e->getMessage()]);
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'value' => $schema->string()->required()->description('JSON con la estructura de la consulta a ejecutar.'),
        ];
    }
}