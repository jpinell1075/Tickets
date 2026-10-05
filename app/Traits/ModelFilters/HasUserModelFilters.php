<?php

namespace App\Traits\ModelFilters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

trait HasUserModelFilters
{
    /**
    * Constants for filter types.
    */
    public const TYPE_SIMPLE = 'simple';
    public const TYPE_RELATIONS = 'relations';
    public const TYPE_FUNCTIONS = 'functions';
    public const TYPE_USER_OWN = 'user_own';

    /**
    * Constants for filter comparison types.
    */
    public const TYPE_OPEN = 'open';
    public const TYPE_STATIC_SELECT = 'static_select';
    public const TYPE_DYNAMIC_SELECT = 'dynamic_select';

    /**
    * Constants for filter operators.
    */
    public const OP_EQUAL = '=';
    public const OP_NOT_EQUAL = '!=';
    public const OP_GREATER = '>';
    public const OP_LESS = '<';
    public const OP_GREATER_OR_EQUAL = '>=';
    public const OP_LESS_OR_EQUAL = '<=';

    /**
     * Boot the trait and add a global scope to apply user model filters.
     * This method is automatically called by Eloquent when the model is booted.
     * @return void
     */
    protected static function bootHasUserModelFilters()
    {
        static::addGlobalScope('user_model_filters', function (Builder $builder) {
            $className = static::getActualModelName();
            $userModelFilters = static::getUserModelFiltersByModel($className);
            foreach ($userModelFilters as $userModelFilter) {
                if($userModelFilter['comparison_type'] == static::TYPE_SIMPLE) {
                    $builder = static::setSimpleFilter($builder, $userModelFilter['field'], $userModelFilter['operator'], $userModelFilter['value']);
                }
                if($userModelFilter['comparison_type'] == static::TYPE_RELATIONS) {
                    $builder = static::setRelationFilter($builder, $userModelFilter['relation'], $userModelFilter['field'], $userModelFilter['operator'], $userModelFilter['value']);
                }
                if($userModelFilter['comparison_type'] == static::TYPE_FUNCTIONS) {
                    $functionName = json_decode($userModelFilter['extra'], true)['method'];
                    if(!method_exists($builder, $functionName)) {
                        throw new \Exception("Function {$functionName} does not exist on the builder.");
                    }
                    $builder = static::setFunctionFilter($builder, $functionName, $userModelFilter['field'], $userModelFilter['operator'], $userModelFilter['value']);
                }
                if($userModelFilter['comparison_type'] == static::TYPE_USER_OWN) {
                    $data = json_decode($userModelFilter['extra'], true);
                    $builder = static::setUserOwnFilter($builder, $data['relation_name'], $data['foreign_key']);
                }
            }
        });
    }

    /**
     * Get the actual model name from the configuration.
     * This method retrieves the model name based on the class name of the current model.
     *
     * @return string The actual model name.
     * @throws \Exception If the model filters configuration is not found.
     */
    protected static function getActualModelName(): string
    {
        $className = (new \ReflectionClass(static::class))->getShortName();
        $modelFiltersConfig = config('modelFilters');

        if (!isset($modelFiltersConfig[$className])) {
            throw new \Exception("Model filters configuration for {$className} not found.");
        }
        return $modelFiltersConfig[$className]['model'];
    }

    /**
     * Get the short name of the model class.
     * This method retrieves the short name of the current model class.
     *
     * @return string The short name of the model class.
     */
    protected static function getShortModelName(): string
    {
        return (new \ReflectionClass(static::class))->getShortName();
    }

    /**
     * Retrieve user model filters for the current authenticated user and model.
     * This method fetches the filters from the database based on the authenticated user and model.
     *
     * @param string $model The model name for which to retrieve filters.
     * @return array An array of user model filters.
     */
    protected static function getUserModelFiltersByModel(string $model): array
    {
        $userId = static::getAuthenticatedUserId();

        if (!$userId) {
            return [];
        }

        return collect(DB::table('user_model_filters')
            ->where('user_id', $userId)
            ->where('model', $model)
            ->get()
            ->toArray())->map(function ($item) {
                return (array) $item;
            })->toArray();
    }

    /**
     * Get the ID of the authenticated user.
     * This method retrieves the user ID from the session cookie.
     *
     * @return int|null The authenticated user ID or null if not authenticated.
     */
    protected static function getAuthenticatedUserId(): ?int
    {
        $cookieName = Config::get('session.cookie');
        $sessionId = request()->cookie($cookieName);

        if (!$sessionId) {
            return null;
        }

        $session = DB::table('sessions')
            ->where('id', $sessionId)
            ->first();

        if (!$session || !$session->user_id) {
            return null;
        }

        return $session->user_id;
    }

    /**
     * Set the filter on the builder based on the comparison type.
     * This method applies the appropriate filter to the Eloquent query builder.
     *
     * @param Builder $builder The Eloquent query builder instance.
     * @param string $field The field to filter on.
     * @param string $operator The operator for the filter.
     * @param mixed $value The value to filter by.
     * @return Builder The modified Eloquent query builder instance.
     */
    protected static function setSimpleFilter(Builder $builder, string $field, string $operator, mixed $value): Builder
    {
        return $builder->where($field, $operator, $value);
    }

    /**
     * Set the relation filter on the builder.
     * This method applies a filter to a related model using the whereHas method.
     *
     * @param Builder $builder The Eloquent query builder instance.
     * @param string|null $relation The name of the relation to filter on.
     * @param string $field The field to filter on.
     * @param string $operator The operator for the filter.
     * @param mixed $value The value to filter by.
     * @return Builder The modified Eloquent query builder instance.
     */
    protected static function setRelationFilter(Builder $builder, ?string $relation, string $field, string $operator, mixed $value): Builder
    {
        if (!$relation) {
            return $builder->where($field, $operator, $value);
        }

        return $builder->whereHas($relation, function (Builder $query) use ($field, $operator, $value) {
            $query->where($field, $operator, $value);
        });
    }

    /**
     * Set the function filter on the builder.
     * This method applies a filter using a custom function on the Eloquent query builder.
     *
     * @param Builder $builder The Eloquent query builder instance.
     * @param string $function The name of the function to call on the builder.
     * @param string $field The field to filter on.
     * @param string $operator The operator for the filter.
     * @param mixed $value The value to filter by.
     * @return Builder The modified Eloquent query builder instance.
     */
    protected static function setFunctionFilter(Builder $builder, string $function ,string $field, string $operator, mixed $value): Builder
    {
        return $builder->$function($field, $operator, $value);
    }

    /**
     * Set the user own filter on the builder.
     * This method applies a filter to ensure that only records owned by the authenticated user are returned.
     *
     * @param Builder $builder The Eloquent query builder instance.
     * @param string|null $relation_name The name of the relation to filter on, if applicable.
     * @param string $foreign_key The foreign key to use for filtering.
     * @return Builder The modified Eloquent query builder instance.
     */
    protected static function setUserOwnFilter(Builder $builder, ?string $relation_name, string $foreign_key): Builder
    {
        $userId = static::getAuthenticatedUserId();
        if (!$userId) {
            return $builder;
        }

        if($relation_name) {
            return $builder->whereHas($relation_name, function (Builder $query) use ($foreign_key, $userId) {
                $query->where($foreign_key, $userId);
            });
        }

        return $builder->where($foreign_key, $userId);
    }
}