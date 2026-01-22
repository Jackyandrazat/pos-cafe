<?php

namespace Database\Factories;

use App\Enums\LoyaltyChallengeType;
use App\Models\LoyaltyChallenge;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LoyaltyChallenge>
 */
class LoyaltyChallengeFactory extends Factory
{
    protected $model = LoyaltyChallenge::class;

    public function definition(): array
    {
        $name = 'Challenge ' . $this->faker->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(),
            'type' => LoyaltyChallengeType::WeeklyVisits,
            'description' => $this->faker->sentence(),
            'target_value' => 5,
            'bonus_points' => 50,
            'reset_period' => 'weekly',
            'badge_name' => 'Test Badge',
            'badge_code' => 'badge_test',
            'badge_color' => '#000000',
            'badge_icon' => 'mdi:star',
            'config' => ['window' => 'weekly'],
            'is_active' => true,
        ];
    }
}
