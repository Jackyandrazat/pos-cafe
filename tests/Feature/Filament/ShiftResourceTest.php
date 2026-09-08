<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\ShiftResource;
use App\Filament\Resources\ShiftResource\Pages\ListShifts;
use App\Models\Role;
use App\Models\Shift;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShiftResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_shift_table_renders_and_status_filter_works_properly(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        $kasir1 = User::factory()->create(['name' => 'Budi Kasir']);
        $kasir2 = User::factory()->create(['name' => 'Siti Kasir']);

        // Shift 1: Open (shift_close_time is null)
        $openShift = Shift::create([
            'user_id' => $kasir1->id,
            'shift_open_time' => now()->subHours(3),
            'shift_close_time' => null,
            'opening_balance' => 100000,
            'closing_balance' => null,
            'total_sales' => 50000,
        ]);

        // Shift 2: Closed (shift_close_time is set)
        $closedShift = Shift::create([
            'user_id' => $kasir2->id,
            'shift_open_time' => now()->subDay(),
            'shift_close_time' => now()->subDay()->addHours(8),
            'opening_balance' => 100000,
            'closing_balance' => 250000,
            'total_sales' => 150000,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($admin);

        // 1. Render shift table normally
        $component = Livewire::test(ListShifts::class)
            ->assertCanSeeTableRecords([$openShift, $closedShift]);

        // 2. Filter by status = 'open' -> should NOT throw SQL 1054 Unknown column 'status'
        $component->filterTable('status', 'open')
            ->assertCanSeeTableRecords([$openShift])
            ->assertCanNotSeeTableRecords([$closedShift]);

        // 3. Filter by status = 'closed' -> should show only closed shift
        $component->filterTable('status', 'closed')
            ->assertCanSeeTableRecords([$closedShift])
            ->assertCanNotSeeTableRecords([$openShift]);

        // 4. Test status helper methods on model
        $this->assertTrue($openShift->isOpen());
        $this->assertFalse($openShift->isClosed());
        $this->assertEquals('open', $openShift->status);

        $this->assertTrue($closedShift->isClosed());
        $this->assertFalse($closedShift->isOpen());
        $this->assertEquals('closed', $closedShift->status);
    }

    public function test_shift_cashier_filter_excludes_guests_and_customers(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        $staffUser = User::factory()->create(['name' => 'Staff Kasir', 'is_guest' => false, 'customer_id' => null]);
        $guestUser = User::factory()->create(['name' => 'Guest Pelanggan 1', 'is_guest' => true, 'customer_id' => null]);
        $customer = \App\Models\Customer::factory()->create();
        $customerUser = User::factory()->create(['name' => 'Member Customer 1', 'is_guest' => false, 'customer_id' => $customer->id]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($admin);

        // Verify that guests and customer users are strictly excluded from staff scope
        $staffIds = User::staff()->pluck('id')->all();
        $this->assertContains($staffUser->id, $staffIds);
        $this->assertNotContains($guestUser->id, $staffIds);
        $this->assertNotContains($customerUser->id, $staffIds);

        // Shift by staff user can be filtered properly
        $shift = Shift::create([
            'user_id' => $staffUser->id,
            'shift_open_time' => now(),
            'opening_balance' => 100000,
        ]);

        Livewire::test(ListShifts::class)
            ->filterTable('user_id', $staffUser->id)
            ->assertCanSeeTableRecords([$shift]);
    }
}
