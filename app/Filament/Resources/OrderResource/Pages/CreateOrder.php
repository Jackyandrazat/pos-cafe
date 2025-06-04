<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Models\Order;
use Filament\Actions;
use App\Models\OrderItem;
use App\Services\StockValidationService;
use Filament\Notifications\Notification;
use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use App\Exceptions\StockValidationException;
use Illuminate\Validation\ValidationException;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
    protected static string $view = 'filament.resources.order.create';

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();

        $items = session('selected_order_items', []);

        $order = new Order();
        $order->subtotal_order = $data['subtotal_order'] ?? 0;
        $order->total_order = $data['total_order'] ?? 0;

        $orderItems = collect();

        foreach ($items as $item) {
            $orderItem = new OrderItem([
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'discount_amount' => $item['discount'],
                'subtotal' => $item['subtotal'],
            ]);
            $orderItems->push($orderItem);
        }

        $order->setRelation('orderItems', $orderItems);

        try {
            StockValidationService::validateStockForOrder($order);
        } catch (StockValidationException  $e) {
            Notification::make()
                ->title('Stok Tidak Cukup')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'stock' => $e->getMessage(),
            ]);
        }
    }
    protected function afterCreate(): void
    {
        $items = session('selected_order_items', []);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $this->record->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'discount_amount' => $item['discount'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        session()->forget('selected_order_items');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = session('selected_order_items', []);
        $data['subtotal_order'] = collect($items)->sum('subtotal');
        $data['total_order'] = max($data['subtotal_order'] - ($data['discount_order'] ?? 0), 0);
        return $data;
    }
}
