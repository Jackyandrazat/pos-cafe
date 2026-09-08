<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return static::getResource()::canAccess();
    }

    protected static string $view = 'filament.resources.payment-resource.pages.list-payments';

    #[Url]
    public string $viewMode = 'list';

    #[Url(as: 'card-search')]
    public string $cardSearch = '';

    protected int $cardViewLimit = 24;

    public bool $isDetailModalOpen = false;

    public ?int $detailPaymentId = null;

    public array $detailPaymentMeta = [];

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

    public function getCardPaymentsProperty(): Collection
    {
        $query = (clone $this->getFilteredSortedTableQuery())
            ->with([
                'order.table',
                'order.order_items.product',
                'order.order_items.toppings',
                'order.user',
                'confirmedBy',
            ]);

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

    public function openPaymentDetailModal(int $paymentId): void
    {
        $payment = Payment::with([
            'order.table',
            'order.order_items.product',
            'order.order_items.toppings',
            'order.user',
            'confirmedBy',
            'shift',
        ])->find($paymentId);

        if (! $payment instanceof Payment) {
            return;
        }

        $order = $payment->order;
        $statusEnum = $payment->status instanceof PaymentStatus ? $payment->status : PaymentStatus::tryFrom($payment->status);

        $this->detailPaymentId = $payment->id;
        $this->detailPaymentMeta = [
            'payment_id' => $payment->id,
            'order_id' => $order?->id,
            'customer_name' => $order?->customer_name ?: __('Tamu'),
            'status' => $payment->status instanceof PaymentStatus ? $payment->status->value : $payment->status,
            'status_label' => $statusEnum?->label() ?? ucfirst((string) $payment->status),
            'status_color' => $statusEnum?->color() ?? 'gray',
            'payment_method' => $payment->payment_method,
            'payment_channel' => $payment->payment_channel,
            'provider' => $payment->provider,
            'external_reference' => $payment->external_reference,
            'order_type_label' => $order ? $this->getOrderTypeLabel($order->order_type) : '-',
            'table_number' => optional($order?->table)->table_number,
            'amount_paid' => (float) ($payment->amount_paid ?? 0),
            'change_return' => (float) ($payment->change_return ?? 0),
            'total_order' => (float) ($order?->total_order ?? $order?->total ?? $payment->amount_paid ?? 0),
            'subtotal_order' => (float) ($order?->subtotal_order ?? 0),
            'discount_order' => (float) ($order?->discount_order ?? 0),
            'service_fee_order' => (float) ($order?->service_fee_order ?? 0),
            'cashier_name' => optional($order?->user)->name ?? optional($payment->confirmedBy)->name ?? __('Sistem'),
            'confirmed_by' => optional($payment->confirmedBy)->name,
            'created_at' => optional($payment->created_at)?->timezone(config('app.timezone'))?->translatedFormat('d M Y • H:i'),
            'paid_at' => optional($payment->paid_at ?? $payment->payment_date)?->timezone(config('app.timezone'))?->translatedFormat('d M Y • H:i'),
        ];

        $this->detailOrderItems = $order?->order_items ? $order->order_items->map(function ($item) {
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
        })->toArray() : [];

        $this->isDetailModalOpen = true;
    }

    public function closePaymentDetailModal(): void
    {
        $this->reset('detailPaymentId', 'detailPaymentMeta', 'detailOrderItems', 'isDetailModalOpen');
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
