<?php

namespace App\Http\Resources;

class TransactionResource extends BaseResource
{
    protected function toDefaultArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'wallet_id' => $this->resource->wallet_id,
            'type' => $this->resource->type,
            'amount' => $this->resource->amount,
            'related_wallet_id' => $this->resource->related_wallet_id,
            'created_at' => $this->resource->created_at,
            'currency_id' => $this->resource->currency_id,
            'currency' => CurrencyResource::make($this->whenLoaded('currency')),
        ];
    }
}
