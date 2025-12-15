<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class SalesPerShiftWidget extends ChartWidget
{
    protected static ?string $heading = 'Penjualan per Shift';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        // Hitung total bayar per shift_id
        $shiftSales = Payment::selectRaw('shift_id, SUM(amount_paid) as total')
            ->groupBy('shift_id')
            ->orderBy('shift_id')
            ->pluck('total', 'shift_id');

        // Map shift_id ke label "Shift 1", "Shift 2", ...
        $labels = $shiftSales->keys()->map(fn ($id) => "Shift {$id}");

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Penjualan per Shift',
                'data' => $shiftSales->values(),
                'backgroundColor' => '#F59E0B', // kuning
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
