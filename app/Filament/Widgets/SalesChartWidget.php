<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Total Penjualan Harian';


    protected static ?int $sort = 6;
    protected function getType(): string
    {
        return 'bar'; // atau 'line'
    }

    protected function getData(): array
    {
        $user = Auth::user();

        $dailySales = Payment::query()
            ->selectRaw('DATE(created_at) as date, SUM(amount_paid) as total')
            ->when($this->shouldRestrictToUser($user), function ($query) use ($user) {
                if (! $user) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereHas('order', function ($orderQuery) use ($user) {
                    $orderQuery->where('user_id', $user->id);
                });
            })
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

    protected function shouldRestrictToUser(?User $user): bool
    {
        if (! $user) {
            return true;
        }

        return ! $user->hasAnyRole(['admin', 'superadmin', 'owner']);
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
