<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Terima notifikasi webhook dari payment gateway (Midtrans, Doku, dll).
 *
 * Route ini PUBLIC (tanpa auth:sanctum) karena dipanggil oleh sistem eksternal.
 * Keamanan dijaga via signature verification di masing-masing gateway handler.
 */
class PaymentWebhookController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }

    /**
     * POST /api/v1/webhooks/midtrans
     */
    public function midtrans(Request $request): JsonResponse
    {
        try {
            $this->paymentService->handleWebhook($request->all());

            return response()->json(['message' => 'OK']);
        } catch (\RuntimeException $e) {
            // Gateway handler belum diimplementasikan atau terjadi error
            return response()->json(['message' => $e->getMessage()], 501);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Internal error'], 500);
        }
    }

    /**
     * POST /api/v1/webhooks/doku
     */
    public function doku(Request $request): JsonResponse
    {
        try {
            $this->paymentService->handleWebhook($request->all());

            return response()->json(['message' => 'OK']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 501);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Internal error'], 500);
        }
    }
}
