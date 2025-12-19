<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientWaste;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\Purchase;
use App\Models\User;
use App\Services\InventoryWasteReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryWasteReportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_aggregates_stock_usage_and_waste(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Bahan',
            'description' => null,
            'status_enabled' => true,
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Susu',
            'stock_qty' => 100,
            'unit' => 'ml',
            'price_per_unit' => 500,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Latte',
            'sku' => 'SKU-LATTE',
            'price' => 25000,
            'cost_price' => 10000,
            'stock_qty' => 10,
            'status_enabled' => true,
        ]);

        ProductIngredient::create([
            'product_id' => $product->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 20,
            'unit' => 'ml',
        ]);

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'invoice_number' => 'INV-1',
            'purchase_date' => '2025-12-09',
            'total_amount' => 100000,
        ]);

        $purchase->items()->create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 60,
            'price_per_unit' => 500,
            'unit' => 'ml',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_type' => 'dine_in',
            'status' => 'open',
            'subtotal_order' => 0,
            'discount_order' => 0,
            'service_fee_order' => 0,
            'total_order' => 0,
        ]);

        $order->forceFill([
            'created_at' => Carbon::parse('2025-12-10 09:00:00'),
        ])->save();

        $order->order_items()->create([
            'product_id' => $product->id,
            'qty' => 2,
            'price' => 25000,
            'discount_amount' => 0,
            'subtotal' => 50000,
        ]);

        IngredientWaste::create([
            'ingredient_id' => $ingredient->id,
            'user_id' => $user->id,
            'quantity' => 3,
            'unit' => 'ml',
            'reason' => 'spillage',
            'recorded_at' => Carbon::parse('2025-12-10 12:00:00'),
        ]);

        $report = InventoryWasteReportService::generate(
            Carbon::parse('2025-12-09 00:00:00'),
            Carbon::parse('2025-12-11 23:59:59')
        );

        $this->assertEquals(60, $report['summary']['total_stock_in']);
        $this->assertEquals(40, $report['summary']['total_usage']);
        $this->assertEquals(3, $report['summary']['total_waste']);

        $row = $report['rows']->first();
        $this->assertEquals('Susu', $row['name']);
        $this->assertEquals(60, $row['stock_in']);
        $this->assertEquals(40, $row['usage']);
        $this->assertEquals(3, $row['waste']);
        $this->assertEquals(500 * 3, $row['waste_cost']);
    }
}
