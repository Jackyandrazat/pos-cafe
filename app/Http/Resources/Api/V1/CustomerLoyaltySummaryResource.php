<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerLoyaltySummaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'customer' => $this['customer'],
            'points' => $this['points'],
            'challenges' => $this->transformNested($this['challenges'], $request),
            'recent_badges' => $this->transformNested($this['recent_badges'], $request),
        ];
    }

    protected function transformNested($value, $request)
    {
        return $value instanceof JsonResource ? $value->toArray($request) : $value;
    }
}
