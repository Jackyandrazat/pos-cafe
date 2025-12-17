<?php

namespace Tests\Feature;

use App\Filament\Resources\PaymentResource;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_whatsapp_link_contains_order_summary(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Beverage',
            'description' => 'Drinks menu',
            'status_enabled' => true,
        ]);

        $product = $category->products()->create([
            'name' => 'Caramel Latte',
            'sku' => Str::uuid()->toString(),
            'price' => 45000,
            'cost_price' => 25000,
            'stock_qty' => 30,
            'description' => 'Test drink',
            'status_enabled' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_type' => 'take_away',
            'customer_name' => 'Latte Lover',
            'status' => 'completed',
            'subtotal_order' => 45000,
            'discount_order' => 0,
            'service_fee_order' => 0,
            'total_order' => 45000,
        ]);

        $order->order_items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 45000,
            'discount_amount' => 0,
            'subtotal' => 45000,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount_paid' => 45000,
            'change_return' => 0,
            'payment_date' => now(),
        ]);

        $order->load('order_items.product');
        $order->setRelation('orderItems', $order->order_items);
        $payment->setRelation('order', $order);

        $link = PaymentResource::generateWhatsappLink($payment);

        $this->assertStringContainsString('*Struk Pembelian Cafe*', $link);
        $this->assertStringContainsString('Latte Lover', $link);
        $this->assertStringContainsString('Caramel Latte', $link);
        $this->assertStringContainsString('Rp45.000', $link);
    }
}
