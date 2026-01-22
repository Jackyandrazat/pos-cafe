<?php

namespace Tests\Unit;

use App\Enums\LoyaltyChallengeType;
use App\Models\Category;
use App\Models\Customer;
use App\Models\LoyaltyChallenge;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\GamifiedLoyaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamifiedLoyaltyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_visit_challenge_awards_bonus_points(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create();
        $user->roles()->attach(Role::create(['name' => 'kasir', 'guard_name' => 'web']));

        $challenge = LoyaltyChallenge::factory()->create([
            'type' => LoyaltyChallengeType::WeeklyVisits,
            'target_value' => 2,
            'bonus_points' => 75,
            'reset_period' => 'weekly',
            'name' => 'Two Visits',
        ]);

        $service = app(GamifiedLoyaltyService::class);

        $orderA = $this->createOrderForCustomer($customer, $user);
        $service->trackOrderProgress($orderA->fresh(['customer', 'items']));

        $this->assertDatabaseHas('loyalty_challenge_progress', [
            'loyalty_challenge_id' => $challenge->id,
            'customer_id' => $customer->id,
            'current_value' => 1,
            'rewarded_at' => null,
        ]);

        $orderB = $this->createOrderForCustomer($customer, $user);
        $service->trackOrderProgress($orderB->fresh(['customer', 'items']));

        $progress = $challenge->progresses()->where('customer_id', $customer->id)->first();

        $this->assertEquals(2, $progress->current_value);
        $this->assertNotNull($progress->rewarded_at);

        $this->assertDatabaseHas('customer_point_transactions', [
            'customer_id' => $customer->id,
            'type' => 'challenge_bonus',
            'description' => 'Completed challenge: ' . $challenge->name,
        ]);

        $this->assertDatabaseHas('loyalty_challenge_awards', [
            'customer_id' => $customer->id,
            'loyalty_challenge_id' => $challenge->id,
            'points_awarded' => 75,
        ]);
    }

    public function test_new_variant_challenge_requires_unique_product(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create();
        $user->roles()->attach(Role::create(['name' => 'kasir', 'guard_name' => 'web']));

        $product = $this->createProduct(['price' => 12000]);

        $challenge = LoyaltyChallenge::factory()->create([
            'type' => LoyaltyChallengeType::NewVariant,
            'target_value' => 1,
            'bonus_points' => 120,
            'reset_period' => 'none',
            'name' => 'First New Menu',
        ]);

        $service = app(GamifiedLoyaltyService::class);

        $order = $this->createOrderForCustomer($customer, $user, $product->id);
        $service->trackOrderProgress($order->fresh(['customer', 'items']));

        $progress = $challenge->progresses()->where('customer_id', $customer->id)->first();

        $this->assertNotNull($progress->rewarded_at);
        $this->assertEquals(1, $progress->current_value);

        $orderRepeat = $this->createOrderForCustomer($customer, $user, $product->id);
        $service->trackOrderProgress($orderRepeat->fresh(['customer', 'items']));

        $progress->refresh();
        $this->assertEquals(1, $progress->current_value, 'Progress should not increase for previously purchased products');
    }

    protected function createOrderForCustomer(Customer $customer, User $user, ?int $productId = null): Order
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

        $productId ??= $this->createProduct()->id;

        $order->items()->create([
            'product_id' => $productId,
            'qty' => 1,
            'price' => 25000,
            'discount_amount' => 0,
            'subtotal' => 25000,
        ]);

        return $order;
    }

    protected function createProduct(array $overrides = []): Product
    {
        $category = Category::create([
            'name' => 'Beverage',
            'description' => 'Test Category',
            'status_enabled' => true,
        ]);

        $defaults = [
            'category_id' => $category->id,
            'name' => 'Test Menu',
            'sku' => 'SKU-' . uniqid(),
            'price' => 25000,
            'cost_price' => 10000,
            'stock_qty' => 10,
            'description' => 'Auto generated product',
            'status_enabled' => true,
        ];

        return Product::create(array_merge($defaults, $overrides));
    }
}
