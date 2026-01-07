<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_name' => fake()->name(),
            'currency_id' => Currency::factory(),
            'balance' => 0,
        ];
    }
}
