<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\CafeTable;
use Illuminate\Database\Seeder;

class CafeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = Area::query()->pluck('id', 'name');

        $tableConfigurations = [
            'Indoor Main Hall' => [
                ['table_number' => 'A01', 'status' => 'occupied'],
                ['table_number' => 'A02', 'status' => 'available'],
                ['table_number' => 'A03', 'status' => 'reserved'],
                ['table_number' => 'A04', 'status' => 'available'],
                ['table_number' => 'A05', 'status' => 'available'],
                ['table_number' => 'A06', 'status' => 'available'],
            ],
            'Outdoor Garden' => [
                ['table_number' => 'B01', 'status' => 'available'],
                ['table_number' => 'B02', 'status' => 'available'],
                ['table_number' => 'B03', 'status' => 'occupied'],
                ['table_number' => 'B04', 'status' => 'available'],
            ],
            'Rooftop Lounge' => [
                ['table_number' => 'C01', 'status' => 'reserved'],
                ['table_number' => 'C02', 'status' => 'available'],
                ['table_number' => 'C03', 'status' => 'available'],
            ],
        ];

        foreach ($tableConfigurations as $areaName => $tables) {
            $areaId = $areas[$areaName] ?? null;

            if (! $areaId) {
                continue;
            }

            foreach ($tables as $table) {
                CafeTable::query()->updateOrCreate(
                    ['table_number' => $table['table_number']],
                    [
                        'area_id' => $areaId,
                        'status' => $table['status'],
                    ]
                );
            }
        }
    }
}
