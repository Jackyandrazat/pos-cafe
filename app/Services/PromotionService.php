<?php

namespace App\Services;

use App\Exceptions\PromotionException;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\User;
use Illuminate\Support\Str;

class PromotionService
{
    public static function normalizeCode(?string $code): ?string
    {
        if (! $code) {
            return null;
        }

        return Str::of($code)->squish()->upper()->value();
    }

    /**
     * @return array{promotion: Promotion, discount: float, code: string}|null
     */
    public static function validateAndCalculate(?string $code, float $subtotal, ?User $user, ?int $ignoreOrderId = null): ?array
    {
        $normalizedCode = self::normalizeCode($code);

        if (! $normalizedCode) {
            return null;
        }

        /** @var Promotion|null $promotion */
        $promotion = Promotion::query()
            ->whereRaw('upper(code) = ?', [$normalizedCode])
            ->first();


        if (! $promotion) {
            throw new PromotionException('Kode promo tidak ditemukan.');
        }

        if (! $promotion->isCurrentlyValid()) {
            throw new PromotionException('Promo belum aktif atau sudah berakhir.');
        }

        if ($subtotal < $promotion->min_subtotal) {
            throw new PromotionException('Subtotal belum memenuhi minimum promo.');
        }

        $globalUsage = PromotionUsage::query()
            ->where('promotion_id', $promotion->id)
            ->when($ignoreOrderId, function ($query) use ($ignoreOrderId) {
                $query->where(function ($subQuery) use ($ignoreOrderId) {
                    $subQuery->whereNull('order_id')
                        ->orWhere('order_id', '!=', $ignoreOrderId);
                });
            })
            ->count();

        if ($promotion->usage_limit !== null && $promotion->usage_limit > 0 && $globalUsage >= $promotion->usage_limit) {
            throw new PromotionException('Kuota penggunaan promo sudah habis.');
        }

        if ($promotion->usage_limit_per_user !== null && $promotion->usage_limit_per_user > 0 && $user) {
            $userUsage = PromotionUsage::query()
                ->where('promotion_id', $promotion->id)
                ->where('user_id', $user->id)
                ->when($ignoreOrderId, function ($query) use ($ignoreOrderId) {
                    $query->where(function ($subQuery) use ($ignoreOrderId) {
                        $subQuery->whereNull('order_id')
                            ->orWhere('order_id', '!=', $ignoreOrderId);
                    });
                })
                ->count();

            if ($userUsage >= $promotion->usage_limit_per_user) {
                throw new PromotionException('Promo sudah mencapai batas pemakaian per pengguna.');
            }
        }

        $discount = match ($promotion->type) {
            'percentage' => $subtotal * ($promotion->discount_value / 100),
            default => $promotion->discount_value,
        };

        if ($promotion->max_discount !== null) {
            $discount = min($discount, $promotion->max_discount);
        }

        $discount = min($discount, $subtotal);

        return [
            'promotion' => $promotion,
            'discount' => round($discount, 2),
            'code' => $promotion->code,
        ];
    }

    public static function syncUsage(Order $order): void
    {
        PromotionUsage::query()->where('order_id', $order->id)->delete();

        if (! $order->promotion_id) {
            return;
        }

        PromotionUsage::create([
            'promotion_id' => $order->promotion_id,
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'discount_amount' => $order->promotion_discount ?? 0,
            'used_at' => now(),
        ]);
    }
}
