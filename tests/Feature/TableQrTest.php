<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\CafeTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableQrTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected CafeTable $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();

        $area = Area::create(['name' => 'Indoor Main']);
        $this->table = CafeTable::create([
            'area_id'      => $area->id,
            'table_number' => 'T01',
            'status'       => 'available',
            'capacity'     => 4,
        ]);
    }

    public function test_can_render_single_table_qr_print_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('tables.qr.print', $this->table));

        $response->assertStatus(200);
        $response->assertSee('T01');
        $response->assertSee('Indoor Main');
        $response->assertSee('<svg', false);
    }

    public function test_can_render_bulk_table_qr_print_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('tables.qr.print-all'));

        $response->assertStatus(200);
        $response->assertSee('T01');
        $response->assertSee('<svg', false);
    }

    public function test_can_download_table_qr_svg(): void
    {
        $response = $this->actingAs($this->admin)->get(route('tables.qr.download', $this->table));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $response->assertSee('<svg', false);
    }

    public function test_can_get_table_info_via_api(): void
    {
        $response = $this->getJson('/api/v1/tables/T01');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'table_number' => 'T01',
                    'area_name'    => 'Indoor Main',
                    'capacity'     => 4,
                ],
            ]);
    }
}
