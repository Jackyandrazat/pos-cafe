<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->unique()->numerify('08##########'),
            'preferred_channel' => $this->faker->randomElement(['whatsapp', 'email', 'sms']),
            'preferences' => $this->faker->randomElements(['hot', 'less sugar', 'extra shot'], 2),
            'points' => 0,
            'lifetime_value' => 0,
        ];
    }
}
