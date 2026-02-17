<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    public function updated(Order $order)
    {
        if ($order->wasChanged('status') && $order->status === 'completed') {

            app(\App\Services\LoyaltyService::class)
                ->rewardOrderPoints($order);

            app(\App\Services\Loyalty\LoyaltyProgressService::class)
                ->handleOrderCompleted($order);

            $order->customer
                ?->challengeProgresses
                ->each(fn ($p) =>
                    app(\App\Services\Loyalty\LoyaltyRewardService::class)
                        ->rewardIfEligible($p)
                );
        }
    }

}
