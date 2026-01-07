<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'id' => 1,
                'code' => 'USD',
                'name' => 'US Dollar',
                'decimal_places' => 2,
            ],
            [
                'id' => 2,
                'code' => 'EUR',
                'name' => 'Euro',
                'decimal_places' => 2,
            ]
        ];

        DB::table('currencies')->insert($currencies);
    }
}
