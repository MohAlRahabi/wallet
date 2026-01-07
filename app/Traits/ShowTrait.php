<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

trait ShowTrait
{
    protected array $showRelation = [];
    protected array $showCountRelation = [];

    public function findItem(int|string|Model $id): ?Model
    {
        if ($id instanceof Model) {
            return $id;
        }

        $query = $this->globalQuery();
        $item = $this->customShowQuery($query)
            ->with($this->getShowRelations())
            ->withCount($this->getShowCountRelations())
            ->find($id);

        if ($item) {
            return $item;
        }
        return null;
    }

    protected function formatItem(Model $item): JsonResource|array|Model
    {
        return $this->resource ? $this->resource::makeWithDetail($item) : $item;
    }

    public function getShowRelations(): array
    {
        return array_merge($this->showRelation, $this->getRelations());
    }


    public function getShowCountRelations(): array
    {
        return $this->showCountRelation;
    }

    public function customShowQuery(Builder $globalQuery): Builder
    {
        return $globalQuery;
    }
}
