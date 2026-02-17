<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Topping;

class ProductToppingSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all()->keyBy('name');
        $toppings = Topping::all()->keyBy('name');

        // ☕ Espresso
        $products['Espresso Single Origin']?->toppings()->sync([
            $toppings['Extra Espresso Shot']->id,
            $toppings['Almond Milk Upgrade']->id,
        ]);

        // 🥤 Caramel Latte
        $products['Caramel Sea Salt Latte']?->toppings()->sync([
            $toppings['Extra Espresso Shot']->id,
            $toppings['Almond Milk Upgrade']->id,
            $toppings['Whipped Cream']->id,
            $toppings['Caramel Drizzle']->id,
        ]);

        // 🧊 Cold Brew
        $products['Cold Brew Citrus']?->toppings()->sync([
            $toppings['Extra Espresso Shot']->id,
            $toppings['Almond Milk Upgrade']->id,
        ]);

        // 🍵 Matcha
        $products['Matcha Frappe']?->toppings()->sync([
            $toppings['Whipped Cream']->id,
            $toppings['Cheese Foam']->id,
        ]);

        // 🍰 Dessert
        $products['Choco Lava Cake']?->toppings()->sync([
            $toppings['Whipped Cream']->id,
            $toppings['Caramel Drizzle']->id,
        ]);

        $products['Seasonal Fruit Tart']?->toppings()->sync([
            $toppings['Whipped Cream']->id,
        ]);

        // 🥪 Food (no topping)
        $products['Butter Croissant']?->toppings()->sync([]);
        $products['Truffle Fries']?->toppings()->sync([]);
        $products['Grilled Chicken Panini']?->toppings()->sync([]);
        $products['Vegan Buddha Bowl']?->toppings()->sync([]);
    }
}
