<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class DailyTopOrdersChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Chart Produk Terlaris Hari Ini';

    protected static ?int $sort = 7;

    protected function getType(): string
    {
        return 'bar';
    }



    protected function getData(): array
    {
        $topProducts = OrderItem::query()
            ->selectRaw('products.name as product_name, SUM(order_items.qty) as total_qty')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereDate('orders.created_at', Carbon::today())
            ->groupBy('products.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->pluck('total_qty', 'product_name');

        return [
            'labels' => $topProducts->keys(),
            'datasets' => [[
                'label' => 'Total Dipesan',
                'data' => $topProducts->values(),
                'backgroundColor' => '#22C55E',
            ]],
        ];
    }

    public function chartOptions(): array
    {
        return [
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
        ];
    }
}
