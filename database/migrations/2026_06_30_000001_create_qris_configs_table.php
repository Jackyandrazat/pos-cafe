<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qris_configs', function (Blueprint $table) {
            $table->id();

            // Raw static QRIS string dari stiker/cetakan merchant (EMVCo format).
            // Digunakan untuk generate dynamic QRIS per transaksi.
            $table->text('static_string')->nullable()->comment('Static QRIS string (EMVCo) dari merchant');

            // Label/nama merchant untuk display di UI
            $table->string('merchant_name', 100)->nullable();

            // Aktif/tidak — hanya 1 record yang aktif
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qris_configs');
    }
};
