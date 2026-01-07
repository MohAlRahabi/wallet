<?php

namespace App\Http\Resources;

class CurrencyResource extends BaseResource
{
    protected function toDefaultArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'code' => $this->resource->code,
            'name' => $this->resource->name,
            'decimal_places' => $this->resource->decimal_places,
        ];
    }
}
