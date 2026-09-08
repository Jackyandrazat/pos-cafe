<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->boolean('calling_waiter')->default(false)->after('status');
            $table->string('waiter_call_reason')->nullable()->after('calling_waiter');
            $table->text('waiter_call_notes')->nullable()->after('waiter_call_reason');
            $table->timestamp('waiter_called_at')->nullable()->after('waiter_call_notes');
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn([
                'calling_waiter',
                'waiter_call_reason',
                'waiter_call_notes',
                'waiter_called_at',
            ]);
        });
    }
};
