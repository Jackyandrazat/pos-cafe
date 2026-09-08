<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PaymentAccount extends Model
{
    use HasFactory;

    protected $table = 'payment_accounts';

    protected $fillable = [
        'type',
        'provider_code',
        'name',
        'account_number',
        'account_name',
        'qr_image',
        'instructions',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'qr_image_url',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeBankTransfers(Builder $query): Builder
    {
        return $query->where('type', 'bank_transfer');
    }

    public function scopeEwallets(Builder $query): Builder
    {
        return $query->where('type', 'ewallet');
    }

    public function getQrImageUrlAttribute(): ?string
    {
        if (! $this->qr_image) {
            return null;
        }

        if (str_starts_with($this->qr_image, 'http://') || str_starts_with($this->qr_image, 'https://')) {
            return $this->qr_image;
        }

        return Storage::disk('public')->url($this->qr_image);
    }
}
