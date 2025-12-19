<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'stock_qty', 'unit', 'price_per_unit', 'expired',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_ingredients')
            ->withPivot('quantity_used', 'unit')
            ->withTimestamps();
    }

     // relasi hasMany ke model pivot (untuk keperluan repeater di Filament)
    public function productIngredients()
    {
        return $this->hasMany(ProductIngredient::class);
    }

    public function wastes()
    {
        return $this->hasMany(IngredientWaste::class);
    }
}
