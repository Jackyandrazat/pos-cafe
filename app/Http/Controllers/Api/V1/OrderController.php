<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Services\ShiftGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = $user->orders()->with(['items.product'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = min(max((int) $request->input('per_page', 10), 1), 50);

        return OrderResource::collection(
            $query->paginate($perPage)->appends($request->query())
        );
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        ShiftGuard::ensureActiveShift($user);

        $order = DB::transaction(function () use ($data, $user) {
            $order = Order::create([
                'user_id' => $user->id,
                'table_id' => $data['table_id'] ?? null,
                'order_type' => $data['order_type'],
                'customer_name' => $data['customer_name'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => OrderStatus::Draft->value,
                'subtotal_order' => 0,
                'discount_order' => 0,
                'service_fee_order' => 0,
                'total_order' => 0,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::query()
                    ->whereKey($item['menu_id'])
                    ->where('status_enabled', true)
                    ->first();

                if (! $product) {
                    abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'One or more menu items are unavailable.');
                }

                $qty = $item['quantity'];
                $discount = min($item['discount_amount'] ?? 0, $product->price);
                $effectivePrice = max($product->price - $discount, 0);
                $subtotal = $effectivePrice * $qty;

                $order->items()->create([
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'price' => $product->price,
                    'discount_amount' => $discount,
                    'subtotal' => $subtotal,
                ]);
            }

            $order->recalculateTotals();
            $order->logStatus(OrderStatus::Draft, 'Order created');

            return $order;
        });

        return (new OrderResource($order->load('items.product')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        $this->ensureOrderOwner($request->user(), $order);

        return new OrderResource($order->load('items.product'));
    }

    public function submit(Request $request, Order $order): OrderResource
    {
        $user = $request->user();
        $this->ensureOrderOwner($user, $order);
        ShiftGuard::ensureActiveShift($user);

        if ($order->status !== OrderStatus::Draft->value) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Only draft orders can be submitted.');
        }

        if ($order->items()->count() === 0) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot submit an order without items.');
        }

        $order->status = OrderStatus::Pending->value;
        $order->save();
        $order->logStatus(OrderStatus::Pending, 'Order submitted');

        return new OrderResource($order->fresh('items.product'));
    }

    public function cancel(Request $request, Order $order): OrderResource
    {
        $user = $request->user();
        $this->ensureOrderOwner($user, $order);
        ShiftGuard::ensureActiveShift($user);

        $currentStatus = (string) $order->status;

        if (! in_array($currentStatus, [OrderStatus::Draft->value, OrderStatus::Pending->value, OrderStatus::Confirmed->value], true)) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Order cannot be cancelled at this stage.');
        }

        $order->status = OrderStatus::Cancelled->value;
        $order->save();
        $order->logStatus(OrderStatus::Cancelled, 'Order cancelled by user');

        return new OrderResource($order->fresh('items.product'));
    }

    protected function ensureOrderOwner($user, Order $order): void
    {
        if ($order->user_id !== $user->id) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have access to this order.');
        }
    }
}
