<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('capacity')->default(2)->after('table_number');
            $table->unsignedInteger('x_position')->nullable()->after('capacity');
            $table->unsignedInteger('y_position')->nullable()->after('x_position');
            $table->string('notes')->nullable()->after('y_position');
        });

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE `tables` MODIFY `status` ENUM('available','occupied','reserved','cleaning') NOT NULL DEFAULT 'available'");
        }

        Schema::create('table_queue_entries', function (Blueprint $table) {
            $table->id();
            $table->string('guest_name');
            $table->unsignedInteger('party_size')->default(1);
            $table->string('contact')->nullable();
            $table->string('status')->default('waiting');
            $table->unsignedInteger('estimated_wait_minutes')->nullable();
            $table->foreignId('assigned_table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('seated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_queue_entries');

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE `tables` MODIFY `status` ENUM('available','occupied','reserved') NOT NULL DEFAULT 'available'");
        }

        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn(['capacity', 'x_position', 'y_position', 'notes']);
        });
    }
};
