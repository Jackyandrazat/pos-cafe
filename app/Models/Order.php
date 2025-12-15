<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'table_id',
        'order_type',
        'customer_name',
        'status',
        'subtotal_order',
        'discount_order',
        'service_fee_order',
        'total_order',
        'notes',
    ];

    protected $casts = [];

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

    public function items()
    {
        return $this->order_items();
    }

    // Relasi ke payment
    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('created_at');
    }

    public function logStatus(OrderStatus $status, ?string $description = null): void
    {
        $this->statusLogs()->create([
            'status' => $status->value,
            'description' => $description,
        ]);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $discount = (float) ($this->getAttribute('discount_order') ?? $this->getAttribute('discount_amount') ?? 0);
        $serviceFee = (float) ($this->getAttribute('service_fee_order') ?? 0);

        if (array_key_exists('subtotal_order', $this->attributes)) {
            $this->setAttribute('subtotal_order', $subtotal);
        } else {
            $this->setAttribute('subtotal', $subtotal);
        }

        $grandTotal = $subtotal - $discount + $serviceFee;

        if (array_key_exists('total_order', $this->attributes)) {
            $this->setAttribute('total_order', $grandTotal);
        } else {
            $this->setAttribute('total', $grandTotal);
        }

        $this->save();
    }
}
