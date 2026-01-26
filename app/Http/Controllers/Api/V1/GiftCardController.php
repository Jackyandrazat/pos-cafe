<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GiftCardController extends Controller
{
    public function validateGiftCard(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'subtotal' => ['nullable', 'numeric', 'min:0'], // optional
        ]);

        $code = trim($request->code);
        $subtotal = (int) ($request->subtotal ?? 0);

        $card = GiftCard::query()
            ->where('code', $code)
            ->first();

        if (!$card) {
            return response()->json(['message' => 'Gift Card tidak ditemukan'], 422);
        }

        if ($card->status !== 'active') {
            return response()->json(['message' => 'Gift Card tidak aktif'], 422);
        }

        // cek expired by date
        if ($card->expires_at && Carbon::now()->gt($card->expires_at)) {
            // opsional: update status otomatis
            $card->update(['status' => 'expired']);

            return response()->json(['message' => 'Gift Card sudah expired'], 422);
        }

        if ($card->balance <= 0) {
            // opsional: update status otomatis
            $card->update(['status' => 'used_up']);

            return response()->json(['message' => 'Saldo Gift Card habis'], 422);
        }

        $appliedAmount = $subtotal > 0 ? min($card->balance, $subtotal) : $card->balance;

        return response()->json([
            'data' => [
                'code' => $card->code,
                'type' => $card->type,
                'balance' => (int) $card->balance,
                'applied_amount' => (int) $appliedAmount,
                'expires_at' => optional($card->expires_at)->toDateTimeString(),
            ],
        ]);
    }
}
