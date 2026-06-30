<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\QrisConfig;
use App\Services\QrisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class QrisController extends Controller
{
    public function __construct(protected QrisService $qrisService)
    {
    }

    /**
     * Generate a dynamic QRIS QR code for a given amount.
     *
     * `static_qris` kini opsional — jika tidak dikirim, sistem otomatis
     * mengambil dari konfigurasi merchant yang disimpan di database.
     *
     * POST /api/v1/qris/generate
     * Body: { "amount": 15000 }
     *   OR: { "static_qris": "000201...", "amount": 15000 }
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'static_qris' => ['sometimes', 'nullable', 'string', 'min:10'],
            'amount'      => ['required', 'numeric', 'gt:0'],
        ]);

        // Ambil static string: dari request → fallback DB/env
        $staticQris = $validated['static_qris'] ?? null;

        if (! $staticQris) {
            $staticQris = QrisConfig::getStaticString();
        }

        if (! $staticQris) {
            return response()->json([
                'message' => 'QRIS belum dikonfigurasi. Hubungi admin untuk menyimpan Static QRIS string merchant.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $dynamicQris = $this->qrisService->convertToDynamic(
                $staticQris,
                (float) $validated['amount'],
            );

            $qrCodeSvg = (string) QrCode::format('svg')
                ->size(300)
                ->errorCorrection('M')
                ->generate($dynamicQris);

            return response()->json([
                'qris_string' => $dynamicQris,
                'amount'      => (int) $validated['amount'],
                'qr_svg'      => $qrCodeSvg,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Generate dynamic QRIS langsung untuk sebuah order.
     *
     * Amount otomatis diambil dari total_order.
     * Cocok digunakan oleh cashier app / frontend POS setelah order dibuat.
     *
     * POST /api/v1/orders/{order}/qris
     */
    public function generateForOrder(Request $request, Order $order): JsonResponse
    {
        // Pastikan order milik user yang request
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        $staticQris = QrisConfig::getStaticString();

        if (! $staticQris) {
            return response()->json([
                'message' => 'QRIS belum dikonfigurasi. Hubungi admin untuk menyimpan Static QRIS string merchant.',
                'configured' => false,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $amount = (float) ($order->total_order ?? 0);

        if ($amount <= 0) {
            return response()->json([
                'message' => 'Total order tidak valid (Rp 0 atau belum ada item).',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $dynamicQris = $this->qrisService->convertToDynamic($staticQris, $amount);

            $qrCodeSvg = (string) QrCode::format('svg')
                ->size(300)
                ->errorCorrection('M')
                ->generate($dynamicQris);

            return response()->json([
                'order_id'    => (string) $order->id,
                'amount'      => (int) $amount,
                'qris_string' => $dynamicQris,
                'qr_svg'      => $qrCodeSvg,
                'note'        => 'Scan QR code ini menggunakan aplikasi e-wallet atau mobile banking. Tunjukkan bukti ke kasir setelah selesai.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Cek apakah QRIS merchant sudah dikonfigurasi.
     *
     * GET /api/v1/qris/status
     */
    public function status(): JsonResponse
    {
        $config = QrisConfig::active();

        return response()->json([
            'configured'    => QrisConfig::isConfigured(),
            'merchant_name' => $config?->merchant_name,
            'updated_at'    => $config?->updated_at?->toIso8601String(),
        ]);
    }
}
