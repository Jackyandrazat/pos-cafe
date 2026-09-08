<?php

namespace App\Observers;

use App\Models\CafeTable;
use App\Models\CustomerPointTransaction;
use App\Models\Order;
use App\Support\Feature;
use Illuminate\Support\Facades\DB;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     *
     * Dipindahkan dari Order::booted() untuk konsolidasi.
     */
    public function created(Order $order): void
    {
        if (! Feature::enabled('table_management')) {
            return;
        }

        if ($order->order_type === 'dine_in' && $order->table_id) {
            CafeTable::whereKey($order->table_id)->update(['status' => 'occupied']);
        }
    }

    /**
     * Handle the Order "updated" event.
     *
     * Menangani:
     * 1. Manajemen status meja (dipindahkan dari Order::booted())
     * 2. Loyalty points + gamified challenge saat order completed
     */
    public function updated(Order $order): void
    {
        // Pastikan status benar-benar berubah
        if (! $order->wasChanged('status')) {
            return;
        }

        // --- 1. Manajemen meja ---
        if (Feature::enabled('table_management')
            && $order->order_type === 'dine_in'
            && $order->table_id
        ) {
            match ($order->status) {
                'completed' => CafeTable::whereKey($order->table_id)->update(['status' => 'cleaning']),
                'cancelled' => CafeTable::whereKey($order->table_id)->update(['status' => 'available']),
                default     => null,
            };
        }

        // --- 2. Loyalty: hanya proses jika status menjadi completed ---
        if (! Feature::enabled('loyalty')) {
            return;
        }

        if ($order->status !== 'completed') {
            return;
        }

        if (! $order->customer_id) {
            return;
        }

        // Jalankan semua logic loyalty setelah DB commit
        DB::afterCommit(function () use ($order) {
            $freshOrder = $order->fresh(['customer', 'items']);

            // --- Poin loyalitas (dengan deduplikasi) ---
            $alreadyRewarded = CustomerPointTransaction::where('source_type', Order::class)
                ->where('source_id', $freshOrder->id)
                ->exists();

            if (! $alreadyRewarded) {
                app(\App\Services\LoyaltyService::class)
                    ->rewardOrderPoints($freshOrder);
            }

            // --- Gamified challenge progress (dengan deduplikasi per-order) ---
            app(\App\Services\GamifiedLoyaltyService::class)
                ->trackOrderProgress($freshOrder);
        });
    }
}
