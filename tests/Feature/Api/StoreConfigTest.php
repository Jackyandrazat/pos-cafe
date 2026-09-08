<?php

namespace Tests\Feature\Api;

use App\Helpers\SettingHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_store_config(): void
    {
        SettingHelper::set('receipt_cafe_name', 'Kafe Kopi KDAI');
        SettingHelper::set('self_order_allow_cash', '0');
        SettingHelper::set('cafe_latitude', '-6.2088');
        SettingHelper::set('cafe_longitude', '106.8456');
        SettingHelper::set('cafe_geofence_radius', '150');

        $response = $this->getJson('/api/v1/store-config');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'cafe_name' => 'Kafe Kopi KDAI',
                    'self_order_allow_cash' => false,
                    'geofence' => [
                        'enabled' => true,
                        'latitude' => -6.2088,
                        'longitude' => 106.8456,
                        'radius_meters' => 150,
                    ],
                ],
            ]);
    }

    public function test_default_store_config_when_no_coordinates(): void
    {
        SettingHelper::clear('cafe_latitude');
        SettingHelper::clear('cafe_longitude');

        $response = $this->getJson('/api/v1/store-config');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'self_order_allow_cash' => true,
                    'has_active_shift'      => false,
                    'geofence' => [
                        'enabled' => false,
                    ],
                ],
            ]);
    }

    public function test_store_config_reflects_active_shift_state(): void
    {
        $user = \App\Models\User::factory()->create();
        \App\Models\Shift::create([
            'user_id'         => $user->id,
            'shift_open_time' => now(),
            'opening_balance' => 50000,
        ]);

        $response = $this->getJson('/api/v1/store-config');

        $response->assertStatus(200)
            ->assertJsonPath('data.has_active_shift', true);
    }
}
