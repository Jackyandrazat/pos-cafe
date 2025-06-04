<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'order_type',
        'customer_name',
        'status',
        'subtotal_order',
        'discount_order',
        'total_order',
    ];

    // Relasi ke meja
    public function table()
    {
        return $this->belongsTo(CafeTable::class, 'table_id');
    }

    // Relasi ke order items
    public function order_items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    // Relasi ke payment
    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id');
    }
}
