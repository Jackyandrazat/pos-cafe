<?php

namespace App\Services\Payments;

use App\Models\Order;

class PaymentGatewayManager
{
    protected PaymentGatewayInterface $gateway;

    public function __construct()
    {
        $mode = config('payment.mode', 'manual');
        $this->gateway = $this->resolveGateway($mode);
    }

    /**
     * Resolve gateway driver berdasarkan mode dari config.
     */
    protected function resolveGateway(string $mode): PaymentGatewayInterface
    {
        return match ($mode) {
            'manual'   => app(ManualGateway::class),
            'midtrans' => new MidtransGateway(),
            'doku'     => new DokuGateway(),
            'sandbox'  => new SandboxGateway(),  // untuk testing
            default    => throw new \InvalidArgumentException("Mode pembayaran tidak dikenal: {$mode}"),
        };
    }

    /**
     * Buat charge untuk order.
     */
    public function createCharge(string $method, Order $order, float $amount, ?string $channel = null): array
    {
        return $this->gateway->createCharge($order, $amount, $method, $channel);
    }

    /**
     * Tangani notifikasi webhook dari payment gateway.
     */
    public function handleWebhook(array $payload): array
    {
        return $this->gateway->handleWebhook($payload);
    }

    /**
     * Apakah mode saat ini membutuhkan konfirmasi manual oleh kasir?
     */
    public function requiresManualConfirmation(): bool
    {
        return config('payment.mode', 'manual') === 'manual';
    }
}
