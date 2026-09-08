<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ThermalReceiptPrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_thermal_receipt(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Coffee',
            'description' => 'Coffee Drinks',
            'status_enabled' => true,
        ]);

        $product = $category->products()->create([
            'name' => 'Cappuccino',
            'sku' => Str::uuid()->toString(),
            'price' => 35000,
            'cost_price' => 15000,
            'stock_qty' => 50,
            'description' => 'Creamy coffee',
            'status_enabled' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_type' => 'dine_in',
            'customer_name' => 'Budi Santoso',
            'status' => 'confirmed',
            'subtotal_order' => 35000,
            'discount_order' => 0,
            'service_fee_order' => 0,
            'total_order' => 35000,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 35000,
            'discount_amount' => 0,
            'subtotal' => 35000,
        ]);

        $response = $this->actingAs($user)->get(route('orders.print.customer', ['order' => $order]));
        $response->assertStatus(200);
        $response->assertSee('KAFE DIGITAL POS');
        $response->assertSee('Cappuccino');
        $response->assertSee('Budi Santoso');

        $kitchenResponse = $this->actingAs($user)->get(route('orders.print.kitchen', ['order' => $order]));
        $kitchenResponse->assertStatus(200);
        $kitchenResponse->assertSee('TIKET DAPUR');
        $kitchenResponse->assertSee('Cappuccino');
    }

    public function test_guest_cannot_view_thermal_receipt(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_type' => 'take_away',
            'customer_name' => 'Test Customer',
            'status' => 'confirmed',
            'subtotal_order' => 20000,
            'discount_order' => 0,
            'service_fee_order' => 0,
            'total_order' => 20000,
        ]);

        $response = $this->get(route('orders.print.customer', ['order' => $order]));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_thermal_receipt_by_payment(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Pastry',
            'description' => 'Bakery and pastry',
            'status_enabled' => true,
        ]);

        $product = $category->products()->create([
            'name' => 'Croissant Butter',
            'sku' => Str::uuid()->toString(),
            'price' => 25000,
            'cost_price' => 10000,
            'stock_qty' => 30,
            'description' => 'Fresh butter croissant',
            'status_enabled' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_type' => 'dine_in',
            'customer_name' => 'Siti Nurhaliza',
            'status' => 'completed',
            'subtotal_order' => 25000,
            'discount_order' => 0,
            'service_fee_order' => 0,
            'total_order' => 25000,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 25000,
            'discount_amount' => 0,
            'subtotal' => 25000,
        ]);

        $payment = \App\Models\Payment::create([
            'order_id' => $order->id,
            'payment_date' => now(),
            'amount_paid' => 50000,
            'change_return' => 25000,
            'payment_method' => 'cash',
            'status' => 'captured',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('payments.print', ['payment' => $payment]));
        $response->assertStatus(200);
        $response->assertSee('KAFE DIGITAL POS');
        $response->assertSee('Croissant Butter');
        $response->assertSee('Siti Nurhaliza');
    }

    public function test_guest_cannot_view_thermal_receipt_by_payment(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_type' => 'take_away',
            'customer_name' => 'Guest Check',
            'status' => 'completed',
            'subtotal_order' => 15000,
            'discount_order' => 0,
            'service_fee_order' => 0,
            'total_order' => 15000,
        ]);

        $payment = \App\Models\Payment::create([
            'order_id' => $order->id,
            'payment_date' => now(),
            'amount_paid' => 15000,
            'change_return' => 0,
            'payment_method' => 'cash',
            'status' => 'captured',
        ]);

        $response = $this->get(route('payments.print', ['payment' => $payment]));
        $response->assertRedirect(route('login'));
    }
}
