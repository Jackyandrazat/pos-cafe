<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\OrderStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'order_id' => (string) $this->id,
            'current_status' => [
                'code' => (string) $this->status,
                'label' => OrderStatus::tryFrom((string) $this->status)?->label() ?? ucfirst((string) $this->status),
                'updated_at' => optional($this->updated_at)->toIso8601String(),
            ],
            'history' => $this->whenLoaded('statusLogs', function () {
                return $this->statusLogs->map(function ($log) {
                    return [
                        'code' => $log->status,
                        'label' => OrderStatus::tryFrom($log->status)?->label() ?? ucfirst($log->status),
                        'description' => $log->description,
                        'timestamp' => optional($log->created_at)->toIso8601String(),
                    ];
                });
            }),
        ];
    }
}
