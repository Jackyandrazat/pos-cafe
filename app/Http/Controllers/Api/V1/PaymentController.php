<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePaymentRequest;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use App\Services\ShiftGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }

    /**
     * Daftar semua pembayaran untuk sebuah order.
     */
    public function index(Request $request, Order $order): AnonymousResourceCollection
    {
        $this->ensureOrderOwner($request->user(), $order);

        return PaymentResource::collection($order->payments()->latest()->get());
    }

    /**
     * Buat pembayaran baru untuk sebuah order.
     *
     * Response:
     * - status: pending  → pelanggan perlu menyelesaikan pembayaran; kasir perlu konfirmasi (mode manual)
     * - status: captured → pembayaran selesai, order diproses
     */
    public function store(StorePaymentRequest $request, Order $order): JsonResponse
    {
        $user  = $request->user();
        $this->ensureOrderOwner($user, $order);
        $shift = ShiftGuard::ensureActiveShift($user);

        try {
            $data            = $request->validated();
            $data['shift_id'] = $shift?->id;

            $payment = $this->paymentService->process($order, $data, $shift?->id);

            return (new PaymentResource($payment))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\DomainException $e) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    /**
     * Kasir konfirmasi pembayaran pending (QRIS/E-Wallet/Transfer manual).
     *
     * PATCH /orders/{order}/payments/{payment}/confirm
     */
    public function confirm(Request $request, Order $order, Payment $payment): JsonResponse
    {
        $this->ensureOrderOwner($request->user(), $order);

        if ($payment->order_id !== $order->id) {
            abort(Response::HTTP_NOT_FOUND, 'Pembayaran tidak ditemukan di order ini.');
        }

        try {
            $confirmed = $this->paymentService->confirm($payment, $request->user());

            return (new PaymentResource($confirmed))->response();
        } catch (\LogicException $e) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    /**
     * Detail pembayaran.
     */
    public function show(Request $request, Payment $payment): PaymentResource
    {
        $order = $payment->order;
        $this->ensureOrderOwner($request->user(), $order);

        return new PaymentResource($payment);
    }

    protected function ensureOrderOwner($user, Order $order): void
    {
        if ($order->user_id !== $user->id) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki akses ke order ini.');
        }
    }
}
