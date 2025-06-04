<?php

namespace App\Models;

use App\Models\User;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['user_id', 'invoice_number', 'purchase_date', 'total_amount', 'notes'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
