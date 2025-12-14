<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected static string $view = 'filament.resources.payment-resource.pages.list-payments';

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

    public function getCardPaymentsProperty(): Collection
    {
        $query = (clone $this->getFilteredSortedTableQuery())
            ->with(['order.table', 'order.order_items.product']);

        $search = trim($this->cardSearch);

        if ($search !== '') {
            $numericSearch = preg_replace('/[^0-9]/', '', $search) ?: null;

            $query->where(function ($q) use ($search, $numericSearch) {
                $q->where('payment_method', 'like', "%{$search}%")
                    ->orWhereRaw('CAST(amount_paid AS CHAR) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('CAST(change_return AS CHAR) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('order', function ($orderQuery) use ($search, $numericSearch) {
                        $orderQuery->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('order_type', 'like', "%{$search}%")
                            ->orWhereHas('table', fn ($tableQuery) => $tableQuery->where('table_number', 'like', "%{$search}%"))
                            ->orWhereHas('order_items.product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"))
                            ->orWhereRaw('CAST(orders.id AS CHAR) LIKE ?', ["%{$search}%"]);

                        if ($numericSearch !== null) {
                            $orderQuery->orWhere('orders.id', (int) $numericSearch);
                        }
                    })
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
