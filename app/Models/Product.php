<?php

namespace App\Models;

use App\Models\ProductSize;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;
    protected $fillable = ['category_id', 'name', 'sku', 'price', 'cost_price', 'stock_qty', 'description', 'status_enabled'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // public function ingredients()
    // {
    //     return $this->belongsToMany(Ingredient::class, 'product_ingredients')
    //         ->withPivot('quantity_used', 'unit')
    //         ->withTimestamps();
    // }

    public function ingredients()
    {
        return $this->hasMany(ProductIngredient::class);
    }
    public function toppings()
    {
        return $this->belongsToMany(Topping::class);
    }
    public function sizes()
    {
        return $this->hasMany(ProductSize::class);
    }

    public function hasSufficientStock(int $qty = 1): bool
    {
        if (! $this->status_enabled) {
            return false;
        }

        $productIngredients = $this->relationLoaded('ingredients')
            ? $this->ingredients
            : $this->ingredients()->with('ingredient')->get();

        if ($productIngredients->isNotEmpty()) {
            foreach ($productIngredients as $pi) {
                $ingredient = $pi->relationLoaded('ingredient') ? $pi->ingredient : $pi->ingredient()->first();
                if (! $ingredient) {
                    continue;
                }
                $needed = (float) $pi->quantity_used * $qty;
                if ((float) $ingredient->stock_qty < $needed) {
                    return false;
                }
            }
        } elseif ($this->stock_qty !== null && (int) $this->stock_qty < $qty) {
            return false;
        }

        return true;
    }
     public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('product')
            ->singleFile();
    }

    /**
     * THUMBNAIL CONVERSION
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->fit(Fit::Crop, 300, 300)
            ->nonQueued();
        $this
            ->addMediaConversion('web')
            ->width(1200)
            ->quality(80);
    }
}
