<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\LoyaltyChallengeType;
use Illuminate\Http\Resources\Json\JsonResource;

class LoyaltyChallengeResource extends JsonResource
{
    public function toArray($request): array
    {
        $progress = $this->resource->relationLoaded('progresses') ? $this->progresses->first() : null;
        $target = max((int) $this->target_value, 1);
        $current = (int) ($progress?->current_value ?? 0);
        $percentage = (int) min(100, round(($current / $target) * 100));

        $latestNewProducts = null;
        if ($progress && is_array($progress->meta)) {
            $latestNewProducts = $progress->meta['latest_new_products'] ?? null;
        }

        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type instanceof LoyaltyChallengeType ? $this->type->value : $this->type,
            'description' => $this->description,
            'goal' => $target,
            'reset_period' => $this->reset_period,
            'config' => $this->config ?? [],
            'reward' => [
                'points' => (int) $this->bonus_points,
                'badge_name' => $this->badge_name,
                'badge_code' => $this->badge_code,
                'badge_color' => $this->badge_color,
                'badge_icon' => $this->badge_icon,
            ],
            'progress' => [
                'current' => $current,
                'percentage' => $percentage,
                'status' => $progress?->status ?? 'available',
                'completed_at' => optional($progress?->completed_at)->toIso8601String(),
                'rewarded_at' => optional($progress?->rewarded_at)->toIso8601String(),
                'window_start' => optional($progress?->window_start)->toIso8601String(),
                'window_end' => optional($progress?->window_end)->toIso8601String(),
                'latest_new_products' => $latestNewProducts,
            ],
            'active_period' => [
                'from' => optional($this->active_from)->toIso8601String(),
                'until' => optional($this->active_until)->toIso8601String(),
            ],
        ];
    }
}
