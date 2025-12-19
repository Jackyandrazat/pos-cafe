<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Services\LoyaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reward_order_points_updates_customer_stats(): void
    {
        $customer = Customer::factory()->create();

        $order = Order::create([
            'user_id' => null,
            'customer_id' => $customer->id,
            'order_type' => 'dine_in',
            'status' => 'open',
            'subtotal_order' => 0,
            'discount_order' => 0,
            'promotion_discount' => 0,
            'service_fee_order' => 0,
            'total_order' => 150000,
        ]);

        $service = new LoyaltyService(pointsPerCurrency: 0.01);
        $service->rewardOrderPoints($order->fresh('customer'));

        $customer->refresh();

        $this->assertEquals(1500, $customer->points);
        $this->assertEquals(150000, (float) $customer->lifetime_value);
        $this->assertNotNull($customer->last_order_at);
        $this->assertDatabaseHas('customer_point_transactions', [
            'customer_id' => $customer->id,
            'source_id' => $order->id,
            'points' => 1500,
        ]);
    }
}
