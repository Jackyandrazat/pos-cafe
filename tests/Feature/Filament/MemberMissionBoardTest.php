<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\MemberMissionBoard;
use App\Http\Middleware\AdminAccessCodeMiddleware;
use App\Models\Customer;
use App\Models\LoyaltyChallenge;
use App\Models\LoyaltyChallengeProgress;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemberMissionBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_view_member_challenges(): void
    {
        $role = Role::create(['name' => 'kasir']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $customer = Customer::factory()->create(['points' => 320]);
        $challenge = LoyaltyChallenge::factory()->create([
            'name' => 'Weekly Regular',
            'target_value' => 3,
            'bonus_points' => 100,
        ]);

        LoyaltyChallengeProgress::create([
            'loyalty_challenge_id' => $challenge->id,
            'customer_id' => $customer->id,
            'current_value' => 2,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->withoutMiddleware(AdminAccessCodeMiddleware::class);
        $this->withSession([AdminAccessCodeMiddleware::SESSION_KEY => true]);
        $this->actingAs($user);

        Livewire::test(MemberMissionBoard::class)
            ->call('loadMemberData', $customer->id)
            ->assertSet('selectedMember.name', $customer->name)
            ->assertSet('challengeCards', function ($cards) use ($challenge) {
                return collect($cards)->pluck('name')->contains($challenge->name);
            });
    }
}
