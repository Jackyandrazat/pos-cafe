<?php

namespace Tests\Feature;

use App\Enums\LoyaltyChallengeType;
use App\Models\Category;
use App\Models\Customer;
use App\Models\LoyaltyChallenge;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyChallengeObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_completion_triggers_weekly_visit_progress_via_observer(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create();
        $user->roles()->attach(Role::create(['name' => 'kasir', 'guard_name' => 'web']));

        $challenge = LoyaltyChallenge::factory()->create([
            'type' => LoyaltyChallengeType::WeeklyVisits,
            'target_value' => 2,
            'bonus_points' => 100,
            'reset_period' => 'weekly',
            'name' => 'Weekly Coffee Fan',
            'is_active' => true,
        ]);

        // 1. Create first order as draft
        $orderA = $this->createOrderForCustomer($customer, $user);
        
        // Assert no progress yet (status is draft)
        $this->assertDatabaseMissing('loyalty_challenge_progress', [
            'loyalty_challenge_id' => $challenge->id,
            'customer_id' => $customer->id,
        ]);

        // 2. Mark first order as completed
        $orderA->status = 'completed';
        $orderA->save();

        // Assert progress is now 1
        $this->assertDatabaseHas('loyalty_challenge_progress', [
            'loyalty_challenge_id' => $challenge->id,
            'customer_id' => $customer->id,
            'current_value' => 1,
            'rewarded_at' => null,
        ]);

        // 3. Complete a second order
        $orderB = $this->createOrderForCustomer($customer, $user);
        $orderB->status = 'completed';
        $orderB->save();

        // Assert progress is now completed & rewarded
        $progress = $challenge->progresses()->where('customer_id', $customer->id)->first();
        $this->assertEquals(2, $progress->current_value);
        $this->assertNotNull($progress->completed_at);
        $this->assertNotNull($progress->rewarded_at);

        // Assert customer received bonus points
        $customer->refresh();
        $this->assertEquals(700, $customer->points);
    }

    protected function createOrderForCustomer(Customer $customer, User $user): Order
    {
        $order = Order::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'table_id' => null,
            'order_type' => 'dine_in',
            'customer_name' => $customer->name,
            'status' => 'draft',
            'subtotal_order' => 25000,
            'discount_order' => 0,
            'promotion_discount' => 0,
            'gift_card_amount' => 0,
            'service_fee_order' => 0,
            'total_order' => 25000,
        ]);

        $category = Category::create([
            'name' => 'Beverage',
            'description' => 'Test Category',
            'status_enabled' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cappuccino',
            'sku' => 'SKU-' . uniqid(),
            'price' => 25000,
            'cost_price' => 10000,
            'stock_qty' => 10,
            'description' => 'Test Cappuccino',
            'status_enabled' => true,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 25000,
            'discount_amount' => 0,
            'subtotal' => 25000,
        ]);

        return $order;
    }
}
