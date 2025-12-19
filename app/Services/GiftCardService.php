<?php

namespace App\Services;

use App\Exceptions\GiftCardException;
use App\Models\GiftCard;
use App\Models\GiftCardTransaction;
use App\Models\Order;
use App\Support\Feature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GiftCardService
{
    public static function normalizeCode(?string $code): ?string
    {
        if (! $code) {
            return null;
        }

        return Str::of($code)->squish()->upper()->value();
    }

    public function prepareRedemption(?string $code, float $requestedAmount, float $maxAllowed): ?array
    {
        $normalized = self::normalizeCode($code);

        if (! $normalized) {
            return null;
        }

        if (! Feature::enabled('gift_cards')) {
            throw new GiftCardException('Fitur gift card sedang dinonaktifkan.');
        }

        /** @var GiftCard|null $giftCard */
        $giftCard = GiftCard::query()
            ->whereRaw('upper(code) = ?', [$normalized])
            ->first();

        if (! $giftCard) {
            throw new GiftCardException('Gift card tidak ditemukan.');
        }

        if (! $giftCard->isRedeemable()) {
            throw new GiftCardException('Gift card tidak aktif atau sudah kadaluarsa.');
        }

        $maxAllowed = max($maxAllowed, 0);

        if ($maxAllowed <= 0) {
            throw new GiftCardException('Tidak ada tagihan yang bisa dibayar dengan gift card.');
        }

        $amount = $requestedAmount > 0 ? $requestedAmount : min($giftCard->availableBalance(), $maxAllowed);

        if ($amount <= 0) {
            throw new GiftCardException('Nominal penggunaan gift card harus lebih dari 0.');
        }

        if ($amount > $giftCard->availableBalance()) {
            throw new GiftCardException('Saldo gift card tidak mencukupi.');
        }

        if ($amount > $maxAllowed) {
            throw new GiftCardException('Nominal gift card melebihi total tagihan.');
        }

        return [
            'gift_card' => $giftCard,
            'amount' => round($amount, 2),
            'code' => $giftCard->code,
        ];
    }

    public function redeemForOrder(Order $order, GiftCard $giftCard, float $amount): void
    {
        DB::transaction(function () use ($order, $giftCard, $amount) {
            if (! Feature::enabled('gift_cards')) {
                throw new GiftCardException('Fitur gift card sedang dinonaktifkan.');
            }

            $giftCard->refresh();

            if (! $giftCard->isRedeemable()) {
                throw new GiftCardException('Gift card tidak lagi dapat digunakan.');
            }

            if ($giftCard->availableBalance() < $amount) {
                throw new GiftCardException('Saldo gift card tidak mencukupi.');
            }

            $giftCard->balance = round($giftCard->balance - $amount, 2);

            if ($giftCard->balance <= 0) {
                $giftCard->balance = 0;
                $giftCard->status = GiftCard::STATUS_EXHAUSTED;
            }

            $giftCard->last_used_at = now();
            $giftCard->save();

            GiftCardTransaction::create([
                'gift_card_id' => $giftCard->id,
                'type' => 'redeem',
                'amount' => $amount,
                'balance_after' => $giftCard->balance,
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'notes' => 'Redeem order #' . $order->id,
            ]);
        });
    }

    public function reload(GiftCard $giftCard, float $amount, ?string $notes = null): void
    {
        if ($amount <= 0) {
            throw new GiftCardException('Nominal top-up gift card harus lebih dari 0.');
        }

        DB::transaction(function () use ($giftCard, $amount, $notes) {
            if (! Feature::enabled('gift_cards')) {
                throw new GiftCardException('Fitur gift card sedang dinonaktifkan.');
            }

            $giftCard->refresh();
            $giftCard->balance = round($giftCard->balance + $amount, 2);

            if ($giftCard->status !== GiftCard::STATUS_ACTIVE) {
                $giftCard->status = GiftCard::STATUS_ACTIVE;
            }

            $giftCard->save();

            GiftCardTransaction::create([
                'gift_card_id' => $giftCard->id,
                'type' => 'reload',
                'amount' => $amount,
                'balance_after' => $giftCard->balance,
                'notes' => $notes,
            ]);
        });
    }
}
