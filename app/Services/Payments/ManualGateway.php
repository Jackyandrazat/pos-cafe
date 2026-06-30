<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\QrisConfig;
use App\Services\QrisService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * ManualGateway — Mode operasional tanpa integrasi API pihak ketiga.
 *
 * Pada mode ini, kasir yang bertanggung jawab mengkonfirmasi pembayaran:
 * - Cash          → Langsung captured, kembalian dihitung otomatis
 * - QRIS Statis   → Menunggu konfirmasi kasir setelah pelanggan scan
 * - E-Wallet      → Menunggu konfirmasi kasir setelah transfer masuk
 * - Transfer/VA   → Menunggu konfirmasi kasir setelah transfer masuk
 */
class ManualGateway implements PaymentGatewayInterface
{
    public function __construct(protected QrisService $qrisService)
    {
    }

    public function createCharge(Order $order, float $amount, string $method, ?string $channel = null): array
    {
        return match ($method) {
            'cash'     => $this->createCashCharge($amount),
            'qris'     => $this->createQrisCharge($order, $amount),
            'ewallet'  => $this->createEwalletCharge($amount, $channel),
            'transfer' => $this->createTransferCharge($amount, $channel),
            default    => throw new \InvalidArgumentException("Metode pembayaran tidak dikenal: {$method}"),
        };
    }

    protected function createCashCharge(float $amount): array
    {
        return [
            'provider'  => 'manual',
            'reference' => null,
            'status'    => 'captured', // Cash langsung diterima
            'payload'   => null,
        ];
    }

    protected function createQrisCharge(Order $order, float $amount): array
    {
        $staticString = QrisConfig::getStaticString();

        $payload = ['amount' => $amount];

        if ($staticString) {
            try {
                // Convert static QRIS → dynamic dengan amount transaksi
                $dynamicQris = $this->qrisService->convertToDynamic($staticString, $amount);

                // Generate SVG QR code (300×300, error-correction M)
                $qrSvg = (string) QrCode::format('svg')
                    ->size(300)
                    ->errorCorrection('M')
                    ->generate($dynamicQris);

                $payload['qris_string'] = $dynamicQris;
                $payload['qr_svg']      = $qrSvg;
                $payload['note']        = 'Scan QR code di bawah menggunakan aplikasi e-wallet atau mobile banking Anda, lalu tunjukkan bukti ke kasir.';
            } catch (\Throwable $e) {
                // Jika generate gagal, fallback ke mode manual
                $payload['note'] = 'Konfirmasi pembayaran QRIS secara manual setelah pelanggan scan.';
                $payload['error'] = $e->getMessage();
            }
        } else {
            $payload['note'] = 'QRIS belum dikonfigurasi. Konfirmasi pembayaran ke kasir setelah pelanggan scan.';
        }

        return [
            'provider'  => 'qris-manual',
            'reference' => null,
            'status'    => 'pending',   // Menunggu konfirmasi kasir
            'payload'   => $payload,
        ];
    }

    protected function createEwalletCharge(float $amount, ?string $channel): array
    {
        $ewallets = config('payment.ewallets', []);
        $info = $ewallets[$channel] ?? null;

        $payload = [
            'amount'  => $amount,
            'channel' => $channel,
        ];

        if ($info && ! empty($info['phone'])) {
            $payload['phone'] = $info['phone'];
            $payload['note']  = "Transfer ke {$info['label']} nomor {$info['phone']}, lalu konfirmasi ke kasir";
        } else {
            $payload['note'] = 'Konfirmasi pembayaran e-wallet ke kasir setelah transfer selesai';
        }

        return [
            'provider'  => 'ewallet-manual',
            'reference' => null,
            'status'    => 'pending',   // Menunggu konfirmasi kasir
            'payload'   => $payload,
        ];
    }

    protected function createTransferCharge(float $amount, ?string $channel): array
    {
        $vas = config('payment.virtual_accounts', []);
        $info = $vas[$channel] ?? null;

        $payload = [
            'amount'  => $amount,
            'bank'    => $channel,
        ];

        if ($info && ! empty($info['account_number'])) {
            $payload['account_number'] = $info['account_number'];
            $payload['note']           = "Transfer ke rekening {$info['label']} nomor {$info['account_number']}";
        } else {
            $payload['note'] = 'Konfirmasi transfer bank ke kasir setelah berhasil';
        }

        return [
            'provider'  => 'transfer-manual',
            'reference' => null,
            'status'    => 'pending',   // Menunggu konfirmasi kasir
            'payload'   => $payload,
        ];
    }

    /**
     * ManualGateway tidak punya webhook — konfirmasi dilakukan oleh kasir.
     */
    public function handleWebhook(array $payload): array
    {
        throw new \LogicException('ManualGateway tidak mendukung webhook. Gunakan endpoint konfirmasi kasir.');
    }
}
