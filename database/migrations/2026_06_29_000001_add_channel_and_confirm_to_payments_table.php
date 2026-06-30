<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Sub-channel untuk metode bayar, contoh: 'gopay', 'ovo', 'dana', 'bca', 'mandiri'
            $table->string('payment_channel')->nullable()->after('payment_method');

            // Konfirmasi oleh kasir untuk pembayaran pending (manual)
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete()->after('paid_at');
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn(['payment_channel', 'confirmed_by', 'confirmed_at']);
        });
    }
};
