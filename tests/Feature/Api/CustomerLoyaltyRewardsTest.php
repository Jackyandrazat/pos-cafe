<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\LoyaltyChallenge;
use App\Models\LoyaltyChallengeAward;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerLoyaltyRewardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_dynamic_rewards(): void
    {
        $role = Role::create(['name' => 'kasir', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->roles()->attach($role);
        Sanctum::actingAs($user);

        $customer = Customer::factory()->create();

        $challenge = LoyaltyChallenge::factory()->create([
            'name' => 'Weekly Visits',
            'description' => 'Test weekly visit',
        ]);

        LoyaltyChallengeAward::create([
            'loyalty_challenge_id' => $challenge->id,
            'customer_id' => $customer->id,
            'points_awarded' => 150,
            'badge_name' => 'Regular',
            'badge_code' => 'badge_loyal_regular',
            'badge_color' => '#FFF',
            'badge_icon' => 'mdi:calendar-check',
            'awarded_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/customers/{$customer->id}/loyalty/rewards");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Regular')
            ->assertJsonPath('data.0.description', 'Test weekly visit')
            ->assertJsonPath('data.0.points_required', 0)
            ->assertJsonPath('data.0.icon', '🏆') // converted from badge_loyal_regular via iconMap
            ->assertJsonPath('data.0.is_available', true);
    }
}
