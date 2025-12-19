<?php

namespace Database\Factories;

use App\Models\GiftCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\GiftCard> */
class GiftCardFactory extends Factory
{
    protected $model = GiftCard::class;

    public function definition(): array
    {
        $value = $this->faker->randomElement([100000, 250000, 500000]);

        return [
            'code' => strtoupper($this->faker->bothify('GC####')),
            'type' => $this->faker->randomElement(['gift_card', 'corporate']),
            'status' => GiftCard::STATUS_ACTIVE,
            'initial_value' => $value,
            'balance' => $value,
            'currency' => 'IDR',
            'issued_to_name' => $this->faker->name(),
            'issued_to_email' => $this->faker->safeEmail(),
            'company_name' => $this->faker->optional()->company(),
            'notes' => $this->faker->optional()->sentence(),
            'activated_at' => now(),
        ];
    }

    public function exhausted(): self
    {
        return $this->state(fn () => [
            'balance' => 0,
            'status' => GiftCard::STATUS_EXHAUSTED,
        ]);
    }

    public function expired(): self
    {
        return $this->state(fn () => [
            'expires_at' => now()->subDay(),
            'status' => GiftCard::STATUS_EXPIRED,
        ]);
    }
}
