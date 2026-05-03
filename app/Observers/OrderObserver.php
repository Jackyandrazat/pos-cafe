<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\CustomerPointTransaction;
use Illuminate\Support\Facades\DB;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // 1️⃣ Pastikan status benar-benar berubah
        if (! $order->wasChanged('status')) {
            return;
        }

        // 2️⃣ Hanya proses jika status menjadi completed
        if ($order->status !== 'completed') {
            return;
        }

        // 3️⃣ Pastikan order punya customer
        if (! $order->customer_id) {
            return;
        }

        // 4️⃣ Cegah double reward (KRUSIAL)
        $alreadyRewarded = CustomerPointTransaction::where('source_type', Order::class)
            ->where('source_id', $order->id)
            ->exists();

        if ($alreadyRewarded) {
            return;
        }

        // 5️⃣ Jalankan semua logic loyalty setelah DB commit
        DB::afterCommit(function () use ($order) {

            app(\App\Services\LoyaltyService::class)
                ->rewardOrderPoints($order);

            app(\App\Services\Loyalty\LoyaltyProgressService::class)
                ->handleOrderCompleted($order);

            $order->customer
                ?->challengeProgresses
                ->each(function ($progress) {
                    app(\App\Services\Loyalty\LoyaltyRewardService::class)
                        ->rewardIfEligible($progress);
                });
        });
    }
}
