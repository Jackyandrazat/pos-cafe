<?php

namespace App\Services;

use Exception;
use App\Models\Order;
use App\Exceptions\StockValidationException;

class StockValidationService
{
    /**
     * Validasi kecukupan stok bahan baku untuk sebuah order.
     *
     * @param Order $order
     * @throws Exception jika stok kurang
     */
    public static function validateStockForOrder(Order $order): void
    {
        foreach ($order->orderItems as $item) {
            $product = $item->product;
            $qty = $item->qty;

            foreach ($product->ingredients as $composition) {
                $ingredient = $composition->ingredient;

                if (!$ingredient) {
                    continue;
                }

                $needed = $composition->quantity_used * $qty;

                if ($ingredient->stock_qty < $needed) {
                    throw new StockValidationException("Stok bahan '{$ingredient->name}' tidak cukup. Stok tersedia: {$ingredient->stock_qty}, dibutuhkan: {$needed}.");
                }
            }
        }
    }
}
