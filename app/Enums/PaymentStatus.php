<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending  = 'pending';   // Menunggu konfirmasi (manual) atau notifikasi (gateway)
    case Captured = 'captured';  // Pembayaran berhasil diterima
    case Failed   = 'failed';    // Pembayaran gagal
    case Expired  = 'expired';   // Kedaluwarsa (QR/VA tidak dibayar tepat waktu)
    case Refunded = 'refunded';  // Dana dikembalikan

    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'Menunggu',
            self::Captured => 'Berhasil',
            self::Failed   => 'Gagal',
            self::Expired  => 'Kedaluwarsa',
            self::Refunded => 'Dikembalikan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending  => 'warning',
            self::Captured => 'success',
            self::Failed   => 'danger',
            self::Expired  => 'gray',
            self::Refunded => 'info',
        };
    }

    /** Apakah status ini merupakan status final (tidak bisa berubah lagi)? */
    public function isFinal(): bool
    {
        return in_array($this, [self::Captured, self::Failed, self::Expired, self::Refunded]);
    }
}
