<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\OrderStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        $subtotal = $this->subtotal_order ?? $this->subtotal ?? 0;
        $discount = $this->discount_order ?? $this->discount_amount ?? 0;
        $serviceFee = $this->service_fee_order ?? 0;
        $total = $this->total_order ?? $this->total ?? ($subtotal - $discount + $serviceFee);

        return [
            'id' => (string) $this->id,
            'status' => $this->status instanceof OrderStatus ? $this->status->value : $this->status,
            'table_id' => $this->table_id,
            'order_type' => $this->order_type,
            'customer_name' => $this->customer_name,
            'notes' => $this->notes,
            'totals' => [
                'subtotal' => (float) $subtotal,
                'discount' => (float) $discount,
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
