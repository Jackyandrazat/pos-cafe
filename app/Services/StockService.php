<?php

namespace App\Services;

use App\Exceptions\StockValidationException;
use App\Models\Ingredient;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class StockService
{
    /**
     * Kurangi stok bahan baku berdasarkan item order (termasuk topping).
     *
     * Menggunakan conditional decrement agar stok tidak bisa negatif.
     * Throw StockValidationException jika stok tidak cukup.
     *
     * @throws StockValidationException
     */
    public static function reduceIngredientsFromOrder(Order $order): void
    {
        $order->loadMissing(['items.product.ingredients.ingredient', 'items.toppings.topping.ingredients.ingredient']);

        // --- Kurangi stok dari bahan baku produk ---
        foreach ($order->items as $item) {
            $product = $item->product;
            $qty     = $item->qty;

            foreach ($product->ingredients as $composition) {
                $ingredient = $composition->ingredient;

                if (! $ingredient) {
                    continue;
                }

                $totalUsed = $composition->quantity_used * $qty;

                $affected = Ingredient::where('id', $ingredient->id)
                    ->where('stock_qty', '>=', $totalUsed)
                    ->update(['stock_qty' => \Illuminate\Support\Facades\DB::raw("stock_qty - {$totalUsed}")]);

                if ($affected === 0) {
                    $ingredient->refresh();
                    throw new StockValidationException(
                        "Stok bahan '{$ingredient->name}' tidak cukup. "
                        . "Tersedia: {$ingredient->stock_qty}, dibutuhkan: {$totalUsed}."
                    );
                }

                Log::info("Kurangi stok {$ingredient->name} sebesar {$totalUsed}");
            }

            // --- Kurangi stok dari bahan baku topping ---
            foreach ($item->toppings as $orderTopping) {
                $topping = $orderTopping->topping;

                if (! $topping || ! $topping->relationLoaded('ingredients')) {
                    continue;
                }

                $toppingQty = $orderTopping->quantity ?? 1;

                foreach ($topping->ingredients as $composition) {
                    $ingredient = $composition->ingredient;

                    if (! $ingredient) {
                        continue;
                    }

                    $totalUsed = $composition->quantity_used * $toppingQty;

                    $affected = Ingredient::where('id', $ingredient->id)
                        ->where('stock_qty', '>=', $totalUsed)
                        ->update(['stock_qty' => \Illuminate\Support\Facades\DB::raw("stock_qty - {$totalUsed}")]);

                    if ($affected === 0) {
                        $ingredient->refresh();
                        throw new StockValidationException(
                            "Stok bahan '{$ingredient->name}' (topping: {$topping->name}) tidak cukup. "
                            . "Tersedia: {$ingredient->stock_qty}, dibutuhkan: {$totalUsed}."
                        );
                    }

                    Log::info("Kurangi stok {$ingredient->name} (topping: {$topping->name}) sebesar {$totalUsed}");
                }
            }
        }
    }

    /**
     * Kembalikan stok bahan baku yang sudah dikurangi untuk sebuah order.
     * Dipanggil saat order dibatalkan setelah pembayaran.
     */
    public static function restoreIngredientsFromOrder(Order $order): void
    {
        $order->loadMissing(['items.product.ingredients.ingredient', 'items.toppings.topping.ingredients.ingredient']);

        // --- Kembalikan stok dari bahan baku produk ---
        foreach ($order->items as $item) {
            $product = $item->product;
            $qty     = $item->qty;

            foreach ($product->ingredients as $composition) {
                $ingredient = $composition->ingredient;

                if (! $ingredient) {
                    continue;
                }

                $totalUsed = $composition->quantity_used * $qty;
                $ingredient->increment('stock_qty', $totalUsed);

                Log::info("Kembalikan stok {$ingredient->name} sebesar {$totalUsed} (order #{$order->id} dibatalkan)");
            }

            // --- Kembalikan stok dari bahan baku topping ---
            foreach ($item->toppings as $orderTopping) {
                $topping = $orderTopping->topping;

                if (! $topping || ! $topping->relationLoaded('ingredients')) {
                    continue;
                }

                $toppingQty = $orderTopping->quantity ?? 1;

                foreach ($topping->ingredients as $composition) {
                    $ingredient = $composition->ingredient;

                    if (! $ingredient) {
                        continue;
                    }

                    $totalUsed = $composition->quantity_used * $toppingQty;
                    $ingredient->increment('stock_qty', $totalUsed);

                    Log::info("Kembalikan stok {$ingredient->name} (topping: {$topping->name}) sebesar {$totalUsed} (order #{$order->id} dibatalkan)");
                }
            }
        }
    }

    /**
     * Alias untuk reduceIngredientsFromOrder agar kompatibel dengan pemanggilan rekonsiliasi.
     */
    public static function decrementIngredientsForOrder(Order $order): void
    {
        self::reduceIngredientsFromOrder($order);
    }
}
