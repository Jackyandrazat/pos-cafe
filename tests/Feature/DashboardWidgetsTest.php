<?php

namespace Tests\Feature;

use App\Filament\Widgets\DailyTopOrdersChartWidget;
use App\Filament\Widgets\SalesChartWidget;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_chart_widget_returns_all_payments_for_admin_users(): void
    {
        Carbon::setTestNow('2025-12-17 10:00:00');

        $admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $admin->roles()->attach($adminRole->id);

        $order = $this->createOrderWithPayment($admin, 50000);

        Auth::login($admin);

        $widget = new class extends SalesChartWidget {
            public function exposedData(): array
            {
                return $this->getData();
            }
        };

        $data = $widget->exposedData();

        $labels = collect($data['labels'])->all();
        $totals = array_map('floatval', collect($data['datasets'][0]['data'])->all());

        $this->assertEquals([Carbon::today()->toDateString()], $labels);
        $this->assertEquals([50000.0], $totals);

        Auth::logout();
        Carbon::setTestNow();
    }

    public function test_sales_chart_widget_falls_back_to_global_totals_for_restricted_users(): void
    {
        Carbon::setTestNow('2025-12-17 10:00:00');

        $cashier = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->createOrderWithPayment($otherUser, 42000);

        Auth::login($cashier);

        $widget = new class extends SalesChartWidget {
            public function exposedData(): array
            {
                return $this->getData();
            }
        };

        $data = $widget->exposedData();

        $labels = collect($data['labels'])->all();
        $totals = array_map('floatval', collect($data['datasets'][0]['data'])->all());

        $this->assertEquals([Carbon::today()->toDateString()], $labels);
        $this->assertEquals([42000.0], $totals);

        Auth::logout();
        Carbon::setTestNow();
    }

    public function test_daily_top_orders_chart_widget_returns_top_products_for_today(): void
    {
        Carbon::setTestNow('2025-12-17 09:00:00');

        $user = User::factory()->create();
        $order = $this->createOrderWithPayment($user, 10000);

        $secondProduct = $order->order_items()->first()->product->replicate()->fill([
            'name' => 'Mocha',
            'sku' => Str::uuid()->toString(),
        ]);
        $secondProduct->save();

        $order->order_items()->create([
            'product_id' => $secondProduct->id,
            'qty' => 2,
            'price' => $secondProduct->price,
            'discount_amount' => 0,
            'subtotal' => $secondProduct->price * 2,
        ]);

        $order->order_items()->first()->update(['qty' => 5, 'subtotal' => $order->order_items()->first()->price * 5]);

        $widget = new class extends DailyTopOrdersChartWidget {
            public function exposedData(): array
            {
                return $this->getData();
            }
        };

        $data = $widget->exposedData();

        $labels = $data['labels'] instanceof Collection
            ? $data['labels']->values()->all()
            : (array) $data['labels'];

        $counts = $data['datasets'][0]['data'] instanceof Collection
            ? $data['datasets'][0]['data']->values()->all()
            : (array) $data['datasets'][0]['data'];

        $this->assertEquals(['Matcha', 'Mocha'], $labels);
        $this->assertEquals([5, 2], $counts);

        Carbon::setTestNow();
    }

    private function createOrderWithPayment(User $user, float $amount): Order
    {
        $category = Category::create([
            'name' => 'Tea',
            'description' => 'Tea menu',
            'status_enabled' => true,
        ]);

        $product = $category->products()->create([
            'name' => 'Matcha',
            'sku' => Str::uuid()->toString(),
            'price' => $amount,
            'cost_price' => $amount / 2,
            'stock_qty' => 20,
            'description' => 'Test product',
            'status_enabled' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_type' => 'take_away',
            'customer_name' => 'Widget QA',
            'status' => 'completed',
            'subtotal_order' => $amount,
            'discount_order' => 0,
            'service_fee_order' => 0,
            'total_order' => $amount,
        ]);

        $order->order_items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => $amount,
            'discount_amount' => 0,
            'subtotal' => $amount,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount_paid' => $amount,
            'change_return' => 0,
            'payment_date' => Carbon::now(),
        ]);

        return $order;
    }
}
