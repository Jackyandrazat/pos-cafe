<?php

namespace App\Services;

use App\Exceptions\StockValidationException;
use App\Models\Order;

class StockValidationService
{
    /**
     * Validasi kecukupan stok bahan baku untuk sebuah order (termasuk topping).
     *
     * @param Order $order
     * @throws StockValidationException jika stok kurang
     */
    public static function validateStockForOrder(Order $order): void
    {
        $order->loadMissing(['items.product.ingredients.ingredient', 'items.toppings.topping.ingredients.ingredient']);

        foreach ($order->items as $item) {
            $product = $item->product;
            $qty     = $item->qty;

            // --- Validasi stok bahan baku produk ---
            foreach ($product->ingredients as $composition) {
                $ingredient = $composition->ingredient;

                if (! $ingredient) {
                    continue;
                }

                $needed = $composition->quantity_used * $qty;

                if ($ingredient->stock_qty < $needed) {
                    throw new StockValidationException(
                        "Stok bahan '{$ingredient->name}' tidak cukup. "
                        . "Stok tersedia: {$ingredient->stock_qty}, dibutuhkan: {$needed}."
                    );
                }
            }

            // --- Validasi stok bahan baku topping ---
            foreach ($item->toppings ?? [] as $orderTopping) {
                $topping = $orderTopping->topping ?? null;

                if (! $topping) {
                    continue;
                }

                $topping->loadMissing('ingredients.ingredient');

                $toppingQty = $orderTopping->quantity ?? 1;

                foreach ($topping->ingredients as $composition) {
                    $ingredient = $composition->ingredient;

                    if (! $ingredient) {
                        continue;
                    }

                    $needed = $composition->quantity_used * $toppingQty;

                    if ($ingredient->stock_qty < $needed) {
                        throw new StockValidationException(
                            "Stok bahan '{$ingredient->name}' (topping: {$topping->name}) tidak cukup. "
                            . "Stok tersedia: {$ingredient->stock_qty}, dibutuhkan: {$needed}."
                        );
                    }
                }
            }
        }
    }
}
