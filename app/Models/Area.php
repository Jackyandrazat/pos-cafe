<?php

namespace App\Models;

use App\Models\CafeTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Area extends Model
{
    /** @use HasFactory<\Database\Factories\AreaFactory> */
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['name', 'description', 'status_enabled'];

    public function tables(): HasMany
    {
        return $this->hasMany(CafeTable::class);
    }
}
