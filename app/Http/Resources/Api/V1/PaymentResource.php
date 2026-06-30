<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => (string) $this->id,
            'order_id'           => (string) $this->order_id,
            'method'             => $this->payment_method,
            'channel'            => $this->payment_channel,
            'provider'           => $this->provider,
            'reference'          => $this->external_reference,
            'amount'             => (float) $this->amount_paid,
            'change_return'      => (float) ($this->change_return ?? 0),
            'status'             => $this->status ?? 'captured',
            'instructions'       => $this->meta,
            // Shortcut untuk QRIS — null jika bukan payment QRIS atau QR belum di-generate
            'qr_svg'             => $this->payment_method === 'qris' ? ($this->meta['qr_svg'] ?? null) : null,
            'qris_string'        => $this->payment_method === 'qris' ? ($this->meta['qris_string'] ?? null) : null,
            'needs_confirmation' => $this->whenNotNull($this->payment_method !== 'cash' ? $this->isPending() : null),
            'confirmed_by'       => $this->whenNotNull($this->confirmed_by),
            'confirmed_at'       => optional($this->confirmed_at)->toIso8601String(),
            'paid_at'            => optional($this->paid_at)->toIso8601String(),
            'payment_date'       => optional($this->payment_date ?? $this->created_at)->toIso8601String(),
            'created_at'         => optional($this->created_at)->toIso8601String(),
            'updated_at'         => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
