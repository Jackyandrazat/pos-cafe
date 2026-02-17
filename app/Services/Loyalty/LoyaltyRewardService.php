<?php

namespace App\Services\Loyalty;

use App\Models\LoyaltyChallengeProgress;
use App\Models\LoyaltyChallengeAward;
use App\Services\LoyaltyService;

class LoyaltyRewardService
{
    public function rewardIfEligible(LoyaltyChallengeProgress $progress): void
    {
        if (! $progress->completed_at || $progress->rewarded_at) return;

        $challenge = $progress->challenge;
        $customer = $progress->customer;

        // bonus points
        if ($challenge->bonus_points > 0) {
            app(LoyaltyService::class)->redeemPoints(
                $customer,
                -$challenge->bonus_points,
                'Bonus: ' . $challenge->name
            );
        }

        LoyaltyChallengeAward::create([
            'loyalty_challenge_id' => $challenge->id,
            'customer_id' => $customer->id,
            'points_awarded' => $challenge->bonus_points,
            'badge_name' => $challenge->badge_name,
            'badge_code' => $challenge->badge_code,
            'badge_color' => $challenge->badge_color,
            'badge_icon' => $challenge->badge_icon,
            'awarded_at' => now(),
        ]);

        $progress->rewarded_at = now();
        $progress->save();
    }
}
