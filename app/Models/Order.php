<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Support\Feature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'table_id',
        'order_type',
        'customer_name',
        'status',
        'subtotal_order',
        'discount_order',
        'promotion_id',
        'promotion_code',
        'promotion_discount',
        'gift_card_id',
        'gift_card_code',
        'gift_card_amount',
        'service_fee_order',
        'total_order',
        'notes',
    ];

    protected $casts = [
        'subtotal_order' => 'float',
        'discount_order' => 'float',
        'promotion_discount' => 'float',
        'gift_card_amount' => 'float',
        'service_fee_order' => 'float',
        'total_order' => 'float',
    ];

    protected static function booted(): void
    {
        if (! Feature::enabled('table_management')) {
            return;
        }

        static::created(function (self $order) {
            if ($order->order_type === 'dine_in' && $order->table_id) {
                CafeTable::whereKey($order->table_id)->update(['status' => 'occupied']);
            }
        });

        static::updated(function (self $order) {
            if ($order->order_type === 'dine_in' && $order->wasChanged('status') && $order->table_id) {
                match ($order->status) {
                    'completed' => CafeTable::whereKey($order->table_id)->update(['status' => 'cleaning']),
                    'cancelled' => CafeTable::whereKey($order->table_id)->update(['status' => 'available']),
                    default => null,
                };
            }
        });
    }

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

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class);
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
        $promoDiscount = (float) ($this->getAttribute('promotion_discount') ?? 0);
        $serviceFee = (float) ($this->getAttribute('service_fee_order') ?? 0);

        if (array_key_exists('subtotal_order', $this->attributes)) {
            $this->setAttribute('subtotal_order', $subtotal);
        } else {
            $this->setAttribute('subtotal', $subtotal);
        }

        $grandTotal = max($subtotal - $discount - $promoDiscount + $serviceFee, 0);

        if (array_key_exists('total_order', $this->attributes)) {
            $this->setAttribute('total_order', $grandTotal);
        } else {
            $this->setAttribute('total', $grandTotal);
        }

        $this->save();
    }
}
