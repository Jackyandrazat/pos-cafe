<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Guest extends Model
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'phone',
        'table_number',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
