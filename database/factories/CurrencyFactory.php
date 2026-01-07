<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->name(),
            'name' => fake()->name(),
            'decimal_places' => fake()->numberBetween(1, 4),
        ];
    }
}
