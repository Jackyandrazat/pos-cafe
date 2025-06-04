<?php

namespace App\Models;

use App\Models\Purchase;
use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = ['purchase_id', 'ingredient_id', 'quantity', 'price_per_unit', 'unit'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
