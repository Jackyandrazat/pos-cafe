<?php

namespace App\Models;

use App\Enums\LoyaltyChallengeType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class LoyaltyChallenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'badge_name',
        'badge_code',
        'badge_color',
        'badge_icon',
        'description',
        'target_value',
        'bonus_points',
        'reset_period',
        'is_active',
        'config',
        'active_from',
        'active_until',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'active_from' => 'datetime',
        'active_until' => 'datetime',
        'target_value' => 'integer',
        'bonus_points' => 'integer',
        'type' => LoyaltyChallengeType::class,
    ];

    public function progresses(): HasMany
    {
        return $this->hasMany(LoyaltyChallengeProgress::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(LoyaltyChallengeAward::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $builder) use ($now) {
                $builder->whereNull('active_from')->orWhere('active_from', '<=', $now);
            })
            ->where(function (Builder $builder) use ($now) {
                $builder->whereNull('active_until')->orWhere('active_until', '>=', $now);
            });
    }

    public function isCurrentlyActive(Carbon $reference = null): bool
    {
        $reference ??= Carbon::now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->active_from && $reference->lt($this->active_from)) {
            return false;
        }

        if ($this->active_until && $reference->gt($this->active_until)) {
            return false;
        }

        return true;
    }

    public function currentWindowBounds(?Carbon $reference = null): array
    {
        $reference ??= Carbon::now();

        return match ($this->reset_period) {
            'weekly' => [$reference->copy()->startOfWeek(), $reference->copy()->endOfWeek()],
            'monthly' => [$reference->copy()->startOfMonth(), $reference->copy()->endOfMonth()],
            default => [null, null],
        };
    }
}
