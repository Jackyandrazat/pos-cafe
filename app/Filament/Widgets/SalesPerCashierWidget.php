<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class SalesPerCashierWidget extends ChartWidget
{
    protected static ?string $heading = 'Total Penjualan per Kasir';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        // Hitung total bayar per kasir (nama user)
       $salesPerCashier = Payment::selectRaw('users.name as cashier, SUM(amount_paid) as total')
            ->join('shifts', 'payments.shift_id', '=', 'shifts.id')
            ->join('users', 'shifts.user_id', '=', 'users.id')
            ->groupBy('users.name')
            ->orderBy('users.name')
            ->pluck('total', 'cashier');


        return [
            'labels' => $salesPerCashier->keys(),
            'datasets' => [[
                'label' => 'Penjualan per Kasir',
                'data' => $salesPerCashier->values(),
                'backgroundColor' => '#3B82F6', // biru
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
