<?php

namespace Tests\Feature\Api;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Mode: manual (default)
    // Cash → langsung captured
    // -------------------------------------------------------------------------

    public function test_cash_payment_is_immediately_captured(): void
    {
        config(['payment.mode' => 'manual']);

        $user  = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'total_order' => 20000]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'payment_method' => 'cash',
            'amount'         => 25000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', PaymentStatus::Captured->value)
            ->assertJsonPath('data.provider', 'manual');

        $this->assertDatabaseHas('payments', [
            'order_id'       => $order->id,
            'payment_method' => 'cash',
            'status'         => PaymentStatus::Captured->value,
            'change_return'  => 5000,
        ]);
    }

    // -------------------------------------------------------------------------
    // Mode: manual — Digital payments → pending, kasir konfirmasi
    // -------------------------------------------------------------------------

    public function test_qris_manual_creates_pending_payment(): void
    {
        config(['payment.mode' => 'manual']);

        $user  = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'total_order' => 20000]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'payment_method' => 'qris',
            'amount'         => 20000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', PaymentStatus::Pending->value)
            ->assertJsonPath('data.provider', 'qris-manual');

        $this->assertDatabaseHas('payments', [
            'order_id'       => $order->id,
            'payment_method' => 'qris',
            'status'         => PaymentStatus::Pending->value,
        ]);
    }

    public function test_ewallet_manual_creates_pending_payment(): void
    {
        config(['payment.mode' => 'manual']);

        $user  = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'total_order' => 20000]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'payment_method'  => 'ewallet',
            'payment_channel' => 'gopay',
            'amount'          => 20000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', PaymentStatus::Pending->value)
            ->assertJsonPath('data.provider', 'ewallet-manual');

        $this->assertDatabaseHas('payments', [
            'order_id'        => $order->id,
            'payment_method'  => 'ewallet',
            'payment_channel' => 'gopay',
            'status'          => PaymentStatus::Pending->value,
        ]);
    }

    public function test_transfer_manual_creates_pending_payment(): void
    {
        config(['payment.mode' => 'manual']);

        $user  = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'total_order' => 20000]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'payment_method'  => 'transfer',
            'payment_channel' => 'bca',
            'amount'          => 20000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', PaymentStatus::Pending->value)
            ->assertJsonPath('data.provider', 'transfer-manual');

        $this->assertDatabaseHas('payments', [
            'order_id'        => $order->id,
            'payment_method'  => 'transfer',
            'payment_channel' => 'bca',
            'status'          => PaymentStatus::Pending->value,
        ]);
    }

    // -------------------------------------------------------------------------
    // Konfirmasi kasir (pending → captured)
    // -------------------------------------------------------------------------

    public function test_cashier_can_confirm_pending_payment(): void
    {
        config(['payment.mode' => 'manual']);

        $user  = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'total_order' => 20000]);

        // Buat pembayaran pending terlebih dahulu
        $payment = $order->payments()->create([
            'payment_method' => 'qris',
            'provider'       => 'qris-manual',
            'status'         => PaymentStatus::Pending->value,
            'amount_paid'    => 20000,
            'payment_date'   => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/payments/{$payment->id}/confirm");

        $response->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Captured->value);

        $this->assertDatabaseHas('payments', [
            'id'           => $payment->id,
            'status'       => PaymentStatus::Captured->value,
            'confirmed_by' => $user->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Mode: sandbox (untuk testing gateway integration)
    // -------------------------------------------------------------------------

    public function test_sandbox_qris_creates_pending_charge(): void
    {
        config(['payment.mode' => 'sandbox']);

        $user  = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'total_order' => 20000]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'payment_method' => 'qris',
            'amount'         => 10000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', PaymentStatus::Pending->value)
            ->assertJsonPath('data.provider', 'qris-sandbox');
    }

    // -------------------------------------------------------------------------
    // Validasi bisnis
    // -------------------------------------------------------------------------

    public function test_cannot_pay_cancelled_order(): void
    {
        config(['payment.mode' => 'manual']);

        $user  = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_order' => 20000,
            'status'  => 'cancelled',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'payment_method' => 'cash',
            'amount'         => 20000,
        ]);

        $response->assertUnprocessable();
    }

    public function test_digital_payment_amount_cannot_exceed_due(): void
    {
        config(['payment.mode' => 'manual']);

        $user  = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'total_order' => 20000]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'payment_method' => 'qris',
            'amount'         => 99999,  // lebih dari total order
        ]);

        $response->assertUnprocessable();
    }

    public function test_repeated_payment_submission_returns_existing_pending_payment_without_creating_duplicates(): void
    {
        config(['payment.mode' => 'manual']);

        $user  = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'total_order' => 20000]);

        Sanctum::actingAs($user);

        // First click/request
        $first = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'payment_method' => 'qris',
            'amount'         => 20000,
        ]);
        $first->assertCreated();
        $firstPaymentId = $first->json('data.id');

        // Repeated clicks/requests
        $second = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'payment_method' => 'qris',
            'amount'         => 20000,
        ]);
        $second->assertCreated();
        $secondPaymentId = $second->json('data.id');

        // Should return the exact same payment record
        $this->assertEquals($firstPaymentId, $secondPaymentId);

        // Database should only have 1 payment record
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_cancelling_pending_payment_deletes_record_and_allows_switching_method(): void
    {
        config(['payment.mode' => 'manual']);

        $user  = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'total_order' => 20000]);

        Sanctum::actingAs($user);

        // Create pending QRIS payment
        $response = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'payment_method' => 'qris',
            'amount'         => 20000,
        ]);
        $response->assertCreated();
        $this->assertDatabaseCount('payments', 1);

        // Cancel pending payment
        $del = $this->deleteJson("/api/v1/orders/{$order->id}/payments/pending");
        $del->assertOk()
            ->assertJsonPath('success', true);

        // Database should now have 0 payments
        $this->assertDatabaseCount('payments', 0);

        // Now user can create a different payment (e.g. transfer)
        $transfer = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'payment_method'  => 'transfer',
            'payment_channel' => 'bca',
            'amount'          => 20000,
        ]);
        $transfer->assertCreated()
            ->assertJsonPath('data.payment_method', 'transfer');

        $this->assertDatabaseCount('payments', 1);
    }
}
