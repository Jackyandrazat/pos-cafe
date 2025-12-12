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

    protected function afterSave(): void
    {
        $payment = $this->record;
        $order = $payment->order()->with('order_items.product.ingredients.ingredient')->first();

        $kasir = auth()->user(); // kasir yang login
        $payment->change_return = max(($payment->amount_paid ?? 0) - ($order->total_order ?? 0), 0);
        $payment->save();
        $totalBayar = $payment->amount_paid;
        $admin = \App\Models\User::first(); // sementara ambil user pertama

        if ($admin) {
            \Filament\Notifications\Notification::make()
                ->title('Pembayaran Berhasil Diedit')
                ->body("Pembayaran sebesar Rp " . number_format($totalBayar, 0, ',', '.') . " berhasil diedit oleh kasir {$kasir->name}.")
                ->success()
                ->sendToDatabase($admin);
        }

        $orderForPaymentExist = \App\Models\Order::find($data['order_id'] ?? null);

        if ($orderForPaymentExist && $orderForPaymentExist->status === 'completed') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'order_id' => 'Order sudah selesai, tidak bisa dibuat pembayaran.'
            ]);
        }
    }

}
