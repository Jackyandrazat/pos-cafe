<?php

namespace Tests\Feature\Filament;

use App\Enums\OrderStatus;
use App\Filament\Pages\KitchenDisplay;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KitchenDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_kitchen_display_can_update_order_status(): void
    {
        $role = Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $order = Order::factory()->create(['status' => OrderStatus::Pending->value]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);

        Livewire::test(KitchenDisplay::class)
            ->call('advanceStatus', $order->id, OrderStatus::Preparing->value);

        $this->assertEquals(OrderStatus::Preparing->value, $order->fresh()->status);
    }
}
