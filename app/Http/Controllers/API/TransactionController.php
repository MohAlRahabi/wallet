<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;

class TransactionController extends BaseApiController
{
    protected string $model = Transaction::class;
    protected ?string $resource = TransactionResource::class;

    public function filterFields(): array
    {
        return [
            ['name' => 'wallet_id'],
        ];
    }
}
