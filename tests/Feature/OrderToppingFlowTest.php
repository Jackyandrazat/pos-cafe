<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Topping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderToppingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_items_persist_optional_toppings_and_totals(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Coffee',
            'description' => 'Hot and cold drinks',
            'status_enabled' => true,
        ]);

        $product = $category->products()->create([
            'name' => 'Latte',
            'sku' => Str::uuid()->toString(),
            'price' => 15000,
            'cost_price' => 8000,
            'stock_qty' => 50,
            'description' => 'Classic latte',
            'status_enabled' => true,
        ]);

        $topping = Topping::create([
            'name' => 'Extra Shot',
            'price' => 5000,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_type' => 'dine_in',
            'customer_name' => 'QA Guest',
            'status' => 'open',
            'subtotal_order' => 0,
            'discount_order' => 0,
            'service_fee_order' => 0,
            'total_order' => 0,
        ]);

        $item = $order->order_items()->create([
            'product_id' => $product->id,
            'qty' => 2,
            'price' => $product->price,
            'discount_amount' => 1000,
            'subtotal' => 39000, // (15000 * 2) + (5000 * 2) - 1000
        ]);

        $item->toppings()->create([
            'topping_id' => $topping->id,
            'name' => $topping->name,
            'price' => $topping->price,
            'quantity' => 2,
            'total' => 10000,
        ]);

        $order->recalculateTotals();
        $order->refresh();

        $this->assertEquals(39000, (float) $order->subtotal_order);
        $this->assertEquals(39000, (float) $order->total_order);
        $this->assertCount(1, $order->order_items);
        $this->assertCount(1, $order->order_items->first()->toppings);
        $this->assertEquals('Extra Shot', $order->order_items->first()->toppings->first()->name);
    }
}
