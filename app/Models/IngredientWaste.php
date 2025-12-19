<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientWaste extends Model
{
    protected $fillable = [
        'ingredient_id',
        'user_id',
        'shift_id',
        'quantity',
        'unit',
        'reason',
        'notes',
        'recorded_at',
    ];

    protected $casts = [
        'quantity' => 'float',
        'recorded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (self $waste) {
            $waste->adjustStock(-1 * $waste->quantity, $waste->ingredient_id);
        });

        static::updated(function (self $waste) {
            $originalQuantity = $waste->getOriginal('quantity');
            $originalIngredient = $waste->getOriginal('ingredient_id');

            if ($originalIngredient) {
                $waste->adjustStock($originalQuantity, $originalIngredient);
            }

            $waste->adjustStock(-1 * $waste->quantity, $waste->ingredient_id);
        });

        static::deleted(function (self $waste) {
            $waste->adjustStock($waste->quantity, $waste->ingredient_id);
        });
    }

    protected function adjustStock(?float $amount, ?int $ingredientId): void
    {
        if (! $amount || ! $ingredientId) {
            return;
        }

        $ingredient = Ingredient::find($ingredientId);

        if (! $ingredient) {
            return;
        }

        if ($amount > 0) {
            $ingredient->increment('stock_qty', $amount);
        } else {
            $ingredient->decrement('stock_qty', abs($amount));
        }
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
