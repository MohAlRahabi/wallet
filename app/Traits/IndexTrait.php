<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait IndexTrait
{
    protected array $indexRelation = [];
    protected array $indexCountRelation = [];
    protected int $limit = 10;
    protected bool $paginated = true;
    protected bool $isSearch = false;

    /**
     * starting a query
     * @return Builder
     */
    public function globalQuery(): Builder
    {
        return $this->model::query();
    }

    protected function getLimit(): int
    {
        return request('limit', $this->limit);

    }

    public function getData(): Collection
    {
        if ($this->paginated) {
            $items = $this->createQuery()->paginate(request('per_page', $this->getLimit()));
        } else {
            $items = $this->createQuery()->get();
        }
        return $this->formatIndexData($items);
    }

    protected function formatIndexData(Collection|LengthAwarePaginator $items): Collection
    {
        if ($this->resource) {
            if ($this->paginated) {
                $paginationData = Arr::only($items->toArray(), ['current_page', 'total', 'per_page', 'total', 'last_page',]);
                return collect([
                    'data' => $this->resource::all($items->getCollection(), withSearch: $this->isSearch),
                    'meta' => [
                        'pagination' => ['pages_count' => $paginationData['last_page'], ...$paginationData],
                    ]
                ]);
            } else {
                return $this->resource::all($items, withSearch: $this->isSearch);
            }
        }
        if (method_exists($this, 'formatIndex')) {
            if ($this->paginated) {
                $paginationData = Arr::only($items->toArray(), ['current_page', 'total', 'per_page', 'total', 'last_page',]);
                return collect([
                    'data' => $items->getCollection()->transform(array($this, 'formatIndex')),
                    'meta' => [
                        'pagination' => ['pages_count' => $paginationData['last_page'], ...$paginationData],
                    ]
                ]);
            } else {
                $items->transform(array($this, 'formatIndex'));
            }
        }
        return $items;
    }


    /**
     * return query applied with filters and ordering with relations
     * @return Builder
     */
    public function createQuery(): Builder
    {
        $query = $this->globalQuery()->with($this->getRelations())
            ->withCount($this->getCountRelations());
        $query = $this->applyFilters($query);
        $query = $this->addSearch($query);
        return $this->applyOrder($query);
    }

    public function addSearch(Builder $query): Builder
    {
        $keyword = request('search');
        if (!is_null($keyword)) {
            $table = (new $this->model)->getTable();
            $query = $query->where(function ($subQuery) use ($keyword, $table) {
                foreach ($this->searchableArray() as $fieldName) {
                    if (str_contains($fieldName, '.')) {
                        [$relation, $field] = explode('.', $fieldName);
                        $subQuery->orWhereRelation($relation, fn($qq) => $qq->where("$table.$field", 'LIKE', "%{$keyword}%"));
                    } else {
                        $subQuery->orWhere("$table.$fieldName", 'LIKE', "%{$keyword}%");
                    }
                }
            });
            $query->orWhere("$table.id", $keyword);
        }
        return $query;
    }

    public function searchableArray(): array
    {
        return (new $this->model())->getFillable();
    }

    /**
     * return array of relations names you need to return in the index
     * @return array
     */
    public function getRelations(): array
    {
        return $this->indexRelation;
    }

    /**
     * return array of relations names you need to get count in the index
     * @return array
     */
    public function getCountRelations(): array
    {
        return $this->indexCountRelation;
    }


    /**
     * return the default order
     * @param Builder $query
     * @return Builder
     */
    public function defaultOrdering(Builder $query): Builder
    {
        return $query;
    }

    /**
     * Define the fields available for ordering in the query.
     *
     *
     * Example usage:
     * ```
     * public function orderingFields(): array
     * {
     *     return [
     *         'name' => 'name',  // Order by the 'name' column
     *         'created_at' => 'created_at',  // Order by the 'created_at' column
     *         'custom_field' => function ($query, $direction) {
     *             $query->orderByRaw("custom_field IS NULL, custom_field {$direction}");
     *         },
     *         'updated_at' => ['field'=>'updated_at','direction'=>'desc'],
     *     ];
     * }
     *
     * @return array An array of fields and corresponding order logic.
     */
    public function orderingFields(): array
    {
        return [];
    }

    /**
     * filter fields applied in the create query
     *
     * Example usage:
     *  ```
     *  Assuming filterFields returns:
     *  [
     *    ['name' => 'status', 'condition' => '=', 'method' => 'where'],
     *    ['name' => 'created_at', 'condition' => '>=', 'value' => '2024-01-01'],
     *    ['name' => 'category_id', 'relation' => 'category']
     *  ]
     *  ```
     *
     * @return array
     */
    public function filterFields(): array
    {
        return [];
    }

    public function defaultFilters(): array
    {
        return [
            ['name' => 'forced_items', 'query' => fn($q, $val) => $q->whereIn('id', Arr::wrap($val))],
        ];
    }

    /**
     * Apply filters to the query builder based on predefined filter fields.
     *
     * Example usage:
     * ```
     * Assuming filterFields returns:
     * [
     *   ['name' => 'status', 'condition' => '=', 'method' => 'where'],
     *   ['name' => 'created_at', 'condition' => '>=', 'value' => '2024-01-01'],
     *   ['name' => 'category_id', 'relation' => 'category']
     * ]
     * ```
     *
     * @param Builder $query The query builder instance to apply filters on.
     * @return Builder The modified query builder with applied filters.
     */
    public function applyFilters(Builder $query): Builder
    {
        $defaultKeys = array_column($this->defaultFilters(), 'name');
        $filteredKeys = array_filter($this->filterFields(), function ($item) use ($defaultKeys) {
            return !in_array($item['name'], $defaultKeys);
        });
        $filterFields = array_merge(array_values($filteredKeys), $this->defaultFilters());;
        foreach ($filterFields as $filter) {
            $field = $filter['name'];
            $value = $filter['value'] ?? request($field);
            if (is_null($value)) continue;
            $condition = $filter['condition'] ?? '=';
            $method = $filter['method'] ?? 'where';
            if (isset($filter['relation'])) {
                $query->whereHas($filter['relation'], fn($subQuery) => $subQuery->$method($field, $condition, $value));
            } elseif (isset($filter['query']) && is_callable($filter['query'])) {
                call_user_func($filter['query'], $query, $value);
            } else {
                $query->{$method}($field, $condition, $value);
            }
        }
        return $query;
    }

    /**
     * Apply ordering to the query builder based on the 'order_by' request parameter.
     *
     *  Example usage:
     *  ```
     *  Request parameters:
     *  order_by: [{"name": "created_at", "direction": "desc"}, {"name": "name", "direction": "asc"}]
     *  ```
     * @param Builder $query The query builder instance to apply ordering on.
     * @return Builder The modified query builder with applied ordering.
     */
    public function applyOrder(Builder $query): Builder
    {
        if ($orderBy = request('order_by')) {
            if (is_array($orderBy)) {
                $orderFields = $this->orderingFields();
                foreach ($orderBy as $field) {
                    $direction = $field['direction'] ?? 'asc';
                    if (isset($orderFields[$field['name']])) {
                        $orderField = $orderFields[$field['name']];
                        if (is_callable($orderField)) {
                            call_user_func($orderField, $query, $direction);
                        } elseif (is_string($orderField)) {
                            $query->orderBy($orderField, $direction);
                        } elseif (is_array($orderField)) {
                            $query->orderBy($orderField['field'], $orderField['direction'] ?? $direction);
                        }
                    }
                }
            } else {
                $direction = request('order_direction', 'asc');
                $query->orderBy($orderBy, $direction);
            }
        } else {
            $query = $this->defaultOrdering($query);
        }
        return $query;
    }
}
