<?php

namespace App\Services;

use App\Enums\LoyaltyChallengeType;
use App\Models\Customer;
use App\Models\LoyaltyChallenge;
use App\Models\LoyaltyChallengeAward;
use App\Models\LoyaltyChallengeProgress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\Feature;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GamifiedLoyaltyService
{
    public function trackOrderProgress(?Order $order): void
    {
        if (! Feature::enabled('loyalty') || ! $order?->customer) {
            return;
        }

        $order->loadMissing(['customer', 'items']);

        $challenges = LoyaltyChallenge::active()->get();

        foreach ($challenges as $challenge) {
            if (! $challenge->isCurrentlyActive()) {
                continue;
            }

            $progress = $this->resolveProgress($challenge, $order->customer);

            $this->refreshProgressWindow($challenge, $progress);

            if ($progress->rewarded_at && $challenge->reset_period === 'none') {
                continue;
            }

            $increment = $this->calculateIncrement($challenge, $order, $progress);

            if ($increment <= 0) {
                continue;
            }

            $progress->current_value = min(
                $challenge->target_value,
                $progress->current_value + $increment,
            );
            $progress->last_progressed_at = Carbon::now();

            if ($progress->current_value >= $challenge->target_value && ! $progress->rewarded_at) {
                $progress->completed_at = Carbon::now();
                $this->rewardProgress($progress, $challenge);
            }

            $progress->save();
        }
    }

    protected function resolveProgress(LoyaltyChallenge $challenge, Customer $customer): LoyaltyChallengeProgress
    {
        [$windowStart, $windowEnd] = $challenge->currentWindowBounds();

        return $challenge->progresses()->firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'window_start' => $windowStart,
                'window_end' => $windowEnd,
            ],
        );
    }

    protected function refreshProgressWindow(LoyaltyChallenge $challenge, LoyaltyChallengeProgress $progress): void
    {
        if ($challenge->reset_period === 'none') {
            if (! $progress->window_start || ! $progress->window_end) {
                [$start, $end] = $challenge->currentWindowBounds();
                $progress->window_start = $progress->window_start ?: $start;
                $progress->window_end = $progress->window_end ?: $end;
            }

            return;
        }

        $now = Carbon::now();
        [$start, $end] = $challenge->currentWindowBounds($now);

        if (! $progress->window_end || $progress->window_end->lt($now)) {
            $progress->resetWindow($start, $end);
        }
    }

    protected function calculateIncrement(LoyaltyChallenge $challenge, Order $order, LoyaltyChallengeProgress $progress): int
    {
        return match ($challenge->type) {
            LoyaltyChallengeType::WeeklyVisits => 1,
            LoyaltyChallengeType::NewVariant => $this->countNewVariantPurchases($challenge, $order, $progress),
            default => 0,
        };
    }

    protected function countNewVariantPurchases(LoyaltyChallenge $challenge, Order $order, LoyaltyChallengeProgress $progress): int
    {
        if (! $order->relationLoaded('items')) {
            $order->load('items');
        }

        $productIds = $order->items
            ->pluck('product_id')
            ->filter()
            ->unique();

        if ($productIds->isEmpty()) {
            return 0;
        }

        $customerId = $order->customer_id;

        $previouslyPurchased = OrderItem::query()
            ->whereIn('product_id', $productIds)
            ->whereHas('order', function ($query) use ($customerId, $order) {
                $query->where('customer_id', $customerId)
                    ->where('id', '!=', $order->id);
            })
            ->pluck('product_id')
            ->unique();

        $newProducts = $productIds->diff($previouslyPurchased);

        if ($newProducts->isEmpty()) {
            return 0;
        }

        $meta = $progress->meta ?? [];
        $meta['latest_new_products'] = $newProducts->values()->all();
        $progress->meta = $meta;

        $required = max((int) ($challenge->config['min_unique_count'] ?? 1), 1);
        $remaining = max($challenge->target_value - $progress->current_value, 1);

        return min($newProducts->count(), $required, $remaining);
    }

    protected function rewardProgress(LoyaltyChallengeProgress $progress, LoyaltyChallenge $challenge): void
    {
        if ($progress->rewarded_at) {
            return;
        }

        DB::transaction(function () use ($progress, $challenge) {
            $now = Carbon::now();
            $customer = $progress->customer()->lockForUpdate()->first();

            $points = (int) $challenge->bonus_points;

            if ($points > 0) {
                $customer->addPoints(
                    $points,
                    'challenge_bonus',
                    $challenge,
                    'Completed challenge: ' . $challenge->name,
                );
            }

            LoyaltyChallengeAward::create([
                'loyalty_challenge_id' => $challenge->id,
                'customer_id' => $customer->id,
                'points_awarded' => $points,
                'badge_name' => $challenge->badge_name,
                'badge_code' => $challenge->badge_code,
                'badge_color' => $challenge->badge_color,
                'badge_icon' => $challenge->badge_icon,
                'meta' => $progress->meta,
                'awarded_at' => $now,
            ]);

            $progress->rewarded_at = $now;
            $progress->save();
        });
    }
}
