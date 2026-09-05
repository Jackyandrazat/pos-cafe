<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ToppingIngredient extends Model
{
    use SoftDeletes;

    protected $fillable = ['topping_id', 'ingredient_id', 'quantity_used', 'unit'];

    public function topping()
    {
        return $this->belongsTo(Topping::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
