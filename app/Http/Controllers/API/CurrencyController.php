<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\CurrencyResource;
use App\Models\Currency;

class CurrencyController extends BaseApiController
{
    protected string $model = Currency::class;
    protected ?string $resource = CurrencyResource::class;
}
