<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CafeTable extends Model
{
    /** @use HasFactory<\Database\Factories\CafeTableFactory> */
    use HasFactory;
    use SoftDeletes;
    protected $table = 'tables';

    protected $fillable = [
        'area_id',
        'table_number',
        'status',
        'calling_waiter',
        'waiter_call_reason',
        'waiter_call_notes',
        'waiter_called_at',
        'capacity',
        'x_position',
        'y_position',
        'notes',
    ];

    protected $casts = [
        'calling_waiter' => 'boolean',
        'waiter_called_at' => 'datetime',
        'capacity' => 'integer',
        'x_position' => 'integer',
        'y_position' => 'integer',
    ];

    public function scopeCallingWaiter($query)
    {
        return $query->where('calling_waiter', true);
    }

    public function callWaiter(string $reason = 'bantuan', ?string $notes = null): self
    {
        $this->update([
            'calling_waiter' => true,
            'waiter_call_reason' => $reason,
            'waiter_call_notes' => $notes,
            'waiter_called_at' => now(),
        ]);

        return $this;
    }

    public function dismissWaiterCall(): self
    {
        $this->update([
            'calling_waiter' => false,
            'waiter_call_reason' => null,
            'waiter_call_notes' => null,
            'waiter_called_at' => null,
        ]);

        return $this;
    }

    public function getWaiterCallReasonLabel(): string
    {
        return match ($this->waiter_call_reason) {
            'minta_bill' => 'Minta Bill / Tagihan',
            'air_es' => 'Minta Air / Es Batu',
            'alat_makan' => 'Alat Makan / Tisu',
            'bantuan' => 'Bantuan Pelayan',
            default => $this->waiter_call_reason ? ucfirst($this->waiter_call_reason) : 'Bantuan Pelayan',
        };
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function queueEntries(): HasMany
    {
        return $this->hasMany(TableQueueEntry::class, 'assigned_table_id');
    }
}
