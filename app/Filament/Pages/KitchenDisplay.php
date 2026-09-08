<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\StockService;
use App\Support\Feature;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class KitchenDisplay extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-fire';

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $title = 'Kitchen Display';

    protected static string $view = 'filament.pages.kitchen-display';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return Feature::enabled('kitchen_display') && $user && $user->hasAnyRole(['admin', 'owner', 'kasir', 'kitchen']);
    }

    public array $orders = [];

    public string $statusFilter = 'active';

    public array $knownOrderIds = [];

    public bool $isFirstLoad = true;

    public int $activeCount = 0;

    public int $readyCount = 0;

    public int $completedCount = 0;

    public int $unpaidCount = 0;

    protected $listeners = ['refreshOrders' => 'refreshOrders'];

    /**
     * Peta transisi status yang diizinkan dari Kitchen Display.
     * Key = status saat ini, Value = status yang diperbolehkan.
     */
    private const ALLOWED_TRANSITIONS = [
        'draft'     => ['confirmed', 'preparing', 'ready', 'cancelled'],
        'pending'   => ['confirmed', 'preparing', 'ready', 'cancelled'],
        'payment'   => ['confirmed', 'preparing', 'ready', 'cancelled'],
        'confirmed' => ['preparing', 'ready', 'cancelled'],
        'preparing' => ['ready', 'completed', 'cancelled'],
        'ready'     => ['completed', 'cancelled'],
    ];

    public function mount(): void
    {
        abort_unless(Feature::enabled('kitchen_display'), 403);
        $this->refreshOrders();
    }

    public function refreshOrders(): void
    {
        // Kebijakan Pay-First: Pesanan aktif yang harus dimasak HANYA yang sudah lunas/dikonfirmasi
        $paidActiveStatuses = [
            OrderStatus::Confirmed->value,
            OrderStatus::Payment->value,
            OrderStatus::Preparing->value,
        ];

        // Hitung badge counter untuk setiap tab
        $this->activeCount = Order::whereIn('status', $paidActiveStatuses)->count();
        $this->readyCount = Order::where('status', OrderStatus::Ready->value)->count();
        $this->completedCount = Order::where('status', OrderStatus::Completed->value)
            ->whereDate('created_at', today())
            ->count();
        $this->unpaidCount = Order::whereIn('status', [OrderStatus::Pending->value, OrderStatus::Draft->value])->count();

        $statuses = match ($this->statusFilter) {
            'ready' => [OrderStatus::Ready->value],
            'completed' => [OrderStatus::Completed->value],
            'unpaid' => [OrderStatus::Pending->value, OrderStatus::Draft->value],
            default => $paidActiveStatuses,
        };

        $ordersCollection = Order::with(['items.product', 'items.size', 'items.toppings', 'table'])
            ->whereIn('status', $statuses)
            ->when($this->statusFilter === 'completed', fn ($q) => $q->whereDate('created_at', today()))
            ->orderBy($this->statusFilter === 'completed' ? 'updated_at' : 'created_at', $this->statusFilter === 'completed' ? 'desc' : 'asc')
            ->limit(30)
            ->get();

        $this->orders = $ordersCollection
            ->map(function (Order $order) {
                $isCompleted = ($order->status === OrderStatus::Completed->value);

                // Jika pesanan sudah selesai, waktu adalah durasi penyelesaian (created_at -> updated_at)
                if ($isCompleted && $order->updated_at && $order->created_at) {
                    $completionMinutes = (int) $order->created_at->diffInMinutes($order->updated_at);
                    if ($completionMinutes < 1) {
                        $elapsedText = 'Selesai < 1m';
                    } elseif ($completionMinutes < 60) {
                        $elapsedText = "Selesai {$completionMinutes}m";
                    } else {
                        $hours = floor($completionMinutes / 60);
                        $rem = $completionMinutes % 60;
                        $elapsedText = "Selesai {$hours}j {$rem}m";
                    }
                } else {
                    $elapsedText = $this->formatElapsedTime($order->created_at);
                }

                return [
                    'id' => $order->id,
                    'status' => (string) $order->status,
                    'table' => $order->table?->table_number,
                    'order_type' => $order->order_type,
                    'customer' => $order->customer_name,
                    'notes' => $order->notes,
                    'created_at' => optional($order->created_at)->format('H:i'),
                    'completed_at' => $isCompleted ? optional($order->updated_at)->format('H:i') : null,
                    'elapsed_minutes' => $isCompleted ? 0 : ($order->created_at ? (int) $order->created_at->diffInMinutes(now()) : 0),
                    'elapsed_text' => $elapsedText,
                    'is_completed' => $isCompleted,
                    'items' => $order->items->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->product?->name ?? 'Menu',
                        'qty' => $item->qty,
                        'size' => $item->size?->name,
                        'notes' => $item->notes ?? null,
                        'toppings' => $item->toppings->map(fn ($t) => $t->name)->filter()->values()->toArray(),
                    ])->toArray(),
                ];
            })
            ->toArray();

        $currentIds = collect($this->orders)->pluck('id')->toArray();

        if (! $this->isFirstLoad) {
            $newIds = array_diff($currentIds, $this->knownOrderIds);
            if (! empty($newIds)) {
                $this->dispatch('play-order-chime', count: count($newIds));
            }
        } else {
            $this->isFirstLoad = false;
        }

        $this->knownOrderIds = $currentIds;
    }

    public function formatElapsedTime(?\Carbon\Carbon $time): string
    {
        if (! $time) {
            return 'Baru saja';
        }

        $minutes = (int) $time->diffInMinutes(now());

        if ($minutes < 1) {
            return 'Baru saja';
        }

        if ($minutes < 60) {
            return "{$minutes}m lalu";
        }

        $hours = floor($minutes / 60);
        $remMinutes = $minutes % 60;

        if ($hours < 24) {
            return $remMinutes > 0 ? "{$hours}j {$remMinutes}m" : "{$hours}j lalu";
        }

        $days = floor($hours / 24);
        return "{$days} hari lalu";
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

        // Validasi bahwa status target adalah OrderStatus yang valid
        $validStatus = OrderStatus::tryFrom($status);
        if (! $validStatus) {
            Notification::make()
                ->title("Status '{$status}' tidak valid.")
                ->danger()
                ->send();
            return;
        }

        // Validasi bahwa transisi ini diizinkan
        $currentStatus = (string) $order->status;
        $allowedTargets = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];

        if (! in_array($status, $allowedTargets, true)) {
            Notification::make()
                ->title("Transisi dari '{$currentStatus}' ke '{$status}' tidak diizinkan.")
                ->danger()
                ->send();
            return;
        }

        // Jika transisi ke cancelled, kembalikan stok jika sudah dikurangi
        if ($status === OrderStatus::Cancelled->value && $order->stock_deducted) {
            StockService::restoreIngredientsFromOrder($order);
            $order->stock_deducted = false;
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
