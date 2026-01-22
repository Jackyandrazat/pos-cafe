<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\LoyaltyChallenge;
use App\Models\LoyaltyChallengeProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyChallengeProgress>
 */
class LoyaltyChallengeProgressFactory extends Factory
{
    protected $model = LoyaltyChallengeProgress::class;

    public function definition(): array
    {
        return [
            'loyalty_challenge_id' => LoyaltyChallenge::factory(),
            'customer_id' => Customer::factory(),
            'current_value' => 0,
            'window_start' => now()->startOfWeek(),
            'window_end' => now()->endOfWeek(),
        };
    }
}
