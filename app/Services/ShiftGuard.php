<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class ShiftGuard
{
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
}
