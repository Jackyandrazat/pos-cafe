<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class SizeMenuResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    protected float $basePrice;

    public function __construct($resource, float $basePrice = 0)
    {
        parent::__construct($resource);
        $this->basePrice = $basePrice;
    }

    public function toArray($request): array
    {
        $finalPrice = $this->basePrice + $this->price_modifier;

        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'price_modifier' => (float) $this->price_modifier,
            'final_price' => (float) $finalPrice,
        ];
    }
}
