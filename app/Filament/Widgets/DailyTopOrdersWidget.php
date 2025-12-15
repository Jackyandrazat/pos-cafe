<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DailyTopOrdersWidget extends TableWidget
{
    protected static ?string $heading = 'Produk Terbanyak Dipesan Hari Ini';

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return OrderItem::query()
            ->selectRaw('products.name as product_name, SUM(order_items.qty) as total_qty')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereDate('orders.created_at', Carbon::today())
            ->groupBy('products.name')
            ->orderByDesc('total_qty')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('product_name')
                ->label('Produk')
                ->searchable(),
            Tables\Columns\TextColumn::make('total_qty')
                ->label('Total Dipesan')
                ->numeric()
                ->sortable(),
        ];
    }
}
