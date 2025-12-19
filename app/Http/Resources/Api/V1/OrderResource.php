<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\OrderStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        $subtotal = $this->subtotal_order ?? $this->subtotal ?? 0;
        $manualDiscount = $this->discount_order ?? $this->discount_amount ?? 0;
        $promotionDiscount = $this->promotion_discount ?? 0;
        $giftCardAmount = $this->gift_card_amount ?? 0;
        $serviceFee = $this->service_fee_order ?? 0;
        $total = $this->total_order ?? $this->total ?? ($subtotal - $manualDiscount - $promotionDiscount - $giftCardAmount + $serviceFee);

        return [
            'id' => (string) $this->id,
            'status' => $this->status instanceof OrderStatus ? $this->status->value : $this->status,
            'table_id' => $this->table_id,
            'order_type' => $this->order_type,
            'customer_name' => $this->customer_name,
            'notes' => $this->notes,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => (string) $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
            ]),
            'promotion' => [
                'code' => $this->promotion_code,
                'discount' => (float) $promotionDiscount,
            ],
            'gift_card' => [
                'code' => $this->gift_card_code,
                'amount' => (float) $giftCardAmount,
            ],
            'totals' => [
                'subtotal' => (float) $subtotal,
                'manual_discount' => (float) $manualDiscount,
                'promotion_discount' => (float) $promotionDiscount,
                'gift_card_amount' => (float) $giftCardAmount,
                'service_fee' => (float) $serviceFee,
                'grand_total' => (float) $total,
                'currency' => config('app.currency', 'IDR'),
            ],
            'items' => OrderItemResource::collection(
                $this->whenLoaded('items', fn () => $this->items, collect())
            ),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
