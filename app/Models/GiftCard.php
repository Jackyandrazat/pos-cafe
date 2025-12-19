<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiftCard extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_EXHAUSTED = 'exhausted';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'code',
        'type',
        'status',
        'initial_value',
        'balance',
        'currency',
        'activated_at',
        'expires_at',
        'last_used_at',
        'issued_to_name',
        'issued_to_email',
        'company_name',
        'company_contact',
        'notes',
    ];

    protected $casts = [
        'initial_value' => 'float',
        'balance' => 'float',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $giftCard) {
            $giftCard->code = strtoupper($giftCard->code);
            if (! $giftCard->activated_at) {
                $giftCard->activated_at = now();
            }

            if ($giftCard->balance === null) {
                $giftCard->balance = $giftCard->initial_value ?? 0;
            }
        });

        static::updating(function (self $giftCard) {
            $giftCard->code = strtoupper($giftCard->code);
        });
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(GiftCardTransaction::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function availableBalance(): float
    {
        return max((float) $this->balance, 0);
    }

    public function isRedeemable(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->expires_at && now()->greaterThan($this->expires_at)) {
            return false;
        }

        return $this->availableBalance() > 0;
    }
}
