<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * QrisConfig — Singleton model untuk konfigurasi QRIS merchant.
 *
 * Hanya satu record aktif yang digunakan. Static QRIS string disimpan
 * di DB sehingga admin bisa mengubahnya via Filament tanpa edit .env.
 */
class QrisConfig extends Model
{
    protected $table = 'qris_configs';

    protected $fillable = [
        'static_string',
        'merchant_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Ambil satu-satunya konfigurasi QRIS yang aktif.
     * Fallback ke env QRIS_STATIC_STRING jika DB kosong.
     */
    public static function active(): ?self
    {
        return static::where('is_active', true)->latest()->first();
    }

    /**
     * Ambil static QRIS string dari DB, fallback ke env/config.
     */
    public static function getStaticString(): ?string
    {
        $dbString = static::active()?->static_string;

        if ($dbString && strlen(trim($dbString)) >= 10) {
            return trim($dbString);
        }

        // Fallback ke env
        $envString = config('payment.qris_static_string', '');

        return $envString && strlen(trim($envString)) >= 10 ? trim($envString) : null;
    }

    /**
     * Apakah QRIS sudah terkonfigurasi dan siap digunakan?
     */
    public static function isConfigured(): bool
    {
        return static::getStaticString() !== null;
    }
}
