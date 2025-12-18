<?php

namespace Database\Seeders;

use App\Models\CafeTable;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = User::query()->value('id');
        $tables = CafeTable::query()->pluck('id', 'table_number');

        $orders = [
            [
                'customer_name' => 'Andi Setiawan',
                'order_type' => 'dine_in',
                'table' => 'A01',
                'status' => 'completed',
                'totals' => ['subtotal' => 95000, 'discount' => 5000, 'service_fee' => 5000],
                'notes' => 'Lunch meeting dengan tim marketing.',
                'created_at' => Carbon::now()->subDays(2)->setTime(12, 30),
            ],
            [
                'customer_name' => 'Take Away - Livia',
                'order_type' => 'take_away',
                'table' => null,
                'status' => 'completed',
                'totals' => ['subtotal' => 172000, 'discount' => 12000, 'service_fee' => 8000],
                'notes' => 'Pickup untuk meeting kantor.',
                'created_at' => Carbon::now()->subDay()->setTime(9, 45),
            ],
            [
                'customer_name' => 'Delivery - Maria',
                'order_type' => 'delivery',
                'table' => null,
                'status' => 'completed',
                'totals' => ['subtotal' => 278000, 'discount' => 0, 'service_fee' => 15000],
                'notes' => 'Kirim ke coworking space Level 5.',
                'created_at' => Carbon::now()->subHours(6),
            ],
        ];

        foreach ($orders as $order) {
            $subtotal = $order['totals']['subtotal'];
            $discount = $order['totals']['discount'];
            $serviceFee = $order['totals']['service_fee'];
            $total = $subtotal - $discount + $serviceFee;

            $createdAt = $order['created_at'];
            $updatedAt = (clone $createdAt)->addMinutes(15);

            Order::query()->updateOrCreate(
                [
                    'customer_name' => $order['customer_name'],
                    'order_type' => $order['order_type'],
                ],
                [
                    'user_id' => $userId,
                    'table_id' => $order['table'] ? ($tables[$order['table']] ?? null) : null,
                    'status' => $order['status'],
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'total' => $total,
                    'subtotal_order' => $subtotal,
                    'discount_order' => $discount,
                    'service_fee_order' => $serviceFee,
                    'total_order' => $total,
                    'notes' => $order['notes'],
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]
            );
        }
    }
}
