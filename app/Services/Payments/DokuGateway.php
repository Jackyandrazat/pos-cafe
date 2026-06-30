<?php

namespace App\Services\Payments;

use App\Models\Order;

/**
 * DokuGateway — Skeleton untuk integrasi DOKU Payment Gateway.
 *
 * ============================================================
 * CARA SETUP — lihat juga docs/payment-gateway-setup.md
 * ============================================================
 *
 * 1. Daftar akun di https://dashboard.doku.com
 * 2. Ambil Client ID dan Secret Key dari Settings > My Account
 * 3. Isi di .env:
 *    PAYMENT_MODE=doku
 *    DOKU_CLIENT_ID=MCN-xxxx
 *    DOKU_SECRET_KEY=SK-xxxx
 *    DOKU_IS_PRODUCTION=false  (true saat live)
 *
 * 4. Setup webhook URL di DOKU Dashboard:
 *    Configuration > Notification URL:
 *    https://yourdomain.com/api/v1/webhooks/doku
 *
 * 5. Referensi API: https://jokul.doku.com/docs
 *
 * 6. Uncomment dan lengkapi implementasi di method createCharge() dan handleWebhook()
 * ============================================================
 */
class DokuGateway implements PaymentGatewayInterface
{
    protected string $clientId;
    protected string $secretKey;
    protected bool $isProduction;

    public function __construct()
    {
        $this->clientId    = config('payment.gateways.doku.client_id', '');
        $this->secretKey   = config('payment.gateways.doku.secret_key', '');
        $this->isProduction = config('payment.gateways.doku.is_production', false);
    }

    protected function baseUrl(): string
    {
        return $this->isProduction
            ? 'https://api.doku.com'
            : 'https://api-sandbox.doku.com';
    }

    public function createCharge(Order $order, float $amount, string $method, ?string $channel = null): array
    {
        $reference = 'DOKU-' . $order->id . '-' . time();

        // ============================================================
        // TODO: Implementasi aktual menggunakan DOKU API
        //
        // $requestId = uniqid();
        // $requestTarget = '/checkout/v1/payment';
        // $digest = base64_encode(hash('sha256', json_encode([...]), true));
        // $signature = base64_encode(hash_hmac('sha256',
        //     "Client-Id:{$this->clientId}\nRequest-Id:{$requestId}\nRequest-Timestamp:" . now()->toIso8601String() . "\nRequest-Target:{$requestTarget}\nDigest:{$digest}",
        //     $this->secretKey, true
        // ));
        //
        // $response = Http::withHeaders([
        //     'Client-Id'         => $this->clientId,
        //     'Request-Id'        => $requestId,
        //     'Request-Timestamp' => now()->toIso8601String(),
        //     'Signature'         => "HMACSHA256={$signature}",
        // ])->post($this->baseUrl() . $requestTarget, [...]);
        // ============================================================

        // Skeleton response (hapus setelah integrasi aktual):
        $payload = ['amount' => $amount, 'note' => 'DOKU sandbox — belum terkoneksi'];

        return [
            'provider'  => 'doku',
            'reference' => $reference,
            'status'    => 'pending',
            'payload'   => $payload,
        ];
    }

    public function handleWebhook(array $payload): array
    {
        // ============================================================
        // TODO: Verifikasi signature dari DOKU
        //
        // Verifikasi dilakukan dengan signature di header request.
        // Referensi: https://jokul.doku.com/docs/notification-spec
        //
        // return [
        //     'reference' => $payload['order']['invoice_number'],
        //     'status'    => $this->mapStatus($payload['transaction']['status']),
        //     'provider'  => 'doku',
        // ];
        // ============================================================

        throw new \RuntimeException('DOKU webhook handler belum diimplementasikan. Lihat docs/payment-gateway-setup.md');
    }

    protected function mapStatus(string $dokuStatus): string
    {
        return match ($dokuStatus) {
            'SUCCESS' => 'captured',
            'PENDING' => 'pending',
            'FAILED'  => 'failed',
            'EXPIRED' => 'expired',
            default   => 'pending',
        };
    }
}
