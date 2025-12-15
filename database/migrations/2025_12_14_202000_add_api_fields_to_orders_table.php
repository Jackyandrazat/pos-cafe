<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'service_fee_order')) {
                $table->decimal('service_fee_order', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('orders', 'notes')) {
                $table->text('notes')->nullable();
            }

            if (! Schema::hasColumn('orders', 'subtotal_order')) {
                $table->decimal('subtotal_order', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('orders', 'discount_order')) {
                $table->decimal('discount_order', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('orders', 'total_order')) {
                $table->decimal('total_order', 12, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            if (Schema::hasColumn('orders', 'service_fee_order')) {
                $table->dropColumn('service_fee_order');
            }

            if (Schema::hasColumn('orders', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('orders', 'subtotal_order')) {
                $table->dropColumn('subtotal_order');
            }

            if (Schema::hasColumn('orders', 'discount_order')) {
                $table->dropColumn('discount_order');
            }

            if (Schema::hasColumn('orders', 'total_order')) {
                $table->dropColumn('total_order');
            }
        });
    }
};
