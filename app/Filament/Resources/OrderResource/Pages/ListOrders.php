<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.list-orders';

    #[Url]
    public string $viewMode = 'list';

    #[Url(as: 'card-search')]
    public string $cardSearch = '';

    protected int $cardViewLimit = 24;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function setViewMode(string $mode): void
    {
        if (! in_array($mode, ['list', 'card'], true)) {
            return;
        }

        $this->viewMode = $mode;
    }

    public function getCardOrdersProperty(): Collection
    {
        $query = (clone $this->getFilteredSortedTableQuery())
            ->with(['table', 'order_items.product'])
            ->withCount('order_items');

        $search = trim($this->cardSearch);

        if ($search !== '') {
            $numericSearch = preg_replace('/[^0-9]/', '', $search) ?: null;

            $query->where(function ($q) use ($search, $numericSearch) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('order_type', 'like', "%{$search}%")
                    ->orWhereHas('table', fn ($tableQuery) => $tableQuery->where('table_number', 'like', "%{$search}%"))
                    ->orWhereHas('order_items.product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereRaw('CAST(id AS CHAR) LIKE ?', ["%{$search}%"]);

                if ($numericSearch !== null) {
                    $q->orWhere('id', (int) $numericSearch);
                }
            });
        }

        return $query
            ->limit($this->cardViewLimit)
            ->get();
    }

    public function getOrderTypeLabel(?string $type): string
    {
        return [
            'dine_in' => __('orders.types.dine_in'),
            'take_away' => __('orders.types.take_away'),
            'delivery' => __('orders.types.delivery'),
        ][$type] ?? ($type ? (string) str($type)->headline() : __('orders.status.unknown'));
    }
}
