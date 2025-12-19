<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GiftCardTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'gift_card_id',
        'type',
        'amount',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_after' => 'float',
    ];

    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
