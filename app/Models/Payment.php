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
        'provider',
        'external_reference',
        'status',
        'meta',
        'amount_paid',
        'change_return',
        'payment_date',
        'paid_at',
        'shift_id',
    ];

    protected $casts = [
        'meta' => 'array',
        'payment_date' => 'datetime',
        'paid_at' => 'datetime',
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
