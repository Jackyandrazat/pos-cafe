<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
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

    public bool $isDetailModalOpen = false;

    public ?int $detailOrderId = null;

    public array $detailOrderMeta = [];

    public array $detailOrderItems = [];

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
            ->with(['table', 'order_items.product', 'order_items.toppings'])
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

    public function openOrderDetailModal(int $orderId): void
    {
        $order = (clone $this->getFilteredSortedTableQuery())
            ->with(['table', 'order_items.product', 'order_items.toppings'])
            ->find($orderId);

        if (! $order instanceof Order) {
            return;
        }

        $this->detailOrderId = $order->id;
        $this->detailOrderMeta = [
            'order_number' => $order->id,
            'customer_name' => $order->customer_name ?: __('Tamu'),
            'status' => $order->status,
            'status_label' => __('orders.status.' . ($order->status ?? 'unknown')),
            'order_type_label' => $this->getOrderTypeLabel($order->order_type),
            'table_number' => optional($order->table)->table_number,
            'total_order' => $order->total_order ?? 0,
            'created_at' => optional($order->created_at)?->timezone(config('app.timezone'))?->translatedFormat('d M Y • H:i'),
        ];
        $this->detailOrderItems = $order->order_items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->product->name ?? $item->product_name ?? 'Menu #' . $item->id,
                'qty' => $item->qty ?? 0,
                'price' => $item->price ?? 0,
                'subtotal' => $item->subtotal ?? 0,
                'toppings' => $item->toppings?->map(function ($topping) {
                    return [
                        'name' => $topping->name,
                        'quantity' => $topping->quantity,
                        'price' => $topping->price,
                        'total' => $topping->total,
                    ];
                })->values()->toArray() ?? [],
            ];
        })->toArray();

        $this->isDetailModalOpen = true;
    }

    public function closeOrderDetailModal(): void
    {
        $this->reset('detailOrderId', 'detailOrderMeta', 'detailOrderItems', 'isDetailModalOpen');
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
