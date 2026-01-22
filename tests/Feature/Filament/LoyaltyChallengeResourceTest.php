<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\LoyaltyChallengeResource;
use App\Filament\Resources\LoyaltyChallengeResource\Pages\ListLoyaltyChallenges;
use App\Http\Middleware\AdminAccessCodeMiddleware;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoyaltyChallengeResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_loyalty_challenge_resource(): void
    {
        $role = Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->withoutMiddleware(AdminAccessCodeMiddleware::class);
        $this->withSession([AdminAccessCodeMiddleware::SESSION_KEY => true]);
        $this->actingAs($user);

        Livewire::test(ListLoyaltyChallenges::class)
            ->assertStatus(200);
    }
}
