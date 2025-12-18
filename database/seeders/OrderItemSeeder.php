<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemTopping;
use App\Models\Product;
use App\Models\Topping;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var Collection<int, \App\Models\Order> $orders */
        $orders = Order::query()->get()->keyBy(fn ($order) => $order->customer_name.'|'.$order->order_type);
        $products = Product::query()->get()->keyBy('sku');
        $toppings = Topping::query()->get()->keyBy('name');

        $items = [
            [
                'order_key' => 'Andi Setiawan|dine_in',
                'sku' => 'CF-ESP-001',
                'qty' => 2,
                'price' => 35000,
                'discount' => 0,
                'toppings' => [
                    ['name' => 'Extra Espresso Shot', 'quantity' => 1],
                ],
            ],
            [
                'order_key' => 'Andi Setiawan|dine_in',
                'sku' => 'PS-CRO-005',
                'qty' => 1,
                'price' => 25000,
                'discount' => 0,
            ],
            [
                'order_key' => 'Take Away - Livia|take_away',
                'sku' => 'CF-LAT-002',
                'qty' => 1,
                'price' => 42000,
                'discount' => 0,
                'toppings' => [
                    ['name' => 'Almond Milk Upgrade', 'quantity' => 1],
                    ['name' => 'Caramel Drizzle', 'quantity' => 1],
                ],
            ],
            [
                'order_key' => 'Take Away - Livia|take_away',
                'sku' => 'TR-MTC-004',
                'qty' => 1,
                'price' => 40000,
                'discount' => 0,
            ],
            [
                'order_key' => 'Take Away - Livia|take_away',
                'sku' => 'DS-LAVA-006',
                'qty' => 2,
                'price' => 45000,
                'discount' => 2000,
            ],
            [
                'order_key' => 'Delivery - Maria|delivery',
                'sku' => 'MC-PAN-008',
                'qty' => 2,
                'price' => 65000,
                'discount' => 0,
            ],
            [
                'order_key' => 'Delivery - Maria|delivery',
                'sku' => 'MC-BUD-009',
                'qty' => 1,
                'price' => 72000,
                'discount' => 0,
            ],
            [
                'order_key' => 'Delivery - Maria|delivery',
                'sku' => 'TR-CB-003',
                'qty' => 2,
                'price' => 38000,
                'discount' => 0,
            ],
        ];

        foreach ($items as $item) {
            $order = $orders[$item['order_key']] ?? null;
            $product = $products[$item['sku']] ?? null;

            if (! $order || ! $product) {
                continue;
            }

            $price = $item['price'] ?? $product->price;
            $discount = $item['discount'] ?? 0;
            $subtotal = ($price * $item['qty']) - $discount;

            $orderItem = OrderItem::query()->updateOrCreate(
                [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                ],
                [
                    'qty' => $item['qty'],
                    'price' => $price,
                    'discount_amount' => $discount,
                    'subtotal' => $subtotal,
                ]
            );

            foreach ($item['toppings'] ?? [] as $toppingData) {
                $topping = $toppings[$toppingData['name']] ?? null;
                $toppingPrice = $toppingData['price'] ?? ($topping?->price ?? 0);
                $quantity = $toppingData['quantity'] ?? 1;

                OrderItemTopping::query()->updateOrCreate(
                    [
                        'order_item_id' => $orderItem->id,
                        'name' => $toppingData['name'],
                    ],
                    [
                        'topping_id' => $topping?->id,
                        'price' => $toppingPrice,
                        'quantity' => $quantity,
                        'total' => $toppingPrice * $quantity,
                    ]
                );
            }
        }
    }
}
