<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Support\Feature;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class KitchenDisplay extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-fire';

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $title = 'Kitchen Display';

    protected static string $view = 'filament.pages.kitchen-display';

    public array $orders = [];

    public string $statusFilter = 'active';

    protected $listeners = ['refreshOrders' => 'refreshOrders'];

    public function mount(): void
    {
        abort_unless(Feature::enabled('kitchen_display'), 403);
        $this->refreshOrders();
    }

    public function refreshOrders(): void
    {
        $statuses = match ($this->statusFilter) {
            'ready' => [OrderStatus::Ready->value],
            'completed' => [OrderStatus::Completed->value],
            default => [
                OrderStatus::Pending->value,
                OrderStatus::Confirmed->value,
                OrderStatus::Preparing->value,
            ],
        };

        $this->orders = Order::with(['items.product', 'table'])
            ->whereIn('status', $statuses)
            ->orderBy('created_at')
            ->limit(30)
            ->get()
            ->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'table' => $order->table?->table_number,
                    'order_type' => $order->order_type,
                    'customer' => $order->customer_name,
                    'created_at' => optional($order->created_at)->format('H:i'),
                    'items' => $order->items->map(fn ($item) => [
                        'name' => $item->product?->name ?? 'Menu',
                        'qty' => $item->qty,
                    ])->toArray(),
                ];
            })
            ->toArray();
    }

    public function setFilter(string $filter): void
    {
        $this->statusFilter = $filter;
        $this->refreshOrders();
    }

    public function advanceStatus(int $orderId, string $status): void
    {
        $order = Order::with('items')->findOrFail($orderId);

        if ($order->status === $status) {
            return;
        }

        $order->status = $status;
        $order->save();

        $order->logStatus(OrderStatus::from($status), 'Updated via Kitchen Display');

        Notification::make()
            ->title("Order #{$order->id} → " . ucfirst($status))
            ->success()
            ->send();

        $this->refreshOrders();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Feature::enabled('kitchen_display');
    }
}
