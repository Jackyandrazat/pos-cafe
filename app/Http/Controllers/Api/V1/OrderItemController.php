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
use App\Models\Topping;
use App\Services\ShiftGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OrderItemController extends Controller
{
    public function store(StoreOrderItemRequest $request, Order $order): JsonResponse
    {
        $this->ensureEditable($request->user(), $order);
        $data = $request->validated();

        return DB::transaction(function () use ($data, $order) {
            $product = Product::query()
                ->whereKey($data['menu_id'])
                ->where('status_enabled', true)
                ->first();

            if (! $product) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Menu item is unavailable.');
            }

            // ==========================
            // HANDLE SIZE
            // ==========================
            $sizeId = $data['size_id'] ?? null;
            $size = null;
            $priceModifier = 0;

            if ($sizeId) {
                $size = $product->sizes()->where('id', $sizeId)->first();

                if (! $size) {
                    abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid size selected.');
                }

                $priceModifier = $size->price_modifier;
            }

            $basePrice = $product->price + $priceModifier;
            $discount = min($data['discount_amount'] ?? 0, $basePrice);
            $effectivePrice = max($basePrice - $discount, 0);

            // Merge berdasarkan (product_id, size_id) — bukan product_id saja
            $item = $order->items()
                ->where('product_id', $product->id)
                ->where('size_id', $size?->id)
                ->first();

            if ($item) {
                $item->qty += $data['quantity'];
                $item->discount_amount = $discount;
                $item->price = $basePrice;
                $item->subtotal = $effectivePrice * $item->qty;
                $item->save();
            } else {
                $item = $order->items()->create([
                    'product_id' => $product->id,
                    'size_id' => $size?->id,
                    'qty' => $data['quantity'],
                    'price' => $basePrice,
                    'discount_amount' => $discount,
                    'subtotal' => $effectivePrice * $data['quantity'],
                ]);
            }

            // ==========================
            // HANDLE TOPPINGS
            // ==========================
            $toppingsSubtotal = 0;
            foreach ($data['toppings'] ?? [] as $toppingData) {
                $toppingModel = Topping::whereKey($toppingData['topping_id'])
                    ->where('is_active', true)
                    ->first();

                if (! $toppingModel) {
                    abort(422, 'Invalid topping selected.');
                }

                $toppingQty = $toppingData['quantity'] ?? 1;
                $toppingTotal = $toppingModel->price * $toppingQty;
                $toppingsSubtotal += $toppingTotal;

                $item->toppings()->create([
                    'topping_id' => $toppingModel->id,
                    'name' => $toppingData['name'] ?? $toppingModel->name,
                    'quantity' => $toppingQty,
                    'price' => $toppingModel->price,
                    'total' => $toppingTotal,
                ]);
            }

            // Recalculate subtotal item jika ada topping baru
            if ($toppingsSubtotal > 0) {
                $currentToppingsTotal = $item->toppings()->sum('total');
                $item->subtotal = ($effectivePrice * $item->qty) + $currentToppingsTotal;
                $item->save();
            }

            $order->recalculateTotals();

            return response()->json([
                'data' => new OrderItemResource($item->load(['product', 'toppings'])),
            ], Response::HTTP_CREATED);
        });
    }

    public function update(UpdateOrderItemRequest $request, Order $order, OrderItem $orderItem): OrderItemResource
    {
        $this->ensureEditable($request->user(), $order);

        if ($orderItem->order_id !== $order->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return DB::transaction(function () use ($request, $order, $orderItem) {
            $data = $request->validated();
            $discount = min($data['discount_amount'] ?? $orderItem->discount_amount, $orderItem->price);
            $effectivePrice = max($orderItem->price - $discount, 0);
            $orderItem->qty = $data['quantity'];
            $orderItem->discount_amount = $discount;

            // Hitung ulang subtotal termasuk topping yang sudah ada
            $toppingsTotal = $orderItem->toppings()->sum('total');
            $orderItem->subtotal = ($effectivePrice * $orderItem->qty) + $toppingsTotal;
            $orderItem->save();

            $order->recalculateTotals();

            return new OrderItemResource($orderItem->load(['product', 'toppings']));
        });
    }

    public function destroy(Request $request, Order $order, OrderItem $orderItem): JsonResponse
    {
        $this->ensureEditable($request->user(), $order);

        if ($orderItem->order_id !== $order->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return DB::transaction(function () use ($order, $orderItem) {
            $orderItem->toppings()->delete();
            $orderItem->delete();
            $order->recalculateTotals();

            return response()->json(data: null, status: Response::HTTP_NO_CONTENT);
        });
    }

    protected function ensureEditable($user, Order $order): void
    {
        if ($order->user_id !== $user->id) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have access to this order.');
        }

        ShiftGuard::ensureActiveShift($user);

        if ($order->status !== OrderStatus::Draft->value) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Only draft orders can be modified.');
        }
    }
}
