<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class LoyaltyChallengeProgress extends Model
{
    use HasFactory;

    protected $table = 'loyalty_challenge_progress';

    protected $fillable = [
        'loyalty_challenge_id',
        'customer_id',
        'current_value',
        'window_start',
        'window_end',
        'last_progressed_at',
        'completed_at',
        'rewarded_at',
        'meta',
    ];

    protected $casts = [
        'current_value' => 'integer',
        'window_start' => 'datetime',
        'window_end' => 'datetime',
        'last_progressed_at' => 'datetime',
        'completed_at' => 'datetime',
        'rewarded_at' => 'datetime',
        'meta' => 'array',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(LoyaltyChallenge::class, 'loyalty_challenge_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getStatusAttribute(): string
    {
        if ($this->rewarded_at) {
            return 'rewarded';
        }

        if ($this->completed_at) {
            return 'completed';
        }

        return $this->current_value > 0 ? 'in_progress' : 'available';
    }

    public function resetWindow(?Carbon $start, ?Carbon $end): void
    {
        $this->current_value = 0;
        $this->completed_at = null;
        $this->rewarded_at = null;
        $this->window_start = $start;
        $this->window_end = $end;
        $this->meta = null;
    }
}
