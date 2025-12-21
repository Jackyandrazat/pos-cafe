<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_qris_payment_creates_pending_charge(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_order' => 20000,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'payment_method' => 'qris',
            'amount' => 10000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.provider', 'qris-sandbox');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);
    }
}
