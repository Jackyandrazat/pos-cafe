<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GuestLoyaltyAndPromoRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_user_cannot_validate_promotion(): void
    {
        $guest = User::factory()->create(['is_guest' => true]);
        Sanctum::actingAs($guest);

        $promo = Promotion::factory()->create([
            'code' => 'HEMAT10',
            'discount_value' => 10,
            'type' => 'percentage',
            'min_subtotal' => 10000,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/promotions/validate', [
            'code' => 'HEMAT10',
            'subtotal' => 20000,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Promo dan voucher hanya berlaku untuk akun Member terdaftar. Silakan masuk sebagai Member.');
    }

    public function test_member_user_can_validate_promotion(): void
    {
        $customer = Customer::factory()->create();
        $member = User::factory()->create([
            'is_guest' => false,
            'customer_id' => $customer->id,
        ]);
        Sanctum::actingAs($member);

        $promo = Promotion::factory()->create([
            'code' => 'MEMBER15',
            'discount_value' => 15,
            'type' => 'percentage',
            'min_subtotal' => 10000,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/promotions/validate', [
            'code' => 'MEMBER15',
            'subtotal' => 50000,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.code', 'MEMBER15')
            ->assertJsonPath('data.discount_amount', 7500);
    }

    public function test_guest_user_cannot_access_loyalty_endpoints(): void
    {
        $guest = User::factory()->create(['is_guest' => true]);
        Sanctum::actingAs($guest);

        $customer = Customer::factory()->create();

        $this->getJson("/api/v1/customers/{$customer->id}/loyalty/summary")
            ->assertStatus(403);

        $this->getJson("/api/v1/customers/{$customer->id}/loyalty/challenges")
            ->assertStatus(403);

        $this->getJson("/api/v1/customers/{$customer->id}/loyalty/transactions")
            ->assertStatus(403);

        $this->getJson("/api/v1/customers/{$customer->id}/loyalty/rewards")
            ->assertStatus(403);
    }

    public function test_member_user_can_access_own_loyalty_summary(): void
    {
        $customer = Customer::factory()->create(['points' => 100]);
        $member = User::factory()->create([
            'is_guest' => false,
            'customer_id' => $customer->id,
        ]);
        Sanctum::actingAs($member);

        $response = $this->getJson("/api/v1/customers/{$customer->id}/loyalty/summary");
        $response->assertOk()
            ->assertJsonPath('data.customer.id', (string) $customer->id)
            ->assertJsonPath('data.points.balance', 100);
    }
}
