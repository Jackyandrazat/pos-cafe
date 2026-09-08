<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->comment('bank_transfer atau ewallet');
            $table->string('provider_code', 50)->comment('bca, mandiri, bri, gopay, dana, dll');
            $table->string('name', 100)->comment('Nama Bank atau E-Wallet');
            $table->string('account_number', 100)->comment('Nomor Rekening atau Nomor HP');
            $table->string('account_name', 100)->comment('Atas Nama Pemilik Rekening / Akun');
            $table->string('qr_image')->nullable()->comment('Path gambar QR statis opsional');
            $table->text('instructions')->nullable()->comment('Catatan atau instruksi pembayaran');
            $table->boolean('is_active')->default(true)->comment('Status aktif metode');
            $table->integer('sort_order')->default(0)->comment('Urutan tampilan');
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index('provider_code');
        });

        // Seed data awal dari konfigurasi yang ada
        $now = now();
        $defaultAccounts = [
            // Bank Transfers
            [
                'type' => 'bank_transfer',
                'provider_code' => 'bca',
                'name' => 'BCA',
                'account_number' => env('VA_BCA', '82710812345678'),
                'account_name' => 'Kafe KDAI',
                'instructions' => 'Transfer tepat sesuai nominal tagihan dan simpan bukti transfer untuk kasir.',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'bank_transfer',
                'provider_code' => 'mandiri',
                'name' => 'Mandiri',
                'account_number' => env('VA_MANDIRI', '89508123456789'),
                'account_name' => 'Kafe KDAI',
                'instructions' => 'Pilih transfer ke rekening Bank Mandiri. Tulis nomor pesanan pada berita transfer.',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'bank_transfer',
                'provider_code' => 'bni',
                'name' => 'BNI',
                'account_number' => env('VA_BNI', '98808123456789'),
                'account_name' => 'Kafe KDAI',
                'instructions' => 'Transfer via ATM, Mobile Banking, atau Internet Banking BNI.',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'bank_transfer',
                'provider_code' => 'bri',
                'name' => 'BRI',
                'account_number' => env('VA_BRI', '12808123456789'),
                'account_name' => 'Kafe KDAI',
                'instructions' => 'Transfer via BRIMO atau ATM BRI.',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // E-Wallets
            [
                'type' => 'ewallet',
                'provider_code' => 'gopay',
                'name' => 'GoPay',
                'account_number' => env('EWALLET_GOPAY_PHONE', '081234567890'),
                'account_name' => 'Kafe KDAI',
                'instructions' => 'Kirim saldo GoPay ke nomor di atas, konfirmasi ke kasir saat selesai.',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'ewallet',
                'provider_code' => 'dana',
                'name' => 'DANA',
                'account_number' => env('EWALLET_DANA_PHONE', '081234567890'),
                'account_name' => 'Kafe KDAI',
                'instructions' => 'Kirim saldo DANA ke nomor akun di atas.',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'ewallet',
                'provider_code' => 'ovo',
                'name' => 'OVO',
                'account_number' => env('EWALLET_OVO_PHONE', '081234567890'),
                'account_name' => 'Kafe KDAI',
                'instructions' => 'Transfer sesama OVO ke nomor akun di atas.',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'ewallet',
                'provider_code' => 'shopeepay',
                'name' => 'ShopeePay',
                'account_number' => env('EWALLET_SHOPEEPAY_PHONE', '081234567890'),
                'account_name' => 'Kafe KDAI',
                'instructions' => 'Transfer ShopeePay ke nomor kontak di atas.',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('payment_accounts')->insert($defaultAccounts);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_accounts');
    }
};
