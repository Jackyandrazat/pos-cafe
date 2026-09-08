<?php

namespace Tests\Feature\Api;

use App\Models\CafeTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableServiceCallTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_call_waiter_for_a_table(): void
    {
        $area = \App\Models\Area::create(['name' => 'Main Hall', 'status_enabled' => true]);
        $table = CafeTable::factory()->create([
            'area_id' => $area->id,
            'table_number' => '05',
            'status' => 'occupied',
            'calling_waiter' => false,
        ]);

        $response = $this->postJson("/api/v1/tables/{$table->table_number}/call-waiter", [
            'reason' => 'minta_bill',
            'notes' => 'Minta struk dan bill pembayaran',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.calling_waiter', true)
            ->assertJsonPath('data.reason', 'minta_bill')
            ->assertJsonPath('data.reason_label', 'Minta Bill / Tagihan');

        $this->assertDatabaseHas('tables', [
            'id' => $table->id,
            'calling_waiter' => true,
            'waiter_call_reason' => 'minta_bill',
        ]);
    }

    public function test_customer_can_check_call_status(): void
    {
        $area = \App\Models\Area::create(['name' => 'Main Hall', 'status_enabled' => true]);
        $table = CafeTable::factory()->create([
            'area_id' => $area->id,
            'table_number' => '07',
            'calling_waiter' => true,
            'waiter_call_reason' => 'air_es',
            'waiter_called_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/tables/{$table->table_number}/call-status");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.calling_waiter', true)
            ->assertJsonPath('data.reason_label', 'Minta Air / Es Batu');
    }

    public function test_customer_can_cancel_waiter_call(): void
    {
        $area = \App\Models\Area::create(['name' => 'Main Hall', 'status_enabled' => true]);
        $table = CafeTable::factory()->create([
            'area_id' => $area->id,
            'table_number' => '08',
            'calling_waiter' => true,
            'waiter_call_reason' => 'bantuan',
        ]);

        $response = $this->postJson("/api/v1/tables/{$table->table_number}/cancel-waiter");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.calling_waiter', false);

        $this->assertDatabaseHas('tables', [
            'id' => $table->id,
            'calling_waiter' => false,
        ]);
    }
}
