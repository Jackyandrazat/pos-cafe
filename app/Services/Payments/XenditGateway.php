<?php

namespace App\Services\Payments;

use App\Models\Order;

class XenditGateway implements PaymentGatewayInterface
{
    public function createCharge(Order $order, float $amount, string $method, ?string $channel = null): array
    {
        // Skeleton logic for future Xendit API integration
        $reference = 'XENDIT-' . $order->id . '-' . time();

        $payload = [
            'amount' => $amount,
            'channel' => $channel,
            'expires_at' => now()->addMinutes(15)->toIso8601String(),
        ];

        if ($method === 'qris') {
            $payload['qr_string'] = '00020101021226...'; // mock dynamic QRIS
        } elseif ($method === 'ewallet') {
            $payload['deeplink'] = 'https://xendit.local/ewallet/pay';
        } elseif ($method === 'transfer') {
            $payload['va_number'] = '8876543210';
            $payload['bank'] = $channel ?? 'mandiri';
        }

        return [
            'provider' => 'xendit',
            'reference' => $reference,
            'status' => 'pending',
            'payload' => $payload,
        ];
    }

    public function handleWebhook(array $payload): array
    {
        $status = match (strtolower($payload['status'] ?? '')) {
            'completed', 'paid', 'settled', 'success' => 'captured',
            'expired' => 'expired',
            'failed' => 'failed',
            default => 'pending',
        };

        return [
            'reference' => (string) ($payload['external_id'] ?? $payload['reference'] ?? ''),
            'status'    => $status,
            'provider'  => 'xendit',
        ];
    }
}
