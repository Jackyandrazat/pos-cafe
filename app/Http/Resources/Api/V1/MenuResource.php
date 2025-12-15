<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        $category = $this->whenLoaded('category');

        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => [
                'amount' => (float) $this->price,
                'currency' => config('app.currency', 'IDR'),
                'formatted' => number_format((float) $this->price, 0, '.', ','),
            ],
            'category' => $category ? [
                'id' => (string) $category->id,
                'name' => $category->name,
            ] : null,
            'is_available' => (bool) $this->status_enabled,
            'image_url' => $this->image_url ?? null,
            'modifiers' => [],
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
