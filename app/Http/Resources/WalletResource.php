<?php

namespace App\Http\Resources;

class WalletResource extends BaseResource
{
    protected function toDefaultArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'owner_name' => $this->resource->owner_name,
            'currency_id' => $this->resource->currency_id,
            'currency' => CurrencyResource::make($this->whenLoaded('currency')),
        ];
    }

    protected function toDetailedArray($request): array
    {
        return [
            'balance' => $this->resource->balance->format(),
        ];
    }
}
