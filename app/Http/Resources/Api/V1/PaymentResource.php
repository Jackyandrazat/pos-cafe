<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'order_id' => (string) $this->order_id,
            'method' => $this->payment_method,
            'amount' => (float) $this->amount_paid,
            'change_return' => (float) ($this->change_return ?? 0),
            'status' => $this->status ?? 'captured',
            'paid_at' => optional($this->payment_date ?? $this->created_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
