<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Customer;
use App\Models\GiftCard;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderStoreWithBenefitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_order_can_apply_promotion_and_gift_card(): void
    {
        $role = Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        Sanctum::actingAs($user);

        $category = Category::create([
            'name' => 'Coffee',
            'status_enabled' => true,
        ]);

        $product = $category->products()->create([
            'name' => 'Latte',
            'sku' => 'SKU-API',
            'price' => 10000,
            'cost_price' => 4000,
            'stock_qty' => 20,
            'status_enabled' => true,
        ]);

        $promotion = Promotion::create([
            'name' => 'Diskon 10%',
            'code' => 'PROMO10',
            'type' => 'percentage',
            'discount_value' => 10,
            'min_subtotal' => 0,
            'is_active' => true,
        ]);

        $giftCard = GiftCard::factory()->create([
            'code' => 'GIFTCARD',
            'balance' => 20000,
        ]);

        $customer = Customer::factory()->create();

        $payload = [
            'order_type' => 'take_away',
            'customer_id' => $customer->id,
            'customer_name' => 'Walk-in',
            'discount_order' => 0,
            'promotion_code' => $promotion->code,
            'gift_card_code' => $giftCard->code,
            'gift_card_amount' => 5000,
            'notes' => 'API test order',
            'items' => [
                [
                    'menu_id' => $product->id,
                    'quantity' => 2,
                    'discount_amount' => 0,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.promotion.code', 'PROMO10')
            ->assertJsonPath('data.gift_card.code', 'GIFTCARD')
            ->assertJsonPath('data.customer.id', (string) $customer->id)
            ->assertJsonPath('data.totals.grand_total', 13000);

        $this->assertDatabaseHas('orders', [
            'promotion_code' => 'PROMO10',
            'gift_card_code' => 'GIFTCARD',
            'customer_id' => $customer->id,
            'total_order' => 13000,
        ]);

        $this->assertDatabaseHas('promotion_usages', [
            'promotion_id' => $promotion->id,
        ]);

        $this->assertDatabaseHas('gift_card_transactions', [
            'gift_card_id' => $giftCard->id,
            'type' => 'redeem',
            'amount' => 5000,
        ]);
    }
}
