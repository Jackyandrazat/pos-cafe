<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class LoyaltyBadgeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'badge_name' => $this->badge_name,
            'badge_code' => $this->badge_code,
            'badge_color' => $this->badge_color,
            'badge_icon' => $this->badge_icon,
            'points_awarded' => (int) $this->points_awarded,
            'awarded_at' => optional($this->awarded_at)->toIso8601String(),
            'meta' => $this->meta ?? [],
            'challenge' => $this->whenLoaded('challenge', fn () => [
                'id' => (string) $this->challenge->id,
                'name' => $this->challenge->name,
                'slug' => $this->challenge->slug,
            ]),
        ];
    }
}
