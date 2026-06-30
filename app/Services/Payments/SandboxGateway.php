<?php

namespace App\Services\Payments;

use App\Models\Order;

class SandboxGateway implements PaymentGatewayInterface
{
    public function createCharge(Order $order, float $amount, string $method, ?string $channel = null): array
    {
        $reference = strtoupper($method) . '-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(12));

        if ($method === 'qris') {
            $qrString = '00020101021126' . \Illuminate\Support\Str::padLeft((string) $order->id, 6, '0') . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10));
            return [
                'provider'  => 'qris-sandbox',
                'reference' => $reference,
                'status'    => 'pending',
                'payload'   => [
                    'qr_string'  => $qrString,
                    'deeplink'   => 'https://pay.local/checkout/' . $reference,
                    'expires_at' => now()->addMinutes(15)->toIso8601String(),
                    'amount'     => $amount,
                ],
            ];
        }

        if ($method === 'ewallet') {
            return [
                'provider'  => 'ewallet-sandbox',
                'reference' => $reference,
                'status'    => 'pending',
                'payload'   => [
                    'channel'    => $channel ?? 'gopay',
                    'deeplink'   => 'https://pay.local/checkout/' . $reference,
                    'expires_at' => now()->addMinutes(15)->toIso8601String(),
                    'amount'     => $amount,
                ],
            ];
        }

        if ($method === 'transfer') {
            return [
                'provider'  => 'transfer-sandbox',
                'reference' => $reference,
                'status'    => 'pending',
                'payload'   => [
                    'bank'           => $channel ?? 'bca',
                    'account_number' => '123456' . \Illuminate\Support\Str::padLeft((string) $order->id, 6, '0'),
                    'expires_at'     => now()->addMinutes(60)->toIso8601String(),
                    'amount'         => $amount,
                ],
            ];
        }

        throw new \InvalidArgumentException("Unsupported payment method in sandbox: {$method}");
    }

    public function handleWebhook(array $payload): array
    {
        // Sandbox webhook — hanya untuk testing
        return [
            'reference' => $payload['reference'] ?? '',
            'status'    => $payload['status'] ?? 'captured',
            'provider'  => 'sandbox',
        ];
    }
}
