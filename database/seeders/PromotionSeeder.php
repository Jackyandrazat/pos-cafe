<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promotions = [
            [
                'name' => 'Hemat Awal Bulan (20%)',
                'code' => 'PROMO20',
                'type' => 'percentage',
                'discount_value' => 20.00,
                'max_discount' => 25000.00,
                'min_subtotal' => 50000.00,
                'usage_limit' => 1000,
                'usage_limit_per_user' => 3,
                'starts_at' => now()->startOfMonth(),
                'ends_at' => now()->endOfMonth()->addMonths(2),
                'is_active' => true,
                'description' => 'Diskon 20% up to Rp 25.000 dengan minimal pembelian Rp 50.000.',
            ],
            [
                'name' => 'Promo Weekend Seru',
                'code' => 'WEEKEND10',
                'type' => 'percentage',
                'discount_value' => 10.00,
                'max_discount' => 50000.00,
                'min_subtotal' => 75000.00,
                'usage_limit' => 500,
                'usage_limit_per_user' => 1,
                'starts_at' => now()->subMonth(),
                'ends_at' => now()->addMonth(6),
                'is_active' => true,
                'schedule_days' => [6, 7], // Saturday and Sunday
                'description' => 'Diskon 10% up to Rp 50.000 khusus hari Sabtu & Minggu.',
            ],
            [
                'name' => 'Potongan Langsung Kopi (Rp 5.000)',
                'code' => 'COFFEE5K',
                'type' => 'fixed',
                'discount_value' => 5000.00,
                'max_discount' => null,
                'min_subtotal' => 25000.00,
                'usage_limit' => 200,
                'usage_limit_per_user' => 5,
                'starts_at' => now()->subWeeks(2),
                'ends_at' => now()->addWeeks(8),
                'is_active' => true,
                'description' => 'Potongan langsung Rp 5.000 dengan minimal transaksi Rp 25.000.',
            ],
            [
                'name' => 'Happy Hour Lunch',
                'code' => 'LUNCHTIME',
                'type' => 'fixed',
                'discount_value' => 10000.00,
                'max_discount' => null,
                'min_subtotal' => 40000.00,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'starts_at' => now()->subMonth(),
                'ends_at' => now()->addMonth(3),
                'is_active' => true,
                'schedule_days' => [1, 2, 3, 4, 5], // Monday to Friday
                'schedule_start_time' => '11:30:00',
                'schedule_end_time' => '14:00:00',
                'description' => 'Potongan langsung Rp 10.000 pada jam makan siang (Senin - Jumat, 11:30 - 14:00).',
            ],
            [
                'name' => 'Promo Grand Opening',
                'code' => 'GRANDOPENING',
                'type' => 'percentage',
                'discount_value' => 50.00,
                'max_discount' => 50000.00,
                'min_subtotal' => 10000.00,
                'usage_limit' => 50,
                'usage_limit_per_user' => 1,
                'starts_at' => now()->subMonths(3),
                'ends_at' => now()->subMonths(2), // Expired
                'is_active' => true,
                'description' => 'Diskon 50% khusus grand opening cafe (Sudah Berakhir).',
            ],
            [
                'name' => 'Diskon Anggota Baru',
                'code' => 'NEWPROMO15',
                'type' => 'percentage',
                'discount_value' => 15.00,
                'max_discount' => 30000.00,
                'min_subtotal' => 30000.00,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'starts_at' => now()->addDays(5), // Future promo
                'ends_at' => now()->addDays(25),
                'is_active' => true,
                'description' => 'Diskon 15% up to Rp 30.000 untuk member baru (Mulai dalam beberapa hari).',
            ]
        ];

        foreach ($promotions as $promo) {
            Promotion::query()->updateOrCreate(
                ['code' => $promo['code']],
                [
                    'name' => $promo['name'],
                    'type' => $promo['type'],
                    'discount_value' => $promo['discount_value'],
                    'max_discount' => $promo['max_discount'],
                    'min_subtotal' => $promo['min_subtotal'],
                    'usage_limit' => $promo['usage_limit'],
                    'usage_limit_per_user' => $promo['usage_limit_per_user'],
                    'starts_at' => $promo['starts_at'],
                    'ends_at' => $promo['ends_at'],
                    'is_active' => $promo['is_active'],
                    'schedule_days' => $promo['schedule_days'] ?? null,
                    'schedule_start_time' => $promo['schedule_start_time'] ?? null,
                    'schedule_end_time' => $promo['schedule_end_time'] ?? null,
                    'description' => $promo['description'],
                ]
            );
        }
    }
}
