<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'amount_paid',
        'change_return',
        'payment_date',
        'shift_id',
    ];

    // Relasi ke order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // Relasi ke shift
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

}
