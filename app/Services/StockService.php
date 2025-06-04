<?php

namespace App\Services;


use App\Models\Order;
use App\Models\Ingredient;
use App\Models\ProductIngredient;
use Illuminate\Support\Facades\Log;

class StockService
{
    public static function reduceIngredientsFromOrder(Order $order): void
    {
        // dd($order);
        foreach ($order->order_items as $item) {
            $product = $item->product;
            $qty = $item->qty;

            foreach ($product->ingredients as $composition) {
                $ingredient = $composition->ingredient;

                if (!$ingredient) {
                    continue;
                }

                $totalUsed = $composition->quantity_used * $qty;
                Log::info("Kurangi stok untuk {$ingredient->name} sebesar {$totalUsed}");
                Log::debug([
                    'product_id' => $product->id,
                    'ingredient' => $ingredient->name,
                    'quantity_used' => $composition->quantity_used,
                    'qty_ordered' => $qty,
                    'totalUsed' => $totalUsed,
                ]);
                $ingredient->decrement('stock_qty', $totalUsed);
            }
        }
    }
}
