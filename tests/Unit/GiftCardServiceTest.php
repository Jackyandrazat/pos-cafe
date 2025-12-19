<?php

namespace Tests\Unit;

use App\Exceptions\GiftCardException;
use App\Models\GiftCard;
use App\Models\Order;
use App\Models\User;
use App\Services\GiftCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftCardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GiftCardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GiftCardService::class);
    }

    public function test_prepare_redemption_returns_valid_payload(): void
    {
        $giftCard = GiftCard::factory()->create([
            'code' => 'GCBUNDLE',
            'balance' => 150000,
        ]);

        $result = $this->service->prepareRedemption('gcbundle', 50000, 80000);

        $this->assertNotNull($result);
        $this->assertEquals($giftCard->id, $result['gift_card']->id);
        $this->assertEquals(50000, $result['amount']);
    }

    public function test_prepare_redemption_limits_amount_to_balance_and_total(): void
    {
        $giftCard = GiftCard::factory()->create([
            'code' => 'GCLIMIT',
            'balance' => 30000,
        ]);

        $result = $this->service->prepareRedemption('gclimit', 0, 20000);

        $this->assertEquals(20000, $result['amount']);

        $this->expectException(GiftCardException::class);
        $this->service->prepareRedemption('gclimit', 40000, 50000);
    }

    public function test_redeem_for_order_updates_balance_and_logs_transaction(): void
    {
        $giftCard = GiftCard::factory()->create([
            'code' => 'GCORDER',
            'balance' => 100000,
        ]);

        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'subtotal_order' => 80000,
            'total_order' => 30000,
            'gift_card_id' => $giftCard->id,
            'gift_card_code' => $giftCard->code,
            'gift_card_amount' => 50000,
        ]);

        $this->service->redeemForOrder($order, $giftCard, 50000);

        $giftCard->refresh();

        $this->assertEquals(50000, $giftCard->balance);
        $this->assertDatabaseHas('gift_card_transactions', [
            'gift_card_id' => $giftCard->id,
            'type' => 'redeem',
            'amount' => 50000,
            'reference_type' => Order::class,
            'reference_id' => $order->id,
        ]);
    }
}
