<?php

namespace Tests\Unit;

use App\Exceptions\PromotionException;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\User;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PromotionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_percentage_promotion(): void
    {
        $user = User::factory()->create();
        $promotion = Promotion::create([
            'name' => 'Diskon 10%',
            'code' => 'PROMO10',
            'type' => 'percentage',
            'discount_value' => 10,
            'max_discount' => null,
            'min_subtotal' => 0,
            'usage_limit' => null,
            'usage_limit_per_user' => null,
            'is_active' => true,
        ]);

        $result = PromotionService::validateAndCalculate('promo10', 200000, $user);

        $this->assertNotNull($result);
        $this->assertSame($promotion->id, $result['promotion']->id);
        $this->assertEquals(20000, $result['discount']);
        $this->assertEquals('PROMO10', $result['code']);
    }

    public function test_usage_limit_per_user_is_enforced(): void
    {
        $user = User::factory()->create();
        $promotion = Promotion::create([
            'name' => 'Diskon Sekali Pakai',
            'code' => 'ONETIME',
            'type' => 'fixed',
            'discount_value' => 15000,
            'max_discount' => null,
            'min_subtotal' => 0,
            'usage_limit' => null,
            'usage_limit_per_user' => 1,
            'is_active' => true,
        ]);

        PromotionUsage::create([
            'promotion_id' => $promotion->id,
            'user_id' => $user->id,
            'discount_amount' => 15000,
            'used_at' => now(),
        ]);

        $this->expectException(PromotionException::class);
        PromotionService::validateAndCalculate('onetime', 200000, $user);
    }

    public function test_zero_usage_limits_are_treated_as_unlimited(): void
    {
        $user = User::factory()->create();
        $promotion = Promotion::create([
            'name' => 'Diskon Tanpa Batas',
            'code' => 'UNLIMIT',
            'type' => 'fixed',
            'discount_value' => 5000,
            'usage_limit' => 0,
            'usage_limit_per_user' => 0,
            'min_subtotal' => 0,
            'is_active' => true,
        ]);

        $result = PromotionService::validateAndCalculate('unlimit', 10000, $user);

        $this->assertNotNull($result);
        $this->assertSame(5000.0, $result['discount']);
    }

    public function test_promotion_with_date_only_end_time_remains_active_until_day_ends(): void
    {
        Carbon::setTestNow('2025-12-19 12:00:00');

        $user = User::factory()->create();

        Promotion::create([
            'name' => 'Promo Seharian',
            'code' => 'HARINI',
            'type' => 'fixed',
            'discount_value' => 1000,
            'min_subtotal' => 0,
            'is_active' => true,
            'ends_at' => Carbon::now()->copy()->startOfDay(),
        ]);

        $result = PromotionService::validateAndCalculate('harini', 10000, $user);

        $this->assertNotNull($result);
        $this->assertSame(1000.0, $result['discount']);

        Carbon::setTestNow();
    }

    public function test_promotion_respects_schedule_days_and_time_window(): void
    {
        Carbon::setTestNow('2025-12-19 14:00:00'); // Friday 14:00

        $user = User::factory()->create();

        $promotion = Promotion::create([
            'name' => 'Happy Hour Coffee',
            'code' => 'HAPPY',
            'type' => 'percentage',
            'discount_value' => 20,
            'schedule_days' => [5], // Friday
            'schedule_start_time' => '13:00:00',
            'schedule_end_time' => '15:00:00',
            'is_active' => true,
        ]);

        $result = PromotionService::validateAndCalculate('happy', 50000, $user);
        $this->assertNotNull($result);
        $this->assertEquals(10000, $result['discount']);

        Carbon::setTestNow('2025-12-19 16:00:00');
        $this->expectException(PromotionException::class);
        PromotionService::validateAndCalculate('happy', 50000, $user);

        Carbon::setTestNow();
    }
}
