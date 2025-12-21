<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Str;

class QrisGateway
{
    public function __construct(protected string $channel = 'qris')
    {
    }

    public function createCharge(Order $order, float $amount): array
    {
        $reference = strtoupper($this->channel) . '-' . Str::upper(Str::random(12));
        $qrString = '00020101021126' . Str::padLeft((string) $order->id, 6, '0') . Str::upper(Str::random(10));

        return [
            'provider' => $this->channel === 'qris' ? 'qris-sandbox' : 'ewallet-sandbox',
            'reference' => $reference,
            'status' => 'pending',
            'payload' => [
                'qr_string' => $qrString,
                'deeplink' => 'https://pay.local/checkout/' . $reference,
                'expires_at' => now()->addMinutes(15)->toIso8601String(),
                'amount' => $amount,
            ],
        ];
    }
}
