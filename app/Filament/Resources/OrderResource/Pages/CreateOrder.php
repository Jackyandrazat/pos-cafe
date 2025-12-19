<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Exceptions\PromotionException;
use App\Exceptions\StockValidationException;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\PromotionService;
use App\Services\LoyaltyService;
use App\Services\StockValidationService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
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
            $orderItem = OrderItem::create([
                'order_id' => $this->record->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'discount_amount' => $item['discount'],
                'subtotal' => $item['subtotal'],
            ]);

            foreach ($item['toppings'] ?? [] as $topping) {
                $quantity = $topping['quantity'] ?? $item['qty'] ?? 1;
                $price = $topping['price'] ?? 0;
                $orderItem->toppings()->create([
                    'topping_id' => $topping['id'] ?? null,
                    'name' => $topping['name'] ?? 'Topping',
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $topping['total'] ?? ($price * $quantity),
                ]);
            }
        }

        PromotionService::syncUsage($this->record);

        session()->forget('selected_order_items');

        $this->record->load('customer');
        app(LoyaltyService::class)->rewardOrderPoints($this->record);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = session('selected_order_items', []);
        $data['subtotal_order'] = collect($items)->sum('subtotal');
        $manualDiscount = max((float) ($data['discount_order'] ?? 0), 0);

        try {
            $promotionResult = PromotionService::validateAndCalculate(
                $data['promotion_code'] ?? null,
                $data['subtotal_order'],
                Auth::user(),
            );
        } catch (PromotionException $e) {
            report($e);

            Notification::make()
                ->title('Kode Promo Gagal Digunakan')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'promotion_code' => $e->getMessage(),
            ]);
        }

        if ($promotionResult) {
            $data['promotion_id'] = $promotionResult['promotion']->id;
            $data['promotion_code'] = $promotionResult['code'];
            $data['promotion_discount'] = $promotionResult['discount'];
        } else {
            $data['promotion_id'] = null;
            $data['promotion_code'] = null;
            $data['promotion_discount'] = 0;
        }

        $promoDiscount = (float) ($data['promotion_discount'] ?? 0);
        $data['total_order'] = max($data['subtotal_order'] - $manualDiscount - $promoDiscount, 0);
        $data['user_id'] = $data['user_id'] ?? Auth::id();

        return $data;
    }
}
