<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIds = Category::query()->pluck('id', 'name');

        $products = [
            [
                'category' => 'Coffee',
                'name' => 'Espresso Single Origin',
                'sku' => 'CF-ESP-001',
                'price' => 35000,
                'cost_price' => 12000,
                'stock_qty' => 120,
                'minimum_stock' => 15,
                'description' => 'Single origin espresso dengan profil cokelat dan citrus.',
            ],
            [
                'category' => 'Coffee',
                'name' => 'Caramel Sea Salt Latte',
                'sku' => 'CF-LAT-002',
                'price' => 42000,
                'cost_price' => 18000,
                'stock_qty' => 80,
                'minimum_stock' => 10,
                'description' => 'Latte creamy dengan saus caramel house-made.',
            ],
            [
                'category' => 'Tea & Refreshers',
                'name' => 'Cold Brew Citrus',
                'sku' => 'TR-CB-003',
                'price' => 38000,
                'cost_price' => 15000,
                'stock_qty' => 60,
                'minimum_stock' => 8,
                'description' => 'Cold brew 18 jam dengan infused jeruk sunkist.',
            ],
            [
                'category' => 'Tea & Refreshers',
                'name' => 'Matcha Frappe',
                'sku' => 'TR-MTC-004',
                'price' => 40000,
                'cost_price' => 17000,
                'stock_qty' => 70,
                'minimum_stock' => 8,
                'description' => 'Matcha premium Uji Kyoto dengan susu oat.',
            ],
            [
                'category' => 'Pastry',
                'name' => 'Butter Croissant',
                'sku' => 'PS-CRO-005',
                'price' => 25000,
                'cost_price' => 9000,
                'stock_qty' => 90,
                'minimum_stock' => 12,
                'description' => 'Croissant flaky dengan butter Prancis Isigny.',
            ],
            [
                'category' => 'Dessert',
                'name' => 'Choco Lava Cake',
                'sku' => 'DS-LAVA-006',
                'price' => 45000,
                'cost_price' => 20000,
                'stock_qty' => 50,
                'minimum_stock' => 6,
                'description' => 'Lava cake valrhona dengan es krim vanilla.',
            ],
            [
                'category' => 'Main Course',
                'name' => 'Truffle Fries',
                'sku' => 'MC-FRY-007',
                'price' => 38000,
                'cost_price' => 15000,
                'stock_qty' => 65,
                'minimum_stock' => 10,
                'description' => 'Kentang goreng tipis dengan minyak truffle dan parmesan.',
            ],
            [
                'category' => 'Main Course',
                'name' => 'Grilled Chicken Panini',
                'sku' => 'MC-PAN-008',
                'price' => 65000,
                'cost_price' => 30000,
                'stock_qty' => 55,
                'minimum_stock' => 10,
                'description' => 'Panini sourdough dengan ayam panggang madu mustard.',
            ],
            [
                'category' => 'Main Course',
                'name' => 'Vegan Buddha Bowl',
                'sku' => 'MC-BUD-009',
                'price' => 72000,
                'cost_price' => 28000,
                'stock_qty' => 40,
                'minimum_stock' => 5,
                'description' => 'Quinoa, hummus, roasted veggies dan tahini dressing.',
            ],
            [
                'category' => 'Dessert',
                'name' => 'Seasonal Fruit Tart',
                'sku' => 'DS-TART-010',
                'price' => 48000,
                'cost_price' => 21000,
                'stock_qty' => 45,
                'minimum_stock' => 6,
                'description' => 'Tart vanilla custard dengan buah segar lokal.',
            ],
        ];

        foreach ($products as $product) {
            $categoryId = $categoryIds[$product['category']] ?? null;

            if (! $categoryId) {
                continue;
            }

            $payload = [
                'category_id' => $categoryId,
                'name' => $product['name'],
                'sku' => $product['sku'],
                'price' => $product['price'],
                'cost_price' => $product['cost_price'],
                'stock_qty' => $product['stock_qty'],
                'description' => $product['description'],
                'status_enabled' => true,
            ];

            $model = Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                $payload
            );

            DB::table('product_stocks')->updateOrInsert(
                ['product_id' => $model->id],
                [
                    'current_stock' => $product['stock_qty'],
                    'minimum_stock' => $product['minimum_stock'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
