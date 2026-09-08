<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\PaymentAccount;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_payment_accounts_endpoint_returns_active_accounts(): void
    {
        // Ada 8 seeded data dari migrasi
        $response = $this->getJson('/api/v1/payment-accounts');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'bank_transfers' => [
                        '*' => [
                            'id',
                            'type',
                            'provider_code',
                            'name',
                            'account_number',
                            'account_name',
                            'instructions',
                            'qr_image_url',
                        ],
                    ],
                    'ewallets' => [
                        '*' => [
                            'id',
                            'type',
                            'provider_code',
                            'name',
                            'account_number',
                            'account_name',
                            'instructions',
                            'qr_image_url',
                        ],
                    ],
                ],
            ]);

        $bankTransfers = $response->json('data.bank_transfers');
        $ewallets = $response->json('data.ewallets');

        $this->assertNotEmpty($bankTransfers);
        $this->assertNotEmpty($ewallets);
    }

    public function test_inactive_accounts_are_hidden_from_api(): void
    {
        PaymentAccount::where('provider_code', 'bca')->update(['is_active' => false]);

        $response = $this->getJson('/api/v1/payment-accounts');

        $response->assertOk();
        $codes = collect($response->json('data.bank_transfers'))->pluck('provider_code')->all();

        $this->assertNotContains('bca', $codes);
    }

    public function test_manual_gateway_uses_payment_account_details(): void
    {
        PaymentAccount::where('provider_code', 'bca')->update([
            'account_number' => '9988776655',
            'account_name'   => 'Kopi Keren Test',
        ]);

        $order = Order::factory()->create([
            'total_order'    => 50000,
            'subtotal_order' => 50000,
        ]);

        /** @var PaymentService $paymentService */
        $paymentService = app(PaymentService::class);

        $payment = $paymentService->process($order, [
            'payment_method'  => 'transfer',
            'payment_channel' => 'bca',
            'amount'          => 50000,
        ]);

        $this->assertEquals('transfer', $payment->payment_method);
        $this->assertEquals('bca', $payment->payment_channel);
        $this->assertEquals('9988776655', $payment->meta['account_number']);
        $this->assertEquals('Kopi Keren Test', $payment->meta['account_name']);
        $this->assertStringContainsString('Kopi Keren Test', $payment->meta['note']);
    }

    public function test_manual_gateway_uses_ewallet_payment_account_details(): void
    {
        PaymentAccount::where('provider_code', 'gopay')->update([
            'account_number' => '089911223344',
            'account_name'   => 'Kasir GoPay Utama',
        ]);

        $order = Order::factory()->create([
            'total_order'    => 25000,
            'subtotal_order' => 25000,
        ]);

        /** @var PaymentService $paymentService */
        $paymentService = app(PaymentService::class);

        $payment = $paymentService->process($order, [
            'payment_method'  => 'ewallet',
            'payment_channel' => 'gopay',
            'amount'          => 25000,
        ]);

        $this->assertEquals('ewallet', $payment->payment_method);
        $this->assertEquals('gopay', $payment->payment_channel);
        $this->assertEquals('089911223344', $payment->meta['phone']);
        $this->assertEquals('Kasir GoPay Utama', $payment->meta['account_name']);
        $this->assertStringContainsString('Kasir GoPay Utama', $payment->meta['note']);
    }
}
