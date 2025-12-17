<?php

namespace Tests\Feature;

use App\Exceptions\StockValidationException;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\User;
use App\Services\StockValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StockValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_passes_when_stock_sufficient(): void
    {
        $order = $this->createOrderWithIngredients(ingredientStock: 10, quantityUsed: 2, orderQty: 3);

        $preparedOrder = $this->prepareOrder($order);

        StockValidationService::validateStockForOrder($preparedOrder);

        $this->assertTrue(true, 'Validation should pass when stock is sufficient.');
    }

    public function test_validation_fails_when_stock_insufficient(): void
    {
        $order = $this->createOrderWithIngredients(ingredientStock: 5, quantityUsed: 3, orderQty: 2);

        $preparedOrder = $this->prepareOrder($order);

        $this->expectException(StockValidationException::class);

        StockValidationService::validateStockForOrder($preparedOrder);
    }

    private function createOrderWithIngredients(int $ingredientStock, int $quantityUsed, int $orderQty): Order
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Kitchen',
            'description' => 'Kitchen menu',
            'status_enabled' => true,
        ]);

        $product = $category->products()->create([
            'name' => 'Signature Bowl',
            'sku' => Str::uuid()->toString(),
            'price' => 25000,
            'cost_price' => 10000,
            'stock_qty' => 20,
            'description' => 'Chef special',
            'status_enabled' => true,
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Rice',
            'stock_qty' => $ingredientStock,
            'unit' => 'gram',
            'price_per_unit' => 1000,
            'expired' => now()->addMonth(),
        ]);

        $product->ingredients()->create([
            'ingredient_id' => $ingredient->id,
            'quantity_used' => $quantityUsed,
            'unit' => 'gram',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_type' => 'dine_in',
            'customer_name' => 'Inventory QA',
            'status' => 'open',
            'subtotal_order' => 0,
            'discount_order' => 0,
            'service_fee_order' => 0,
            'total_order' => 0,
        ]);

        $order->order_items()->create([
            'product_id' => $product->id,
            'qty' => $orderQty,
            'price' => $product->price,
            'discount_amount' => 0,
            'subtotal' => $product->price * $orderQty,
        ]);

        return $order;
    }

    private function prepareOrder(Order $order): Order
    {
        $order->load(['order_items.product.ingredients.ingredient']);
        $order->setRelation('orderItems', $order->order_items);

        return $order;
    }
}
