<?php

namespace App\Models;

use App\Models\ProductSize;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItemTopping> $toppings
 */

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'size_id',
        'qty',
        'price',
        'discount_amount',
        'subtotal',
    ];

    // Relasi ke order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // Relasi ke produk
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function toppings()
    {
        return $this->hasMany(OrderItemTopping::class);
    }
    public function size()
    {
        return $this->belongsTo(ProductSize::class);
    }
}
