<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'preferred_channel',
        'preferences',
        'points',
        'lifetime_value',
        'last_order_at',
    ];

    protected $casts = [
        'preferences' => 'array',
        'last_order_at' => 'datetime',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(CustomerPointTransaction::class);
    }

    public function addPoints(int $points, string $type, ?EloquentModel $source = null, ?string $description = null): void
    {
        $this->increment('points', $points);

        $this->pointTransactions()->create([
            'points' => $points,
            'type' => $type,
            'description' => $description,
            'source_type' => $source ? $source::class : null,
            'source_id' => $source?->getKey(),
        ]);
    }

    public function redeemPoints(int $points, string $type, ?EloquentModel $source = null, ?string $description = null): void
    {
        $this->decrement('points', $points);

        $this->pointTransactions()->create([
            'points' => -abs($points),
            'type' => $type,
            'description' => $description,
            'source_type' => $source ? $source::class : null,
            'source_id' => $source?->getKey(),
        ]);
    }
}
