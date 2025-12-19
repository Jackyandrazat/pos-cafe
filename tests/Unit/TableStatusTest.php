<?php

namespace Tests\Unit;

use App\Models\Area;
use App\Models\CafeTable;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_status_updates_when_order_created_and_completed(): void
    {
        $area = Area::create([
            'name' => 'Indoor',
            'status_enabled' => true,
        ]);

        $table = CafeTable::create([
            'area_id' => $area->id,
            'table_number' => 'A1',
            'status' => 'available',
            'capacity' => 2,
        ]);

        $order = Order::create([
            'table_id' => $table->id,
            'order_type' => 'dine_in',
            'status' => 'open',
            'subtotal_order' => 0,
            'discount_order' => 0,
            'promotion_discount' => 0,
            'service_fee_order' => 0,
            'total_order' => 0,
        ]);

        $table->refresh();
        $this->assertEquals('occupied', $table->status);

        $order->update(['status' => 'completed']);
        $table->refresh();
        $this->assertEquals('cleaning', $table->status);
    }
}
