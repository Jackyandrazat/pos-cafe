<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['gift_card', 'corporate'])->default('gift_card');
            $table->enum('status', ['active', 'inactive', 'suspended', 'exhausted', 'expired'])->default('active');
            $table->decimal('initial_value', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->string('issued_to_name')->nullable();
            $table->string('issued_to_email')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_contact')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('gift_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_card_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['issue', 'reload', 'redeem', 'refund', 'adjustment']);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->nullableMorphs('reference');
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('gift_card_id')->nullable()->after('promotion_id')->constrained()->nullOnDelete();
            $table->string('gift_card_code', 50)->nullable()->after('promotion_code');
            $table->decimal('gift_card_amount', 12, 2)->default(0)->after('promotion_discount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['gift_card_id']);
            $table->dropColumn(['gift_card_id', 'gift_card_code', 'gift_card_amount']);
        });

        Schema::dropIfExists('gift_card_transactions');
        Schema::dropIfExists('gift_cards');
    }
};
