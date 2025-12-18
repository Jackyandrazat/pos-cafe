<?php

namespace Database\Seeders;

use App\Models\Topping;
use Illuminate\Database\Seeder;

class ToppingSeeder extends Seeder
{
    public function run(): void
    {
        $toppings = [
            ['name' => 'Extra Espresso Shot', 'price' => 8000],
            ['name' => 'Almond Milk Upgrade', 'price' => 6000],
            ['name' => 'Whipped Cream', 'price' => 5000],
            ['name' => 'Cheese Foam', 'price' => 7000],
            ['name' => 'Caramel Drizzle', 'price' => 4000],
        ];

        foreach ($toppings as $topping) {
            Topping::query()->updateOrCreate(
                ['name' => $topping['name']],
                [
                    'price' => $topping['price'],
                    'is_active' => true,
                ]
            );
        }
    }
}
