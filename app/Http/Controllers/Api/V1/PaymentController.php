<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePaymentRequest;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function index(Request $request, Order $order): AnonymousResourceCollection
    {
        $this->ensureOrderOwner($request->user(), $order);

        return PaymentResource::collection($order->payments()->latest()->get());
    }

    public function store(StorePaymentRequest $request, Order $order): JsonResponse
    {
        $this->ensureOrderOwner($request->user(), $order);

        $data = $request->validated();

        if ($order->status === OrderStatus::Cancelled->value) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot add payments to a cancelled order.');
        }

        $paid = (float) $order->payments()->sum('amount_paid');
        $grandTotal = (float) ($order->total_order ?? 0);
        $due = max($grandTotal - $paid, 0);

        if ($due <= 0) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Order already fully paid.');
        }

        $amount = (float) $data['amount'];

        if ($data['payment_method'] !== 'cash' && $amount > $due) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Amount exceeds outstanding balance.');
        }

        $change = $data['payment_method'] === 'cash' ? max($amount - $due, 0) : 0;

        $payment = $order->payments()->create([
            'payment_method' => $data['payment_method'],
            'amount_paid' => $amount,
            'change_return' => $change,
            'payment_date' => now(),
        ]);

        if (($due - $amount) <= 0 && $order->status !== OrderStatus::Completed->value) {
            $order->status = OrderStatus::Completed->value;
            $order->save();
            $order->logStatus(OrderStatus::Completed, 'Order fully paid');
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
