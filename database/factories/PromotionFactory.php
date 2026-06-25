<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['percentage', 'fixed']);
        $discountValue = $type === 'percentage' 
            ? $this->faker->randomElement([5, 10, 15, 20, 25]) // percentage
            : $this->faker->randomElement([5000, 10000, 15000, 20000, 50000]); // fixed amount in IDR

        $minSubtotal = $this->faker->randomElement([0, 50000, 100000, 150000]);

        return [
            'name' => $this->faker->words(3, true) . ' Promo',
            'code' => strtoupper($this->faker->unique()->bothify('PROMO##??')),
            'type' => $type,
            'discount_value' => $discountValue,
            'max_discount' => $type === 'percentage' ? $this->faker->randomElement([15000, 25000, 50000, null]) : null,
            'min_subtotal' => $minSubtotal,
            'usage_limit' => $this->faker->randomElement([100, 200, 500, null]),
            'usage_limit_per_user' => $this->faker->randomElement([1, 2, 3, null]),
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
            'schedule_days' => null,
            'schedule_start_time' => null,
            'schedule_end_time' => null,
            'description' => $this->faker->sentence(),
        ];
    }

    public function percentage(): self
    {
        return $this->state(fn () => [
            'type' => 'percentage',
            'discount_value' => $this->faker->randomElement([10, 15, 20, 30]),
            'max_discount' => $this->faker->randomElement([20000, 50000]),
        ]);
    }

    public function fixed(): self
    {
        return $this->state(fn () => [
            'type' => 'fixed',
            'discount_value' => $this->faker->randomElement([5000, 10000, 20000]),
            'max_discount' => null,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    public function scheduled(): self
    {
        return $this->state(fn () => [
            'schedule_days' => [1, 2, 3, 4, 5], // Monday to Friday
            'schedule_start_time' => '14:00:00',
            'schedule_end_time' => '17:00:00',
        ]);
    }
}
