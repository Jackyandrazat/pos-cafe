<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyChallengeAward extends Model
{
    use HasFactory;

    protected $fillable = [
        'loyalty_challenge_id',
        'customer_id',
        'points_awarded',
        'badge_name',
        'badge_code',
        'badge_color',
        'badge_icon',
        'meta',
        'awarded_at',
    ];

    protected $casts = [
        'points_awarded' => 'integer',
        'meta' => 'array',
        'awarded_at' => 'datetime',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(LoyaltyChallenge::class, 'loyalty_challenge_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
