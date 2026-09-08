<?php

namespace App\Models;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'shift_open_time', 'shift_close_time', 'opening_balance', 'closing_balance', 'total_sales', 'notes'
    ];

    protected $casts = [
        'shift_open_time'  => 'datetime',
        'shift_close_time' => 'datetime',
        'opening_balance'  => 'float',
        'closing_balance'  => 'float',
        'total_sales'      => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(Payment::class, 'shift_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'shift_id');
    }

    public function getStatusAttribute(): string
    {
        return $this->shift_close_time === null ? 'open' : 'closed';
    }

    public function isOpen(): bool
    {
        return $this->shift_close_time === null;
    }

    public function isClosed(): bool
    {
        return $this->shift_close_time !== null;
    }

    public function scopeOpen($query)
    {
        return $query->whereNull('shift_close_time');
    }

    public function scopeClosed($query)
    {
        return $query->whereNotNull('shift_close_time');
    }

}
