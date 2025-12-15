<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TopSellingProductsWidget extends ChartWidget
{
    protected static ?string $heading = 'Produk Paling Laris';
    protected static ?int $sort = 8;

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('products.name, SUM(order_items.qty) as total')
            ->groupBy('products.name')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'products.name');

        return [
            'labels' => $topProducts->keys(),
            'datasets' => [[
                'label' => 'Total Terjual',
                'data' => $topProducts->values(),
                'backgroundColor' => '#EF4444', // merah
            ]],
        ];
    }

    public function chartOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true],
            ],
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
        ];
    }

    public static function canView(): bool
    {
        return static::userCanView(Auth::user());
    }

    protected static function userCanView(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['admin', 'superadmin', 'owner']);
    }
}
