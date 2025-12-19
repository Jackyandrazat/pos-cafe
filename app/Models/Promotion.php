<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'discount_value',
        'max_discount',
        'min_subtotal',
        'usage_limit',
        'usage_limit_per_user',
        'starts_at',
        'ends_at',
        'is_active',
        'description',
    ];

    protected $casts = [
        'discount_value' => 'float',
        'max_discount' => 'float',
        'min_subtotal' => 'float',
        'usage_limit' => 'integer',
        'usage_limit_per_user' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isCurrentlyValid(): bool
    {
        $now = now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $now->lt($this->starts_at)) {
            dd('here');
            return false;
        }

        $endsAt = $this->ends_at?->copy();

        if ($endsAt && $endsAt->isStartOfDay()) {
            dd('there');
            $endsAt = $endsAt->endOfDay();
        }

        if ($endsAt && $now->gt($endsAt)) {
            dd('everywhere');
            return false;
        }

        return true;
    }
}
