<?php

namespace Tests\Feature;

use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderCreationWithPromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created_with_valid_promotion_code(): void
    {
        $role = Role::create(['name' => 'admin']);
        /** @var User $user */
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $category = Category::create([
            'name' => 'Coffee',
            'status_enabled' => true,
        ]);

        $product = $category->products()->create([
            'name' => 'Latte',
            'sku' => 'SKU-TEST',
            'price' => 15000,
            'cost_price' => 8000,
            'stock_qty' => 50,
            'status_enabled' => true,
        ]);

        Promotion::create([
            'name' => 'Diskon 10%',
            'code' => 'PROMO10',
            'type' => 'percentage',
            'discount_value' => 10,
            'min_subtotal' => 0,
            'is_active' => true,
        ]);

        session()->put('selected_order_items', [[
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'qty' => 1,
            'discount' => 0,
            'subtotal' => $product->price,
        ]]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs($user);

        Livewire::test(CreateOrder::class)
            ->set('data.order_type', 'dine_in')
            ->set('data.discount_order', 0)
            ->set('data.promotion_code', 'PROMO10')
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('orders', [
            'promotion_code' => 'PROMO10',
            'promotion_discount' => 1500,
        ]);
    }

}
