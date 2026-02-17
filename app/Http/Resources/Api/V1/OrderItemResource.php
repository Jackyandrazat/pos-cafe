<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\ToppingResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $product = $this->whenLoaded('product');

        return [
            'id' => (string) $this->id,
            'product_id' => (string) $this->product_id,
            'product_name' => $product?->name,
            'quantity' => (int) $this->qty,
            'unit_price' => (float) $this->price,
            'discount_amount' => (float) ($this->discount_amount ?? 0),
            'subtotal' => (float) $this->subtotal,
            'toppings' => ToppingResource::collection(
                $this->whenLoaded('toppings')
            ),
        ];
    }
}
