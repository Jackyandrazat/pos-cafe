<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class SalesChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Total Penjualan Harian';

    protected function getType(): string
    {
        return 'bar'; // atau 'line'
    }

    protected function getData(): array
    {
        $dailySales = Payment::selectRaw('DATE(created_at) as date, SUM(amount_paid) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->pluck('total', 'date');

        return [
            'labels' => $dailySales->keys(),
            'datasets' => [
                [
                    'label' => 'Total Penjualan',
                    'data' => $dailySales->values(),
                    'backgroundColor' => '#4CAF50',
                ],
            ],
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
