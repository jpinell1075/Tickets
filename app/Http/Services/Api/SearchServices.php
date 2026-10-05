<?php

namespace App\Http\Services\Api;

class SearchServices
{
    /**
     * Retrieve the key associated with a specific value in an array.
     *
     * @param array $array
     * @param mixed $value
     * @return mixed|null The key if found, otherwise null.
     */
    private static function getKeyByValue(array $array, $value)
    {
        foreach ($array as $key => $item) {
            if ($item === $value) {
                return $key;
            }
        }
    }

    /**
     * Add a where condition to the query based on the field, search type, and value.
     * Handles morphable fields and relations if applicable.
     *
     * @param string $field
     * @param string $search_type
     * @param mixed $search
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $model
     * @return \Illuminate\Database\Query\Builder The updated query.
     */
    private static function addWhere($field, $search_type, $search, $query, $model)
    {
        if (isset($model::$morphable_fiels[$field])) {
            $morphField = $model::$morphable_fiels[$field];
            if (isset($model::$$morphField)) {
                $search = self::getKeyByValue($model::$$morphField, $search);
            }
        }

        if(isset($model::$searchable_fields_is_relation[$field]) && $model::$searchable_fields_is_relation[$field]) {
            $query->whereHas($field, function ($query) use ($search_type, $search, $model, $field) {
                if ($search_type == 'like')
                {
                    $query->where($model::$relation_fields[$field]['foreign_field'], $search_type, "%$search%");
                }
                else
                {
                    $query->where($model::$relation_fields[$field]['foreign_field'], $search_type, $search);
                }
            });
        } else {
            if ($search_type == 'like')
            {
                $query->where($field, $search_type, "%$search%");
            }
            else
            {
                $query->where($field, $search_type, $search);
            }
        }

        return $query;
    }

    /**
     * Verify if a field is searchable in the given model.
     *
     * @param string $model
     * @param string|null $field
     * @return bool True if the field is searchable, otherwise false.
     */
    public static function verifyFieldIsSearchable(string $model, string $field = null): bool
    {
        if($field === null) {
            return true;
        }

        return in_array($field, $model::$table_fields_searchable);
    }

    /**
     * Check if the authenticated user has permission to access the model.
     *
     * @param string $model
     * @return bool True if the user has permission, otherwise false.
     */
    public static function verifyUsersHasPermission(string $model): bool
    {
        if(!isset($model::$permisson_modules)) {
            return false;
        }

        foreach ($model::$permisson_modules as $module) {
            if(auth()->user()->hasAccessToModule($module, 'SEARCH')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Perform a search query on the model with optional parameters and pagination.
     *
     * @param string $model
     * @param mixed|null $extra_query_parameter
     * @param int $pagination
     * @param array|null $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator The paginated search results.
     */
    public static function search(string $model, $extra_query_parameter = null, $pagination = 20, $params = null, $orderByField = null, $orderByDirection = 'asc', $showSoftDeleted = false, $withRelations = [])
    {
        $permited_search_types = ['like', '=', '!=', '>', '<', '>=', '<='];

        foreach ($params as $param) {
            if(!in_array($param['search_type'], $permited_search_types)) {
                return false;
            }

            if(!self::verifyFieldIsSearchable($model, $param['field'])) {
                return false;
            }
        }

        $query = $model::query();

        if($orderByField && (self::verifyFieldIsSearchable($model, $orderByField ) || $orderByField === 'created_at')) {
            $query->orderBy($orderByField, $orderByDirection);
        }

        foreach ($params as $param) {
            if($param['field'] && $param['search_type'] && $param['search']){
                $query = self::addWhere($param['field'], $param['search_type'], $param['search'], $query, $model);
            }
        }

        if(isset($model::$relation_fields))
        {
            foreach ($model::$relation_fields as $relation_field) {
                $query->with($relation_field['relation']);
            }
        }

        self::loadExtraQuery($model, $query, $extra_query_parameter);

        if($showSoftDeleted && in_array('App\Traits\ModelSoftDeleteTrait', class_uses($model))) {
            $query->withTrashed();
        }

        if(!empty($withRelations)) {
            $query->with($withRelations);
        }

        return $query->paginate($pagination);
    }

    /**
     * Perform a search query for select options on the model.
     *
     * @param string $model
     * @param string $search
     * @return \Illuminate\Support\Collection The search results.
     */
    public static function searchSelect(string $model, string $search, ?string $extra_query_parameter = null, $withRelations = [])
    {
        $query = $model::query();

        if($search != "*")
        {
            $query = self::addDinamicWhere($model::$name_field, $search, $query);
        }

        if($extra_query_parameter) {
            self::loadExtraQuery($model, $query, $extra_query_parameter);
        }

        if(!empty($withRelations)) {
            $query->with($withRelations);
        }

        $data = $query->get();

        $data = self::loadSearchName($data);

        return $data;
    }

    public static function addDinamicWhere($field, $search, $query)
    {
        $fields = explode('.', $field);
        if(count($fields) > 1) {
            $relation = implode('.', array_slice($fields, 0, -1));
            $foreign_field = $fields[count($fields) - 1];

            return $query->whereHas($relation, function ($query) use ($foreign_field, $search) {
                $query->where($foreign_field, 'like', "%$search%");
            });
        }

        return $query->where($field, 'like', "%$search%");
    }

    /**
     * Load additional query parameters into the query if the model supports it.
     *
     * @param string $model
     * @param \Illuminate\Database\Query\Builder $query
     * @param mixed $extra_query_parameter
     */
    public static function loadExtraQuery($model, $query, $extra_query_parameter)
    {
        if(method_exists($model, 'extraQuery')) {
            $model::extraQuery($query, $extra_query_parameter);
        }
    }

    /**
     * Map morphable fields to their corresponding values in the data.
     *
     * @param string $model
     * @param \Illuminate\Support\Collection $data
     * @return \Illuminate\Support\Collection The updated data.
     */
    public static function loadMorphableFields(string $model, $data)
    {
        if(!isset($model::$morphable_fiels)) {
            return $data;
        }

        foreach ($model::$morphable_fiels as $field => $morphable_name) {
            $data->map(function ($item) use ($field, $morphable_name, $model) {
                if (!isset($model::$$morphable_name[$item->$field])) {
                    return $item;
                }

                $item->$field = $model::$$morphable_name[$item->$field];
                return $item;
            });
        }

        return $data;
    }

    /**
     * Load options field content for each item in the data.
     *
     * @param \Illuminate\Support\Collection $data
     * @return \Illuminate\Support\Collection The updated data.
     */
    public static function loadOptionsFieldContent($data)
    {
        $data->map(function ($item) {
            $item->options = $item->getOptionsFieldContent();
            return $item;
        });

        return $data;
    }

    /**
     * Load the search name for each item in the data.
     *
     * @param \Illuminate\Support\Collection $data
     * @return \Illuminate\Support\Collection The updated data.
     */
    public static function loadSearchName($data)
    {
        $data->map(function ($item) {
            if($item::$name_field) {
                $item->name = data_get($item, $item::$name_field);
            }
            if (method_exists($item, 'getSearchName')) {
                $item->search_name = $item->getSearchName();
            }
            return $item;
        });
        return $data;
    }

    /**
     * Load relation fields for each item in the data and unset the relation.
     *
     * @param \Illuminate\Support\Collection $data
     * @return \Illuminate\Support\Collection The updated data.
     */
    public static function loadRelationFields($data)
    {
        $data->map(function ($item) {
            if(isset($item::$relation_fields)) {
                foreach ($item::$relation_fields as $relation_field) {
                    $item->{$relation_field['field']} = $item->{$relation_field['relation']}->{$relation_field['foreign_field']};
                    $item->unsetRelation($relation_field['relation']);
                }
            }
        });

        return $data;
    }
}