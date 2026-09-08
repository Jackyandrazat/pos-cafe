<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class ShiftGuard
{
    /**
     * Pastikan kasir memiliki shift yang sedang aktif.
     * Hanya berlaku untuk kasir (POS). Customer self-order tidak dibatasi oleh shift kasir.
     */
    public static function ensureActiveShift(User $user): ?Shift
    {
        if (! $user->hasRole('kasir')) {
            return null;
        }

        $shift = $user->activeShift();

        if (! $shift) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Kasir harus membuka shift sebelum melakukan transaksi.');
        }

        return $shift;
    }

    /**
     * Pastikan kafe memiliki shift yang sedang aktif untuk transaksi self-order.
     */
    public static function ensureStoreHasActiveShift(): Shift
    {
        $shift = self::getOpenStoreShift();

        if (! $shift) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Kasir belum membuka shift. Silakan hubungi staf kafe sebelum melakukan pembayaran.');
        }

        return $shift;
    }

    /**
     * Dapatkan shift yang valid untuk konfirmasi pembayaran oleh kasir/admin.
     */
    public static function getActiveShiftForConfirmation(User $user): Shift
    {
        // 1. Cek apakah user yang mengonfirmasi punya shift aktif sendiri
        $shift = $user->activeShift();

        // 2. Jika user adalah kasir dan belum membuka shift sendiri
        if (! $shift && $user->hasRole('kasir')) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Kasir harus membuka shift terlebih dahulu sebelum mengonfirmasi pembayaran.');
        }

        // 3. Jika user adalah admin/owner dan tidak punya shift pribadi, gunakan shift kafe yang sedang buka
        if (! $shift) {
            $shift = self::getOpenStoreShift();
        }

        // 4. Jika tetap tidak ada shift yang buka sama sekali di kafe
        if (! $shift) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Shift kasir belum dibuka. Buka shift kasir terlebih dahulu sebelum mengonfirmasi pembayaran.');
        }

        return $shift;
    }

    /**
     * Cari shift yang sedang terbuka saat ini di kafe.
     */
    public static function getOpenStoreShift(): ?Shift
    {
        return Shift::open()->latest('shift_open_time')->first();
    }
}
