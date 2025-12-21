<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePaymentRequest;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\ShiftGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function __construct(protected PaymentGatewayManager $paymentGatewayManager)
    {
    }

    public function index(Request $request, Order $order): AnonymousResourceCollection
    {
        $this->ensureOrderOwner($request->user(), $order);

        return PaymentResource::collection($order->payments()->latest()->get());
    }

    public function store(StorePaymentRequest $request, Order $order): JsonResponse
    {
        $user = $request->user();
        $this->ensureOrderOwner($user, $order);
        $shift = ShiftGuard::ensureActiveShift($user);

        $data = $request->validated();

        if ($order->status === OrderStatus::Cancelled->value) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot add payments to a cancelled order.');
        }

        $captured = (float) $order->payments()->where('status', 'captured')->sum('amount_paid');
        $grandTotal = (float) ($order->total_order ?? 0);
        $due = max($grandTotal - $captured, 0);

        if ($due <= 0) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Order already fully paid.');
        }

        $amount = (float) $data['amount'];
        $method = $data['payment_method'];

        if ($method !== 'cash' && $amount > $due) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Amount exceeds outstanding balance.');
        }

        if (in_array($method, ['qris', 'ewallet'], true)) {
            $charge = $this->paymentGatewayManager->createCharge($method, $order, $amount);

            $payment = $order->payments()->create([
                'payment_method' => $method,
                'provider' => $charge['provider'],
                'external_reference' => $charge['reference'],
                'status' => $charge['status'],
                'meta' => $charge['payload'],
                'amount_paid' => $amount,
                'change_return' => 0,
                'payment_date' => now(),
                'paid_at' => null,
                'shift_id' => $shift?->id,
            ]);
        } else {
            $change = $method === 'cash' ? max($amount - $due, 0) : 0;

            $payment = $order->payments()->create([
                'payment_method' => $method,
                'provider' => 'manual',
                'external_reference' => null,
                'status' => 'captured',
                'meta' => null,
                'amount_paid' => $amount,
                'change_return' => $change,
                'payment_date' => now(),
                'paid_at' => now(),
                'shift_id' => $shift?->id,
            ]);

            $captured += $amount;

            if (($grandTotal - $captured) <= 0 && $order->status !== OrderStatus::Completed->value) {
                $order->status = OrderStatus::Completed->value;
                $order->save();
                $order->logStatus(OrderStatus::Completed, 'Order fully paid');
            }
        }

        return (new PaymentResource($payment))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, Payment $payment): PaymentResource
    {
        $order = $payment->order;
        $this->ensureOrderOwner($request->user(), $order);

        return new PaymentResource($payment);
    }

    protected function ensureOrderOwner($user, Order $order): void
    {
        if ($order->user_id !== $user->id) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have access to this order.');
        }
    }
}
