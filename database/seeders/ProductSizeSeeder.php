<?php

namespace Database\Seeders;

use App\Models\ProductSize;
use Illuminate\Database\Seeder;

class ProductSizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductSize::insert([
        ['product_id' => 4, 'name' => 'Regular', 'price_modifier' => 0],
        ['product_id' => 4, 'name' => 'Large', 'price_modifier' => 5000],
        ['product_id' => 4, 'name' => 'Extra Large', 'price_modifier' => 10000],

        // Espresso (id 1)
        ['product_id' => 1, 'name' => 'Regular', 'price_modifier' => 0],
        ['product_id' => 1, 'name' => 'Large', 'price_modifier' => 4000],

        // Cold Brew (id 3)
        ['product_id' => 3, 'name' => 'Regular', 'price_modifier' => 0],
        ['product_id' => 3, 'name' => 'Large', 'price_modifier' => 6000],
        ]);
    }
}
