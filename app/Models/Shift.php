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

}
