<?php

namespace App\Services\Loyalty;

use App\Models\Order;
use App\Models\LoyaltyChallenge;
use App\Models\LoyaltyChallengeProgress;
use Carbon\Carbon;

class LoyaltyProgressService
{
    public function handleOrderCompleted(Order $order): void
    {
        $customer = $order->customer;
        if (! $customer) return;

        $challenges = LoyaltyChallenge::active()->get();

        foreach ($challenges as $challenge) {
            match ($challenge->type) {
                'weekly_visits' => $this->handleWeeklyVisit($challenge, $customer),
                'new_variant' => $this->handleNewVariant($challenge, $customer, $order),
                default => null,
            };
        }
    }

    protected function handleWeeklyVisit($challenge, $customer)
    {
        $progress = LoyaltyChallengeProgress::firstOrCreate([
            'loyalty_challenge_id' => $challenge->id,
            'customer_id' => $customer->id,
        ], [
            'window_start' => now()->startOfWeek(),
            'window_end' => now()->endOfWeek(),
        ]);

        // reset kalau lewat window
        if ($progress->window_end && now()->gt($progress->window_end)) {
            $progress->update([
                'current_value' => 0,
                'completed_at' => null,
                'rewarded_at' => null,
                'window_start' => now()->startOfWeek(),
                'window_end' => now()->endOfWeek(),
            ]);
        }

        if ($progress->completed_at) return;

        $progress->increment('current_value');
        $progress->last_progressed_at = now();

        if ($progress->current_value >= $challenge->target_value) {
            $progress->completed_at = now();
        }

        $progress->save();
    }

    protected function handleNewVariant($challenge, $customer, Order $order)
    {
        $progress = LoyaltyChallengeProgress::firstOrCreate([
            'loyalty_challenge_id' => $challenge->id,
            'customer_id' => $customer->id,
        ]);

        if ($progress->completed_at) return;

        $products = collect($order->items)->pluck('product_id')->unique();

        $meta = $progress->meta ?? [];
        $meta['latest_new_products'] = $products;

        $progress->current_value = $products->count();
        $progress->meta = $meta;

        if ($progress->current_value >= $challenge->target_value) {
            $progress->completed_at = now();
        }

        $progress->save();
    }
}
