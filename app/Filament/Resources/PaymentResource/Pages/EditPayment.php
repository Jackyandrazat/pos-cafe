<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $orderTotal = 0;

        if (!empty($data['order_id'])) {
            $order = \App\Models\Order::find($data['order_id']);
            // Ambil total order dari model Order
            if ($order) {
                $orderTotal = $order->total_order;
            }
        }
        $data['change_return'] = max(($data['amount_paid'] ?? 0) - $orderTotal, 0);

        $orderForPaymentExist = \App\Models\Order::find($data['order_id'] ?? null);

        if ($orderForPaymentExist && $orderForPaymentExist->status === 'completed') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'order_id' => 'Order sudah selesai, tidak bisa dibuat pembayaran.'
            ]);
        }

        return $data;
    }
}
