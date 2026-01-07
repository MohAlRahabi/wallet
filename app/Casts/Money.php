<?php

namespace App\Casts;

use App\Objects\MoneyObject;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Money implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $decimalPlaces = $model->currency?->decimal_places ?? 2;

        return new MoneyObject((int)$value, $decimalPlaces);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof MoneyObject) {
            return $value->getMinorUnits();
        }

        $decimalPlaces = $model->currency?->decimal_places ?? 2;

        if (is_int($value)) {
            return $value;
        }

        return (int)round((float)$value * pow(10, $decimalPlaces));
    }
}
