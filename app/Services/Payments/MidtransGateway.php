<?php

namespace App\Services\Payments;

use App\Models\Order;

/**
 * MidtransGateway — Skeleton untuk integrasi Midtrans Payment Gateway.
 *
 * ============================================================
 * CARA SETUP — lihat juga docs/payment-gateway-setup.md
 * ============================================================
 *
 * 1. Daftar akun di https://dashboard.midtrans.com
 * 2. Ambil Server Key dan Client Key dari Settings > Access Keys
 * 3. Isi di .env:
 *    PAYMENT_MODE=midtrans
 *    MIDTRANS_SERVER_KEY=SB-Mid-server-xxxx
 *    MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxx
 *    MIDTRANS_IS_PRODUCTION=false  (true saat live)
 *    MIDTRANS_MERCHANT_ID=Gxxxx
 *
 * 4. Install SDK (opsional, atau gunakan HTTP client langsung):
 *    composer require midtrans/midtrans-php
 *
 * 5. Setup webhook URL di Midtrans Dashboard:
 *    Payment > Configuration > Notification URL:
 *    https://yourdomain.com/api/v1/webhooks/midtrans
 *
 * 6. Uncomment dan lengkapi implementasi di method createCharge() dan handleWebhook()
 * ============================================================
 */
class MidtransGateway implements PaymentGatewayInterface
{
    protected string $serverKey;
    protected bool $isProduction;

    public function __construct()
    {
        $this->serverKey    = config('payment.gateways.midtrans.server_key', '');
        $this->isProduction = config('payment.gateways.midtrans.is_production', false);
    }

    public function createCharge(Order $order, float $amount, string $method, ?string $channel = null): array
    {
        $reference = 'MID-' . $order->id . '-' . time();

        // ============================================================
        // TODO: Implementasi aktual menggunakan Midtrans SDK atau HTTP Client
        //
        // Contoh request ke Midtrans:
        //
        // $payload = [
        //     'transaction_details' => [
        //         'order_id' => $reference,
        //         'gross_amount' => (int) $amount,
        //     ],
        //     'payment_type' => $this->mapMethod($method, $channel),
        //     // tambahkan qris / bank_transfer / ewallet sesuai metode
        // ];
        //
        // $response = Http::withBasicAuth($this->serverKey, '')
        //     ->post('https://api.sandbox.midtrans.com/v2/charge', $payload);
        //
        // $result = $response->json();
        //
        // return [
        //     'provider'  => 'midtrans',
        //     'reference' => $result['transaction_id'] ?? $reference,
        //     'status'    => $this->mapStatus($result['transaction_status']),
        //     'payload'   => $result,
        // ];
        // ============================================================

        // Skeleton response (hapus setelah integrasi aktual):
        $payload = ['amount' => $amount, 'note' => 'Midtrans sandbox — belum terkoneksi'];

        return [
            'provider'  => 'midtrans',
            'reference' => $reference,
            'status'    => 'pending',
            'payload'   => $payload,
        ];
    }

    public function handleWebhook(array $payload): array
    {
        // ============================================================
        // TODO: Verifikasi signature dari Midtrans
        //
        // $orderId           = $payload['order_id'];
        // $statusCode        = $payload['status_code'];
        // $grossAmount       = $payload['gross_amount'];
        // $signatureKey      = $payload['signature_key'];
        //
        // $expectedSignature = hash('sha512',
        //     $orderId . $statusCode . $grossAmount . $this->serverKey
        // );
        //
        // if ($signatureKey !== $expectedSignature) {
        //     throw new \RuntimeException('Invalid Midtrans webhook signature');
        // }
        //
        // return [
        //     'reference' => $orderId,
        //     'status'    => $this->mapStatus($payload['transaction_status']),
        //     'provider'  => 'midtrans',
        // ];
        // ============================================================

        throw new \RuntimeException('Midtrans webhook handler belum diimplementasikan. Lihat docs/payment-gateway-setup.md');
    }

    /**
     * Map status Midtrans ke status internal.
     */
    protected function mapStatus(string $midtransStatus): string
    {
        return match ($midtransStatus) {
            'capture', 'settlement' => 'captured',
            'pending'               => 'pending',
            'deny', 'cancel'        => 'failed',
            'expire'                => 'expired',
            'refund'                => 'refunded',
            default                 => 'pending',
        };
    }
}
