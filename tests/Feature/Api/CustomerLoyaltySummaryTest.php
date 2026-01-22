<?php

namespace Tests\Feature\Api;

use App\Enums\LoyaltyChallengeType;
use App\Models\Customer;
use App\Models\LoyaltyChallenge;
use App\Models\LoyaltyChallengeAward;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerLoyaltySummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_member_loyalty_summary(): void
    {
        $role = Role::create(['name' => 'kasir', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->roles()->attach($role);
        Sanctum::actingAs($user);

        $customer = Customer::factory()->create(['points' => 220]);

        $weeklyChallenge = LoyaltyChallenge::factory()->create([
            'name' => 'Weekly Visits',
            'slug' => 'weekly-visits-test',
            'type' => LoyaltyChallengeType::WeeklyVisits,
        ]);

        $weeklyChallenge->progresses()->create([
            'customer_id' => $customer->id,
            'current_value' => 3,
            'window_start' => now()->startOfWeek(),
            'window_end' => now()->endOfWeek(),
        ]);

        LoyaltyChallengeAward::create([
            'loyalty_challenge_id' => $weeklyChallenge->id,
            'customer_id' => $customer->id,
            'points_awarded' => 120,
            'badge_name' => 'Explorer',
            'badge_code' => 'badge_explorer',
            'badge_color' => '#000',
            'badge_icon' => 'mdi:star',
            'awarded_at' => now(),
        ]);

        $secondChallenge = LoyaltyChallenge::factory()->create([
            'name' => 'New Variant',
            'slug' => 'new-variant-demo',
            'type' => LoyaltyChallengeType::NewVariant,
        ]);

        $response = $this->getJson("/api/v1/customers/{$customer->id}/loyalty/summary");

        $response->assertOk()
            ->assertJsonPath('data.customer.id', (string) $customer->id)
            ->assertJsonPath('data.points.balance', 220)
            ->assertJsonPath('data.recent_badges.0.badge_name', 'Explorer');

        $challengeSlugs = collect($response->json('data.challenges'))->pluck('slug');
        $this->assertTrue($challengeSlugs->contains('new-variant-demo'));
        $this->assertTrue($challengeSlugs->contains('weekly-visits-test'));

        $weeklyChallengeData = collect($response->json('data.challenges'))
            ->firstWhere('slug', 'weekly-visits-test');

        $this->assertEquals(3, $weeklyChallengeData['progress']['current']);
    }
}
