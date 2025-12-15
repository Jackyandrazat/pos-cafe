<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreOrderItemRequest;
use App\Http\Requests\Api\V1\UpdateOrderItemRequest;
use App\Http\Resources\Api\V1\OrderItemResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderItemController extends Controller
{
    public function store(StoreOrderItemRequest $request, Order $order): JsonResponse
    {
        $this->ensureEditable($request->user(), $order);
        $data = $request->validated();

        $product = Product::query()
            ->whereKey($data['menu_id'])
            ->where('status_enabled', true)
            ->first();

        if (! $product) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Menu item is unavailable.');
        }
        $item = $order->items()->where('product_id', $product->id)->first();
        $discount = min($data['discount_amount'] ?? 0, $product->price);
        $effectivePrice = max($product->price - $discount, 0);

        if ($item) {
            $item->qty += $data['quantity'];
            $item->discount_amount = $discount;
            $item->subtotal = $effectivePrice * $item->qty;
            $item->save();
        } else {
            $item = $order->items()->create([
                'product_id' => $product->id,
                'qty' => $data['quantity'],
                'price' => $product->price,
                'discount_amount' => $discount,
                'subtotal' => $effectivePrice * $data['quantity'],
            ]);
        }

        $order->recalculateTotals();

        return response()->json([
            'data' => new OrderItemResource($item->load('product')),
        ], Response::HTTP_CREATED);
    }

    public function update(UpdateOrderItemRequest $request, Order $order, OrderItem $orderItem): OrderItemResource
    {
        $this->ensureEditable($request->user(), $order);

        if ($orderItem->order_id !== $order->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $data = $request->validated();
        $discount = min($data['discount_amount'] ?? $orderItem->discount_amount, $orderItem->price);
        $effectivePrice = max($orderItem->price - $discount, 0);
        $orderItem->qty = $data['quantity'];
        $orderItem->discount_amount = $discount;
        $orderItem->subtotal = $effectivePrice * $orderItem->qty;
        $orderItem->save();

        $order->recalculateTotals();

        return new OrderItemResource($orderItem->load('product'));
    }

    public function destroy(Request $request, Order $order, OrderItem $orderItem): JsonResponse
    {
        $this->ensureEditable($request->user(), $order);

        if ($orderItem->order_id !== $order->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $orderItem->delete();
        $order->recalculateTotals();

        return response()->json(data: null, status: Response::HTTP_NO_CONTENT);
    }

    protected function ensureEditable($user, Order $order): void
    {
        if ($order->user_id !== $user->id) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have access to this order.');
        }

        if ($order->status !== OrderStatus::Draft->value) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Only draft orders can be modified.');
        }
    }
}
