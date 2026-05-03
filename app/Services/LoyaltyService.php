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

        // 🔐 Cegah double reward
        $alreadyRewarded = $customer->pointTransactions()
            ->where('source_type', Order::class)
            ->where('source_id', $order->id)
            ->exists();

        if ($alreadyRewarded) {
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

        $customer->increment('points', $points);
        $customer->increment('lifetime_value', $order->total_order);

        $customer->update([
            'last_order_at' => now(),
        ]);
    }

    public function redeemPoints(Customer $customer, int $points, string $description = ''): void
    {
        $points = abs($points);

        if ($points <= 0) {
            return;
        }

        if ($customer->points < $points) {
            throw new \Exception('Insufficient points');
        }

        $customer->pointTransactions()->create([
            'points' => -$points,
            'type' => 'redeem',
            'description' => $description,
        ]);

        $customer->decrement('points', $points);
    }

    public function addBonusPoints(Customer $customer, int $points, string $description = ''): void
    {
        if ($points <= 0) return;

        $customer->pointTransactions()->create([
            'points' => $points,
            'type' => 'bonus',
            'description' => $description,
        ]);

        $customer->increment('points', $points);
    }

}
