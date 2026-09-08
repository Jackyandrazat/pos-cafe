<?php

namespace Tests\Feature\Filament;

use App\Livewire\OrderItemBuilder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Topping;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class OrderItemBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_order_item_builder_renders_and_adds_products(): void
    {
        $category = Category::create([
            'name' => 'Coffee',
            'status_enabled' => true,
        ]);

        $product = $category->products()->create([
            'name' => 'Espresso',
            'sku' => Str::uuid()->toString(),
            'price' => 20000,
            'cost_price' => 10000,
            'stock_qty' => 50,
            'status_enabled' => true,
        ]);

        $topping = Topping::create([
            'name' => 'Extra Shot',
            'price' => 5000,
            'is_active' => true,
        ]);
        $product->toppings()->attach($topping->id);

        $test = Livewire::test(OrderItemBuilder::class)
            ->assertSuccessful()
            ->assertSee('Katalog Menu')
            ->assertSee('Espresso')
            ->assertSee('Rp20.000');

        // Klik produk dengan opsi topping -> membuka modal
        $test->call('clickProduct', $product->id)
            ->assertSet('showCustomModal', true)
            ->assertSet('modalProductId', $product->id);

        // Pilih topping dan tambahkan
        $test->call('toggleModalTopping', $topping->id)
            ->call('addCustomizedItem')
            ->assertSet('showCustomModal', false);

        // Verifikasi item masuk ke keranjang
        $this->assertCount(1, session('selected_order_items', []));
        $item = session('selected_order_items')[0];
        $this->assertEquals($product->id, $item['product_id']);
        $this->assertEquals(25000, $item['subtotal']); // 20000 + 5000

        // Test stepper increment & decrement
        $test->call('incrementQty', 0);
        $updatedItem = session('selected_order_items')[0];
        $this->assertEquals(2, $updatedItem['qty']);
        $this->assertEquals(50000, $updatedItem['subtotal']);

        $test->call('decrementQty', 0);
        $updatedItem = session('selected_order_items')[0];
        $this->assertEquals(1, $updatedItem['qty']);

        // Test dialog reset/clear keranjang konfirmasi
        $test->call('promptResetCart')
            ->assertSet('showResetConfirmModal', true);

        $test->call('cancelResetCart')
            ->assertSet('showResetConfirmModal', false);
        $this->assertCount(1, session('selected_order_items', []));

        $test->call('promptResetCart')
            ->call('confirmResetCart')
            ->assertSet('showResetConfirmModal', false);
        $this->assertCount(0, session('selected_order_items', []));
    }
}
