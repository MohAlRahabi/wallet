<?php

namespace App\Http\Resources;

use Countable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;

class BaseResource extends JsonResource
{
    protected bool $search = false;
    protected bool $detailed = false;
    protected bool $hasAbilities = false;
    protected ?Authenticatable $user = null;

    public function toArray($request): array
    {
        if ($this->search) {
            return $this->toSearchArray($request);
        }
        if ($this->detailed) {
            return array_merge(
                $this->toDefaultArray($request),
                $this->toDetailedArray($request)
            );
        }

        return $this->toDefaultArray($request);
    }

    protected function toDefaultArray($request): array
    {
        return $this->resource->toArray();
    }

    protected function toDetailedArray($request): array
    {
        return [];
    }

    protected function toSearchArray($request): array
    {
        return [
            'name' => $this->resource->name ?? $this->resource->id,
            'id' => $this->resource->id,
        ];
    }

    public function withDetail(bool $detailed = true): static
    {
        $this->detailed = $detailed;
        return $this;
    }

    public function withSearch(bool $detailed = true): static
    {
        $this->search = $detailed;
        return $this;
    }

    public static function makeWithDetail(mixed $resource, bool $detailed = true, bool $withSearch = false)
    {
        return self::make($resource)->withDetail($detailed)->withSearch($withSearch);
    }

    public static function all(Countable $resource, bool $detailed = false, bool $withSearch = false)
    {
        $collection = $resource instanceof AbstractPaginator
            ? $resource->getCollection()
            : collect($resource);
        $mapped = $collection->map(fn($item) => self::make($item)->withDetail($detailed)->withSearch($withSearch));

        if ($resource instanceof AbstractPaginator) {
            return $resource->setCollection($mapped);
        }
        return $mapped->values();
    }
}
