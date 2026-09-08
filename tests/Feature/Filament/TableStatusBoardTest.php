<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\TableStatusBoard;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Models\Area;
use App\Models\CafeTable;
use App\Models\Role;
use App\Models\TableQueueEntry;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TableStatusBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_status_board_renders_and_executes_pos_actions(): void
    {
        $role = Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $area = Area::create(['name' => 'Main Dining', 'status_enabled' => true]);
        $table1 = CafeTable::create([
            'table_number' => 'T1',
            'area_id' => $area->id,
            'capacity' => 4,
            'status' => 'available',
        ]);
        $table2 = CafeTable::create([
            'table_number' => 'T2',
            'area_id' => $area->id,
            'capacity' => 2,
            'status' => 'cleaning',
        ]);

        $queue = TableQueueEntry::create([
            'guest_name' => 'Bpk. Hendra',
            'party_size' => 4,
            'status' => 'waiting',
            'check_in_at' => now()->subMinutes(10),
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);

        Livewire::test(TableStatusBoard::class)
            ->assertSee('T1')
            ->assertSee('T2')
            ->assertSee('Bpk. Hendra')
            // Test 1-click status filter
            ->call('setStatusFilter', 'cleaning')
            ->assertSet('statusFilter', 'cleaning')
            // Test area filter
            ->call('selectArea', $area->id)
            ->assertSet('selectedArea', $area->id)
            // Test clean table 1-tap action
            ->call('setStatus', $table2->id, 'available');

        $this->assertEquals('available', $table2->fresh()->status);

        // Test seat queue entry
        Livewire::test(TableStatusBoard::class)
            ->set('assignments.' . $queue->id, $table1->id)
            ->call('seatQueueEntry', $queue->id);

        $this->assertEquals('seated', $queue->fresh()->status);
        $this->assertEquals('occupied', $table1->fresh()->status);
    }

    public function test_order_create_page_preselects_table_and_customer_name_from_query_parameter(): void
    {
        $role = Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $area = Area::create(['name' => 'VIP Room', 'status_enabled' => true]);
        $table = CafeTable::create([
            'table_number' => 'V1',
            'area_id' => $area->id,
            'capacity' => 6,
            'status' => 'available',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);

        Livewire::withQueryParams([
            'table_id' => $table->id,
            'customer_name' => 'Pak Joko',
        ])
            ->test(CreateOrder::class)
            ->assertFormSet([
                'table_id' => $table->id,
                'order_type' => 'dine_in',
                'customer_name' => 'Pak Joko',
            ]);
    }

    public function test_table_status_board_displays_and_dismisses_waiter_call(): void
    {
        $role = Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $area = Area::create(['name' => 'Patio', 'status_enabled' => true]);
        $table = CafeTable::create([
            'table_number' => 'P1',
            'area_id' => $area->id,
            'capacity' => 4,
            'status' => 'occupied',
            'calling_waiter' => true,
            'waiter_call_reason' => 'minta_bill',
            'waiter_called_at' => now(),
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);

        Livewire::test(TableStatusBoard::class)
            ->assertSee('Panggil Pelayan!')
            ->assertSee('Minta Bill / Tagihan')
            ->call('dismissWaiterCall', $table->id);

        $this->assertFalse($table->fresh()->calling_waiter);
        $this->assertNull($table->fresh()->waiter_call_reason);
    }

    public function test_occupied_table_kasir_button_links_to_payment_create(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $area = Area::create(['name' => 'Indoor', 'status_enabled' => true]);
        $table = CafeTable::create([
            'table_number' => 'A01',
            'area_id' => $area->id,
            'capacity' => 2,
            'status' => 'occupied',
        ]);

        $order = \App\Models\Order::create([
            'table_id' => $table->id,
            'order_type' => 'dine_in',
            'customer_name' => 'Doni',
            'status' => 'pending',
            'subtotal_order' => 35000,
            'total_order' => 35000,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);

        // Verify TableStatusBoard renders link to payment create with order_id
        $expectedUrl = \App\Filament\Resources\PaymentResource::getUrl('create', ['order_id' => $order->id]);

        Livewire::test(TableStatusBoard::class)
            ->assertSee($expectedUrl, false)
            ->assertSee('Kasir #' . $order->id);

        // Verify CreatePayment preselects order and amount from query param
        Livewire::withQueryParams(['order_id' => $order->id])
            ->test(\App\Filament\Resources\PaymentResource\Pages\CreatePayment::class)
            ->assertFormSet([
                'order_id' => $order->id,
                'amount_paid' => 35000,
                'payment_method' => 'cash',
            ]);
    }
}
