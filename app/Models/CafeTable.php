<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CafeTable extends Model
{
    /** @use HasFactory<\Database\Factories\CafeTableFactory> */
    use HasFactory;
    use SoftDeletes;
    protected $table = 'tables';

    protected $fillable = ['area_id', 'table_number', 'status'];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
