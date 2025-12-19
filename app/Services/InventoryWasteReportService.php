<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\IngredientWaste;
use App\Models\OrderItem;
use App\Models\PurchaseItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryWasteReportService
{
    public static function generate(Carbon $start, Carbon $end): array
    {
        $purchaseTotals = PurchaseItem::query()
            ->selectRaw('ingredient_id, SUM(quantity) as total_in')
            ->whereHas('purchase', function ($query) use ($start, $end) {
                $query->whereBetween(DB::raw('DATE(purchase_date)'), [$start->toDateString(), $end->toDateString()]);
            })
            ->groupBy('ingredient_id')
            ->pluck('total_in', 'ingredient_id');

        $usageTotals = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_ingredients', 'order_items.product_id', '=', 'product_ingredients.product_id')
            ->selectRaw('product_ingredients.ingredient_id as ingredient_id, SUM(order_items.qty * product_ingredients.quantity_used) as total_used')
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('product_ingredients.ingredient_id')
            ->pluck('total_used', 'ingredient_id');

        $wasteTotals = IngredientWaste::query()
            ->selectRaw('ingredient_id, SUM(quantity) as total_waste')
            ->whereBetween('recorded_at', [$start, $end])
            ->groupBy('ingredient_id')
            ->pluck('total_waste', 'ingredient_id');

        $ingredientIds = $purchaseTotals->keys()
            ->merge($usageTotals->keys())
            ->merge($wasteTotals->keys())
            ->unique()
            ->values();

        $ingredients = Ingredient::whereIn('id', $ingredientIds)->get()->keyBy('id');

        $rows = $ingredientIds->map(function ($ingredientId) use ($ingredients, $purchaseTotals, $usageTotals, $wasteTotals) {
            $ingredient = $ingredients[$ingredientId] ?? null;

            $stockIn = (float) ($purchaseTotals[$ingredientId] ?? 0);
            $usage = (float) ($usageTotals[$ingredientId] ?? 0);
            $waste = (float) ($wasteTotals[$ingredientId] ?? 0);
            $consumption = $usage + $waste;
            $wasteCost = $waste * (float) ($ingredient?->price_per_unit ?? 0);
            $wasteRatio = $consumption > 0 ? ($waste / $consumption) * 100 : 0;
            $variance = $stockIn - $consumption;

            return [
                'ingredient_id' => $ingredientId,
                'name' => $ingredient?->name ?? 'Bahan',
                'unit' => $ingredient?->unit,
                'stock_in' => round($stockIn, 2),
                'usage' => round($usage, 2),
                'waste' => round($waste, 2),
                'consumption' => round($consumption, 2),
                'variance' => round($variance, 2),
                'current_stock' => (float) ($ingredient?->stock_qty ?? 0),
                'waste_cost' => round($wasteCost, 2),
                'waste_ratio' => round($wasteRatio, 2),
            ];
        });

        $summary = [
            'total_stock_in' => round($rows->sum('stock_in'), 2),
            'total_usage' => round($rows->sum('usage'), 2),
            'total_waste' => round($rows->sum('waste'), 2),
            'total_waste_cost' => round($rows->sum('waste_cost'), 2),
        ];

        $summary['waste_ratio'] = ($summary['total_usage'] + $summary['total_waste']) > 0
            ? round(($summary['total_waste'] / ($summary['total_usage'] + $summary['total_waste'])) * 100, 2)
            : 0;

        return [
            'rows' => $rows,
            'summary' => $summary,
        ];
    }
}
