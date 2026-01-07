<?php

namespace Database\Factories;

use App\Enums\TransactionTypeEnum;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'currency_id' => fn($attrs) => Wallet::find($attrs['wallet_id'])->currency_id,
            'type' => fake()->randomElement(TransactionTypeEnum::cases()),
            'amount' => fake()->numberBetween(1, 100),
            'related_wallet_id' => null,
        ];
    }
}
