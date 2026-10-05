<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\Api\SearchServices;

/**
 * Handles search-related API endpoints.
 */
class SearchController extends Controller
{
    /**
     * Perform a search on a specified model.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $model
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request, string $model)
    {
        $model = config('model')[$model];

        if ($model === null) {
            return response()->json([
                'message' => 'Modelo no encontrado',
                'data' => null,
            ], 404);
        }
        
        if (!class_exists($model)) {
            return response()->json([
                'message' => 'Modelo no encontrado',
                'data' => null,
            ], 404);
        }

        if(!SearchServices::verifyUsersHasPermission($model)) {
            return response()->json([
                'message' => 'No tienes permisos para realizar esta acción',
                'data' => null,
            ], 403);
        }

        $request->showSoftDeleted = $request->showSoftDeleted === 'true' ? true : false;
        $data = SearchServices::search(
            $model, 
            $request->extraQueryParameter, 
            $request->pagination, 
            $request->params, 
            $request->orderByField, 
            $request->orderByDirection, 
            $request->showSoftDeleted,
            $request->withRelations ?? []
        );

        if($data === false) {
            return response()->json([
                'message' => 'Tipo de búsqueda no permitido',
                'data' => null,
            ], 400);
        }

        if($data->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron resultados',
                'data' => null,
            ], 404);
        }

        $data = SearchServices::loadMorphableFields($model, $data);

        $data = SearchServices::loadOptionsFieldContent($data);

        $data = SearchServices::loadRelationFields($data);

        return response()->json([
            'message' => 'Búsqueda realizada con éxito',
            'data' => $data,
        ], 200);
    }

    /**
     * Perform a search for select options on a specified model.
     *
     * @param string $model
     * @param string $search
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchSelect(string $model, string $search, ?string $extraQueryParameter = null)
    {
        $model = config('model')[$model];

        if ($model === null) {
            return response()->json([
                'message' => 'Modelo no encontrado',
                'data' => null,
            ], 404);
        }
        
        if (!class_exists($model)) {
            return response()->json([
                'message' => 'Modelo no encontrado',
                'data' => null,
            ], 404);
        }

        if(!SearchServices::verifyUsersHasPermission($model)) {
            return response()->json([
                'message' => 'No tienes permisos para realizar esta acción',
                'data' => null,
            ], 403);
        }

        $data = SearchServices::searchSelect($model, $search, $extraQueryParameter, $request->withRelations ?? []);

        return response()->json([
            'message' => 'Búsqueda realizada con éxito',
            'data' => $data,
        ], 200);
    }
}
