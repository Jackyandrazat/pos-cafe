<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_channel',
        'provider',
        'external_reference',
        'status',
        'meta',
        'amount_paid',
        'change_return',
        'payment_date',
        'paid_at',
        'shift_id',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'meta'         => 'array',
        'payment_date' => 'datetime',
        'paid_at'      => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relasi
    // -------------------------------------------------------------------------

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::Pending->value;
    }

    public function isCaptured(): bool
    {
        return $this->status === PaymentStatus::Captured->value;
    }

    public function needsConfirmation(): bool
    {
        return $this->isPending() && in_array($this->payment_method, ['qris', 'ewallet', 'transfer']);
    }
}
