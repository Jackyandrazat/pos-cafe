<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Coffee', 'description' => 'Signature espresso based drinks dan manual brew.'],
            ['name' => 'Tea & Refreshers', 'description' => 'Pilihan teh premium, mocktail, dan minuman segar.'],
            ['name' => 'Pastry', 'description' => 'Croissant, puff pastry, dan kue artisan harian.'],
            ['name' => 'Main Course', 'description' => 'Menu berat untuk brunch hingga dinner ringan.'],
            ['name' => 'Dessert', 'description' => 'Dessert manis dan plated cake untuk penutup.'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'status_enabled' => true,
                ]
            );
        }
    }
}
