<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;

class LoyaltyService
{
    public function __construct(protected float $pointsPerCurrency = 0.01)
    {
    }

    public function rewardOrderPoints(Order $order): void
    {
        $customer = $order->customer;

        if (! $customer) {
            return;
        }

        $points = (int) floor($order->total_order * $this->pointsPerCurrency);

        if ($points <= 0) {
            return;
        }

        $customer->pointTransactions()->create([
            'points' => $points,
            'type' => 'earn',
            'description' => 'Order #' . $order->id,
            'source_type' => Order::class,
            'source_id' => $order->id,
        ]);

        $customer->points += $points;
        $customer->lifetime_value += $order->total_order;
        $customer->last_order_at = now();
        $customer->save();
    }

    public function redeemPoints(Customer $customer, int $points, string $description = ''): void
    {
        $customer->pointTransactions()->create([
            'points' => -abs($points),
            'type' => 'redeem',
            'description' => $description,
        ]);

        $customer->decrement('points', $points);
    }
}
