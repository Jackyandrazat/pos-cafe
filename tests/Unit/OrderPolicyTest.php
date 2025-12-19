<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_orders(): void
    {
        $ownerRole = Role::create(['name' => 'owner']);
        $user = User::factory()->create();
        $user->roles()->attach($ownerRole);

        $policy = new OrderPolicy();

        $this->assertTrue($policy->create($user));
    }

    public function test_owner_can_update_orders(): void
    {
        $ownerRole = Role::create(['name' => 'owner']);
        $user = User::factory()->create();
        $user->roles()->attach($ownerRole);

        $order = Order::factory()->create();

        $policy = new OrderPolicy();

        $this->assertTrue($policy->update($user, $order));
    }
}
