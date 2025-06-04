<?php

namespace App\Models;

use App\Models\Product;
use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductIngredient extends Model
{
    use SoftDeletes;

    protected $fillable = ['product_id', 'ingredient_id', 'quantity_used', 'unit'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
