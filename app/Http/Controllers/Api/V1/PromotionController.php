<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class PromotionController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $promos = Promotion::query()
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $promos
        ]);
    }

    public function validatePromo(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'guest_id' => ['nullable', 'numeric'], // optional kalau mau track per user
        ]);

        $now = Carbon::now();
        $code = strtoupper(trim($request->code));
        $subtotal = (int) $request->subtotal;

        $promo = Promotion::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$promo) {
            return response()->json(['message' => 'Promo tidak ditemukan'], 422);
        }

        // cek periode berlaku
        if ($promo->start_at && $now->lt($promo->start_at)) {
            return response()->json(['message' => 'Promo belum berlaku'], 422);
        }
        if ($promo->end_at && $now->gt($promo->end_at)) {
            return response()->json(['message' => 'Promo sudah berakhir'], 422);
        }

        // cek minimal subtotal
        if ($subtotal < $promo->min_subtotal) {
            return response()->json([
                'message' => 'Subtotal belum memenuhi minimum promo'
            ], 422);
        }

        // cek batas global
        if (!is_null($promo->global_limit) && $promo->used_count >= $promo->global_limit) {
            return response()->json([
                'message' => 'Promo sudah mencapai batas penggunaan'
            ], 422);
        }

        // cek jadwal dinamis (optional)
        // kalau active_days kosong -> dianggap berlaku setiap hari
        if (is_array($promo->active_days) && count($promo->active_days) > 0) {
            $day = strtolower($now->format('l')); // monday, tuesday, ...
            if (!in_array($day, $promo->active_days)) {
                return response()->json([
                    'message' => 'Promo tidak berlaku hari ini'
                ], 422);
            }
        }

        // cek jam berlaku (optional)
        if ($promo->start_time && $promo->end_time) {
            $currentTime = $now->format('H:i:s');

            // NOTE: handle range normal, tidak handle "melewati tengah malam" (nanti bisa ditambah)
            if (!($currentTime >= $promo->start_time && $currentTime <= $promo->end_time)) {
                return response()->json([
                    'message' => 'Promo tidak berlaku pada jam ini'
                ], 422);
            }
        }

        // hitung diskon
        $discount = 0;

        if ($promo->type === 'fixed') {
            $discount = min($promo->discount_value, $subtotal);
        } else {
            // percentage
            $discount = (int) floor($subtotal * ($promo->discount_value / 100));
            if (!is_null($promo->max_discount)) {
                $discount = min($discount, $promo->max_discount);
            }
        }

        return response()->json([
            'data' => [
                'code' => $promo->code,
                'name' => $promo->name,
                'discount_amount' => $discount,
            ]
        ]);
    }
}
