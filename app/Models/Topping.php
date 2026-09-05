<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Topping extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function ingredients()
    {
        return $this->hasMany(ToppingIngredient::class);
    }

    public function hasSufficientStock(int $qty = 1): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $toppingIngredients = $this->relationLoaded('ingredients')
            ? $this->ingredients
            : $this->ingredients()->with('ingredient')->get();

        if ($toppingIngredients->isNotEmpty()) {
            foreach ($toppingIngredients as $ti) {
                $ingredient = $ti->relationLoaded('ingredient') ? $ti->ingredient : $ti->ingredient()->first();
                if (! $ingredient) {
                    continue;
                }
                $needed = (float) $ti->quantity_used * $qty;
                if ((float) $ingredient->stock_qty < $needed) {
                    return false;
                }
            }
        }

        return true;
    }
}
