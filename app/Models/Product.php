<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
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
}
