<?php

namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\OrderResource;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;
    protected static string $view = 'filament.resources.order.edit';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $order = $this->record; // model Order yang sedang diedit

        // Ambil data order items dari session
        $items = session('selected_order_items', []);

        // Bisa gunakan transaction untuk aman
        DB::transaction(function () use ($order, $items) {
            // Hapus dulu item order lama (opsional, tergantung logika update)
            $order->order_items()->delete();

            // Simpan ulang item yang baru
            foreach ($items as $item) {
                $orderItem = $order->order_items()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'discount_amount' => $item['discount'] ?? 0,
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
        });

        // Clear session setelah simpan
        session()->forget('selected_order_items');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $items = session('selected_order_items', []);
        $data['subtotal_order'] = collect($items)->sum('subtotal');
        $data['total_order'] = max($data['subtotal_order'] - ($data['discount_order'] ?? 0), 0);
        $data['user_id'] = $data['user_id'] ?? $this->record->user_id ?? Auth::id();
        return $data;
    }


}
