<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loyalty_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type');
            $table->string('badge_name')->nullable();
            $table->string('badge_code')->nullable();
            $table->string('badge_color')->nullable();
            $table->string('badge_icon')->nullable();
            $table->string('description')->nullable();
            $table->unsignedInteger('target_value')->default(1);
            $table->unsignedInteger('bonus_points')->default(0);
            $table->string('reset_period')->default('none');
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->timestamp('active_from')->nullable();
            $table->timestamp('active_until')->nullable();
            $table->timestamps();
        });

        Schema::create('loyalty_challenge_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_value')->default(0);
            $table->timestamp('window_start')->nullable();
            $table->timestamp('window_end')->nullable();
            $table->timestamp('last_progressed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rewarded_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['loyalty_challenge_id', 'customer_id'], 'customer_challenge_unique');
        });

        Schema::create('loyalty_challenge_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_challenge_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('points_awarded')->default(0);
            $table->string('badge_name')->nullable();
            $table->string('badge_code')->nullable();
            $table->string('badge_color')->nullable();
            $table->string('badge_icon')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('awarded_at')->nullable();
            $table->timestamps();
        });

        $now = Carbon::now();

        DB::table('loyalty_challenges')->insert([
            [
                'name' => 'Weekly 5x Visit',
                'slug' => 'weekly-five-visits',
                'type' => 'weekly_visits',
                'description' => 'Datang 5x dalam satu minggu untuk bonus poin dan badge Loyal Regular.',
                'target_value' => 5,
                'bonus_points' => 150,
                'reset_period' => 'weekly',
                'badge_name' => 'Loyal Regular',
                'badge_code' => 'badge_loyal_regular',
                'badge_color' => '#F97316',
                'badge_icon' => 'mdi:calendar-check',
                'config' => json_encode([
                    'window' => 'weekly',
                    'count_field' => 'orders',
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Coba Varian Baru',
                'slug' => 'new-variant-explorer',
                'type' => 'new_variant',
                'description' => 'Beli menu yang belum pernah dicoba untuk mendapatkan badge Explorer.',
                'target_value' => 1,
                'bonus_points' => 100,
                'reset_period' => 'none',
                'badge_name' => 'Menu Explorer',
                'badge_code' => 'badge_menu_explorer',
                'badge_color' => '#14B8A6',
                'badge_icon' => 'mdi:compass',
                'config' => json_encode([
                    'require_unique_product' => true,
                    'min_unique_count' => 1,
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_challenge_awards');
        Schema::dropIfExists('loyalty_challenge_progress');
        Schema::dropIfExists('loyalty_challenges');
    }
};
