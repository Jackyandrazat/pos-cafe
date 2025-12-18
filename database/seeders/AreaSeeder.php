<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            [
                'name' => 'Indoor Main Hall',
                'description' => 'Ruang utama dengan AC dan akses langsung ke barista station.',
                'status_enabled' => true,
            ],
            [
                'name' => 'Outdoor Garden',
                'description' => 'Area semi outdoor dengan banyak tanaman dan stopkontak.',
                'status_enabled' => true,
            ],
            [
                'name' => 'Rooftop Lounge',
                'description' => 'Area premium dengan panorama kota untuk acara privat.',
                'status_enabled' => true,
            ],
        ];

        foreach ($areas as $area) {
            Area::query()->updateOrCreate(
                ['name' => $area['name']],
                [
                    'description' => $area['description'],
                    'status_enabled' => $area['status_enabled'],
                ]
            );
        }
    }
}
