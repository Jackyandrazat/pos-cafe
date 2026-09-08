<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\PaymentResource\Pages\CreatePayment;
use App\Models\Area;
use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Shift;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentQuickCashTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_create_cash_payment_with_quick_cash(): void
    {
        $role = Role::create(['name' => 'kasir']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        // Kasir harus punya active shift
        Shift::create([
            'user_id' => $user->id,
            'shift_open_time' => now()->subHour(),
            'opening_balance' => 100000,
            'total_sales' => 0,
        ]);

        $area = Area::create(['name' => 'Lantai 1', 'status_enabled' => true]);
        $table = CafeTable::create([
            'table_number' => '01',
            'area_id' => $area->id,
            'capacity' => 4,
            'status' => 'occupied',
        ]);

        $category = Category::create([
            'name' => 'Coffee',
            'description' => 'Coffee drinks',
            'status_enabled' => true,
        ]);

        $product = $category->products()->create([
            'name' => 'Cappuccino',
            'sku' => Str::uuid()->toString(),
            'price' => 35000,
            'cost_price' => 15000,
            'stock_qty' => 50,
            'status_enabled' => true,
        ]);

        $order = Order::create([
            'table_id' => $table->id,
            'order_type' => 'dine_in',
            'customer_name' => 'Mas Doni',
            'status' => 'submitted',
            'subtotal_order' => 35000,
            'total_order' => 35000,
        ]);

        $order->order_items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 35000,
            'subtotal' => 35000,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);

        // Kasir mengisi form pembayaran: pilih order, bayar tunai Rp 50.000 (kembalian 15.000)
        Livewire::test(CreatePayment::class)
            ->fillForm([
                'order_id' => $order->id,
                'payment_method' => 'cash',
                'amount_paid' => 50000,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount_paid' => 50000,
            'change_return' => 15000,
            'status' => 'captured',
        ]);
    }

    public function test_cashier_cannot_submit_cash_payment_with_insufficient_amount(): void
    {
        $role = Role::firstOrCreate(['name' => 'kasir']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        Shift::create([
            'user_id' => $user->id,
            'shift_open_time' => now()->subHour(),
            'opening_balance' => 100000,
            'total_sales' => 0,
        ]);

        $order = Order::create([
            'order_type' => 'takeaway',
            'customer_name' => 'Pak Rudi',
            'status' => 'submitted',
            'subtotal_order' => 50000,
            'total_order' => 50000,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);

        Livewire::test(CreatePayment::class)
            ->fillForm([
                'order_id' => $order->id,
                'payment_method' => 'cash',
                'amount_paid' => 30000, // Kurang 20.000!
            ])
            ->call('create')
            ->assertHasFormErrors(['amount_paid']);

        $this->assertDatabaseMissing('payments', [
            'order_id' => $order->id,
        ]);
    }

    public function test_cashier_quick_cash_rounding_and_reset_actions(): void
    {
        $role = Role::firstOrCreate(['name' => 'kasir']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        Shift::create([
            'user_id' => $user->id,
            'shift_open_time' => now()->subHour(),
            'opening_balance' => 100000,
            'total_sales' => 0,
        ]);

        $order = Order::create([
            'order_type' => 'dine_in',
            'customer_name' => 'Siti',
            'status' => 'submitted',
            'subtotal_order' => 68000,
            'total_order' => 68000,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);

        // Test bahwa memilih order_id otomatis mengisi amount_paid dengan total_order
        $component = Livewire::test(CreatePayment::class)
            ->set('data.order_id', $order->id);

        $this->assertEquals(68000, (float) $component->get('data.amount_paid'));

        // Jika diset ke kelipatan 100k
        $component->set('data.amount_paid', 100000)
            ->set('data.payment_method', 'cash')
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount_paid' => 100000,
            'change_return' => 32000,
            'status' => 'captured',
        ]);
    }
}
