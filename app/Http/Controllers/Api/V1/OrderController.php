<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Exceptions\GiftCardException;
use App\Exceptions\PromotionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Topping;
use App\Services\GamifiedLoyaltyService;
use App\Services\GiftCardService;
use App\Services\LoyaltyService;
use App\Services\PromotionService;
use App\Services\ShiftGuard;
use App\Support\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function __construct(
        protected GiftCardService $giftCardService,
        protected LoyaltyService $loyaltyService,
        protected GamifiedLoyaltyService $gamifiedLoyaltyService,
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = $user->orders()->with(['items.product', 'customer'])->latest();

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

        $pendingGiftCard = null;

        $order = DB::transaction(function () use ($data, $user, &$pendingGiftCard) {
            $customer = null;
            if (Feature::enabled('loyalty') && ! empty($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'customer_id' => $customer?->id,
                'table_id' => Feature::enabled('table_management') ? ($data['table_id'] ?? null) : null,
                'order_type' => $data['order_type'],
                'customer_name' => $customer?->name ?? ($data['customer_name'] ?? null),
                'notes' => $data['notes'] ?? null,
                'status' => OrderStatus::Draft->value,
                'subtotal_order' => 0,
                'discount_order' => (float) ($data['discount_order'] ?? 0),
                'promotion_id' => null,
                'promotion_code' => null,
                'promotion_discount' => 0,
                'gift_card_id' => null,
                'gift_card_code' => null,
                'gift_card_amount' => 0,
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
                $productSubtotal  = $effectivePrice * $qty;

                $orderItem = $order->items()->create([
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'price' => $product->price,
                    'discount_amount' => $discount,
                    'subtotal' => 0,
                ]);

                $toppingsSubtotal = 0;
                foreach ($item['toppings'] ?? [] as $topping) {

                    $toppingModel = Topping::whereKey($topping['topping_id'])
                        ->where('is_active', true)
                        ->first();

                    if (! $toppingModel) {
                        abort(422, 'Invalid topping selected.');
                    }

                    $toppingQty = $topping['quantity'] ?? 1;
                    $toppingSubtotal = $toppingModel->price * $toppingQty;

                    $toppingsSubtotal += $toppingSubtotal;

                    $orderItem->toppings()->create([
                        'topping_id' => $toppingModel->id,
                        'name' => $topping['name'],
                        'quantity' => $toppingQty,
                        'price' => $toppingModel->price,
                        'total' => $toppingSubtotal,
                    ]);
                }

                $totalItemSubtotal = $productSubtotal + $toppingsSubtotal;

                $orderItem->update([
                    'subtotal' => $totalItemSubtotal
                ]);
            }

            $order->recalculateTotals();

            $subtotal = $order->subtotal_order;
            $manualDiscount = max((float) ($order->discount_order ?? 0), 0);
            $remaining = max($subtotal - $manualDiscount, 0);

            if (Feature::enabled('promotions') && ! empty($data['promotion_code'])) {
                try {
                    $promotionResult = PromotionService::validateAndCalculate(
                        $data['promotion_code'],
                        $subtotal,
                        $user,
                    );
                } catch (PromotionException $e) {
                    abort(Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
                }

                if ($promotionResult) {
                    $order->promotion_id = $promotionResult['promotion']->id;
                    $order->promotion_code = $promotionResult['code'];
                    $order->promotion_discount = $promotionResult['discount'];
                    $remaining = max($remaining - $promotionResult['discount'], 0);
                }
            }

            if (Feature::enabled('gift_cards') && ! empty($data['gift_card_code'])) {
                try {
                    $giftCardResult = $this->giftCardService->prepareRedemption(
                        $data['gift_card_code'],
                        (float) ($data['gift_card_amount'] ?? 0),
                        $remaining,
                    );
                } catch (GiftCardException $e) {
                    abort(Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
                }

                if ($giftCardResult) {
                    $order->gift_card_id = $giftCardResult['gift_card']->id;
                    $order->gift_card_code = $giftCardResult['code'];
                    $order->gift_card_amount = $giftCardResult['amount'];
                    $remaining = max($remaining - $giftCardResult['amount'], 0);
                    $pendingGiftCard = $giftCardResult;
                }
            }

            $order->total_order = $remaining;
            $order->save();
            $order->logStatus(OrderStatus::Draft, 'Order created');

            return $order;
        });

        if (Feature::enabled('promotions')) {
            PromotionService::syncUsage($order);
        }

        if ($pendingGiftCard) {
            $this->giftCardService->redeemForOrder(
                $order,
                $pendingGiftCard['gift_card'],
                $pendingGiftCard['amount'],
            );
        }

        if (Feature::enabled('loyalty')) {
            $freshOrder = $order->fresh(['customer', 'items']);
            $this->loyaltyService->rewardOrderPoints($freshOrder);
            $this->gamifiedLoyaltyService->trackOrderProgress($freshOrder);
        }

        return (new OrderResource($order->load(['items.product', 'customer'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        $this->ensureOrderOwner($request->user(), $order);

        return new OrderResource($order->load(['items.product','items.toppings', 'customer']));
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
        $order->logStatus(OrderStatus::Pending, 'Order submitted at the cashier');

        return new OrderResource($order->fresh(['items.product', 'customer']));
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

        return new OrderResource($order->fresh(['items.product', 'customer']));
    }

    protected function ensureOrderOwner($user, Order $order): void
    {
        if ($order->user_id !== $user->id) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have access to this order.');
        }
    }
}
