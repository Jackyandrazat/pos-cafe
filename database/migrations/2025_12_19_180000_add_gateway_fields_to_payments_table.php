<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('payment_method');
            $table->string('external_reference')->nullable()->after('provider');
            $table->string('status')->default('captured')->after('external_reference');
            $table->json('meta')->nullable()->after('status');
            $table->timestamp('paid_at')->nullable()->after('payment_date');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['provider', 'external_reference', 'status', 'meta', 'paid_at']);
        });
    }
};
