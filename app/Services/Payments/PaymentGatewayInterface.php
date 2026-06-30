<?php

namespace App\Services\Payments;

use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Buat charge untuk order.
     *
     * @param  Order  $order
     * @param  float  $amount
     * @param  string $method   - 'qris', 'ewallet', 'transfer'
     * @param  string|null $channel - sub-channel: 'gopay', 'ovo', 'bca', dll
     * @return array{
     *     provider: string,
     *     reference: string|null,
     *     status: string,
     *     payload: array|null
     * }
     */
    public function createCharge(Order $order, float $amount, string $method, ?string $channel = null): array;

    /**
     * Tangani notifikasi webhook dari payment gateway.
     *
     * @param  array  $payload  Raw payload dari gateway
     * @return array{
     *     reference: string,
     *     status: string,
     *     provider: string
     * }
     */
    public function handleWebhook(array $payload): array;
}
