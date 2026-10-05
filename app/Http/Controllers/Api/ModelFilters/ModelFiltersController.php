<?php

namespace App\Http\Controllers\Api\ModelFilters;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ModelFiltersController extends Controller
{
    public function getAvailableFilterByModel(string $model)
    {
        if (!isset(config('model')[$model]::$available_model_filters)) {
            return response()->json([
                'message' => 'No se encontraron filtros disponibles para el modelo especificado.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Filtros encontrados exitosamente.',
            'data' => config('model')[$model]::$available_model_filters,
        ]);
    }
}
