<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Arr;

class PaymentGatewayManager
{
    /** @var array<string, PaymentGateway> */
    protected array $gateways;

    public function __construct()
    {
        $this->gateways = [
            'qris' => new QrisGateway('qris'),
            'ewallet' => new QrisGateway('ewallet'),
        ];
    }

    public function createCharge(string $method, Order $order, float $amount): array
    {
        $gateway = Arr::get($this->gateways, $method);

        if (! $gateway) {
            throw new \InvalidArgumentException("Unsupported payment method: {$method}");
        }

        return $gateway->createCharge($order, $amount);
    }
}
