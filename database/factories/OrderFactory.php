<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_type' => $this->faker->randomElement(['dine_in', 'take_away', 'delivery']),
            'status' => 'open',
            'customer_name' => $this->faker->name(),
            'subtotal_order' => 100000,
            'discount_order' => 0,
            'promotion_discount' => 0,
            'gift_card_amount' => 0,
            'service_fee_order' => 0,
            'total_order' => 100000,
        ];
    }
}
