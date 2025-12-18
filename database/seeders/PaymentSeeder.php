<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $payments = [
            [
                'customer_name' => 'Andi Setiawan',
                'order_type' => 'dine_in',
                'payment_method' => 'cash',
                'amount_paid' => 100000,
                'change_return' => 5000,
                'payment_date' => Carbon::now()->subDays(2)->setTime(13, 0),
            ],
            [
                'customer_name' => 'Take Away - Livia',
                'order_type' => 'take_away',
                'payment_method' => 'qris',
                'amount_paid' => 168000,
                'change_return' => 0,
                'payment_date' => Carbon::now()->subDay()->setTime(10, 15),
            ],
            [
                'customer_name' => 'Delivery - Maria',
                'order_type' => 'delivery',
                'payment_method' => 'transfer',
                'amount_paid' => 293000,
                'change_return' => 0,
                'payment_date' => Carbon::now()->subHours(5),
            ],
        ];

        foreach ($payments as $payment) {
            $order = Order::query()
                ->where('customer_name', $payment['customer_name'])
                ->where('order_type', $payment['order_type'])
                ->first();

            if (! $order) {
                continue;
            }

            Payment::query()->updateOrCreate(
                [
                    'order_id' => $order->id,
                    'payment_method' => $payment['payment_method'],
                ],
                [
                    'amount_paid' => $payment['amount_paid'],
                    'change_return' => $payment['change_return'],
                    'payment_date' => $payment['payment_date'],
                    'created_at' => $payment['payment_date'],
                    'updated_at' => $payment['payment_date'],
                ]
            );
        }
    }
}
