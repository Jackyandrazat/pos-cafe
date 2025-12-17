<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItemTopping extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'topping_id',
        'name',
        'price',
        'quantity',
        'total',
    ];

    protected $casts = [
        'price' => 'integer',
        'quantity' => 'integer',
        'total' => 'integer',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function topping()
    {
        return $this->belongsTo(Topping::class);
    }
}
