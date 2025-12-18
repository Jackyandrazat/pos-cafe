<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CashierShiftRequirementTest extends TestCase
{
    use RefreshDatabase;

    protected Role $kasirRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasirRole = Role::create(['name' => 'kasir']);
    }

    public function test_cashier_cannot_create_order_without_active_shift(): void
    {
        $user = $this->createKasirUser();
        $product = $this->createProduct();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/orders', [
            'order_type' => 'dine_in',
            'customer_name' => 'Tester',
            'items' => [
                [
                    'menu_id' => $product->id,
                    'quantity' => 1,
                    'discount_amount' => 0,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertStringContainsString('Kasir harus membuka shift', $response->json('message'));
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cashier_can_create_order_when_shift_is_active(): void
    {
        $user = $this->createKasirUser();
        $product = $this->createProduct();

        $this->openShift($user);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/orders', [
            'order_type' => 'dine_in',
            'customer_name' => 'Tester',
            'items' => [
                [
                    'menu_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_cashier_cannot_add_payment_without_active_shift(): void
    {
        $user = $this->createKasirUser();
        $order = $this->createOrderFor($user);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'payment_method' => 'cash',
            'amount' => 50000,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertStringContainsString('Kasir harus membuka shift', $response->json('message'));
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_cashier_payment_links_to_active_shift(): void
    {
        $user = $this->createKasirUser();
        $order = $this->createOrderFor($user);
        $shift = $this->openShift($user);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'payment_method' => 'cash',
            'amount' => 50000,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $payment = Payment::first();
        $this->assertNotNull($payment);
        $this->assertEquals($shift->id, $payment->shift_id);
        $order->refresh();
        $this->assertEquals(OrderStatus::Completed->value, $order->status);
    }

    protected function createKasirUser(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach($this->kasirRole);

        return $user;
    }

    protected function createProduct()
    {
        $category = Category::create([
            'name' => 'Coffee',
            'description' => 'Hot drinks',
            'status_enabled' => true,
        ]);

        return $category->products()->create([
            'name' => 'Latte',
            'sku' => Str::uuid()->toString(),
            'price' => 25000,
            'cost_price' => 10000,
            'stock_qty' => 50,
            'description' => 'Test product',
            'status_enabled' => true,
        ]);
    }

    protected function openShift(User $user): Shift
    {
        return Shift::create([
            'user_id' => $user->id,
            'shift_open_time' => now()->subHour(),
            'opening_balance' => 100000,
            'total_sales' => 0,
        ]);
    }

    protected function createOrderFor(User $user): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'order_type' => 'dine_in',
            'customer_name' => 'Tester',
            'status' => OrderStatus::Pending->value,
            'subtotal_order' => 50000,
            'discount_order' => 0,
            'service_fee_order' => 0,
            'total_order' => 50000,
        ]);
    }
}
