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
        'capacity',
        'x_position',
        'y_position',
        'notes',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'x_position' => 'integer',
        'y_position' => 'integer',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function queueEntries(): HasMany
    {
        return $this->hasMany(TableQueueEntry::class, 'assigned_table_id');
    }
}
