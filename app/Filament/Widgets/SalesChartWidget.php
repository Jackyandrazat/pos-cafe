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
        $restrict = $this->shouldRestrictToUser($user);

        $dailySales = $this->buildDailySalesQuery($restrict, $user)->pluck('total', 'date');

        if ($restrict && $dailySales->isEmpty()) {
            $dailySales = $this->buildDailySalesQuery(false, $user)->pluck('total', 'date');
        }

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

    protected function buildDailySalesQuery(bool $restrict, ?User $user)
    {
        $builder = Payment::query()
            ->selectRaw('DATE(COALESCE(payment_date, created_at)) as date, SUM(amount_paid) as total');

        if ($restrict) {
            if (! $user) {
                $builder->whereRaw('1 = 0');
            } else {
                $builder->whereHas('order', function ($orderQuery) use ($user) {
                    $orderQuery->where('user_id', $user->id);
                });
            }
        }

        return $builder
            ->groupBy(DB::raw('DATE(COALESCE(payment_date, created_at))'))
            ->orderBy('date');
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
