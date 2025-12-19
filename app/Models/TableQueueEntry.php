<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableQueueEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_name',
        'party_size',
        'contact',
        'status',
        'estimated_wait_minutes',
        'assigned_table_id',
        'notes',
        'check_in_at',
        'seated_at',
    ];

    protected $casts = [
        'party_size' => 'integer',
        'estimated_wait_minutes' => 'integer',
        'check_in_at' => 'datetime',
        'seated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $entry) {
            $entry->check_in_at ??= now();
        });
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(CafeTable::class, 'assigned_table_id');
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }
}
