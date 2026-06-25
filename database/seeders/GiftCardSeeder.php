<?php

namespace Database\Seeders;

use App\Models\GiftCard;
use Illuminate\Database\Seeder;

class GiftCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cards = [
            // Standard Gift Cards
            [
                'code' => 'GC100A',
                'type' => 'gift_card',
                'status' => 'active',
                'initial_value' => 100000.00,
                'balance' => 100000.00,
                'currency' => 'IDR',
                'activated_at' => now(),
                'expires_at' => now()->addYear(),
                'issued_to_name' => 'Budi Sudarsono',
                'issued_to_email' => 'budi@example.com',
                'notes' => 'Gift card Rp 100.000 untuk ulang tahun.',
            ],
            [
                'code' => 'GC250B',
                'type' => 'gift_card',
                'status' => 'active',
                'initial_value' => 250000.00,
                'balance' => 250000.00,
                'currency' => 'IDR',
                'activated_at' => now(),
                'expires_at' => now()->addYear(),
                'issued_to_name' => 'Siti Aminah',
                'issued_to_email' => 'siti@example.com',
                'notes' => 'Gift card Rp 250.000 reward karyawan bulanan.',
            ],
            [
                'code' => 'GC500C',
                'type' => 'gift_card',
                'status' => 'active',
                'initial_value' => 500000.00,
                'balance' => 500000.00,
                'currency' => 'IDR',
                'activated_at' => now(),
                'expires_at' => now()->addYear(),
                'issued_to_name' => 'Dewi Lestari',
                'issued_to_email' => 'dewi@example.com',
                'notes' => 'Gift card VIP Rp 500.000.',
            ],
            [
                'code' => 'GC100X',
                'type' => 'gift_card',
                'status' => 'exhausted',
                'initial_value' => 100000.00,
                'balance' => 0.00,
                'currency' => 'IDR',
                'activated_at' => now()->subMonths(2),
                'expires_at' => now()->addMonths(10),
                'last_used_at' => now()->subDays(5),
                'issued_to_name' => 'Joko Widodo',
                'issued_to_email' => 'joko@example.com',
                'notes' => 'Gift card sudah terpakai habis.',
            ],
            [
                'code' => 'GC100E',
                'type' => 'gift_card',
                'status' => 'expired',
                'initial_value' => 100000.00,
                'balance' => 100000.00,
                'currency' => 'IDR',
                'activated_at' => now()->subYear()->subDay(),
                'expires_at' => now()->subDay(),
                'issued_to_name' => 'Rian Hidayat',
                'notes' => 'Gift card kadaluarsa.',
            ],

            // Corporate Accounts
            [
                'code' => 'CORPGOOG',
                'type' => 'corporate',
                'status' => 'active',
                'initial_value' => 10000000.00,
                'balance' => 9500000.00,
                'currency' => 'IDR',
                'activated_at' => now(),
                'expires_at' => now()->addYears(2),
                'company_name' => 'Google Indonesia',
                'company_contact' => 'Sarah Wijaya (08123456789)',
                'notes' => 'Akun korporat Google Indonesia untuk meeting & catering harian.',
            ],
            [
                'code' => 'CORPMAJU',
                'type' => 'corporate',
                'status' => 'active',
                'initial_value' => 5000000.00,
                'balance' => 5000000.00,
                'currency' => 'IDR',
                'activated_at' => now(),
                'expires_at' => now()->addYears(2),
                'company_name' => 'PT Maju Mundur Sejahtera',
                'company_contact' => 'Hendra Pratama (08111222333)',
                'notes' => 'Akun korporat PT Maju Mundur Sejahtera.',
            ],
            [
                'code' => 'CORPTOKO',
                'type' => 'corporate',
                'status' => 'exhausted',
                'initial_value' => 2000000.00,
                'balance' => 0.00,
                'currency' => 'IDR',
                'activated_at' => now()->subMonths(6),
                'expires_at' => now()->addMonths(6),
                'last_used_at' => now()->subWeek(),
                'company_name' => 'PT Tokopedia',
                'company_contact' => 'Rini (08222333444)',
                'notes' => 'Akun korporat Tokopedia (Saldo Habis).',
            ]
        ];

        foreach ($cards as $card) {
            GiftCard::query()->updateOrCreate(
                ['code' => $card['code']],
                [
                    'type' => $card['type'],
                    'status' => $card['status'],
                    'initial_value' => $card['initial_value'],
                    'balance' => $card['balance'],
                    'currency' => $card['currency'],
                    'activated_at' => $card['activated_at'],
                    'expires_at' => $card['expires_at'] ?? null,
                    'last_used_at' => $card['last_used_at'] ?? null,
                    'issued_to_name' => $card['issued_to_name'] ?? null,
                    'issued_to_email' => $card['issued_to_email'] ?? null,
                    'company_name' => $card['company_name'] ?? null,
                    'company_contact' => $card['company_contact'] ?? null,
                    'notes' => $card['notes'] ?? null,
                ]
            );
        }
    }
}
