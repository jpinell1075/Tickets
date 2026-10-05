<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Exception;

class DynamicQueryService
{
    public function execute(array $payload)
    {
        $query = $this->buildQuery($payload);

        if (!empty($payload['aggregations'])) {
            return $this->executeAggregation($query, $payload['aggregations']);
        }

        $limit = min($payload['limit'] ?? 100, 500);
        $query->limit($limit);

        if (isset($payload['offset'])) {
            $query->offset($payload['offset']);
        }

        return $query->get();
    }

    protected function buildQuery(array $payload): Builder
    {
        $modelClass = $this->resolveModelClass($payload['model']);
        $query = $modelClass::query();

        if (!empty($payload['select'])) {
            $query->select($payload['select']);
        }

        if (!empty($payload['filters'])) {
            $this->applyFilters($query, $payload['filters']);
        }

        if (!empty($payload['relations'])) {
            $this->applyRelations($query, $payload['relations']);
        }

        if (!empty($payload['sort'])) {
            foreach ($payload['sort'] as $sort) {
                $query->orderBy($sort['column'], $sort['direction']);
            }
        }

        return $query;
    }

    protected function resolveModelClass(string $modelName): string
    {
        $class = str_contains($modelName, 'App\\Models\\') 
            ? $modelName 
            : 'App\\Models\\' . $modelName;

        if (!class_exists($class)) {
            throw new Exception("El modelo {$class} no existe o no está permitido.");
        }

        return $class;
    }

    protected function applyFilters(Builder|Relation $query, array $filters): void
    {
        foreach ($filters as $filter) {
            $type = $filter['type'] ?? 'where';
            $column = $filter['column'] ?? null;
            $boolean = $filter['boolean'] ?? 'and';

            match ($type) {
                'where' => $query->where($column, $filter['operator'] ?? '=', $filter['value'], $boolean),
                'whereIn' => $query->whereIn($column, $filter['value'], $boolean),
                'whereNotIn' => $query->whereNotIn($column, $filter['value'], $boolean),
                'whereNull' => $query->whereNull($column, $boolean),
                'whereNotNull' => $query->whereNotNull($column, $boolean),
                'whereBetween' => $query->whereBetween($column, $filter['value'], $boolean),
                
                'whereNested' => $query->where(function ($q) use ($filter) {
                    $this->applyFilters($q, $filter['filters'] ?? []);
                }, boolean: $boolean),

                'whereHas' => $query->whereHas($filter['relation'], function ($q) use ($filter) {
                    if (!empty($filter['filters'])) {
                        $this->applyFilters($q, $filter['filters']);
                    }
                }, boolean: $boolean),

                'whereDoesntHave' => $query->whereDoesntHave($filter['relation'], function ($q) use ($filter) {
                    if (!empty($filter['filters'])) {
                        $this->applyFilters($q, $filter['filters']);
                    }
                }, boolean: $boolean),

                default => null
            };
        }
    }

    protected function applyRelations(Builder|Relation $query, array $relations): void
    {
        foreach ($relations as $relationInfo) {
            $relationName = $relationInfo['name'];

            $query->with([$relationName => function ($q) use ($relationInfo) {
                
                if (!empty($relationInfo['select'])) {
                    $q->select($relationInfo['select']);
                }

                if (!empty($relationInfo['filters'])) {
                    $this->applyFilters($q, $relationInfo['filters']);
                }

                if (!empty($relationInfo['relations'])) {
                    $this->applyRelations($q, $relationInfo['relations']);
                }
                
            }]);
        }
    }

    protected function executeAggregation(Builder $query, array $aggregation)
    {
        if (!empty($aggregation['group_by'])) {
            $query->groupBy($aggregation['group_by']);
            $query->selectRaw("{$aggregation['group_by']}, {$aggregation['type']}({$aggregation['column']}) as aggregate_result");
            return $query->get();
        }
        
        return $query->{$aggregation['type']}($aggregation['column']);
    }
}